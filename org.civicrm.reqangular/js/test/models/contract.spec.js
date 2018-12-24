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
    var $provide, $q, $rootScope, Contract, ContractInstance, contractAPI, contractAPIMock;

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

    beforeEach(inject(['api.contract', function (_contractAPI_) {
      contractAPI = _contractAPI_;
    }]));

    beforeEach(inject(function (_$q_, _$rootScope_, _Contract_, _ContractInstance_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      Contract = _Contract_;
      ContractInstance = _ContractInstance_;
    }));

    it('has the expected api', function () {
      expect(Object.keys(Contract)).toEqual(['all', 'activeForContact']);
    });

    describe('all()', function () {
      var contractPromise;
      var params = { contact_id: '202' };

      beforeEach(function () {
        contractAPI.spyOnMethods();

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

    describe('activeForContact()', function () {
      var contract1, contract2, result;
      var contactId = '44';

      beforeEach(function (done) {
        contract1 = ContractInstance.init({ id: '50', is_current: '1' });
        contract2 = ContractInstance.init({ id: '51', is_current: '1' });

        spyOn(Contract, 'all').and.returnValue($q.resolve([
          { id: '49', is_current: '0' },
          contract1,
          contract2,
          { id: '52', is_current: '0' }
        ]));
        Contract
          .activeForContact(contactId)
          .then(function (_result_) {
            result = _result_;
          })
          .finally(done);
        $rootScope.$digest();
      });

      it('fetches all contracts for a contact', function () {
        expect(Contract.all).toHaveBeenCalledWith({ contact_id: contactId });
      });

      it('returns contracts instances', function () {
        expect(result.length).toBe(2);
        expect(result[0]).toBe(contract1);
        expect(result[1]).toBe(contract2);
      });
    });
  });
});
