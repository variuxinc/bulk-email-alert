<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Api\Data;

interface SentMailsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get sentMails list.
     * @return \Variux\EmailNotification\Api\Data\SentMailsInterface[]
     */
    public function getItems();

    /**
     * Set sent_num list.
     * @param \Variux\EmailNotification\Api\Data\SentMailsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

