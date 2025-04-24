<?php

namespace Sqala\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignCheckoutCustomObserver extends AbstractDataAssignObserver
{
    public const PAYMENT_METHOD_ID = 'payment_method_id';
    public const PAYER_DOCUMENT_TYPE = 'payer_document_type';
    public const PAYER_DOCUMENT_IDENTIFICATION = 'payer_document_identification';

    /**
     * @var array
     */
    protected $addInformationList = [
        self::PAYMENT_METHOD_ID,
        self::PAYER_DOCUMENT_TYPE,
        self::PAYER_DOCUMENT_IDENTIFICATION,
    ];

    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->addInformationList as $addInformationKey) {
            if (isset($additionalData[$addInformationKey])) {
                if ($additionalData[$addInformationKey]) {
                    $paymentInfo->setAdditionalInformation(
                        $addInformationKey,
                        $additionalData[$addInformationKey]
                    );
                }
            }
        }
    }
}
