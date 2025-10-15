<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Variux\EmailNotification\Model\BulkEmailLogsRepository;
use Variux\EmailNotification\Model\BulkEmailLogs;
use Variux\EmailNotification\Model\BulkEmailLogsFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    public const BULK_EMAILS_SMTP_DISABLE = 'bulkemails/smtp/disable';
    public const BULK_EMAILS_ENABLE = 'bulkemails/general/enabled';

    protected $bulkEmailLogsRepository;

    protected $bulkEmailLogs;

    protected $bulkEmailLogsFactory;

    /**
     * @var Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        BulkEmailLogsRepository $bulkEmailLogsRepository,
        BulkEmailLogs $bulkEmailLogs,
        BulkEmailLogsFactory $bulkEmailLogsFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        parent::__construct($context);
        $this->bulkEmailLogsRepository = $bulkEmailLogsRepository;
        $this->bulkEmailLogs = $bulkEmailLogs;
        $this->bulkEmailLogsFactory = $bulkEmailLogsFactory;
        $this->configWriter = $configWriter;
    }

    /**
     * @param $emailList
     *
     * @return array
     */
    public function extractEmailInfo($emailList)
    {
        $data = [];

        $emailList = preg_replace('/\s+/', '', $emailList);
        if (strpos($emailList, '<') !== false) {
            $emails = explode('<', $emailList);
            $name   = '';
            if (count($emails) > 1) {
                $name = $emails[0];
            }
            $email       = trim($emails[1], '>');
            $data[$name] = $email;
        } else {
            $emails = explode(',', $emailList);
            foreach ($emails as $email) {
                $data[] = $email;
            }
        }

        return $data;
    }

    /**
     * @param $message
     * @return void
     */
    public function saveEmailLog($message, $status = true)
    {
        $log = $this->bulkEmailLogsFactory->create();
        if ($message->getSubject()) {
            $log->setSubject($message->getSubject());
        }
        if ($message->getFrom()){
            $from = $message->getFrom();
            $from->rewind();
            $log->setSender($from->current()->getName() . ' <' . $from->current()->getEmail() . '>');
        }
        $toArr = [];
        foreach ($message->getTo() as $toAddr) {
            $toArr[] = $toAddr->getEmail();
        }
        $log->setRecipient(implode(',', $toArr));
        $ccArr = [];
        foreach ($message->getCc() as $ccAddr) {
            $ccArr[] = $ccAddr->getEmail();
        }
        $log->setCc(implode(',', $ccArr));

        $bccArr = [];
        foreach ($message->getBcc() as $bccAddr) {
            $bccArr[] = $bccAddr->getEmail();
        }
        $log->setBcc(implode(',', $bccArr));
        $messageBody = quoted_printable_decode($message->getBodyText());
        $content     = htmlspecialchars($messageBody);
        $log->setContent($content);
        $log->setStatus($status);
        $this->bulkEmailLogsRepository->save($log);
    }

    public function setValue($path, $value)
    {
        $this->configWriter->save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

    }
}

