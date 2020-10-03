<?php

namespace Yosmy\Payment;

use Yosmy\Mongo;

class BaseManageQuotaCollection extends Mongo\BaseManageCollection implements ManageQuotaCollection
{
    /**
     * @param string $uri
     * @param string $db
     * @param string $collection
     * @param string $root
     */
    public function __construct(
        string $uri,
        string $db,
        string $collection,
        string $root
    ) {
        parent::__construct(
            $uri,
            $db,
            $collection,
            [
                'typeMap' => array(
                    'root' => $root,
                ),
            ]
        );
    }
}
