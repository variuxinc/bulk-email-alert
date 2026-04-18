<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Helper;

use Laminas\Mail\Message as LaminasMessage;
use Magento\Framework\App\Helper\AbstractHelper;
use Variux\EmailNotification\Model\BulkEmailLogsRepository;
use Variux\EmailNotification\Model\BulkEmailLogs;
use Variux\EmailNotification\Model\BulkEmailLogsFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    public const BULK_EMAILS_SMTP_DISABLE = 'bulkemails/smtp/disable';

    protected $bulkEmailLogsRepository;

    protected $bulkEmailLogs;

    protected $bulkEmailLogsFactory;

    /**
     * @var Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        BulkEmailLogsRepository $bulkEmailLogsRepository,
        BulkEmailLogs $bulkEmailLogs,
        BulkEmailLogsFactory $bulkEmailLogsFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        parent::__construct($context);
        $this->bulkEmailLogsRepository = $bulkEmailLogsRepository;
        $this->bulkEmailLogs = $bulkEmailLogs;
        $this->bulkEmailLogsFactory = $bulkEmailLogsFactory;
        $this->configWriter = $configWriter;
    }

    /**
     * @param $emailList
     *
     * @return array
     */
    public function extractEmailInfo($emailList)
    {
        $data = [];

        $emailList = preg_replace('/\s+/', '', $emailList);
        if (strpos($emailList, '<') !== false) {
            $emails = explode('<', $emailList);
            $name   = '';
            if (count($emails) > 1) {
                $name = $emails[0];
            }
            $email       = trim($emails[1], '>');
            $data[$name] = $email;
        } else {
            $emails = explode(',', $emailList);
            foreach ($emails as $email) {
                $data[] = $email;
            }
        }

        return $data;
    }

    /**
     * Persist a log row for an outgoing message.
     *
     * Reads headers directly off the Magento message (EmailMessage exposes
     * getSubject/getFrom/getTo/getCc/getBcc natively). Falls back to parsing
     * the raw MIME via LaminasMessage::fromString() only for legacy message
     * objects that do not expose those getters.
     *
     * The original code always re-parsed via fromString(), which loses data
     * on Symfony-generated MIME in Magento 2.4.7+ and produced NULL columns.
     *
     * @param object $message
     * @param int|bool $status
     * @return void
     */
    public function saveEmailLog($message, $status = true)
    {
        $log = $this->bulkEmailLogsFactory->create();
        $fallback = null;

        $subject = (string) $this->safeCall($message, 'getSubject');
        if ($subject === '') {
            $fallback = $fallback ?? $this->parseLaminasFallback($message);
            if ($fallback !== null) {
                $subject = (string) $this->safeCall($fallback, 'getSubject');
            }
        }
        if ($subject !== '') {
            $log->setSubject($subject);
        }

        $sender = $this->extractSender($message);
        if ($sender === '') {
            $fallback = $fallback ?? $this->parseLaminasFallback($message);
            if ($fallback !== null) {
                $sender = $this->extractSender($fallback);
            }
        }
        if ($sender !== '') {
            $log->setSender($sender);
        }

        $log->setRecipient($this->resolveAddressField($message, $fallback, 'getTo'));
        $log->setCc($this->resolveAddressField($message, $fallback, 'getCc'));
        $log->setBcc($this->resolveAddressField($message, $fallback, 'getBcc'));

        $content = $this->extractBody($message);
        if ($content === '' && ($fallback ?? ($fallback = $this->parseLaminasFallback($message))) !== null) {
            $content = $this->extractBody($fallback);
        }
        $log->setContent($content);

        $log->setStatus($status);
        $this->bulkEmailLogsRepository->save($log);
    }

    /**
     * Try the method on the original message first; if that yields nothing,
     * try the same method on a LaminasMessage parsed from the raw MIME.
     *
     * @param mixed $message
     * @param mixed $fallback
     * @param string $method
     * @return string
     */
    private function resolveAddressField($message, $fallback, string $method): string
    {
        $value = $this->formatAddressList($this->safeCall($message, $method));
        if ($value !== '') {
            return $value;
        }
        if ($fallback !== null) {
            return $this->formatAddressList($this->safeCall($fallback, $method));
        }
        return '';
    }

    /**
     * Parse raw MIME into a LaminasMessage for legacy messages that do not
     * expose getFrom/getTo/getCc/getBcc directly. Returns null if parsing
     * fails or no raw message is available.
     *
     * @param mixed $message
     * @return LaminasMessage|null
     */
    private function parseLaminasFallback($message)
    {
        if (!is_object($message) || !method_exists($message, 'getRawMessage')) {
            return null;
        }
        try {
            $raw = (string) $message->getRawMessage();
            if ($raw === '') {
                return null;
            }
            return LaminasMessage::fromString($raw)->setEncoding('utf-8');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param mixed $object
     * @param string $method
     * @return mixed
     */
    private function safeCall($object, string $method)
    {
        if (!is_object($object) || !method_exists($object, $method)) {
            return null;
        }
        try {
            return $object->{$method}();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param mixed $message
     * @return string
     */
    private function extractSender($message): string
    {
        $from = $this->safeCall($message, 'getFrom');
        if ($from === null) {
            return '';
        }

        if (is_iterable($from)) {
            foreach ($from as $address) {
                $formatted = $this->formatSingleAddress($address);
                if ($formatted !== '') {
                    return $formatted;
                }
            }
            return '';
        }

        return $this->formatSingleAddress($from);
    }

    /**
     * @param mixed $address
     * @return string
     */
    private function formatSingleAddress($address): string
    {
        $email = $this->safeCall($address, 'getEmail');
        if (!$email) {
            return '';
        }
        $name = (string) $this->safeCall($address, 'getName');

        return trim(($name !== '' ? $name . ' ' : '') . '<' . $email . '>');
    }

    /**
     * @param mixed $addresses
     * @return string
     */
    private function formatAddressList($addresses): string
    {
        if (!is_iterable($addresses)) {
            return '';
        }
        $emails = [];
        foreach ($addresses as $address) {
            $email = $this->safeCall($address, 'getEmail');
            if ($email) {
                $emails[] = $email;
            }
        }

        return implode(',', $emails);
    }

    /**
     * Extract body text for logging, trying several shapes:
     *   1. getBodyText()            — Magento/Laminas plain-text part
     *   2. getBody()                — string, Laminas\Mime\Message, or Symfony part
     *      2a. generateMessage()    — Laminas\Mime\Message
     *      2b. getParts()/getRawContent()/getBody()/getContent() — iterate MIME parts
     *      2c. bodyToString()/__toString() — Symfony and similar
     *   3. getRawMessage()          — last-resort raw MIME dump
     *
     * This mirrors Amasty's multi-path body extraction and guarantees the
     * `content` column is populated for HTML-only, plaintext, and multipart
     * emails across Magento 2.4.x and custom transports.
     *
     * @param mixed $message
     * @return string
     */
    private function extractBody($message): string
    {
        $text = $this->safeCall($message, 'getBodyText');
        if (is_string($text) && $text !== '') {
            return $this->cleanBody($text);
        }

        $body = $this->safeCall($message, 'getBody');
        $fromBody = $this->extractFromBodyObject($body);
        if ($fromBody !== '') {
            return $this->cleanBody($fromBody);
        }

        $raw = $this->safeCall($message, 'getRawMessage');
        if (is_string($raw) && $raw !== '') {
            return $this->cleanBody($raw);
        }

        return '';
    }

    /**
     * @param mixed $body
     * @return string
     */
    private function extractFromBodyObject($body): string
    {
        if (is_string($body)) {
            return $body;
        }
        if (!is_object($body)) {
            return '';
        }

        $generated = $this->safeCall($body, 'generateMessage');
        if (is_string($generated) && $generated !== '') {
            return $generated;
        }

        $parts = $this->safeCall($body, 'getParts');
        if (is_iterable($parts)) {
            foreach ($parts as $part) {
                foreach (['getRawContent', 'getBody', 'getContent', 'bodyToString'] as $method) {
                    $content = $this->safeCall($part, $method);
                    if (is_string($content) && $content !== '') {
                        return $content;
                    }
                }
            }
        }

        foreach (['bodyToString', 'toString'] as $method) {
            $content = $this->safeCall($body, $method);
            if (is_string($content) && $content !== '') {
                return $content;
            }
        }

        if (method_exists($body, '__toString')) {
            return (string) $body;
        }

        return '';
    }

    /**
     * @param string $body
     * @return string
     */
    private function cleanBody(string $body): string
    {
        return htmlspecialchars(quoted_printable_decode($body));
    }

    public function setValue($path, $value)
    {
        $this->configWriter->save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

    }
}