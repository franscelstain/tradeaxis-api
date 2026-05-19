<?php

namespace App\Application\MarketData\Exceptions;

use RuntimeException;

class NoReadablePublicationException extends RuntimeException
{
    public const REASON_CODE = 'NO_READABLE_PUBLICATION';

    private $tradeDate;

    public function __construct($tradeDate, string $context)
    {
        $this->tradeDate = (string) $tradeDate;

        parent::__construct(self::REASON_CODE.': '.$context.' requires a readable current publication for trade date '.$this->tradeDate.'.');
    }

    public function reasonCode(): string
    {
        return self::REASON_CODE;
    }

    public function tradeDate(): string
    {
        return $this->tradeDate;
    }
}
