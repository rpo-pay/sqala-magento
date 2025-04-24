<?php

namespace Sqala\Payment\Gateway\Http\Client;

use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Sqala\Payment\Gateway\Config\Config;

class RefundClient implements ClientInterface
{
    public const SQALA_PAYMENT_ID = 'sqala_id';

    protected Logger $logger;
    protected Json $json;
    protected SqalaService $sqalaService;

    public function __construct(
        Logger $logger,
        Json $json,
        SqalaService $sqalaService
    ) {
        $this->logger = $logger;
        $this->json = $json;
        $this->sqalaService = $sqalaService;
    }

    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        $paymentId = $request[self::SQALA_PAYMENT_ID];

        try {
            $response = $this->sqalaService->refundPix($paymentId, __('Refund order'));
        } catch (RequestException $e) {
            $this->logger->debug(
                [
                    'url'      => SqalaService::PIX_URL,
                    'request'  => $this->json->serialize($e->getRequest()),
                    'response' => $this->json->serialize($e->getResponse()),
                    'error'    => $e->getMessage(),
                ]
            );
            throw new LocalizedException(__($e->getMessage()));
        } catch (\Throwable $e) {
            $this->logger->debug(
                [
                    'url' => SqalaService::PIX_URL,
                    'request' => $this->json->serialize($request),
                    'error' => $e->getMessage(),
                ]
            );
            throw new LocalizedException(__($e->getMessage()));
        }


        return $response;
    }
}
