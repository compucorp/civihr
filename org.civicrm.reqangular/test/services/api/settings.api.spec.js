/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/services/api/settings.api',
  'common/mocks/services/api/settings-mock'
], function () {
  'use strict';

  describe('api.settings', function () {
    var $q, $rootScope, settingsAPI, SettingsAPIMock;

    beforeEach(module('common.apis', 'common.mocks'));

    beforeEach(inject(['$q', '$rootScope', 'api.settings', 'api.settings.mock',
      function (_$q_, _$rootScope_, _settingsAPI_, _SettingsAPIMock_) {
        settingsAPI = _settingsAPI_;
        SettingsAPIMock = _SettingsAPIMock_;
        $rootScope = _$rootScope_;
        $q = _$q_;
      }
    ]));

    it('has expected interface', function () {
      expect(Object.keys(settingsAPI)).toContain('get');
    });

    describe('get()', function () {
      var settingsApiPromise;

      beforeEach(function () {
        spyOn(settingsAPI, 'get').and.returnValue($q.resolve(SettingsAPIMock.get()));
        settingsApiPromise = settingsAPI.get();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns all defined settings', function () {
        settingsApiPromise.then(function (result) {
          expect(result).toEqual(SettingsAPIMock.mockedSettings());
        });
      });

      it('calls get() method', function () {
        expect(settingsAPI.get).toHaveBeenCalledWith();
      });
    });
  });
});
