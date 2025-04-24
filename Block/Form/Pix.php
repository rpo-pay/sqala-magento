<?php

namespace Sqala\Payment\Block\Form;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form;
use Sqala\Payment\Gateway\Config\Config;
use Sqala\Payment\Gateway\Config\ConfigPix;

class Pix extends Form
{
    protected $_template = 'Sqala_Payment::form/pix.phtml';

    protected Config $config;
    protected ConfigPix $configPix;
    protected Quote $sessionQuote;

    /**
     * @param Context   $context
     * @param Config    $config
     * @param ConfigPix $configPix
     * @param Quote     $sessionQuote
     */
    public function __construct(
        Context $context,
        Config $config,
        ConfigPix $configPix,
        Quote $sessionQuote
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->configPix = $configPix;
        $this->sessionQuote = $sessionQuote;
    }
}
