<?php

namespace Sqala\Payment\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Sqala\Payment\Gateway\Config\Config;
use Psr\Log\LoggerInterface;

class PayerDataRequest implements BuilderInterface
{
    public const PAYER_OBJ = 'payer';
    public const TAX_ID = 'taxId';
    public const PAYER_NAME = 'name';

    protected Config $config;
    protected $logger;

    public function __construct(
        Config $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        if (
            !isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $docIdentification = $this->getDocumentIdentification($paymentDO);

        if (!$docIdentification) {
            throw new InvalidArgumentException('Document identification should be provided');
        }

        $docIdentification = preg_replace('/[^0-9A-Za-z]/', '', $docIdentification);
        $billingAddress = $paymentDO->getOrder()->getBillingAddress();
        $payerFirstName = $payment->getAdditionalInformation('payer_first_name') ?: $billingAddress?->getFirstname();
        $payerLastName = $payment->getAdditionalInformation('payer_last_name') ?: $billingAddress?->getLastname();

        $result = [
            self::PAYER_OBJ => [
                self::TAX_ID => $docIdentification,
                self::PAYER_NAME => "{$payerFirstName} {$payerLastName}"
            ],
        ];
        $this->logger->debug('PayerDataRequest - result', $result);

        return $result;
    }

    private function getDocumentIdentification($paymentDO): ?string
    {
        $payment = $paymentDO->getPayment();
        $this->logger->debug('Additional Info:', $payment->getAdditionalInformation());
        $order = $paymentDO->getOrder();
        $docIdentification = $payment->getAdditionalInformation('payer_document_identification');

        return $docIdentification;
    }
}
