<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Plugin\Magento\Framework\Mail;

use Magento\Framework\Mail\TransportInterface as MagentoTransportInterface;
use Variux\EmailNotification\Helper\Config;
use Variux\EmailNotification\Helper\Data;
use Variux\EmailNotification\Logger\Logger;
use Variux\EmailNotification\Model\Source\Status;
use Variux\EmailNotification\Model\TransportMessageRegistry;

/**
 * Provider-agnostic hook that logs every outgoing email and short-circuits
 * delivery when the spam breaker is tripped (bulkemails/smtp/disable = 1).
 *
 * Works with Magento default transport, Mageplaza SMTP and Amasty SMTP.
 *
 * Why the three-tier message resolution:
 *
 *   1. TransportMessageRegistry — populated by TransportInterfaceFactoryPlugin
 *      at the moment the transport is built. Authoritative when available.
 *
 *   2. Reflection scan over the transport's own properties — recovers the real
 *      message even when an SMTP provider's getMessage() misbehaves. This is
 *      required because Amasty\Smtp\Model\Transport::getMessage() returns
 *      $this->_mailMessage (an empty Laminas fallback), not $this->_message
 *      (the actual EmailMessage). A plain reliance on getMessage() produces
 *      log rows with NULL subject/sender/recipient and a content column that
 *      contains only a bare "Date:" header — the tell-tale symptom of
 *      serialising an empty Laminas\Mail\Message.
 *
 *   3. $subject->getMessage() — the interface contract. Used last because some
 *      providers return placeholders here.
 *
 * The first strategy that yields a populated message (non-empty subject or body)
 * wins. Empty candidates fall through.
 */
class TransportInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TransportMessageRegistry
     */
    private $registry;

    public function __construct(
        Config $config,
        Data $data,
        Logger $logger,
        TransportMessageRegistry $registry
    ) {
        $this->config = $config;
        $this->data = $data;
        $this->logger = $logger;
        $this->registry = $registry;
    }

    public function aroundSendMessage(
        MagentoTransportInterface $subject,
        \Closure $proceed
    ) {
        if (!$this->config->isEnabled()) {
            return $proceed();
        }

        $message = $this->resolveMessage($subject);
        if ($message === null) {
            return $proceed();
        }

        $blocked = (bool) $this->config->isDisabled();
        $status = $blocked ? Status::STATUS_BLOCKED : Status::STATUS_SUCCESS;

        try {
            $this->data->saveEmailLog($message, $status);
        } catch (\Throwable $e) {
            $this->logger->warning(
                'EmailNotification: failed to write log - ' . $e->getMessage()
            );
        }

        try {
            if ($blocked) {
                return null;
            }
            return $proceed();
        } finally {
            $this->registry->remove($subject);
        }
    }

    /**
     * @return object|null
     */
    private function resolveMessage(MagentoTransportInterface $subject)
    {
        $captured = $this->registry->get($subject);
        if ($captured !== null && $this->isPopulated($captured)) {
            return $captured;
        }

        foreach ($this->scanMessageCandidates($subject) as $candidate) {
            if ($this->isPopulated($candidate)) {
                return $candidate;
            }
        }

        $native = null;
        try {
            $native = $subject->getMessage();
        } catch (\Throwable $e) {
            $native = null;
        }
        if ($native !== null && $this->isPopulated($native)) {
            return $native;
        }

        return $captured ?: $native;
    }

    /**
     * A message is "populated" if it exposes a non-empty subject or a non-empty
     * body. Strict enough to reject an empty Laminas Message that serialises to
     * just an auto-generated Date header.
     *
     * @param mixed $message
     */
    private function isPopulated($message): bool
    {
        if (!is_object($message)) {
            return false;
        }
        if (method_exists($message, 'getSubject')) {
            try {
                if ((string) $message->getSubject() !== '') {
                    return true;
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }
        if (method_exists($message, 'getBody')) {
            try {
                $body = $message->getBody();
                if ($body !== null && $body !== '' && $body !== false) {
                    return true;
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }
        return false;
    }

    /**
     * Walk the transport's class hierarchy and yield object-typed properties
     * that look like a mail message. Recovers Amasty's real $_message when
     * getMessage() returns the empty $_mailMessage fallback, and similarly
     * covers any other provider that stashes the real message on a property.
     *
     * @return \Generator
     */
    private function scanMessageCandidates(object $subject): \Generator
    {
        try {
            $ref = new \ReflectionObject($subject);
        } catch (\Throwable $e) {
            return;
        }
        while ($ref) {
            foreach ($ref->getProperties() as $prop) {
                try {
                    $prop->setAccessible(true);
                    $value = $prop->getValue($subject);
                } catch (\Throwable $e) {
                    continue;
                }
                if (!is_object($value)) {
                    continue;
                }
                if (method_exists($value, 'getSubject')
                    || method_exists($value, 'getBody')
                    || method_exists($value, 'getRawMessage')
                ) {
                    yield $value;
                }
            }
            $ref = $ref->getParentClass();
        }
    }
}
