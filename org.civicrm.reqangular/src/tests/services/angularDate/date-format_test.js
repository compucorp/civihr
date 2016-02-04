define([
    'common/angular',
    'common/angularMocks',
    'common/services/angularDate/date-format',
    'common/services/settings/hr-settings'
], function () {
    'use strict';

    describe('angularDate: DateFormatService Unit Test', function () {
        var DateFormatService, HR_settings;


        beforeEach(module('common.angularDate', 'common.settings'));
        beforeEach(inject(['DateFormatService', 'HR_settings',
            function (_DateFormatService, _HR_settings) {
                DateFormatService = _DateFormatService;
                HR_settings = _HR_settings;
            }
        ]));

        it('DateFormatService to be defined', function () {
            expect(DateFormatService).toBeDefined();
        });

        it('DateFormatService to be defined', function () {
            expect(DateFormatService.dateFormat).toBe(null);
        });
        
    });
});
