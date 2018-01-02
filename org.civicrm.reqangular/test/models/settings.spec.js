/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/models/settings.model',
  'common/services/api/settings.api',
  'common/mocks/services/api/settings-mock'
], function () {
  'use strict';

  describe('Settings', function () {
    var $provide, $rootScope, Settings, settingsAPI, SettingsAPIMock, settingPromise;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject([
        'api.settings.mock',
        function (_SettingsAPIMock_) {
          SettingsAPIMock = _SettingsAPIMock_;
          $provide.value('api.settings', _SettingsAPIMock_);
        }
      ]);
    });

    beforeEach(inject([
      '$rootScope', 'Settings', 'api.settings',
      function (_$rootScope_, _Settings_, _settingsAPI_) {
        $rootScope = _$rootScope_;
        Settings = _Settings_;
        settingsAPI = _settingsAPI_;

        settingsAPI.spyOnMethods();
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(Settings)).toEqual(['get', 'fetchSeparators']);
    });

    describe('get()', function () {
      beforeEach(function () {
        settingPromise = Settings.get();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls according method', function () {
        settingPromise.then(function (response) {
          expect(settingsAPI.get).toHaveBeenCalled();
        });
      });
    });

    describe('fetchSeparators()', function () {
      beforeEach(function () {
        settingPromise = Settings.fetchSeparators();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls according method', function () {
        settingPromise.then(function (response) {
          expect(response.decimal).toBe(SettingsAPIMock.mockedSettings()[0].monetaryDecimalPoint);
          expect(response.thousand).toBe(SettingsAPIMock.mockedSettings()[0].monetaryThousandSeparator);
        });
      });
    });
  });
});
