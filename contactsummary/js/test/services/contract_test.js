/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'contact-summary/app',
  'contact-summary/services/contract',
  'mocks/services'
], function (angular, _) {
  'use strict';

  describe('ContractService', function () {
    var ContractService,
      ApiServiceMock, ContactDetailsServiceMock, ModelServiceMock,
      rootScope;

    beforeEach(module('contactsummary', 'contactsummary.mocks',
      'contact-summary.templates'));

    beforeEach(module(function ($provide) {
      $provide.factory('ApiService', function () {
        return ApiServiceMock;
      });

      $provide.factory('ModelService', function () {
        return ModelServiceMock;
      });

      $provide.factory('ContactDetailsService', function () {
        return ContactDetailsServiceMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      ApiServiceMock = $injector.get('ApiServiceMock');
      ContactDetailsServiceMock = $injector.get('ContactDetailsServiceMock');
      ModelServiceMock = $injector.get('ModelServiceMock');
      rootScope = $injector.get('$rootScope');
    }));

    beforeEach(inject(function (_ContractService_) {
      ContractService = _ContractService_;
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
        ApiServiceMock.respondGet('HRJobContract', expectedContracts);
        ApiServiceMock.respondGet('HRJobDetails', expectedContractDetails);
        ContactDetailsServiceMock.respond('get', {id: 123});

        ContractService.get().then(function (response) {
          contracts = response;
        });

        rootScope.$digest();

        ApiServiceMock.flush();
        ContactDetailsServiceMock.flush();
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
    });
  });
});
