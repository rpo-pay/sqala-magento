<?php

namespace Sqala\Payment\Gateway\Http\Client;

use GuzzleHttp\Exception\RequestException;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Sqala\Payment\Gateway\Config\Config;

class CreateOrderPaymentCustomClient implements ClientInterface
{
    protected Logger $logger;
    protected Config $config;
    protected Json $json;
    protected Session $checkoutSession;
    protected SqalaService $sqalaService;

    public function __construct(
        Logger $logger,
        Config $config,
        Json $json,
        Session $checkoutSession,
        SqalaService $sqalaService,
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->json = $json;
        $this->checkoutSession = $checkoutSession;
        $this->sqalaService = $sqalaService;
    }

    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        try {
            $response = $this->sqalaService->createPix($request);

            $this->logger->debug(
                [
                    'url'      => SqalaService::PIX_URL,
                    'header'   => $this->json->serialize($this->sqalaService->getHeaders()),
                    'request'  => $this->json->serialize($request),
                    'response' => $this->json->serialize($response),
                ]
            );
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
                    'url'      => SqalaService::PIX_URL,
                    'request'  => $this->json->serialize($request),
                    'error'    => $e->getMessage(),
                ]
            );
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }
}
