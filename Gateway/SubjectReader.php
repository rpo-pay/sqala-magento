<?php
namespace Sqala\Payment\Gateway;

use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper;

class SubjectReader
{
    protected Session $checkoutSession;

    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    public function readStoreId(array $subject): ?int
    {
        $storeId = $subject['store_id'] ?? null;

        if (empty($storeId)) {
            try {
                $storeId = (int) $this->readPayment($subject)
                    ->getOrder()
                    ->getStoreId();
            } catch (\InvalidArgumentException $e) {}
        }

        return $storeId ? (int) $storeId : null;
    }

    public function readAmount(array $subject): string
    {
        return (string) Helper\SubjectReader::readAmount($subject);
    }

    public function readResponse(array $subject): array
    {
        return Helper\SubjectReader::readResponse($subject);
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }
}
