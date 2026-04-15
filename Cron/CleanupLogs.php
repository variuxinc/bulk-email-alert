<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Cron;

use Variux\EmailNotification\Helper\Config;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\CollectionFactory;
use Variux\EmailNotification\Logger\Logger;

class CleanupLogs
{
    private const DEFAULT_RETENTION_DAYS = 90;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        Logger $logger
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * Delete email logs older than configured retention period
     *
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }

        $retentionDays = $this->config->getLogRetentionDays();
        if ($retentionDays < 0) {
            $retentionDays = self::DEFAULT_RETENTION_DAYS;
        }

        try {
            $collection = $this->collectionFactory->create();
            $deleted = $collection->deleteOlderThan($retentionDays);
            if ($deleted > 0) {
                $this->logger->info(
                    sprintf('Email notification cleanup: deleted %d log(s) older than %d days.', $deleted, $retentionDays)
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Email notification cleanup failed: ' . $e->getMessage());
        }

        return $this;
    }
}
