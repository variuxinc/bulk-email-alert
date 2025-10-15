<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Variux\EmailNotification\Helper\Config;
use Variux\EmailNotification\Helper\Data;
use Variux\EmailNotification\Helper\Mail;
use Variux\EmailNotification\Model\BulkEmailLogsRepository;
use Variux\EmailNotification\Model\BulkEmailLogs;
use Variux\EmailNotification\Model\BulkEmailLogsFactory;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\Collection;
use Variux\EmailNotification\Model\Source\Status;
use Magento\Framework\App\Cache\TypeListInterface;

class BulkNotification extends Command
{
    /** @var \Magento\Framework\App\State **/
    private $state;

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
     * @param \Magento\Framework\App\State $state
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
        \Magento\Framework\App\State $state,
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
        parent::__construct();
        $this->state = $state;
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
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            if (!$this->config->isEnabled()) {
                $output->writeln('<info>There Bulk emails is not enabled. </info>');
                return false;
            }

            $now = new \DateTime();
            $maxSents = $this->config->getMaxSentEmails();
            $lockThreshold = $this->config->getdDurationThreshold();

            if (!($lockThreshold && $maxSents)) {
                $output->writeln('<info>Missing Bulk emails configurable values. </info>');
                return false;
            }

            $sender = $this->config->getConfigValue('bulkemails/email_template/sender');
            $receiver = $this->config->getConfigValue('bulkemails/email_template/receiver');

            if(empty($sender) || empty($receiver)) {
                $output->writeln('<info>Missing Bulk emails sender or receiver configurable values. </info>');
                return false;
            }

            $timeThreshold = $now->getTimestamp() - $lockThreshold;
            $dateThreshold = date('Y-m-d H:i:s', $timeThreshold);
            $collection = $this->collection->addFieldToSelect('*')
                ->setPageSize($maxSents)
                ->addFieldToFilter('created_at', ['gteq' => $dateThreshold])
                ->setOrder('bulkemaillogs_id', 'DESC')
                ->load();
            $send = 0;
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
                    $output->writeln('<info>Success send Bulk emails notification. </info>');
                    $send ++;
                }
                if($send == 0) {
                    $output->writeln('<comment>The block email notification has been sended. Skip.</comment>');
                }
                // Set disable email sending communication
                if (!$this->config->isDisabled()) {
                    $this->data->setValue(Data::BULK_EMAILS_SMTP_DISABLE, 1);
                    $this->typeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                }
                $output->writeln('<info>Set disable email sending communication. </info>');
            } else {
                if($send == 0) {
                    $output->writeln('<comment>There is nothing to do. Skip.</comment>');
                }
            }
            $output->writeln('<info>Finish to run. </info>');
            return true;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("variux_emailnotification:bulknotification");
        $this->setDescription("Bulk Emails Notification Command Line");
        parent::configure();
    }
}

