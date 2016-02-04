define([
    'common/angular',
    'common/angularMocks',
    'common/services/angularDate/date-format',
    'common/services/settings/hr-settings'
], function () {
    'use strict';

    describe('angularDate: DateFormatService Unit Test', function () {
        var DateFormatService, HR_settings, http;

        beforeEach(module('common.angularDate', 'common.settings'));
        beforeEach(inject(['DateFormatService', 'HR_settings', '$httpBackend',
            function (_DateFormatService, _HR_settings, $httpBackend) {
                DateFormatService = _DateFormatService;
                HR_settings = _HR_settings;
                http = $httpBackend;
            }
        ]));

        it('DateFormatService to be defined', function () {
            expect(DateFormatService).toBeDefined();
        });

        it('Initial values should be null', function () {
            expect(DateFormatService.dateFormat).toBe(null);
            expect(HR_settings.DATE_FORMAT).toBe(null);
        });

        describe('DateFormatService - Async calls', function(){
            it('Should fetch Date format', function(){
                spyOn(DateFormatService, 'getDateFormat').and.callFake(function() {
                    return {
                        then: function(callback) {
                            return callback('DD/MM/YYYY');
                        }
                    };
                });

                DateFormatService.getDateFormat().then(function(result){
                    HR_settings.DATE_FORMAT = result;
                });

                expect(DateFormatService.getDateFormat).toHaveBeenCalled();
                expect(HR_settings.DATE_FORMAT).toEqual('DD/MM/YYYY');
            });
        });
    });
});
