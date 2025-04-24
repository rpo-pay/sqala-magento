<?php

namespace Sqala\Payment\Block\Info;

use Magento\Payment\Block\ConfigurableInfo;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\ObjectManager;

class Pix extends ConfigurableInfo
{
    protected $_template = 'Sqala_Payment::info/pix/instructions.phtml';

    public function getQrCode(): ?string
    {
        $payment = $this->getInfo();

        if (!$payment) {
            return null;
        }

        return $payment->getAdditionalInformation('qr_code');
    }

    public function getExpiresAt(): ?string
    {
        $payment = $this->getInfo();

        if (!$payment) {
            return null;
        }

        return $payment->getAdditionalInformation('sqala_expiresAt');
    }

    public function getSqalaId(): ?string
    {
        $payment = $this->getInfo();

        if (!$payment) {
            return null;
        }

        return $payment->getAdditionalInformation('sqala_id');
    }

    public function date($date)
    {
        $timezone = ObjectManager::getInstance()->get(TimezoneInterface::class);
        $localeDate = $timezone->date($date);

        $format = $localeDate->format('Y-m-d\TH:i:s.000O');


        return $this->formatDate(
            $format,
            \IntlDateFormatter::MEDIUM,
            true,
        );
    }
}
