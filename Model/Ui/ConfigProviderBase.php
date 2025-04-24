<?php

namespace Sqala\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Sqala\Payment\Gateway\Config\Config;

class ConfigProviderBase implements ConfigProviderInterface
{
    public const CODE = 'sqala_payment';

    protected Config $config;
    protected CartInterface $cart;

    public function __construct(
        Config $config,
        CartInterface $cart,
    ) {
        $this->config = $config;
        $this->cart = $cart;
    }

    public function getConfig(): array
    {
        return [
            'payment' => [
                Config::METHOD => [
                    'isActive'    => false
                ],
            ],
        ];
    }
}
