define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var label = this._super(record);

            if (label !== '') {
                if (record.status == 1) {
                    label = '<span class="grid-severity-notice"><span>' + label + '</span></span>';
                } else if(record.status == 3) {
                    label = '<span class="grid-severity-minor"><span>' + label + '</span></span>';
                } else {
                    label = '<span class="grid-severity-critical"><span>' + label + '</span></span>';
                }
            }

            return label;
        }
    });
});

