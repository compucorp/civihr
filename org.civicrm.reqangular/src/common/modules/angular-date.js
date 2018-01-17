define([
    'common/angular',
    'common/decorators/angular-date/datepicker-popup',
    'common/decorators/angular-date/date-filter',
    'common/modules/services',
    'common/angularBootstrap',
    'common/modules/templates'
], function (angular, datepickerPopup, dateFilter) {
    'use strict';

    var $templateCache;

    /**
     * Customizes the datepicker template with the given name
     *
     * Swaps the glyphicon chevron icons with the equivalent font-awesome ones
     * Adds a horizontal line below the month name in the day template
     *
     * After the customization, it puts the html back in the template cache
     *
     * @param {string} name
     */
    function customizeDatepickerTpl(name) {
        var tplPath = 'uib/template/datepicker/' + name + '.html';
        var tpl = $templateCache.get(tplPath);

        tpl = tpl.replace(/glyphicon glyphicon-chevron-(left|right)/gm, 'fa fa-chevron-$1');

        if (name === 'day') {
            tpl = (function ($tpl) {
                jQuery($tpl).find('th[ng-if="showWeeks"]').parent().css({ 'border-top': '1px solid #DDD' });

                return jQuery($tpl).prop('outerHTML');
            })(jQuery.parseHTML(tpl));
        }

        $templateCache.put(tplPath, tpl);
    }

    /**
     * Provides decorator for the datepicker popup (sets default config) and date filter (adds suppported formats)
     * It also customizes the datepicker templates
     */
    return angular.module("common.angularDate", ['common.services', 'ui.bootstrap', 'common.templates']).config([
        '$provide', function ($provide) {
            $provide.decorator('uibDatepickerPopupDirective', datepickerPopup);
            $provide.decorator('dateFilter', dateFilter);
        }
    ]).run(['$templateCache', function (_$templateCache_) {
        $templateCache = _$templateCache_;

        ['day', 'month', 'year'].forEach(customizeDatepickerTpl);
    }]);
});
