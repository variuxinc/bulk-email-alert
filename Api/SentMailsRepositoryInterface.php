<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SentMailsRepositoryInterface
{

    /**
     * Save sentMails
     * @param \Variux\EmailNotification\Api\Data\SentMailsInterface $sentMails
     * @return \Variux\EmailNotification\Api\Data\SentMailsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Variux\EmailNotification\Api\Data\SentMailsInterface $sentMails
    );

    /**
     * Retrieve sentMails
     * @param string $sentmailsId
     * @return \Variux\EmailNotification\Api\Data\SentMailsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($sentmailsId);

    /**
     * Retrieve sentMails matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Variux\EmailNotification\Api\Data\SentMailsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete sentMails
     * @param \Variux\EmailNotification\Api\Data\SentMailsInterface $sentMails
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Variux\EmailNotification\Api\Data\SentMailsInterface $sentMails
    );

    /**
     * Delete sentMails by ID
     * @param string $sentmailsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sentmailsId);
}

