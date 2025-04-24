<?php

namespace Sqala\Payment\Gateway\Response;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;

class PixResponseHandler implements HandlerInterface
{
    public const PAYMENT_ID = 'id';
    public const SQALA_PAYMENT_ID = 'sqala_id';
    public const CREATED_AT = 'createdAt';
    public const SQALA_CREATED_AT = 'sqala_createdAt';
    public const PROCESSED_AT = 'processedAt';
    public const SQALA_PROCESSED_AT = 'sqala_processedAt';
    public const PAID_AT = 'paidAt';
    public const SQALA_PAID_AT = 'sqala_paidAt';
    public const EXPIRES_AT = 'expiresAt';
    public const SQALA_EXPIRES_AT = 'sqala_expiresAt';
    public const FAILED_AT = 'failedAt';
    public const SQALA_FAILED_AT = 'sqala_failedAt';
    public const PAYLOAD = 'payload';
    public const QR_CODE = 'qr_code';
    public const STATUS = 'status';
    public const SQALA_STATUS = 'sqala_status';

    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        $this->setAddtionalInformation($payment, $response);

        $transactionId = $response[self::PAYMENT_ID];
        $payment->setTransactionId($transactionId);
        $payment->setIsTransactionPending(1);
        $payment->setIsTransactionClosed(false);
        $payment->setAuthorizationTransaction($transactionId);
        $payment->addTransaction(Transaction::TYPE_AUTH);

        $order = $payment->getOrder();

        $order->setExtOrderId($response[self::PAYMENT_ID]);
        $order->setState(Order::STATE_NEW);
        $order->setStatus('pending');
        $comment = __('Awaiting payment through Pix.');
        $order->addStatusHistoryComment($comment, $payment->getOrder()->getStatus());
    }

    public function setAddtionalInformation($payment, $response)
    {
        $payment->setAdditionalInformation(
            self::SQALA_PAYMENT_ID,
            $response[self::PAYMENT_ID]
        );

        $payment->setAdditionalInformation(
            self::QR_CODE,
            $response[self::PAYLOAD]
        );

        $payment->setAdditionalInformation(
            self::SQALA_STATUS,
            $response[self::STATUS]
        );

        $payment->setAdditionalInformation(
            self::SQALA_CREATED_AT,
            $response[self::CREATED_AT]
        );

        $payment->setAdditionalInformation(
            self::SQALA_EXPIRES_AT,
            $response[self::EXPIRES_AT]
        );

        $payment->setAdditionalInformation(
            self::SQALA_FAILED_AT,
            $response[self::FAILED_AT]
        );

        $payment->setAdditionalInformation(
            self::SQALA_PROCESSED_AT,
            $response[self::PROCESSED_AT] ?? null
        );

        $payment->setAdditionalInformation(
            self::SQALA_PAID_AT,
            $response[self::PAID_AT]
        );
    }
}
