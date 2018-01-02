/* eslint-env amd, jasmine */

define([
  'job-contract/modules/job-contract.module'
], function () {
  'use strict';

  describe('FormPayController', function () {
    var $controller, $rootScope, $scope, FormatCurrencyService, settings,
      deferred, expectedValues, defaultValues;

    beforeEach(module('job-contract'));

    beforeEach(inject(function (_$controller_, _$rootScope_, $q, _settings_, _FormatCurrencyService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      FormatCurrencyService = _FormatCurrencyService_;
      settings = _settings_;
      deferred = $q.defer();

      defaultValues = {
        formatted: '0,00',
        unformatted: '0.00'
      };
      expectedValues = {
        formatted: "24'456'654,88",
        unformatted: '24456654.88'
      };

      initController();
    }));

    describe('calcAnnualPayEst', function () {
      beforeEach(function () {
        sypFormatCurrencyService(expectedValues);
        $scope.calcAnnualPayEst();
        $scope.$apply();
      });

      it('formats annual pay amount', function () {
        expect($scope.entity.pay.pay_annualized_est).toBe("24'456'654,88");
      });
    });

    describe('calcBenefitsPerCycleNet', function () {
      beforeEach(function () {
        sypFormatCurrencyService(expectedValues);
        $scope.calcBenefitsPerCycleNet();
        $scope.$apply();
      });

      it('formats benefits per cycle pay', function () {
        expect($scope.entity.pay.benefits_per_cycle_net).toBe("24'456'654,88");
      });
    });

    describe('calcPayPerCycleGross', function () {
      beforeEach(function () {
        sypFormatCurrencyService(expectedValues);
        $scope.calcPayPerCycleGross();
        $scope.$apply();
      });

      it('formats pay per cycle gross', function () {
        expect($scope.entity.pay.pay_per_cycle_gross).toBe("24'456'654,88");
      });
    });

    describe('calcPayPerCycleNet', function () {
      beforeEach(function () {
        sypFormatCurrencyService(expectedValues);
        $scope.calcPayPerCycleNet();
        $scope.$apply();
      });

      it('formats the pay per cycle', function () {
        expect($scope.entity.pay.pay_per_cycle_net).toBe("24'456'654,88");
      });
    });

    describe('setDefaults', function () {
      beforeEach(function () {
        sypFormatCurrencyService(defaultValues);
        $scope.setDefaults();
        $scope.$apply();
      });

      it('gets degault value for format curreny service', function () {
        expect(FormatCurrencyService.format).toHaveBeenCalled();
      });

      it('sets default values form format currency service', function () {
        expect($scope.entity.pay.pay_amount).toBe(defaultValues.formatted);
        expect($scope.entity.pay.pay_annualized_est).toBe(defaultValues.formatted);
        expect($scope.entity.pay.pay_per_cycle_gross).toBe(defaultValues.formatted);
        expect($scope.entity.pay.pay_per_cycle_net).toBe(defaultValues.formatted);
      });
    });

    /**
     * Initializes the form controller
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.utils = {
        payScaleGrade: 'weekly'
      };
      $scope.entity = {
        'pay': {
          'is_paid': 1,
          'pay_is_auto_est': true,
          'pay_annualized_est': 0,
          'annual_benefits': 0,
          'annual_deductions': 0,
          'pay_amount': 0
        }
      };
      $controller('FormPayController', {
        $scope: $scope,
        settings: settings,
        FormatCurrencyService:
        FormatCurrencyService
      });
    }

    /**
     * Create  a spy to resolve with expected values
     *
     * @param  {Object} expectedValues
     * @return {Promise}
     */
    function sypFormatCurrencyService (expectedValues) {
      spyOn(FormatCurrencyService, 'format').and.callFake(function () {
        deferred.resolve(expectedValues);

        return deferred.promise;
      });
    }
  });
});
