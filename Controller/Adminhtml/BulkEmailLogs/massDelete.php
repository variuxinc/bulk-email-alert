<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Variux\EmailNotification\Controller\Adminhtml\BulkEmailLogs as BulkEmailLogsController;
use Variux\EmailNotification\Api\BulkEmailLogsRepositoryInterface;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use Variux\EmailNotification\Logger\Logger;

class massDelete extends BulkEmailLogsController implements HttpPostActionInterface
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
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param BulkEmailLogsRepositoryInterface|null $bulkEmailLogsRepository
     * @param Logger|null $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BulkEmailLogsRepositoryInterface $bulkEmailLogsRepository = null,
        Logger $logger = null
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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

        $bulkEmailDeleted = 0;
        $bulkEmailDeletedError = 0;
        /** @var \Variux\EmailNotification\Model\BulkEmailLogs $bulkEmail */
        foreach ($collection->getItems() as $bulkEmail) {
            try {
                $this->bulkEmailLogsRepository->delete($bulkEmail);
                $bulkEmailDeleted++;
            } catch (LocalizedException $exception) {
                $this->logger->error($exception->getLogMessage());
                $bulkEmailDeletedError++;
            }
        }

        if ($bulkEmailDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $bulkEmailDeleted)
            );
        }

        if ($bulkEmailDeletedError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been deleted. Please see server logs for more details.',
                    $bulkEmailDeletedError
                )
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
