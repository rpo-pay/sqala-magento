<?php

namespace Sqala\Payment\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Psr\Log\LoggerInterface;
use Sqala\Payment\Gateway\Config\Config;

class Notify extends Action implements CsrfAwareActionInterface
{
    protected JsonFactory $resultJsonFactory;
    protected LoggerInterface $logger;
    protected OrderCollectionFactory $orderCollectionFactory;
    protected CommandPoolInterface $commandPool;
    protected OrderRepositoryInterface $orderRepository;
    protected Config $config;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        OrderCollectionFactory $orderCollectionFactory,
        CommandPoolInterface $commandPool,
        OrderRepositoryInterface $orderRepository,
        Config $config
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->commandPool = $commandPool;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
    }

    // CSRF bypass
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $body = json_decode($this->getRequest()->getContent(), true);

        if ($body['event'] !== 'payment.paid') {
            return $result->setData(['error' => 'Invalid event']);
        }

        $shouldCheckHash = false;
        $secretSqala = $this->config->getAddtionalValue('webhook_secret_id') ?? '';
        $hash = hash_hmac('sha256', json_encode($body['data']), $secretSqala);
        $hashMatch = hash_equals($hash, $body['signature']);

        if ($shouldCheckHash && !$hashMatch) {
            return $result->setData(['error' => 'Invalid signature']);
        }

        $orderIncrementId = $body['data']['code'];
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderCollectionFactory->create()
                ->addFieldToFilter('increment_id', $orderIncrementId)
                ->getFirstItem();

            if (!$order->getId()) {
                return $result->setData(['error' => 'Order not found']);
            }

            $payment = $order->getPayment();
            $fetchCommand = $this->commandPool->get('fetch');
            $fetchCommand->execute(['payment' => $payment]);
            $this->orderRepository->save($order);


            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('[Sqala Pix Webhook] ' . $e->getMessage());
            return $result->setData(['error' => 'Exception: ' . $e->getMessage()]);
        }
    }
}
