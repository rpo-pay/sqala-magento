<?php

namespace Sqala\Payment\Gateway\Response;

use InvalidArgumentException;
use Magento\Payment\Gateway\Response\HandlerInterface;

class FetchPaymentHandler implements HandlerInterface
{
    public const PAID_AT = 'paidAt';
    public const FAILED_AT = 'failedAt';
    public const RECEIPT_URL = 'receiptUrl';
    public const TRANSACTION_ID = 'transactionId';
    public const PAID_AMOUNT = 'paidAmount';
    public const STATUS = 'status';
    public const STATUS_PAID = 'PAID';
    public const STATUS_CREATED = 'CREATED';
    public const STATUS_PROCESSED = 'PROCESSED';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_REFUNDED = 'REFUNDED';
    public const SQALA_STATUS = 'sqala_status';
    public const SQALA_FAILED_AT = 'sqala_failedAt';
    public const SQALA_PAID_AT = 'sqala_paidAt';

    public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($handlingSubject['payment'])) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $handlingSubject['payment'];
        $order = $payment->getOrder();
        $baseAmount = (float) $order->getBaseGrandTotal();
        $amount = (float) $order->getGrandTotal();

        \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('====== FetchPaymentHandler - $response: ', $response);

        if (in_array($response[self::STATUS], [self::STATUS_ERROR,self::STATUS_FAILED])) {
            $payment->setPreparedMessage(__('Order Canceled.'));
            $payment->registerVoidNotification($amount);
            $payment->setIsTransactionApproved(false);
            $payment->setIsTransactionDenied(true);
            $payment->setIsTransactionPending(false);
            $payment->setIsInProcess(true);
            $payment->setIsTransactionClosed(true);
            $payment->setShouldCloseParentTransaction(true);
            $payment->setAmountCanceled($amount);
            $payment->setBaseAmountCanceled($baseAmount);

            $payment->setAdditionalInformation(
                self::SQALA_FAILED_AT,
                $response[self::FAILED_AT]
            );
        }

        if ($response[self::STATUS] === self::STATUS_PAID) {
            $paidAmount = !empty($response[self::PAID_AMOUNT]) ? (float) ($response[self::PAID_AMOUNT] / 100) : (float) $baseAmount;

            if ($paidAmount !== $baseAmount) {
                $this->createInvoice($order, $payment, $paidAmount);
            }

            $payment->registerCaptureNotification($paidAmount, true);
            $payment->setIsTransactionApproved(true);
            $payment->setIsTransactionDenied(false);
            $payment->setIsInProcess(true);
            $payment->setIsTransactionClosed(true);
            $payment->setShouldCloseParentTransaction(true);
            $payment->setAmountAuthorized($paidAmount);

            $payment->setAdditionalInformation(
                self::SQALA_PAID_AT,
                $response[self::PAID_AT]
            );
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('====== FetchPaymentHandler - paid ok: ', []);
        }

        if ($response[self::STATUS] === self::STATUS_REFUNDED) {}

        $payment->setAdditionalInformation(
            self::SQALA_STATUS,
            $response[self::STATUS]
        );
    }

    private function createInvoice($order, $payment, $paidAmount)
    {
        $invoice = $order->prepareInvoice()->register();
        $invoice->setOrder($order);

        $invoice->setBaseGrandTotal($paidAmount);
        $invoice->setGrandTotal($paidAmount);
        $invoice->setSubtotal($paidAmount);
        $invoice->setBaseSubtotal($paidAmount);

        $invoice->addComment(__('Captured by collector from Sqala API'));

        $order->addRelatedObject($invoice);
        $payment->setCreatedInvoice($invoice);
        $payment->setShouldCloseParentTransaction(true);
    }
}
