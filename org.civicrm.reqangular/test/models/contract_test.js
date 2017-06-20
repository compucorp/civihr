/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/contract',
  'common/mocks/services/hr-settings-mock',
  'common/mocks/services/api/contract-mock'
], function (_) {
  'use strict';

  describe('Contract', function () {
    var $provide, $rootScope, Contract,
      contractAPI, contractAPIMock;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject([
        'api.contract.mock', 'HR_settingsMock',
        function (_contractAPIMock_, HRSettingsMock) {
          contractAPIMock = _contractAPIMock_;
          $provide.value('api.contract', contractAPIMock);
          $provide.value('HR_settings', HRSettingsMock);
        }
      ]);
    });

    beforeEach(inject([
      '$rootScope', 'Contract', 'api.contract',
      function (_$rootScope_, _Contract_, _contractAPI_) {
        $rootScope = _$rootScope_;
        Contract = _Contract_;
        contractAPI = _contractAPI_;

        contractAPI.spyOnMethods();
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(Contract)).toEqual(['all']);
    });

    describe('all()', function () {
      var contractPromise;
      var params = { contact_id: '202' };

      beforeEach(function () {
        contractPromise = Contract.all();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns model instances', function () {
        contractPromise.then(function (response) {
          expect(response.every(function (modelInstance) {
            return 'init' in modelInstance;
          })).toBe(true);
        });
      });

      it('calls according method', function () {
        contractPromise.then(function (response) {
          expect(contractAPI.all).toHaveBeenCalled();
        });
      });

      it('accepts params', function () {
        Contract.all(params).then(function (response) {
          expect(contractAPI.all).toHaveBeenCalledWith(params);
        });
      });
    });
  });
});
