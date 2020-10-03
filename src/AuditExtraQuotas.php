<?php

namespace Yosmy\Payment;

use Yosmy;
use Traversable;

/**
 * @di\service()
 */
class AuditExtraQuotas
{
    /**
     * @var ManageQuotaCollection
     */
    private $manageQuotaCollection;

    /**
     * @param ManageQuotaCollection    $manageQuotaCollection
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
        return $this->manageQuotaCollection->aggregate(
            [
                [
                    '$lookup' => [
                        'localField' => '_id',
                        'from' => $manageCollection->getName(),
                        'as' => 'parent',
                        'foreignField' => '_id',
                    ]
                ],
                [
                    '$match' => [
                        'parent._id' => [
                            '$exists' => false
                        ]
                    ],
                ]
            ]
        );
    }
}