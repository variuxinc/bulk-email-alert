<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Model;

use Magento\Framework\Model\AbstractModel;
use Variux\EmailNotification\Api\Data\BulkEmailLogsInterface;

class BulkEmailLogs extends AbstractModel implements BulkEmailLogsInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Variux\EmailNotification\Model\ResourceModel\BulkEmailLogs::class);
    }

    /**
     * @inheritDoc
     */
    public function getBulkemaillogsId()
    {
        return $this->getData(self::BULKEMAILLOGS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setBulkemaillogsId($bulkemaillogsId)
    {
        return $this->setData(self::BULKEMAILLOGS_ID, $bulkemaillogsId);
    }

    /**
     * @inheritDoc
     */
    public function getSubject()
    {
        return $this->getData(self::SUBJECT);
    }

    /**
     * @inheritDoc
     */
    public function setSubject($subject)
    {
        return $this->setData(self::SUBJECT, $subject);
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @inheritDoc
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * @inheritDoc
     */
    public function getSender()
    {
        return $this->getData(self::SENDER);
    }

    /**
     * @inheritDoc
     */
    public function setSender($sender)
    {
        return $this->setData(self::SENDER, $sender);
    }

    /**
     * @inheritDoc
     */
    public function getRecipient()
    {
        return $this->getData(self::RECIPIENT);
    }

    /**
     * @inheritDoc
     */
    public function setRecipient($recipient)
    {
        return $this->setData(self::RECIPIENT, $recipient);
    }

    /**
     * @inheritDoc
     */
    public function getCc()
    {
        return $this->getData(self::CC);
    }

    /**
     * @inheritDoc
     */
    public function setCc($cc)
    {
        return $this->setData(self::CC, $cc);
    }

    /**
     * @inheritDoc
     */
    public function getBcc()
    {
        return $this->getData(self::BCC);
    }

    /**
     * @inheritDoc
     */
    public function setBcc($bcc)
    {
        return $this->setData(self::BCC, $bcc);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

