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
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\Collection;

class Clear extends BulkEmailLogs implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Collection
     */
    protected $collection;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Collection $collection
    ){
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->collection = $collection;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if($this->collection->getSize()) {
                $this->collection->clearLog();
                $this->messageManager->addSuccessMessage(__('You deleted the logs.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } else {
                // display error message
                $this->messageManager->addNoticeMessage(__('There is no thing to clear.Please check it again.'));
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit');
            }
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // go back to edit form
            return $resultRedirect->setPath('*/*/edit');
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a logs to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

