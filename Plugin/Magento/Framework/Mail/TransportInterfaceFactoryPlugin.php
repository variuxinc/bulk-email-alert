<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Plugin\Magento\Framework\Mail;

use Magento\Framework\Mail\TransportInterface as MagentoTransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Variux\EmailNotification\Model\TransportMessageRegistry;

/**
 * Capture the real email message at factory-create time.
 *
 * Magento's core TransportBuilder::getTransport() always calls
 * TransportInterfaceFactory::create(['message' => clone $this->message]),
 * so this is the single authoritative entry point that holds the real
 * EmailMessage regardless of which SMTP provider ultimately builds the
 * Transport (Magento default, Mageplaza, Amasty, ...).
 *
 * Why we need this: Amasty\Smtp\Model\Transport::getMessage() returns
 * $this->_mailMessage — a blank Laminas Message injected as a fallback —
 * not $this->_message (the real EmailMessage). Any plugin hooking
 * TransportInterface::sendMessage and calling $subject->getMessage()
 * receives an empty object, which is why bulk email logs showed NULL
 * subject/sender/cc and content containing only a bare "Date:" header.
 *
 * By grabbing $data['message'] here and associating it with the built
 * transport via spl_object_id, the aroundSendMessage plugin can recover
 * the real message regardless of provider quirks.
 */
class TransportInterfaceFactoryPlugin
{
    /**
     * @var TransportMessageRegistry
     */
    private $registry;

    public function __construct(TransportMessageRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param TransportInterfaceFactory $subject
     * @param MagentoTransportInterface $result
     * @param array $data
     * @return MagentoTransportInterface
     */
    public function afterCreate(
        TransportInterfaceFactory $subject,
        MagentoTransportInterface $result,
        array $data = []
    ): MagentoTransportInterface {
        if (isset($data['message']) && is_object($data['message'])) {
            $this->registry->set($result, $data['message']);
        }
        return $result;
    }
}
