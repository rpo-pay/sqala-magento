<?php

namespace Sqala\Payment\Cron;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;

class SyncOrders
{
    protected $orderCollectionFactory;
    protected $logger;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        CommandPoolInterface $commandPool
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->commandPool = $commandPool;
    }

    public function execute()
    {
        $orders = $this->orderCollectionFactory->create();
        $orders->addFieldToFilter('main_table.status', ['in' => ['pending', 'pending_payment']]);
        $orders->addFieldToFilter('main_table.created_at', ['gteq' => (new \DateTime('-2 days'))->format('Y-m-d H:i:s')]);
        $orders->getSelect()->join(
            ['payment' => $orders->getTable('sales_order_payment')],
            'main_table.entity_id = payment.parent_id',
            []
        )->where('payment.method = ?', 'sqala_pix');

        foreach ($orders as $order) {
            try {
                $payment = $order->getPayment();
                $this->logger->info("Order found #" . $order->getIncrementId());

                $fetchCommand = $this->commandPool->get('fetch');
                $fetchCommand->execute(['payment' => $payment]);
                $this->orderRepository->save($order);
                $this->logger->info("Fetched payment for order #" . $order->getIncrementId());
            } catch (\Exception $e) {
                $this->logger->error("Error syncing order #" . $order->getIncrementId() . ": " . $e->getMessage());
            }
        }
    }
}
