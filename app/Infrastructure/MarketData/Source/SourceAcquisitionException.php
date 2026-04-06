<?php

namespace App\Infrastructure\MarketData\Source;

class SourceAcquisitionException extends \RuntimeException
{
    private $reasonCode;
    private $context;

    public function __construct($message, $reasonCode, $code = 0, \Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->reasonCode = $reasonCode;
        $this->context = $context;
    }

    public function reasonCode()
    {
        return $this->reasonCode;
    }

    public function context()
    {
        return $this->context;
    }

    public function withContext(array $context)
    {
        return new self(
            $this->getMessage(),
            $this->reasonCode,
            $this->getCode(),
            $this->getPrevious(),
            $context
        );
    }
}