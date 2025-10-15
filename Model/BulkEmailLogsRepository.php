<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Variux\EmailNotification\Api\BulkEmailLogsRepositoryInterface;
use Variux\EmailNotification\Api\Data\BulkEmailLogsInterface;
use Variux\EmailNotification\Api\Data\BulkEmailLogsInterfaceFactory;
use Variux\EmailNotification\Api\Data\BulkEmailLogsSearchResultsInterfaceFactory;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs as ResourceBulkEmailLogs;
use Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs\CollectionFactory as BulkEmailLogsCollectionFactory;

class BulkEmailLogsRepository implements BulkEmailLogsRepositoryInterface
{

    /**
     * @var BulkEmailLogsInterfaceFactory
     */
    protected $bulkEmailLogsFactory;

    /**
     * @var BulkEmailLogs
     */
    protected $searchResultsFactory;

    /**
     * @var BulkEmailLogsCollectionFactory
     */
    protected $bulkEmailLogsCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceBulkEmailLogs
     */
    protected $resource;


    /**
     * @param ResourceBulkEmailLogs $resource
     * @param BulkEmailLogsInterfaceFactory $bulkEmailLogsFactory
     * @param BulkEmailLogsCollectionFactory $bulkEmailLogsCollectionFactory
     * @param BulkEmailLogsSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceBulkEmailLogs $resource,
        BulkEmailLogsInterfaceFactory $bulkEmailLogsFactory,
        BulkEmailLogsCollectionFactory $bulkEmailLogsCollectionFactory,
        BulkEmailLogsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->bulkEmailLogsFactory = $bulkEmailLogsFactory;
        $this->bulkEmailLogsCollectionFactory = $bulkEmailLogsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(BulkEmailLogsInterface $bulkEmailLogs)
    {
        try {
            $this->resource->save($bulkEmailLogs);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the bulkEmailLogs: %1',
                $exception->getMessage()
            ));
        }
        return $bulkEmailLogs;
    }

    /**
     * @inheritDoc
     */
    public function get($bulkEmailLogsId)
    {
        $bulkEmailLogs = $this->bulkEmailLogsFactory->create();
        $this->resource->load($bulkEmailLogs, $bulkEmailLogsId);
        if (!$bulkEmailLogs->getId()) {
            throw new NoSuchEntityException(__('BulkEmailLogs with id "%1" does not exist.', $bulkEmailLogsId));
        }
        return $bulkEmailLogs;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->bulkEmailLogsCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(BulkEmailLogsInterface $bulkEmailLogs)
    {
        try {
            $bulkEmailLogsModel = $this->bulkEmailLogsFactory->create();
            $this->resource->load($bulkEmailLogsModel, $bulkEmailLogs->getBulkemaillogsId());
            $this->resource->delete($bulkEmailLogsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the BulkEmailLogs: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($bulkEmailLogsId)
    {
        return $this->delete($this->get($bulkEmailLogsId));
    }
}

