<?php

namespace Sqala\Payment\Gateway\Response;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Creditmemo;

class PixRefundHandler implements HandlerInterface
{
    public const RESPONSE_STATUS = 'status';
    public const RESPONSE_STATUS_SUCCESS = 200;
    public const RESPONSE_STATUS_DENIED = 400;

    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();

        if ($response[self::RESPONSE_STATUS] === self::RESPONSE_STATUS_SUCCESS) {
            $creditmemo = $payment->getCreditmemo();
            $creditmemo->setState(Creditmemo::STATE_REFUNDED);
        }
        if ($response[self::RESPONSE_STATUS] === self::RESPONSE_STATUS_DENIED) {
            $creditmemo = $payment->getCreditmemo();
            $creditmemo->setState(Creditmemo::STATE_CANCELED);
        }
    }
}
