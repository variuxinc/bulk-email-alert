<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Variux\EmailNotification\Ui\Component\Listing\Column;

class BulkEmailLogsActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const URL_PATH_DELETE = 'variux_emailnotification/bulkemaillogs/delete';
    const URL_PATH_EDIT = 'variux_emailnotification/bulkemaillogs/edit';
    const URL_PATH_DETAILS = 'variux_emailnotification/bulkemaillogs/details';
    const URL_PATH_RESUME = 'variux_emailnotification/bulkemaillogs/resend';

    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['bulkemaillogs_id'])) {
                    $item[$this->getData('name')] = [
                        'detail' => [
                            'label' => __('View Detail')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'bulkemaillogs_id' => $item['bulkemaillogs_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete'),
                                'message' => __('Are you sure you wan\'t to delete a record?')
                            ]
                        ],
                        'resume' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_RESUME,
                                [
                                    'bulkemaillogs_id' => $item['bulkemaillogs_id']
                                ]
                            ),
                            'label' => __('Re-Send'),
                            'confirm' => [
                                'title' => __('Re-Send'),
                                'message' => __('Are you sure you wan\'t to Re-Send email a record?')
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}

