<?php

namespace Yosmy\Payment;

/**
 * @di\service()
 */
class GatherQuota
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
     *
     * @return BaseQuota
     */
    public function gather(
        string $user
    ): BaseQuota {
        /** @var BaseQuota $quota */
        $quota = $this->manageCollection->findOne([
            '_id' => $user
        ]);

        return $quota;
    }
}
