<?php

namespace Yosmy\Payment;

interface Quota
{
    /**
     * @return string
     */
    public function getUser(): string;

    /**
     * @return string
     */
    public function getPeriod(): string;

    /**
     * @return int
     */
    public function getTimes(): int;

    /**
     * @return int
     */
    public function getAmount(): int;
}
