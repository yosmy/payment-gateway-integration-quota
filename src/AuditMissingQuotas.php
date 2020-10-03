<?php

namespace Yosmy\Payment;

use Yosmy;
use Traversable;

/**
 * @di\service()
 */
class AuditMissingQuotas
{
    /**
     * @var ManageQuotaCollection
     */
    private $manageQuotaCollection;

    /**
     * @param ManageQuotaCollection $manageQuotaCollection
     */
    public function __construct(
        ManageQuotaCollection $manageQuotaCollection
    ) {
        $this->manageQuotaCollection = $manageQuotaCollection;
    }

    /**
     * @param Yosmy\Mongo\ManageCollection $manageCollection
     *
     * @return Traversable
     */
    public function audit(
        Yosmy\Mongo\ManageCollection $manageCollection
    ): Traversable
    {
        return $manageCollection->aggregate(
            [
                [
                    '$lookup' => [
                        'localField' => '_id',
                        'from' => $this->manageQuotaCollection->getName(),
                        'as' => 'quotas',
                        'foreignField' => '_id',
                    ]
                ],
                [
                    '$match' => [
                        'quotas._id' => [
                            '$exists' => false
                        ]
                    ],
                ]
            ]
        );
    }
}