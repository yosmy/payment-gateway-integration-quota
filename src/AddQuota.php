<?php

namespace Yosmy\Payment;

/**
 * @di\service({
 *     private: true
 * })
 */
class AddQuota
{
    /**
     * @var ManageQuotaCollection
     */
    private $manageCollection;

    /**
     * @param ManageQuotaCollection $manageCollection
     */
    public function __construct(
        ManageQuotaCollection $manageCollection
    ) {
        $this->manageCollection = $manageCollection;
    }

    /**
     * @param string $user
     * @param string $period
     * @param int    $times
     * @param int    $amount
     */
    public function add(
        string $user,
        string $period,
        int $times,
        int $amount
    ) {
        $this->manageCollection->insertOne([
            '_id' => $user,
            'period' => $period,
            'times' => $times,
            'amount' => $amount,
        ]);
    }
}
