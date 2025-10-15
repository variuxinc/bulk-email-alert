<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs;

class Edit extends \Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('bulkemaillogs_id');
        $model = $this->_objectManager->create(\Variux\EmailNotification\Model\BulkEmailLogs::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Bulkemaillogs no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('variux_emailnotification_bulkemaillogs', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Bulkemaillogs') : __('New Bulkemaillogs'),
            $id ? __('Edit Bulkemaillogs') : __('New Bulkemaillogs')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Bulkemaillogss'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Bulkemaillogs %1', $model->getId()) : __('New Bulkemaillogs'));
        return $resultPage;
    }
}

