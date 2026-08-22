<?php

use PHPUnit\Framework\TestCase;

class ProductionValidationRuntimeProofStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function readProjectFile(string $path): string
    {
        $fullPath = $this->projectPath($path);
        $this->assertFileExists($fullPath, $path.' must exist.');

        return file_get_contents($fullPath);
    }





    /**
     * Command names resolved from the kernel's registered command classes.
     *
     * @return array<int, string>
     */
    private function registeredMarketDataCommands(): array
    {
        $kernel = new ReflectionClass(\App\Console\Kernel::class);
        $property = $kernel->getProperty('commands');
        $property->setAccessible(true);

        $names = [];

        foreach ($property->getValue($kernel->newInstanceWithoutConstructor()) as $commandClass) {
            if (! class_exists($commandClass)) {
                continue;
            }

            $signatureProperty = (new ReflectionClass($commandClass))->getProperty('signature');
            $signatureProperty->setAccessible(true);

            $signature = (string) $signatureProperty->getValue(
                (new ReflectionClass($commandClass))->newInstanceWithoutConstructor()
            );

            $name = trim(strtok($signature, " \n\t{"));

            if (strpos($name, 'market-data:') === 0) {
                $names[] = $name;
            }
        }

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }













    
    
    public function test_runtime_parity_command_output_artifacts_are_utf8_without_null_bytes(): void
    {
        $reportPath = 'storage/app/market-data/production-rollout-validation-runtime-parity/command-output/encoding-normalization-report.txt';
        $report = $this->readProjectFile($reportPath);
        $this->assertStringContainsString('ENCODING: UTF-8', $report);
        $this->assertStringContainsString('NORMALIZED_FILE_COUNT: 20', $report);

        $globalReport = $this->readProjectFile('storage/app/market-data/evidence-encoding-normalization-report.txt');
        $this->assertStringContainsString('ENCODING: UTF-8', $globalReport);
        $this->assertStringContainsString('SCOPE: storage/app/market-data/**/*.txt', $globalReport);

        $directory = $this->projectPath('storage/app/market-data/production-rollout-validation-runtime-parity/command-output');
        $this->assertDirectoryExists($directory);

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'txt') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            $this->assertStringNotContainsString("\0", $contents, $file->getPathname().' must be normalized to UTF-8/plain text without null-byte evidence noise.');
        }

        $globalDirectory = $this->projectPath('storage/app/market-data');
        $globalIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($globalDirectory));
        foreach ($globalIterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'txt') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            $this->assertStringNotContainsString("\0", $contents, $file->getPathname().' must be normalized to UTF-8/plain text without null-byte evidence noise.');
        }
    }
}
