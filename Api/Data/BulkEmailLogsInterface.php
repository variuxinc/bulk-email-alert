<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Api\Data;

interface BulkEmailLogsInterface
{

    const UPDATED_AT = 'updated_at';
    const RECIPIENT = 'recipient';
    const SUBJECT = 'subject';
    const CONTENT = 'content';
    const BCC = 'bcc';
    const BULKEMAILLOGS_ID = 'bulkemaillogs_id';
    const CREATED_AT = 'created_at';
    const STATUS = 'status';
    const SENDER = 'sender';
    const CC = 'cc';

    /**
     * Get bulkemaillogs_id
     * @return string|null
     */
    public function getBulkemaillogsId();

    /**
     * Set bulkemaillogs_id
     * @param string $bulkemaillogsId
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setBulkemaillogsId($bulkemaillogsId);

    /**
     * Get subject
     * @return string|null
     */
    public function getSubject();

    /**
     * Set subject
     * @param string $subject
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setSubject($subject);

    /**
     * Get content
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     * @param string $content
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setContent($content);

    /**
     * Get sender
     * @return string|null
     */
    public function getSender();

    /**
     * Set sender
     * @param string $sender
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setSender($sender);

    /**
     * Get recipient
     * @return string|null
     */
    public function getRecipient();

    /**
     * Set recipient
     * @param string $recipient
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setRecipient($recipient);

    /**
     * Get cc
     * @return string|null
     */
    public function getCc();

    /**
     * Set cc
     * @param string $cc
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setCc($cc);

    /**
     * Get bcc
     * @return string|null
     */
    public function getBcc();

    /**
     * Set bcc
     * @param string $bcc
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setBcc($bcc);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Variux\EmailNotification\BulkEmailLogs\Api\Data\BulkEmailLogsInterface
     */
    public function setUpdatedAt($updatedAt);
}

