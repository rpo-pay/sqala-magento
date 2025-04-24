<?php

namespace Sqala\Payment\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Psr\Log\LoggerInterface;

class SqalaIdDataRequest implements BuilderInterface
{
    public const SQALA_PAYMENT_ID = 'sqala_id';
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        $this->logger->debug('SqalaIdDataRequest', $buildSubject);
        if (!isset($buildSubject['payment'])) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $payment = ($buildSubject['payment'] instanceof PaymentDataObjectInterface)
            ? SubjectReader::readPayment($buildSubject)->getPayment()
            : $buildSubject['payment'];
        $sqalaPaymentId = $payment->getAdditionalInformation(self::SQALA_PAYMENT_ID);

        $result = [
            self::SQALA_PAYMENT_ID => $sqalaPaymentId,
        ];

        return $result;
    }
}
