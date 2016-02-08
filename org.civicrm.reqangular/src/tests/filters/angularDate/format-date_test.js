define([
    'common/angular',
    'common/moment',
    'common/angularMocks',
    'common/filters/angularDate/format-date',
    'common/services/angularDate/date-format',
    'common/services/settings/hr-settings'
], function (angular, moment) {
    'use strict';

    describe('angularDate: FormatDateFilter Unit Test', function () {
        var DateFormat, HR_settings, $filter;

        beforeEach(module('common.angularDate'));

        beforeEach(inject(['DateFormat', 'HR_settings', '$filter',
            function (_DateFormat, _HR_settings, _$filter) {
                DateFormat = _DateFormat;
                HR_settings = _HR_settings;
                $filter = _$filter;
            }
        ]));

        it('FormatDateFilter should be defined', function () {
            expect($filter('formatDate')).toBeDefined();
        });

        it('FormatDateFilter fallbacks to "YYYY-MM-DD" format if both format parameter and HR_settings.DATE_FORMAT are not set', function () {
            expect($filter('formatDate')(moment())).toEqual(moment().format('YYYY-MM-DD'));
        });

        it('Format parameter has the highest priority', function () {
            expect($filter('formatDate')(moment(), 'D/M/YY')).toEqual(moment().format('D/M/YY'));
        });

        it('Handle not set & invalid date', function () {
            expect($filter('formatDate')('')).toEqual('Unspecified');
            expect($filter('formatDate')('29/02/2011')).toEqual('Unspecified');
            expect($filter('formatDate')('0000-00-00 00:00:00')).toEqual('Unspecified');
            expect($filter('formatDate')('testString')).toEqual('Unspecified');
            expect($filter('formatDate')(undefined)).toEqual('Unspecified');
            expect($filter('formatDate')(null)).toEqual('Unspecified');
        });

        it('Should Handle number of valid formats', function () {
            // Moment.js Object
            expect($filter('formatDate')(moment())).toEqual(moment().format('YYYY-MM-DD'));
            // Date Object
            expect($filter('formatDate')(new Date())).toEqual(moment().format('YYYY-MM-DD'));
            // YYYY-MM-DD string
            expect($filter('formatDate')(moment().format('YYYY-MM-DD'))).toEqual(moment().format('YYYY-MM-DD'));
            // DD/MM/YYYY string
            expect($filter('formatDate')(moment().format('DD/MM/YYYY'))).toEqual(moment().format('YYYY-MM-DD'));
            // timestamp (miliseconds)
            expect($filter('formatDate')(moment().valueOf())).toEqual(moment().format('YYYY-MM-DD'));
        });

        describe('Async Behaviour', function () {
            beforeEach(function () {
                // Imitate route resolve
                spyOn(DateFormat, 'getDateFormat').and.callFake(function () {
                    return {
                        then: function (callback) {
                            return callback('DD/MM/YYYY');
                        }
                    };
                });

                DateFormat.getDateFormat().then(function (result) {
                    HR_settings.DATE_FORMAT = result;
                });
            });

            it('Async call finished', function(){
                expect(DateFormat.getDateFormat).toHaveBeenCalled();
            });

            it('Date Format is correctly saved to HR_settings service', function(){
                expect(HR_settings.DATE_FORMAT).toEqual('DD/MM/YYYY');
            });

            it('When no format parameter is given, filter respects the one from HR_settings', function(){
                expect($filter('formatDate')(moment())).toEqual(moment().format('DD/MM/YYYY'));
            });

            it('Parameter should have the heighest priority', function(){
                expect($filter('formatDate')(moment(), 'MM-DD-YY')).toEqual(moment().format('MM-DD-YY'));
            });
        });
    });
});
