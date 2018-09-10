/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/contract.data',
  'common/angularMocks',
  'common/services/api/contract',
  'common/mocks/services/api/contract-mock'
], function (_, jobContractData) {
  'use strict';

  describe('api.contract', function () {
    var JobContractAPI, $rootScope, $q;

    beforeEach(module('common.apis', 'common.mocks'));

    beforeEach(inject(['api.contract', '$rootScope', '$q',
      function (_JobContractAPI_, _$rootScope_, _$q_) {
        JobContractAPI = _JobContractAPI_;
        $rootScope = _$rootScope_;
        $q = _$q_;
      }]));

    describe('getContactsWithContractsInPeriod()', function () {
      var result;
      var sampleDates = {
        start: '2018-01-01',
        end: '2018-01-31'
      };

      beforeEach(function (done) {
        spyOn(JobContractAPI, 'sendGET').and.returnValue(
          $q.resolve(jobContractData.contactsWithContractsInPeriod));
        JobContractAPI.getContactsWithContractsInPeriod(
          sampleDates.start, sampleDates.end)
          .then(function (_result_) {
            result = _result_;
          })
          .finally(function () {
            done();
          });
        $rootScope.$digest();
      });

      it('calls an according API endpoint', function () {
        expect(JobContractAPI.sendGET).toHaveBeenCalledWith(
          'HRJobContract', 'getcontactswithcontractsinperiod', {
            start_date: sampleDates.start,
            end_date: sampleDates.end
          });
      });

      it('returns the collections of contacts', function () {
        expect(result).toEqual(jobContractData.contactsWithContractsInPeriod.values);
      });
    });
  });
});
