<?php

namespace Sqala\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Psr\Log\LoggerInterface;

class AmountDataRequest implements BuilderInterface
{
    use Formatter;
    public const AMOUNT = 'amount';

    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        $order = $buildSubject['payment']->getOrder();
        $result = [
            self::AMOUNT => $order->getGrandTotalAmount() * 100
        ];

        return $result;
    }
}
