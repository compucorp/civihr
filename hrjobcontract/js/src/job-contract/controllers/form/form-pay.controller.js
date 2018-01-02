/* eslint-env amd */

define(function () {
  'use strict';

  FormPayController.__name = 'FormPayController';
  FormPayController.$inject = ['$filter', '$log', '$scope', 'settings', 'FormatCurrencyService'];

  function FormPayController ($filter, $log, $scope, settings, FormatCurrencyService) {
    $log.debug('Controller: FormPayController');

    var calcBenefitsPerCycleNetAmount = 0;
    var calcPayPerCycleGrossAmount = 0;
    var entityPay = $scope.entity.pay || {};
    var defaults = {
      pay_amount: 0,
      pay_currency: settings.CRM.defaultCurrency,
      pay_cycle: '2',
      pay_unit: 'Year'
    };
    var utilsPayScaleGrade = $scope.utils.payScaleGrade;
    var workPerYear = {
      Year: 1,
      Month: 12,
      Bimonthly: 24,
      Week: 52,
      Biweekly: 104,
      Fortnight: 26,
      Day: 260,
      Hour: 2080
    };

    $scope.collapseBenDed = !entityPay.annual_benefits.length && !entityPay.annual_deductions.length;
    $scope.benefits_per_cycle = (0).toFixed(2);
    $scope.benefits_per_cycle_net = 0;
    $scope.deductions_per_cycle = (0).toFixed(2);
    $scope.calcPayAnnualizedEst = 0;

    $scope.add = add;
    $scope.applyPayScaleGradeData = applyPayScaleGradeData;
    $scope.calcAnnualPayEst = calcAnnualPayEst;
    $scope.calcBenefitsPerCycle = calcBenefitsPerCycle;
    $scope.calcBenefitsPerCycleNet = calcBenefitsPerCycleNet;
    $scope.calcDeductionsPerCycle = calcDeductionsPerCycle;
    $scope.calcPayPerCycleGross = calcPayPerCycleGross;
    $scope.calcPayPerCycleNet = calcPayPerCycleNet;
    $scope.onPayAmountChange = onPayAmountChange;
    $scope.remove = remove;
    $scope.resetData = resetData;
    $scope.setDefaults = setDefaults;

    (function init () {
      entityPay.is_paid = entityPay.is_paid || 0;
      entityPay.pay_is_auto_est = '0';
      entityPay.annual_benefits = entityPay.annual_benefits || [];
      entityPay.annual_deductions = entityPay.annual_deductions || [];

      initWatchers();
    }());

    function add (array) {
      array.push({
        'name': '',
        'type': '',
        'amount_pct': '',
        'amount_abs': ''
      });
    }

    function applyPayScaleGradeData () {
      if (entityPay.pay_scale) {
        var payScaleGrade = $filter('getObjById')(utilsPayScaleGrade, entityPay.pay_scale);
        entityPay.pay_amount = payScaleGrade.amount || defaults.pay_amount;
        entityPay.pay_currency = payScaleGrade.currency || defaults.pay_currency;
        entityPay.pay_unit = payScaleGrade.pay_frequency || defaults.pay_unit;
      }
    }

    function calcAnnualPayEst () {
      if (+entityPay.is_paid) {
        FormatCurrencyService.format(entityPay.pay_amount).then(function (amount) {
          $scope.calcPayAnnualizedEst = ((+amount.parsed) * (workPerYear[entityPay.pay_unit] || 0)).toFixed(2);
          entityPay.pay_annualized_est = amount.formatted;
        });
      }
    }

    function calcBenefitsPerCycle () {
      if (+entityPay.is_paid) {
        var i = 0;
        var len = entityPay.annual_benefits.length;
        var annualBenefits = 0;

        for (i; i < len; i++) {
          if (+entityPay.annual_benefits[i].type === 2) {
            entityPay.annual_benefits[i].amount_abs = (entityPay.annual_benefits[i].amount_pct / 100 * $scope.calcPayAnnualizedEst).toFixed(2);
          }

          annualBenefits += +entityPay.annual_benefits[i].amount_abs;
        }
        $scope.benefits_per_cycle = (annualBenefits / getCycles()).toFixed(2);
      }
    }

    function calcBenefitsPerCycleNet () {
      if (+entityPay.is_paid) {
        calcBenefitsPerCycleNetAmount = ($scope.benefits_per_cycle - $scope.deductions_per_cycle);

        FormatCurrencyService.format(calcBenefitsPerCycleNetAmount).then(function (amount) {
          entityPay.benefits_per_cycle_net = amount.formatted;
        });
      }
    }

    function calcDeductionsPerCycle () {
      if (+entityPay.is_paid) {
        var i = 0;
        var len = entityPay.annual_deductions.length;
        var annualDeductions = 0;

        for (i; i < len; i++) {
          if (+entityPay.annual_deductions[i].type === 2) {
            entityPay.annual_deductions[i].amount_abs = (entityPay.annual_deductions[i].amount_pct / 100 * $scope.calcPayAnnualizedEst).toFixed(2);
          }

          annualDeductions += +entityPay.annual_deductions[i].amount_abs;
        }
        $scope.deductions_per_cycle = (annualDeductions / getCycles()).toFixed(2);
      }
    }

    function calcPayPerCycleGross () {
      if (+entityPay.is_paid) {
        calcPayPerCycleGrossAmount = ($scope.calcPayAnnualizedEst / getCycles()).toFixed(2);

        FormatCurrencyService.format(calcPayPerCycleGrossAmount).then(function (amount) {
          entityPay.pay_per_cycle_gross = amount.formatted;
        });
      }
    }

    function calcPayPerCycleNet () {
      if (+entityPay.is_paid) {
        var calcPayPerCycleNet = (+calcPayPerCycleGrossAmount + +calcBenefitsPerCycleNetAmount).toFixed(2);

        FormatCurrencyService.format(calcPayPerCycleNet).then(function (amount) {
          entityPay.pay_per_cycle_net = amount.formatted;
        });
      }
    }

    /**
     * Display formatted pay amount in field as per the settings
     */
    function formatPayAmount () {
      FormatCurrencyService.format(entityPay.pay_amount).then(function (amount) {
        entityPay.pay_amount = amount.formatted;
      });
    }

    function getCycles () {
      var cycles = 1;

      switch (+entityPay.pay_cycle) {
        case 1:
          cycles = workPerYear.Week;
          break;
        case 2:
          cycles = workPerYear.Month;
          break;
        case 3:
          cycles = workPerYear.Biweekly;
          break;
        case 4:
          cycles = workPerYear.Bimonthly;
          break;
      }

      return cycles;
    }

    function initWatchers () {
      $scope.$watch('entity.pay.pay_unit', $scope.onPayAmountChange);
      $scope.$watch('calcPayAnnualizedEst', function () {
        $scope.calcPayPerCycleGross();
        $scope.calcBenefitsPerCycle();
        $scope.calcDeductionsPerCycle();
      });
      $scope.$watch('entity.pay.annual_benefits', $scope.calcBenefitsPerCycle, true);
      $scope.$watch('entity.pay.annual_deductions', $scope.calcDeductionsPerCycle, true);
      $scope.$watch('benefits_per_cycle', $scope.calcBenefitsPerCycleNet);
      $scope.$watch('deductions_per_cycle', $scope.calcBenefitsPerCycleNet);
      $scope.$watch('benefits_per_cycle_net', $scope.calcPayPerCycleNet);
      $scope.$watch('entity.pay.pay_per_cycle_gross', $scope.calcPayPerCycleNet);
    }

    /**
     * Handler for changed pay amount value in pay form field
     */
    function onPayAmountChange () {
      formatPayAmount();
      $scope.calcAnnualPayEst();
    }

    function remove (array, index) {
      array.splice(index, 1);
    }

    function resetData () {
      entityPay.pay_scale = '';
      entityPay.pay_amount = '';
      entityPay.pay_unit = '';
      entityPay.pay_currency = '';
      entityPay.pay_annualized_est = '';
      entityPay.pay_is_auto_est = '';
      entityPay.annual_benefits = [];
      entityPay.annual_deductions = [];
      entityPay.pay_cycle = '';
      entityPay.pay_per_cycle_gross = '';
      entityPay.pay_per_cycle_net = '';
      $scope.benefits_per_cycle = '';
      $scope.deductions_per_cycle = '';
    }

    function setDefaults () {
      entityPay.pay_currency = defaults.pay_currency;
      entityPay.pay_cycle = defaults.pay_cycle;
      entityPay.pay_is_auto_est = '0';
      calcPayPerCycleGrossAmount = '0';
      calcBenefitsPerCycleNetAmount = '0';
      FormatCurrencyService.format('0')
        .then(function (amount) {
          var defaultAmount = amount.formatted;

          entityPay.pay_amount = defaultAmount;
          entityPay.pay_annualized_est = defaultAmount;
          entityPay.pay_per_cycle_gross = defaultAmount;
          entityPay.pay_per_cycle_net = defaultAmount;
        });
    }
  }

  return FormPayController;
});
