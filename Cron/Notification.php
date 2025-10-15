<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Cron;

use Variux\EmailNotification\Helper\Config;
use Variux\EmailNotification\Helper\Data;
use Variux\EmailNotification\Helper\Mail;
use Variux\EmailNotification\Model\BulkEmailLogsRepository;
use Variux\EmailNotification\Model\BulkEmailLogs;
use Variux\EmailNotification\Model\BulkEmailLogsFactory;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\Collection;
use Variux\EmailNotification\Model\Source\Status;
use Magento\Framework\App\Cache\TypeListInterface;

class Notification
{
    protected $config;

    protected $data;

    protected $mailHelper;

    protected $bulkEmailLogsRepository;

    protected $bulkEmailLogs;

    protected $bulkEmailLogsFactory;

    protected $collection;

    protected $logger;

    protected $typeList;

    /**
     * @param Config $config
     * @param Data $data
     * @param Mail $mailHelper
     * @param BulkEmailLogsRepository $bulkEmailLogsRepository
     * @param BulkEmailLogs $bulkEmailLogs
     * @param BulkEmailLogsFactory $bulkEmailLogsFactory
     * @param Collection $collection
     * @param TypeListInterface $typeList
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        Data $data,
        Mail $mailHelper,
        BulkEmailLogsRepository $bulkEmailLogsRepository,
        BulkEmailLogs $bulkEmailLogs,
        BulkEmailLogsFactory $bulkEmailLogsFactory,
        Collection $collection,
        TypeListInterface $typeList,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->data = $data;
        $this->mailHelper = $mailHelper;
        $this->bulkEmailLogsRepository = $bulkEmailLogsRepository;
        $this->bulkEmailLogs = $bulkEmailLogs;
        $this->bulkEmailLogsFactory = $bulkEmailLogsFactory;
        $this->collection = $collection;
        $this->typeList = $typeList;
        $this->logger = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }

        $now = new \DateTime();
        $maxSents = $this->config->getMaxSentEmails();
        $lockThreshold = $this->config->getdDurationThreshold();

        if (!($lockThreshold && $maxSents)) {
            return $this;
        }

        $sender = $this->config->getConfigValue('bulkemails/email_template/sender');
        $receiver = $this->config->getConfigValue('bulkemails/email_template/receiver');

        if(empty($sender) || empty($receiver)) {
            return $this;
        }

        $timeThreshold = $now->getTimestamp() - $lockThreshold;
        $dateThreshold = date('Y-m-d H:i:s', $timeThreshold);
        $collection = $this->collection->addFieldToSelect('*')
            ->setPageSize($maxSents)
            ->addFieldToFilter('created_at', ['gteq' => $dateThreshold])
            ->setOrder('bulkemaillogs_id', 'DESC')
            ->load();

        if ($collection->getSize() > $maxSents) {
            $firstItem = $collection->getFirstItem()->getSubject();
            $subject = "Block Bulk Email Notification";
            if (!empty($firstItem) && $firstItem != $subject || !$this->config->isDisabled()) {
                // Sent Bulk Alert Email
                $copyTo = $this->config->getTemplateMethodEmails();
                if (!empty($copyTo) && $this->config->getTemplateMethod() == 'bcc') {
                    $copyTo = $this->config->getTemplateMethodEmails();
                }
                $sender = ['email' => $sender, 'name' => 'Bulk Alert Email Sender'];
                $to = ['email' => $receiver, 'name' => 'Bulk Alert Email Receiver'];
                $this->mailHelper->sendTemplateEmail(
                    $sender,
                    $to,
                    $copyTo
                );
            }
            // Set disable email sending communication
            if (!$this->config->isDisabled()) {
                $this->data->setValue(Data::BULK_EMAILS_SMTP_DISABLE, 1);
                $this->typeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
            }
        }

        return $this;
    }
}

