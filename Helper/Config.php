<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    /**
     * @var Store
     */
    protected $store;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public const BULK_EMAILS_CONFIG = "bulkemails/general";
    public const BULK_EMAILS_TEMPLATE = "bulkemails/email_template";

    public const BULK_EMAILS_SMTP = "bulkemails/smtp";

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (int)$this->getConfigValue(self::BULK_EMAILS_CONFIG . "/enabled", $this->getStore()->getStoreId());
    }

    /**
     * @return int
     */
    public function getMaxSentEmails($storeId = null)
    {
        return (int)$this->getConfigValue(self::BULK_EMAILS_CONFIG . "/max_sentemails", $this->getStore()->getStoreId());
    }

    /**
     * @return int
     */
    public function getdDurationThreshold($storeId = null)
    {
        return (int)$this->getConfigValue(self::BULK_EMAILS_CONFIG . "/duration_threshold", $this->getStore()->getStoreId());
    }

    /**
     * @return string|bool
     */
    public function getTemplateMethod($storeId = null)
    {
        return $this->getConfigValue(self::BULK_EMAILS_TEMPLATE . "/copyto_method", $this->getStore()->getStoreId());
    }

    /**
     * @return bool
     */
    public function isDisabled($storeId = null)
    {
        return $this->getConfigValue(self::BULK_EMAILS_SMTP . "/disable", $this->getStore()->getStoreId());
    }

    /**
     * @return array|bool
     */
    public function getTemplateMethodEmails($storeId = null)
    {
        $data = $this->getConfigValue(self::BULK_EMAILS_TEMPLATE . "/copyto", $this->getStore()->getStoreId());
        if (!empty($data)) {
            return array_map('trim', explode(',', $data));
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getConfigValue($path, $storeId = null)
    {
       return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $this->getStore()->getStoreId());
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        //current store
        if ($this->store instanceof Store) {
            return $this->store;
        }
        return $this->storeManager->getStore();
    }
}

