<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Model;

use Magento\Framework\Mail\TransportInterface;

/**
 * Per-request map of Transport instance -> real message passed at factory time.
 *
 * Works around providers whose TransportInterface::getMessage() returns an
 * empty placeholder instead of the actual message being sent (e.g. Amasty
 * SMTP's Transport returns $this->_mailMessage — a bare Laminas Message —
 * rather than $this->_message). Keyed by spl_object_id to avoid holding
 * references that would prevent garbage collection.
 */
class TransportMessageRegistry
{
    /**
     * @var array<int, object>
     */
    private $messages = [];

    public function set(TransportInterface $transport, object $message): void
    {
        $this->messages[spl_object_id($transport)] = $message;
    }

    /**
     * @return object|null
     */
    public function get(TransportInterface $transport)
    {
        return $this->messages[spl_object_id($transport)] ?? null;
    }

    public function remove(TransportInterface $transport): void
    {
        unset($this->messages[spl_object_id($transport)]);
    }
}
