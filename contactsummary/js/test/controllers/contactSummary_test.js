define([
    'common/angularMocks',
    'contact-summary/app',
    'contact-summary/controllers/contactSummary',
    'mocks/constants',
    'mocks/services'
], function () {
    'use strict';

    xdescribe('ContactSummaryCtrl', function () {
        var ctrlConstructor;
        var KeyDetailsServiceMock, settingsMock;
        var rootScope;

        beforeEach(module('contactsummary', 'contactsummary.mocks'));

        beforeEach(module(function ($provide) {
            $provide.factory('KeyDetailsService', function () {
                return KeyDetailsServiceMock;
            });
            $provide.value('settings', function () {
                return settingsMock;
            });
        }));

        beforeEach(inject(function ($injector) {
            // Instantiating injector isn't allowed before calls to 'module()'. Due to this limitation, we're returning
            // references to mock variables above, and injecting the actual mocks into them here, since they would be
            // lazily loaded anyway.
            KeyDetailsServiceMock = $injector.get('KeyDetailsServiceMock');
            settingsMock = $injector.get('settingsMock');
            rootScope = $injector.get('$rootScope');
        }));

        beforeEach(inject(function (_$controller_) {
            ctrlConstructor = _$controller_;
        }));

        describe('key details', function () {
            it('should call "get" on KeyDetailsService', inject(function () {
                ctrlConstructor('ContactSummaryCtrl');
                expect(KeyDetailsServiceMock.get).toHaveBeenCalled();
            }));

            it('should have the expected key details', function () {
                var expectedResult = {data: true};

                KeyDetailsServiceMock.respond('get', expectedResult);

                var ctrl = ctrlConstructor('ContactSummaryCtrl');

                // Run the digest loop manually, in order to resolve the promise in KeyDetailsServiceMock.get()
                rootScope.$digest();

                expect(ctrl.keyDetails).toEqual(expectedResult);

                // Reset expectations of the mock
                KeyDetailsServiceMock.flush();
            });
        });
    });
});
