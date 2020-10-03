<?php

namespace Yosmy\Payment;

use Yosmy\Payment;
use Yosmy\Mongo;
use Yosmy;

/**
 * @di\service({
 *     tags: [
 *         'yosmy.payment.pre_execute_charge',
 *     ]
 * })
 */
class AnalyzePreExecuteChargeToMonitorQuota implements Payment\AnalyzePreExecuteCharge
{
    /**
     * @var GatherQuota
     */
    private $gatherQuota;

    /**
     * @var Payment\ManageChargeCollection
     */
    private $manageChargeCollection;

    /**
     * @var Yosmy\LogEvent
     */
    private $logEvent;

    /**
     * @param GatherQuota                    $gatherQuota
     * @param Payment\ManageChargeCollection $manageChargeCollection
     * @param Yosmy\LogEvent                 $logEvent
     */
    public function __construct(
        GatherQuota $gatherQuota,
        Payment\ManageChargeCollection $manageChargeCollection,
        Yosmy\LogEvent $logEvent
    ) {
        $this->gatherQuota = $gatherQuota;
        $this->manageChargeCollection = $manageChargeCollection;
        $this->logEvent = $logEvent;
    }

    /**
     * {@inheritDoc}
     */
    public function analyze(
        Payment\Card $card,
        int $amount,
        string $description,
        string $statement
    ) {
        $quota = $this->gatherQuota->gather(
            $card->getUser()
        );

        $from = strtotime(sprintf(
            '%s -%s',
            date('Y-m-d H:i:s'),
            $quota->getPeriod()
        ));

        try {
            $this->analyzeTimesForQuota(
                $from,
                $quota
            );
        } catch (Payment\KnownException $e) {
            $this->logEvent->log(
                [
                    'yosmy.payment.quota',
                ],
                [
                    'user' => $card->getUser(),
                    'card' => $card
                ],
                [
                    'amount' => $amount,
                    'exception' => $e->jsonSerialize()
                ]
            );

            throw $e;
        }

        try {
            $this->analyzeAmountForQuota(
                $from,
                $quota,
                $amount
            );
        } catch (Payment\KnownException $e) {
            $this->logEvent->log(
                [
                    'yosmy.payment.quota',
                ],
                [
                    'user' => $card->getUser(),
                    'card' => $card
                ],
                [
                    'amount' => $amount,
                    'exception' => $e->jsonSerialize()
                ]
            );

            throw $e;
        }
    }

    /**
     * @param string    $from
     * @param BaseQuota $quota
     *
     * @throws Payment\KnownException
     */
    private function analyzeTimesForQuota(
        string $from,
        BaseQuota $quota
    ) {
        $count = $this->manageChargeCollection->count([
            'user' => $quota->getUser(),
            'date' => ['$gte' => new Mongo\DateTime($from * 1000)]
        ]);

        if ($count == (int) $quota->getTimes()) {
            throw new Payment\KnownException(sprintf(
                'Has llegado a tu limite de %s cobros cada %s',
                $quota->getTimes(),
                $quota->getPeriod()
            ));
        }
    }

    /**
     * @param string    $from
     * @param BaseQuota $quota ,
     * @param int       $amount
     *
     * @throws Payment\KnownException
     */
    private function analyzeAmountForQuota(
        string $from,
        BaseQuota $quota,
        int $amount
    ) {
        $data = iterator_to_array($this->manageChargeCollection->aggregate(
            [
                ['$match' => [
                    'user' => $quota->getUser(),
                    'date' => ['$gte' => new Mongo\DateTime($from * 1000)]
                ]],
                ['$group' => [
                    '_id' => '',
                    'total' => ['$sum' => '$amount']
                ]]
            ],
            [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array'
                ],
            ]
        ));

        $total = 0;

        if (
            isset($data[0])
            && isset($data[0]['total'])
        ) {
            $total = $data[0]['total'];
        }

        if ($total + $amount > (int) $quota->getAmount()) {
            throw new Payment\KnownException(sprintf(
                'Con este cobro superarías tu límite de $%s cada %s. Itenta una cantidad menor',
                $quota->getAmount() / 100,
                $quota->getPeriod()
            ));
        }
    }
}