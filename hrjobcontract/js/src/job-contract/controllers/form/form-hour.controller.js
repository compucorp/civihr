/* eslint-env amd */

define([
  'common/lodash',
  'job-contract/vendor/fraction'
], function (_, Fraction) {
  'use strict';

  FormHourController.$inject = ['$log', '$filter', '$rootScope', '$scope'];

  function FormHourController ($log, $filter, $rootScope, $scope) {
    $log.debug('Controller: FormHourController');

    var entityHour = $scope.entity.hour;
    var utilsHoursLocation = $scope.utils.hoursLocation;
    var locStandHrs = {};
    var hourTypeMapping = {
      0: 'CASUAL',
      4: 'PART_TIME',
      8: 'FULL_TIME'
    };

    $scope.hrsTypeDefined = false;
    $scope.hrsAmountDefined = false;

    (function init () {
      entityHour.location_standard_hours = entityHour.location_standard_hours || _.get(utilsHoursLocation, '[0].id');
      locStandHrs = $filter('getObjById')(utilsHoursLocation, entityHour.location_standard_hours);

      initWatchers();
    }());

    function initWatchers () {
      $scope.$watch('entity.hour.location_standard_hours', function (locStandHrsId) {
        locStandHrs = $filter('getObjById')(utilsHoursLocation, locStandHrsId);
        updateHours(locStandHrs, entityHour.hours_type);
        updateFTE(locStandHrs.standard_hours, entityHour.hours_amount);
      });

      $scope.$watch('entity.hour.hours_type', function (hrsTypeId, hrsTypeIdPrev) {
        if (hrsTypeId !== hrsTypeIdPrev) {
          updateHours(locStandHrs, hrsTypeId);
          updateFTE(locStandHrs.standard_hours, entityHour.hours_amount);
        }
      });

      $scope.$watch('entity.hour.hours_amount', function (hrsAmount, hrsAmountPrev) {
        if (hrsAmount !== hrsAmountPrev) {
          updateFTE(locStandHrs.standard_hours, hrsAmount);
        }
      });

      $scope.$watch('entity.hour.hours_unit', function (hrsUnit, hrsUnitPrev) {
        if (hrsUnit !== hrsUnitPrev) {
          updateFTE(locStandHrs.standard_hours, entityHour.hours_amount);
        }
      });
    }

    function updateHours (locStandHrs, hrsTypeId) {
      $scope.hrsTypeDefined = !!entityHour.hours_type;
      $scope.hrsAmountDefined = !!entityHour.hours_amount;
      entityHour.hours_unit = locStandHrs.periodicity;

      // reset if hours are not defined or if new choice is "full time"
      if ($scope.hrsTypeDefined && (!$scope.hrsAmountDefined || hourTypeMapping[+hrsTypeId] === 'FULL_TIME')) {
        switch (hourTypeMapping[+hrsTypeId]) {
          case 'FULL_TIME':
            entityHour.hours_amount = locStandHrs.standard_hours;
            break;
          case 'PART_TIME':
            entityHour.hours_amount = Math.round(locStandHrs.standard_hours / 2);
            break;
          case 'CASUAL':
            entityHour.hours_amount = 0;
            break;
          default:
            entityHour.hours_amount = '';
        }
      } else if (!$scope.hrsAmountDefined && !$scope.hrsAmountDefined) {
        entityHour.hours_amount = '';
        entityHour.hours_unit = '';
      }
    }

    function updateFTE (hrsStandard, hrsAmount) {
      hrsAmount = parseFloat(hrsAmount) || 0;
      hrsStandard = parseFloat(hrsStandard) || 0;

      var fteFraction = new Fraction(hrsAmount, hrsStandard);

      entityHour.fte_num = String(+entityHour.hours_type ? fteFraction.numerator : 0);
      entityHour.fte_denom = String(+entityHour.hours_type ? fteFraction.denominator : 0);
      entityHour.hours_fte = String(parseFloat(((entityHour.fte_num / entityHour.fte_denom) || 0).toFixed(2)));

      $scope.fteFraction = entityHour.fte_num + '/' + entityHour.fte_denom;
    }
  }

  return { FormHourController: FormHourController };
});
