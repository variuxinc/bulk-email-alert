<?php

namespace Variux\EmailNotification\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Mageplaza\Smtp\Model\Source
 */
class Status implements OptionSourceInterface
{
    const STATUS_BLOCKED   = 3;
    const STATUS_PENDING   = 2;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR   = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_BLOCKED, 'label' => __('Blocked')],
            ['value' => self::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => self::STATUS_SUCCESS, 'label' => __('Success')],
            ['value' => self::STATUS_ERROR, 'label' => __('Error')],
        ];
    }
}
