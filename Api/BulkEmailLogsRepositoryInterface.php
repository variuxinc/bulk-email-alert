<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface BulkEmailLogsRepositoryInterface
{

    /**
     * Save BulkEmailLogs
     * @param \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface $bulkEmailLogs
     * @return \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface $bulkEmailLogs
    );

    /**
     * Retrieve BulkEmailLogs
     * @param string $bulkemaillogsId
     * @return \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($bulkemaillogsId);

    /**
     * Retrieve BulkEmailLogs matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Variux\EmailNotification\Api\Data\BulkEmailLogsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete BulkEmailLogs
     * @param \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface $bulkEmailLogs
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface $bulkEmailLogs
    );

    /**
     * Delete BulkEmailLogs by ID
     * @param string $bulkemaillogsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bulkemaillogsId);
}

