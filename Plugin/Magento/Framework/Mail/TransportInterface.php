<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Plugin\Magento\Framework\Mail;

use Laminas\Mail\Message as LaminasMessage;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;
use mysql_xdevapi\Exception;
use Variux\EmailNotification\Helper\Config;
use Variux\EmailNotification\Helper\Data;
use Variux\EmailNotification\Model\Source\Status;

class TransportInterface
{
    protected $config;

    protected $data;

    public function __construct(
        Config $config,
        Data $data
    )
    {
        $this->config = $config;
        $this->data = $data;
    }

    public function aroundSendMessage(
        \Magento\Framework\Mail\TransportInterface $subject,
        \Closure $proceed
    ) {
        //Your plugin code
        $message = $subject->getMessage();

        if ($this->config->isEnabled() && $message ) {
            try {
                if (!$this->config->isDisabled()) {
                    $message = LaminasMessage::fromString($message->getRawMessage())->setEncoding('utf-8');
                    $this->data->saveEmailLog($message);
                } else {
                    $message = LaminasMessage::fromString($message->getRawMessage())->setEncoding('utf-8');
                    $this->data->saveEmailLog($message, Status::STATUS_BLOCKED);
                }
            } catch (Exception $e) {
                $this->data->saveEmailLog($message, false);
                throw new MailException(new Phrase($e->getMessage()), $e);
            }
        } else {
            $result = $proceed();
            return $result;
        }
    }
}

