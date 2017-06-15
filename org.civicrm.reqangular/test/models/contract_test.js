/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/contract',
  'common/mocks/services/hr-settings-mock',
  'common/mocks/services/api/contract-mock',
  'common/mocks/models/instances/contract-instance-mock'
], function (_) {
  'use strict';

  describe('Contract', function () {
    var $provide;
    var $rootScope;
    var Contract;
    var ContractInstanceMock;
    var contractAPI;
    var contractAPIMock;
    var contracts;

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
      '$rootScope', 'Contract', 'ContractInstanceMock',
      'api.contract',
      function (_$rootScope_, _Contract_, _ContractInstanceMock_, _contractAPI_) {
        $rootScope = _$rootScope_;

        Contract = _Contract_;

        ContractInstanceMock = _ContractInstanceMock_;

        contractAPI = _contractAPI_;

        contractAPI.spyOnMethods();

        contracts = contractAPI.all();
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(Contract)).toEqual(['all']);
    });

    describe('all()', function () {
      describe('instances', function () {
        it('returns a list of model instances', function (done) {
          Contract.all().then(function (response) {
            expect(response.every(function (contract) {
              return ContractInstanceMock.isInstance(contract);
            })).toBe(true);
          }).finally(done) && $rootScope.$digest();
        });
      });

      describe('when called without arguments', function () {
        it('returns all contracts', function (done) {
          Contract.all().then(function (response) {
            expect(contractAPI.all).toHaveBeenCalled();
            expect(response.length).toEqual(contracts.$$state.value.length);
          }).finally(done) && $rootScope.$digest();
        });
      });
    });
  });
});
