<?php

namespace Sqala\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Gateway\Config\Config as PaymentConfig;
use Magento\Store\Model\ScopeInterface;

class ConfigPix extends PaymentConfig
{
    public const METHOD = 'sqala_pix';
    public const ACTIVE = 'enable_pix';

    protected ScopeConfigInterface $scopeConfig;
    protected DateTime $date;
    protected Config $config;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        DateTime $date,
    ) {
        parent::__construct($scopeConfig, self::METHOD);
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->date = $date;
    }

    public function isActive($storeId = null): bool
    {
        $pathPattern = 'payment/%s/%s';

        return (bool) $this->scopeConfig->getValue(
            sprintf($pathPattern, self::METHOD, self::ACTIVE),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getTitle(): string
    {
        return __('Pix');
    }

    public function getInstructionCheckout($storeId = null): string
    {
        $pathPattern = 'payment/%s/%s';

        return $this->scopeConfig->getValue(
            sprintf($pathPattern, self::METHOD, 'instruction_checkout'),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getExpirationTextFormatted($storeId = null): Phrase
    {
        $pathPattern = 'payment/%s/%s';
        $due = $this->scopeConfig->getValue(
            sprintf($pathPattern, self::METHOD, 'expiration'),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($due === '15' || $due === '30') {
            return __('%1 minutes', $due);
        }

        if ($due === '60') {
            return __('1 hour');
        }

        if ($due === '720') {
            return __('12 hours');
        }

        if ($due === '1440') {
            return __('1 day');
        }

        return __('%1 minutes', $due);
    }
}
