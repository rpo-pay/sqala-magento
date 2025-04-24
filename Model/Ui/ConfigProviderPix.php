<?php

namespace Sqala\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfig;
use Magento\Quote\Api\Data\CartInterface;
use Sqala\Payment\Gateway\Config\ConfigPix;

class ConfigProviderPix implements ConfigProviderInterface
{
    public const CODE = 'sqala_pix';

    protected ConfigPix $config;
    protected CartInterface $cart;
    protected CcConfig $ccConfig;
    protected Escaper $escaper;
    protected Source $assetSource;

    public function __construct(
        ConfigPix $config,
        CartInterface $cart,
        CcConfig $ccConfig,
        Escaper $escaper,
        Source $assetSource
    ) {
        $this->config = $config;
        $this->cart = $cart;
        $this->escaper = $escaper;
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    public function getConfig(): array
    {
        $storeId = $this->cart->getStoreId();
        $isActive = $this->config->isActive($storeId);

        if (!$isActive) {
            return [];
        }

        return [
            'payment' => [
                self::CODE => [
                    'isActive'                        => $isActive,
                    'title'                           => $this->config->getTitle($storeId),
                    'document_identification_capture' => true,
                    'instruction_checkout'            => nl2br($this->getDescriptions($storeId)),
                    'logo'                            => $this->getLogo(),
                    'fingerprint'                     => ''
                ],
            ],
        ];
    }

    public function getLogo(): array
    {
        $logo = [];
        $asset = $this->ccConfig->createAsset('Sqala_Payment::images/pix/logo.svg');
        $placeholder = $this->assetSource->findSource($asset);
        if ($placeholder) {
            list($width, $height) = getimagesizefromstring($asset->getSourceFile());
            $logo = [
                'url'    => $asset->getUrl(),
                'width'  => $width,
                'height' => $height,
                'title'  => __('Pix (Sqala)'),
            ];
        }

        return $logo;
    }

    public function getDescriptions($storeId): string
    {
        $time = $this->config->getExpirationTextFormatted($storeId);
        $text = $this->config->getInstructionCheckout($storeId);

        return __($text, $time);
    }
}
