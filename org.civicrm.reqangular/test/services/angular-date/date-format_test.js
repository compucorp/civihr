define([
    'common/angular',
    'common/angularMocks',
    'common/services/angular-date/date-format',
    'common/services/hr-settings'
], function () {
    'use strict';

    describe('angularDate: DateFormat Unit Test', function () {
        var DateFormat, HR_settings, http;

        beforeEach(module('common.angularDate', 'common.services'));
        beforeEach(inject(['DateFormat', 'HR_settings', '$httpBackend',
            function (_DateFormat, _HR_settings, $httpBackend) {
                DateFormat = _DateFormat;
                HR_settings = _HR_settings;
                http = $httpBackend;
            }
        ]));

        it('DateFormat to be defined', function () {
            expect(DateFormat).toBeDefined();
        });

        it('Initial values should be null', function () {
            expect(DateFormat.dateFormat).toBe(null);
            expect(HR_settings.DATE_FORMAT).toBe(null);
        });

        describe('DateFormat - Async calls', function(){
            it('Should fetch Date format', function(){
                spyOn(DateFormat, 'getDateFormat').and.callFake(function() {
                    return {
                        then: function(callback) {
                            return callback('DD/MM/YYYY');
                        }
                    };
                });

                DateFormat.getDateFormat().then(function(result){
                    HR_settings.DATE_FORMAT = result;
                });

                expect(DateFormat.getDateFormat).toHaveBeenCalled();
                expect(HR_settings.DATE_FORMAT).toEqual('DD/MM/YYYY');
            });
        });
    });
});
