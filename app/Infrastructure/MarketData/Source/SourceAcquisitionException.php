<?php

namespace App\Infrastructure\MarketData\Source;

class SourceAcquisitionException extends \RuntimeException
{
    private $reasonCode;

    public function __construct($message, $reasonCode, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->reasonCode = $reasonCode;
    }

    public function reasonCode()
    {
        return $this->reasonCode;
    }
}
