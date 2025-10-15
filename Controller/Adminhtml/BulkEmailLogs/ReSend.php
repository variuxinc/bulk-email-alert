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
use Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs;
use Variux\EmailNotification\Helper\Mail as MailHelper;
use Variux\EmailNotification\Helper\Data as DataHelper;
use Variux\EmailNotification\Model\BulkEmailLogsRepository;

class ReSend extends BulkEmailLogs implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $mailHelper;

    protected $dataHelper;

    protected $bulkEmailLogsRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MailHelper $mailHelper,
        DataHelper $dataHelper,
        BulkEmailLogsRepository $bulkEmailLogsRepository
    ){
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->mailHelper = $mailHelper;
        $this->dataHelper = $dataHelper;
        $this->bulkEmailLogsRepository = $bulkEmailLogsRepository;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('bulkemaillogs_id');
        if ($id) {
            $log = $this->bulkEmailLogsRepository->get($id);
            $data = $log->getData();
            $data['content'] = htmlspecialchars_decode($data['content']);
            $sender = $this->dataHelper->extractEmailInfo($data['sender']);

            foreach ($sender as $name => $email) {
                $sender = compact('name', 'email');
                break;
            }

            $recipient = $data['recipient'];
            $name = $data['subject'];

            if(empty($recipient)) {
                $this->messageManager->addErrorMessage(__('We can\'t Re-send an email.'));
                return $resultRedirect->setPath('*/*/');
            }
            $to = ['email' => $recipient, 'name' => $name];
            $this->mailHelper->reSendEmail(
                'bulkemails_resend_email_template',
                $sender,
                $to,
                $data
            );
            $this->messageManager->addSuccessMessage(__('You Re-send the email successfull.'));
            return $resultRedirect->setPath('*/*/');
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t Re-send an email.'));
        return $resultRedirect->setPath('*/*/');
    }
}

