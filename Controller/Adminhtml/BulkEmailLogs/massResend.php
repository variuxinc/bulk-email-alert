<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Variux\EmailNotification\Api\BulkEmailLogsRepositoryInterface;
use Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs as BulkEmailLogsController;
use Variux\EmailNotification\Helper\Mail as MailHelper;
use Variux\EmailNotification\Logger\Logger;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\CollectionFactory;
use Variux\EmailNotification\Helper\Data as DataHelper;

class massResend extends BulkEmailLogsController implements HttpPostActionInterface
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var BulkEmailLogsRepositoryInterface
     */
    private $bulkEmailLogsRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var MailHelper
     */
    protected $mailHelper;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DataHelper $dataHelper
     * @param MailHelper $mailHelper
     * @param BulkEmailLogsRepositoryInterface|null $bulkEmailLogsRepository
     * @param Logger|null $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DataHelper $dataHelper,
        MailHelper $mailHelper,
        BulkEmailLogsRepositoryInterface $bulkEmailLogsRepository = null,
        Logger $logger = null
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->mailHelper = $mailHelper;
        $this->bulkEmailLogsRepository = $bulkEmailLogsRepository ?:
            ObjectManager::getInstance()->create(BulkEmailLogsRepositoryInterface::class);
        $this->logger = $logger ?:
            ObjectManager::getInstance()->create(Logger::class);
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Mass Delete Action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $bulkEmailReSend = 0;
        $bulkEmailReSendError = 0;

        /** @var \Variux\EmailNotification\Model\BulkEmailLogs $bulkEmail */
        foreach ($collection->getItems() as $bulkEmail) {
            try {
                $itemBulkEmail = $this->bulkEmailLogsRepository->get($bulkEmail->getBulkemaillogsId());
                $emailData = $this->dataHelper->getEmailData($itemBulkEmail);
                if(!empty($emailData)) {
                    $this->mailHelper->reSendEmail(
                        'bulkemails_resend_email_template',
                        $emailData['sender'],
                        $emailData['to'],
                        $emailData['bcc']
                    );
                    $bulkEmailReSend++;
                }
            } catch (LocalizedException $exception) {
                $this->logger->error($exception->getLogMessage());
                $bulkEmailReSendError++;
            }
        }

        if ($bulkEmailReSend) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been Re-Send emails.', $bulkEmailReSend)
            );
        }

        if ($bulkEmailReSendError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been Re-Send emails. Please see server logs for more details.',
                    $bulkEmailReSendError
                )
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
