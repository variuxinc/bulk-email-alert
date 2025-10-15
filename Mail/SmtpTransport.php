<?php

namespace Variux\EmailNotification\Mail;

use Closure;
use Exception;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\Smtp\Helper\Data;
use Mageplaza\Smtp\Mail\Rse\Mail;
use Mageplaza\Smtp\Model\Log;
use Mageplaza\Smtp\Model\LogFactory;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Laminas\Mail\Message;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Zend_Exception;
use Variux\EmailNotification\Helper\Config;
use Variux\EmailNotification\Helper\Data AS BulkEmailsHelperData;
use Variux\EmailNotification\Model\Source\Status;

class SmtpTransport
{

    /**
     * @var int Store Id
     */
    protected $_storeId;

    /**
     * @var Mail
     */
    protected $resourceMail;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Registry $registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var BulkEmailsHelperData
     */
    protected $data;

    /**
     * Transport constructor.
     *
     * @param Mail $resourceMail
     * @param LogFactory $logFactory
     * @param Registry $registry
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Mail $resourceMail,
        LogFactory $logFactory,
        Registry $registry,
        Data $helper,
        LoggerInterface $logger,
        Config $config,
        BulkEmailsHelperData $data
    ) {
        $this->resourceMail = $resourceMail;
        $this->logFactory   = $logFactory;
        $this->registry     = $registry;
        $this->helper       = $helper;
        $this->logger       = $logger;
        $this->config       = $config;
        $this->data         = $data;
    }

    /**
     * @param TransportInterface $subject
     * @param Closure $proceed
     *
     * @throws MailException
     * @throws Zend_Exception
     */
    public function aroundSendMessage(TransportInterface $subject, Closure $proceed)
    {
        $this->_storeId = $this->registry->registry('mp_smtp_store_id');
        if (!$this->resourceMail->isModuleEnable($this->_storeId)) {
            $proceed();

            return;
        }
        $message = $this->getMessage($subject);
        if (!$this->validateBlacklist($message)) {
            try {
                if (!$this->resourceMail->isDeveloperMode($this->_storeId)) {
                    if ($this->helper->versionCompare('2.4.8')) {
                        if (!$message instanceof Email) {
                            $message = $this->convertToSymfonyEmail($message);
                        }

                        $transport = $this->resourceMail->getSymfonyTransport($this->_storeId);
                        $mailer    = new Mailer($transport);
                        $mailer->send($message);
                    } else {
                        if ($this->helper->versionCompare('2.2.8')) {
                            $message = Message::fromString($message->getRawMessage())->setEncoding('utf-8');
                        }
                        $message = $this->resourceMail->processMessage($message, $this->_storeId);
                        if ($this->helper->versionCompare('2.3.3')) {
                            $message->getHeaders()->removeHeader("Content-Disposition");
                        }

                        $transport = $this->resourceMail->getTransport($this->_storeId);
                        if ($this->config->isEnabled()) {
                            if (!$this->config->isDisabled()) {
                                $transport->send($message);
                            }
                        } else {
                            $transport->send($message);
                        }


                        if ($this->helper->versionCompare('2.2.8')) {
                            $messageTmp = $this->getMessage($subject);
                            if ($messageTmp && is_object($messageTmp)) {
                                $body = $messageTmp->getBody();
                                if (is_object($body) && $body->isMultiPart()) {
                                    $message->setBody($body->getPartContent("0"));
                                }
                            }
                        }
                    }
                } 

                $this->emailLog($message);
                if ($this->config->isEnabled()) {
                    if (!$this->config->isDisabled()) {
                        $this->data->saveEmailLog($message);
                    } else {
                        $this->data->saveEmailLog($message, Status::STATUS_BLOCKED);
                    }
                }
            } catch (Exception $e) {
                $this->emailLog($message, false);
                if ($this->config->isEnabled()) {
                    $this->data->saveEmailLog($message, false);
                }
                throw new MailException(new Phrase($e->getMessage()), $e);
            }
        }
    }

