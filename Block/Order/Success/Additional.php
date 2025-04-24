<?php

namespace Sqala\Payment\Block\Order\Success;

use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Sqala\Payment\Gateway\Config\Config as PaymentConfig;
use Psr\Log\LoggerInterface;

class Additional extends Template
{
    protected $logger;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        PaymentConfig $paymentConfig,
        PriceCurrencyInterface $priceCurrency,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->paymentConfig = $paymentConfig;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;

        $methodCode = $this->getMethodCode();

        if ($methodCode === 'sqala_pix') {
            $this->setTemplate('Sqala_Payment::order/success/pix.phtml');
        }
    }

    public function getMethodCode()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment()->getMethodInstance();
        return $payment->getCode();
    }

    public function getQrCode(): ?string
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();

        if (!$payment) {
            return null;
        }

        return $payment->getAdditionalInformation('qr_code');
    }
}
