define([
  'angularMocks',
  'app',
  'mocks/module',
  'mocks/constants',
  'mocks/services',
  'controllers/contactSummary'
], function () {
  'use strict';

  describe('ContactSummaryCtrl', function () {
    var ctrlConstructor;
    var keyDetailsServiceMock, keyDatesServiceMock, settingsMock;

    beforeEach(module('contactsummary', 'contactsummaryMocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('KeyDetailsService', function () {
        return keyDetailsServiceMock;
      });
      $provide.factory('KeyDatesService', function () {
        return keyDatesServiceMock;
      });
      $provide.value('settings', function () {
        return settingsMock;
      });
    }));

    beforeEach(function () {
      inject(function ($injector) {
        // Instantiating injector isn't allowed before calls to 'module()'. Due to this limitation, we're returning
        // references to mock variables above, and injecting the actual mocks into them here, since they would be
        // lazily loaded anyway.
        keyDetailsServiceMock = $injector.get('KeyDetailsServiceMock');
        keyDatesServiceMock = $injector.get('KeyDatesServiceMock');
        settingsMock = $injector.get('settingsMock');
      });
    });

    beforeEach(inject(function (_$controller_) {
      ctrlConstructor = _$controller_;
    }));

    it('should call "get" on KeyDetailsService', inject(function () {
      ctrlConstructor('ContactSummaryCtrl');
      expect(keyDetailsServiceMock.get).toHaveBeenCalled();
    }));

    it('should have key details', function () {
      keyDetailsServiceMock.get.and.returnValue({});
      var ctrl = ctrlConstructor('ContactSummaryCtrl');
      expect(ctrl.keyDetails).toBeDefined();
    });

    it('should have key dates', function () {
      keyDatesServiceMock.get.and.returnValue([]);
      var ctrl = ctrlConstructor('ContactSummaryCtrl');
      expect(ctrl.keyDates).toBeDefined();
    });
  });
});
