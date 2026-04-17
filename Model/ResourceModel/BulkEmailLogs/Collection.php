<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'bulkemaillogs_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Variux\EmailNotification\Model\BulkEmailLogs::class,
            \Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs::class
        );
    }

    /**
     * Truncate table emails log
     *
     * @return void
     */
    public function clearLog()
    {
        $this->getConnection()->delete($this->getMainTable());
    }

    /**
     * Delete email logs older than specified days in batches using LIMIT
     *
     * Leverages the created_at index and stops naturally when fewer rows than
     * the batch size are affected, avoiding an extra MAX(id) round-trip.
     *
     * @param int $days
     * @param int $batchSize
     * @return int Number of deleted records
     */
    public function deleteOlderThan(int $days, int $batchSize = 1000): int
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $sql = sprintf(
            'DELETE FROM %s WHERE %s < ? LIMIT %d',
            $connection->quoteIdentifier($table),
            $connection->quoteIdentifier('created_at'),
            $batchSize
        );

        $totalDeleted = 0;
        do {
            $deleted = (int) $connection->query($sql, [$threshold])->rowCount();
            $totalDeleted += $deleted;
        } while ($deleted === $batchSize);

        return $totalDeleted;
    }
}

