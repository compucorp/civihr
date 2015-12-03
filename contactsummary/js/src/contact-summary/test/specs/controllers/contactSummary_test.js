define([
  'common/angularMocks',
  'contact-summary/app',
  'contact-summary/modules/mocks',
  'contact-summary/mocks/constants',
  'contact-summary/mocks/services',
  'contact-summary/controllers/contactSummary'
], function () {
  'use strict';

  xdescribe('ContactSummaryCtrl', function () {
    var ctrlConstructor;
    var KeyDetailsServiceMock, KeyDatesServiceMock, settingsMock;
    var rootScope;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('KeyDetailsService', function () {
        return KeyDetailsServiceMock;
      });
      $provide.factory('KeyDatesService', function () {
        return KeyDatesServiceMock;
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
      KeyDatesServiceMock = $injector.get('KeyDatesServiceMock');
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

    describe('key dates', function () {
      it('should call "get" on KeyDatesService', inject(function () {
        ctrlConstructor('ContactSummaryCtrl');
        expect(KeyDatesServiceMock.get).toHaveBeenCalled();
      }));

      it('should have the expected key dates', function () {
        var expectedResult = [{data: true}];

        KeyDatesServiceMock.resolvePromise = true;
        KeyDatesServiceMock.respond('get', expectedResult);

        var ctrl = ctrlConstructor('ContactSummaryCtrl');

        // Run the digest loop manually, in order to resolve the promise in KeyDatesServiceMock.get()
        rootScope.$digest();

        expect(ctrl.keyDates).toEqual(expectedResult);

        // Reset expectations of the mock
        KeyDatesServiceMock.flush();
      });
    });
  });
});
