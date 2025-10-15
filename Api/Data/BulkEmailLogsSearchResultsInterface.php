<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Api\Data;

interface BulkEmailLogsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get BulkEmailLogs list.
     * @return \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface[]
     */
    public function getItems();

    /**
     * Set subject list.
     * @param \Variux\EmailNotification\Api\Data\BulkEmailLogsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

