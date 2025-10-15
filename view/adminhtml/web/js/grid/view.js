define([
    'jquery',
    'Magento_Ui/js/grid/columns/actions',
    'Magento_Ui/js/modal/modal'
], function ($, Column) {
    'use strict';

    function strip(html){
        var doc = new DOMParser().parseFromString(html, 'text/html');

        return doc.body.textContent || "";
    }

    return Column.extend({
        modal: {},

        /**
         * @inheritDoc
         */
        defaultCallback: function (actionIndex, recordId, action) {
            if (actionIndex !== 'detail') {
                return this._super();
            }

            if (typeof this.modal[action.rowIndex] === 'undefined') {
                var row = this.rows[action.rowIndex],
                    modalHtml = '<iframe srcdoc="' + row['content'] + '" style="width: 100%; height: 100%"></iframe>';

                this.modal[action.rowIndex] = $('<div/>')
                    .html(modalHtml)
                    .modal({
                        type: 'slide',
                        title: strip(row['subject']),
                        modalClass: 'bulk-modal-email',
                        innerScroll: true,
                        buttons: []
                    });
            }

            this.modal[action.rowIndex].trigger('openModal');
        }
    });
});

