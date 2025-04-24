<?php

namespace Sqala\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class FetchPaymentClient implements ClientInterface
{
    public const SQALA_PAYMENT_ID = 'sqala_id';

    protected SqalaService $sqalaService;

    public function __construct(SqalaService $sqalaService)
    {
        $this->sqalaService = $sqalaService;
    }

    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        $paymentId = $request[self::SQALA_PAYMENT_ID];

        try {
            $response = $this->sqalaService->getPix($paymentId);
        } catch (\Throwable $exception) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($exception->getMessage());
        }

        return $response;
    }
}
