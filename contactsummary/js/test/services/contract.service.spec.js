/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function (angular, _) {
  'use strict';

  describe('contractService', function () {
    var apiServiceMock, contactDetailsServiceMock, contractService,
       modelServiceMock, $rootScope;

    beforeEach(module('contactsummary', 'contactsummary.mocks',
      'contact-summary.templates'));

    beforeEach(module(function ($provide) {
      $provide.constant('settings', {
        CRM: { options: { 'HRJobDetails': { 'fieldName': 'fieldValues' } } }
      });

      $provide.factory('apiService', function () {
        return apiServiceMock;
      });

      $provide.factory('modelService', function () {
        return modelServiceMock;
      });

      $provide.factory('contactDetailsService', function () {
        return contactDetailsServiceMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      apiServiceMock = $injector.get('apiServiceMock');
      contactDetailsServiceMock = $injector.get('contactDetailsServiceMock');
      modelServiceMock = $injector.get('modelServiceMock');
      $rootScope = $injector.get('$rootScope');
    }));

    beforeEach(inject(function (_contractService_) {
      contractService = _contractService_;
    }));

    describe('get()', function () {
      var contracts;
      var expectedContracts = {
        values: [
          {api_HRJobContractRevision_getcurrentrevision: {values: {id: '4'}}, deleted: '0', id: '1', is_current: '1'}
        ]
      };
      var expectedContractDetails = {
        values: [{
          api_HRJobHour_get: {
            values: []
          },
          api_HRJobPay_get: {
            values: []
          },
          title: 'Project Manager',
          period_start_date: '2015-01-31',
          period_end_date: '2015-11-01'
        }]
      };

      beforeEach(function () {
        apiServiceMock.respondGet('HRJobContract', expectedContracts);
        apiServiceMock.respondGet('HRJobDetails', expectedContractDetails);
        contactDetailsServiceMock.respond('get', { id: 123 });

        contractService.get().then(function (response) {
          contracts = response;
        });

        $rootScope.$digest();

        apiServiceMock.flush();
        contactDetailsServiceMock.flush();
      });

      it('should return contracts', function () {
        expect(angular.isObject(contracts)).toBe(true);
      });

      describe('a contract', function () {
        it('should have the required fields', function () {
          expect(_.size(contracts)).toBeGreaterThan(0);

          angular.forEach(contracts, function (contract) {
            expect(contract.start_date).toBeDefined();
            expect(contract.end_date).toBeDefined();
            expect(contract.title).toBeDefined();
          });
        });
      });

      describe('getOptions()', function () {
        var options;

        describe('when options are fetched without field name', function () {
          beforeEach(function () {
            options = contractService.getOptions();

            $rootScope.$digest();
          });

          it('returns the list of all contract options', function () {
            expect(options).toEqual({ details: { fieldName: 'fieldValues' } });
          });
        });

        describe('when options are fetched with specific field name', function () {
          beforeEach(function () {
            options = contractService.getOptions('fieldName');

            $rootScope.$digest();
          });

          it('returns the list of contract options for given field name only', function () {
            expect(options).toEqual({ details: 'fieldValues' });
          });
        });
      });

      describe('removeContract()', function () {
        beforeEach(function () {
          contractService.removeContract('1');
        });

        it('removes the contract form the list', function () {
          expect(_.isEmpty(contractService.collection.get())).toBe(true);
        });
      });
    });
  });
});
