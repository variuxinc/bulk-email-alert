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
        $where = "status = 1";
        $this->getConnection()->delete($this->getMainTable(), $where);
    }
}

