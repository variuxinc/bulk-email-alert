<?php

namespace Variux\EmailNotification\Mail;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\ScopeInterface;
use Variux\EmailNotification\Helper\Config;

class EmailTransport
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config       = $config;
    }

    /**
     * Omit email sending depending on the system configuration setting
     *
     * @param TransportInterface $subject
     * @param \Closure $proceed
     * @return void
     * @throws MailException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        \Closure $proceed
    ) {
        if ($this->config->isEnabled() && !$this->config->isDisabled()) {
            $proceed();
        } else {
            if (!$this->scopeConfig->isSetFlag('system/smtp/disable', ScopeInterface::SCOPE_STORE)) {
                $proceed();
            }
        }
    }
}
