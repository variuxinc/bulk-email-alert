<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Setup\Exception;
use Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs;
use Variux\EmailNotification\Helper\Mail as MailHelper;
use Variux\EmailNotification\Helper\Data as DataHelper;
use Variux\EmailNotification\Helper\Config as ConfigHelper;
use Variux\EmailNotification\Model\BulkEmailLogsRepository;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\Collection;
use Variux\EmailNotification\Model\Source\Status;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs as LogResource;

class Resume extends BulkEmailLogs implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $mailHelper;

    protected $dataHelper;

    protected $configHelper;

    protected $bulkEmailLogsRepository;

    protected $collection;

    protected $resource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MailHelper $mailHelper,
        DataHelper $dataHelper,
        BulkEmailLogsRepository $bulkEmailLogsRepository,
        Collection $collection,
        ConfigHelper $configHelper,
        LogResource $resource
    ){
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->mailHelper = $mailHelper;
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->bulkEmailLogsRepository = $bulkEmailLogsRepository;
        $this->collection = $collection;
        $this->resource = $resource;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->configHelper->isEnabled()) {
            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t Re-send an email.'));
            return $resultRedirect->setPath('*/*/');
        }

        $maxSents = $this->configHelper->getMaxSentEmails();
        $collection = $this->collection->addFieldToFilter('status', ['eq' => Status::STATUS_PENDING])
            ->setPageSize($maxSents);

        if ($collection->getSize()) {
            $success = 0;
            $failed = 0;
            foreach ($collection as $item) {
                $id = $item->getBulkemaillogsId();
                if ($id) {
                    try {
                        $log = $this->bulkEmailLogsRepository->get($id);
                        $data = $log->getData();
                        $data['content'] = htmlspecialchars_decode($data['content']);
                        $sender = $this->dataHelper->extractEmailInfo($data['sender']);

                        foreach ($sender as $name => $email) {
                            $sender = compact('name', 'email');
                            break;
                        }

                        $recipient = $data['recipient'];

                        if(empty($recipient)) {
                            if ($success > 0) {
                                $this->messageManager->addSuccessMessage(__('You Re-send %1 emails successfull.', $success));
                            }
                            if ($failed > 0) {
                                $this->messageManager->addErrorMessage(__('You Re-send %1 emails failed.', $failed));
                            } else {
                                $this->messageManager->addErrorMessage(__('You Re-send email ID %1 failed.', $id));
                            }
                            return $resultRedirect->setPath('*/*/');
                        }

                        $name = $data['subject'];
                        $to = ['email' => $recipient, 'name' => $name];

                        $this->mailHelper->reSendEmail(
                            'bulkemails_resend_email_template',
                            $sender,
                            $to,
                            $data
                        );
                        $where = $this->resource->getConnection()->quoteInto("status = ? AND bulkemaillogs_id = ?", Status::STATUS_PENDING, $id);
                        $this->resource->getConnection()->update($this->resource->getMainTable(), ['status' => Status::STATUS_SUCCESS], $where);
                        $success ++;
                    } catch (Exception $exception) {
                        $failed ++;
                    }
                }
            }
            if ($success > 0) {
                $this->messageManager->addSuccessMessage(__('You Re-send %1 emails successfull.', $success));
            }

            if ($failed > 0) {
                $this->messageManager->addErrorMessage(__('You Re-send %1 emails failed.', $failed));
            }
            return $resultRedirect->setPath('*/*/');
        }

        // display error message
        $this->messageManager->addWarningMessage(__('There is nothing to Re-send email.'));
        return $resultRedirect->setPath('*/*/');
    }
}

