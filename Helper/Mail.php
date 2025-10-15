<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class Mail extends AbstractHelper
{

    protected $transportBuilder;
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $to email and name of the receiver
     * @param array $templateParams
     * @param int|null $storeId
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmailTemplate(
        $template,
        $sender,
        $to = [],
        $bcc = [],
        $templateParams = [],
        $storeId = null
    ) {
        if (!isset($to['email']) || empty($to['email'])) {
            throw new LocalizedException(
                __('We could not send the email because the receiver data is invalid.')
            );
        }
        $storeId = $storeId ? $storeId : $this->storeManager->getStore()->getId();
        $name = isset($to['name']) ? $to['name'] : '';

        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $this->transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $sender, $storeId
        )->addTo(
            $to['email'],
            $name
        );

        if (isset($bcc) || !empty($bcc)) {
            foreach ($bcc as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * Send the Template Email
     */
    public function sendTemplateEmail(
        $sender = 'example@example.com',
        $to = ['email' => '', 'name' => ''],
        $bcc = []
    ) {
        $this->sendEmailTemplate(
            'bulkemails/email_template/template',
            $sender,
            $to,
            $bcc
        );
    }

    /**
     * @param string $template email template
     * @param string $sender configuration path of email identity
     * @param array $to email and name of the receiver
     * @param array $templateParams
     * @param int|null $storeId
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reSendEmail(
        $template,
        $sender,
        $to = [],
        $templateParams = [],
        $storeId = null
    )
    {
        if (!isset($to['email']) || empty($to['email'])) {
            throw new LocalizedException(
                __('We could not send the email because the receiver data is invalid.')
            );
        }
        $storeId = $storeId ? $storeId : $this->storeManager->getStore()->getId();
        $name = isset($to['name']) ? $to['name'] : '';

        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $sender, $storeId
        )->addTo(
            $to['email'],
            $name
        )->getTransport();
        $transport->sendMessage();
    }
}

