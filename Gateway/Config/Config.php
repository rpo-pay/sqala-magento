<?php

namespace Sqala\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Config\Config as PaymentConfig;
use Magento\Payment\Model\Method\Logger;
use Magento\Store\Model\ScopeInterface;

class Config extends PaymentConfig
{
    public const METHOD = 'sqala_payment';

    protected Logger $logger;
    protected ProductMetadataInterface $productMetadata;
    protected ResourceInterface $resourceModule;
    protected ScopeConfigInterface $scopeConfig;
    protected Json $json;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        ResourceInterface $resourceModule,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Json $json,
        $methodCode = self::METHOD
    ) {
        parent::__construct($scopeConfig, $methodCode);
        $this->productMetadata = $productMetadata;
        $this->resourceModule = $resourceModule;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->json = $json;
    }

    public function getAddtionalValue($field, $storeId = null, $scope = ScopeInterface::SCOPE_STORES): ?string
    {
        $pathPattern = 'payment/%s/%s';

        return $this->scopeConfig->getValue(
            sprintf($pathPattern, self::METHOD, $field),
            $scope,
            $storeId
        );
    }
}