    /**
     * @param $laminasMessage
     * @return Email
     */
    protected function convertToSymfonyEmail($laminasMessage)
    {
        $email = new Email();

        $fromList = $laminasMessage->getFrom();
        if (is_array($fromList) && count($fromList)) {
            $from = $fromList[0];
            $email->from(new Address($from->getEmail(), $from->getName()));
        }

        $to = $laminasMessage->getTo();
        if (is_array($to)) {
            foreach ($to as $toAddress) {
                $email->to(new Address($toAddress->getEmail(), $toAddress->getName()));
            }
        }

        $email->subject((string) $laminasMessage->getSubject());
        $body = $laminasMessage->getBody();
        if ($body instanceof TextPart) {
            $mediaSubtype = $body->getMediaSubtype();
            $content      = $body->getBody();

            if ($mediaSubtype === 'html') {
                $email->html($content);
            } else {
                $email->text($content);
            }
        } elseif ($body instanceof MixedPart) {
            foreach ($body->getParts() as $part) {
                if ($part instanceof TextPart) {
                    $mediaSubtype = $part->getMediaSubtype();
                    $content      = $part->getBody();

                    if ($mediaSubtype === 'html') {
                        $email->html($content);
                    } else {
                        $email->text($content);
                    }
                }
                if ($part instanceof DataPart) {
                    $email->addPart($part);
                }
            }
        } else {
            $email->text('No readable content.');
        }

        if ($laminasMessage->getCc()) {
            foreach ($laminasMessage->getCc() as $ccAddress) {
                $email->addCc(new Address($ccAddress->getEmail(), $ccAddress->getName()));
            }
        }

        if ($laminasMessage->getBcc()) {
            foreach ($laminasMessage->getBcc() as $bccAddress) {
                $email->addBcc(new Address($bccAddress->getEmail(), $bccAddress->getName()));
            }
        }

        if ($laminasMessage->getReplyTo()) {
            foreach ($laminasMessage->getReplyTo() as $replyTo) {
                $email->replyTo(new Address($replyTo->getEmail(), $replyTo->getName()));
            }
        }

        return $email;
    }

    /**
     * Get message
     *
     * @param TransportInterface $transport
     *
     * @return mixed|null
     */
    protected function getMessage($transport)
    {
        if ($this->helper->versionCompare('2.2.0')) {
            return $transport->getMessage();
        }

        try {
            $reflectionClass = new ReflectionClass($transport);
            $message         = $reflectionClass->getProperty('_message');
        } catch (Exception $e) {
            return null;
        }

        $message->setAccessible(true);

        return $message->getValue($transport);
    }

    /**
     * @param EmailMessage $message
     *
     * @return string
     */
    public function getRecipient($message)
    {
        $emails = [];
        if ($message->getTo()) {
            foreach ($message->getTo() as $address) {
                $emails[] = $address->getEmail();
            }
        }

        return implode(',', $emails);
    }

    /**
     * @param EmailMessage $message
     *
     * @return bool
     */
    public function validateBlacklist($message)
    {
        $result = false;
        if ($this->helper->isTestEmail()) {
            return $result;
        }

        $blacklist = $this->helper->getBlacklist();
        if ($blacklist) {
            $recipient = $this->getRecipient($message);
            $patterns  = array_unique(explode(PHP_EOL, $blacklist));
            foreach ($patterns as $pattern) {
                try {
                    if (preg_match($pattern, $recipient)) {
                        $result = true;
                        break;
                    }
                } catch (Exception $e) {
                    // Ignore validate if the pattern is error
                    continue;
                }
            }
        }

        return $result;
    }

    /**
     * Save Email Sent
     *
     * @param $message
     * @param bool $status
     */
    protected function emailLog($message, $status = true)
    {
        if ($this->helper->isEnabled($this->_storeId) && $this->resourceMail->isEnableEmailLog($this->_storeId)) {
            /** @var Log $log */
            $log = $this->logFactory->create();
            try {
                if ($this->helper->versionCompare('2.4.8')) {
                    $log->saveLogSymfony($message, $status, $this->_storeId);
                } else {
                    $log->saveLog($message, $status, $this->_storeId);
                }

                if ($status) {
                    $this->saveLogIdForAbandonedCart($log);
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @param Log $log
     */
    protected function saveLogIdForAbandonedCart($log)
    {
        try {
            $quote = $this->registry->registry('smtp_abandoned_cart');

            if ($quote) {
                $ids = $quote->getMpSmtpAceLogIds() ?
                    $quote->getMpSmtpAceLogIds() . ',' . $log->getId() : $log->getId();
                $quote->setMpSmtpAceSent(1)->setMpSmtpAceLogIds($ids)->save();
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
