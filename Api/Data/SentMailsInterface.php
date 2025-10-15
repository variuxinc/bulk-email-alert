<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Api\Data;

interface SentMailsInterface
{

    const UNLOCK_SENT = 'unlock_sent';
    const SENT_NUM = 'sent_num';
    const FIRST_SENT = 'first_sent';
    const SENTMAILS_ID = 'sentmails_id';
    const EXTRA = 'extra';
    const LOCK_SENT = 'lock_sent';

    /**
     * Get sentmails_id
     * @return string|null
     */
    public function getSentmailsId();

    /**
     * Set sentmails_id
     * @param string $sentmailsId
     * @return \Variux\EmailNotification\SentMails\Api\Data\SentMailsInterface
     */
    public function setSentmailsId($sentmailsId);

    /**
     * Get sent_num
     * @return string|null
     */
    public function getSentNum();

    /**
     * Set sent_num
     * @param string $sentNum
     * @return \Variux\EmailNotification\SentMails\Api\Data\SentMailsInterface
     */
    public function setSentNum($sentNum);

    /**
     * Get first_sent
     * @return string|null
     */
    public function getFirstSent();

    /**
     * Set first_sent
     * @param string $firstSent
     * @return \Variux\EmailNotification\SentMails\Api\Data\SentMailsInterface
     */
    public function setFirstSent($firstSent);

    /**
     * Get lock_sent
     * @return string|null
     */
    public function getLockSent();

    /**
     * Set lock_sent
     * @param string $lockSent
     * @return \Variux\EmailNotification\SentMails\Api\Data\SentMailsInterface
     */
    public function setLockSent($lockSent);

    /**
     * Get unlock_sent
     * @return string|null
     */
    public function getUnlockSent();

    /**
     * Set unlock_sent
     * @param string $unlockSent
     * @return \Variux\EmailNotification\SentMails\Api\Data\SentMailsInterface
     */
    public function setUnlockSent($unlockSent);

    /**
     * Get extra
     * @return string|null
     */
    public function getExtra();

    /**
     * Set extra
     * @param string $extra
     * @return \Variux\EmailNotification\SentMails\Api\Data\SentMailsInterface
     */
    public function setExtra($extra);
}

