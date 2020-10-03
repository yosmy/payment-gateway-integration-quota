<?php

namespace Yosmy\Payment;

use Yosmy\Mongo;

class BaseQuota extends Mongo\Document implements Quota
{
    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->offsetGet('_id');
    }

    /**
     * @return string
     */
    public function getPeriod(): string
    {
        return $this->offsetGet('period');
    }

    /**
     * @return int
     */
    public function getTimes(): int
    {
        return $this->offsetGet('times');
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->offsetGet('amount');
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): object
    {
        $data = parent::jsonSerialize();

        $data->user = $data->_id;

        unset($data->_id);

        return $data;
    }
}
