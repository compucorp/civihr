define([
    'common/angularMocks',
    'contact-summary/app',
    'contact-summary/mocks/services',
    'contact-summary/services/keyDetails'
], function () {
    'use strict';

    xdescribe('KeyDetailsService', function () {
        var settingsMock;

        beforeEach(module('contactsummary', 'contactsummary.mocks'));

        beforeEach(module(function ($provide) {
            $provide.value('settings', function () {
                return settingsMock;
            });
        }));

        beforeEach(inject(function ($injector) {
            settingsMock = $injector.get('settingsMock');
        }));

        describe('get', function () {
            var service, details;

            beforeEach(inject(function (_KeyDetailsService_) {
                service = _KeyDetailsService_;
                details = service.get();
            }));

            it('should have age', function () {
                expect(details.age).toBeDefined();
            });

            it('should have date of birth', function () {
                expect(details.dateOfBirth).toBeDefined();
            });

            it('should have length of service', function () {
                expect(details.lengthOfService).toBeDefined();
            });

            it('should have government ID', function () {
                expect(details.governmentId).toBeDefined();
            });

            it('should have contract type', function () {
                expect(details.contractType).toBeDefined();
            });

            it('should have gross annual pay', function () {
                expect(details.grossAnnualPay).toBeDefined();
            });

            it('should have hours', function () {
                expect(details.hours).toBeDefined();
            });
        });
    });
});
