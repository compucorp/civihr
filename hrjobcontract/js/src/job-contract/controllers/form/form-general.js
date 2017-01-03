define([
    'common/moment',
    'job-contract/controllers/controllers',
    'common/filters/angular-date/format-date'
], function (moment, controllers){
  'use strict';

  controllers.controller('FormGeneralCtrl',['$scope', '$log', 'HR_settings',
    function ($scope, $log, HR_settings) {
      $log.debug('Controller: FormGeneralCtrl');

      var entityDetails = $scope.entity.details;

      $scope.format = HR_settings.DATE_FORMAT;
      $scope.datepickerOptions = initDatepickerOptions();

      $scope.dpOpen = function($event, opened){
        $event.preventDefault();
        $event.stopPropagation();

        $scope[opened] = true;
      };

      $scope.$watch('entity.details.period_start_date', function(){
        $scope.datepickerOptions.end.minDate = getLimitDate(entityDetails.period_start_date, 'min');
        $scope.duration = duration(entityDetails.period_start_date, entityDetails.period_end_date);
      });

      $scope.$watch('entity.details.period_end_date', function(){
        if (entityDetails.period_end_date) {
          $scope.datepickerOptions.start.maxDate = getLimitDate(entityDetails.period_end_date, 'max');
        } else {
          $scope.datepickerOptions.start.maxDate = null;
          entityDetails.end_reason = null;
        }

        $scope.duration = duration(entityDetails.period_start_date, entityDetails.period_end_date);
      });

      $scope.$watch('entity.details.position', function(newVal, oldVal){
        if (newVal !== oldVal && entityDetails.title === oldVal) {
          $scope.contractForm.detailsTitle.$setViewValue(newVal);
          $scope.contractForm.detailsTitle.$render();
        }
      });

      $scope.$watch('entity.details.notice_amount', function(newVal, oldVal){
        if (+newVal && !entityDetails.notice_unit) {
          $scope.contractForm.detailsNoticeUnit.$setValidity('required', false);
          $scope.contractForm.detailsNoticeUnit.$dirty = true;
        }

        if (newVal !== oldVal && entityDetails.notice_amount_employee === oldVal) {
          entityDetails.notice_amount_employee = newVal;
        }
      });

      $scope.$watch('entity.details.notice_amount_employee', function(newVal){
        if (+newVal && !entityDetails.notice_unit_employee) {
          $scope.contractForm.detailsNoticeUnitEmployee.$setValidity('required', false);
          $scope.contractForm.detailsNoticeUnitEmployee.$dirty = true;
        }
      });

      $scope.$watch('entity.details.notice_unit', function(newVal, oldVal){
        if (newVal !== oldVal && entityDetails.notice_unit_employee === oldVal) {
          entityDetails.notice_unit_employee = newVal;
        }
      });

      /**
       * Calculates the duration of the period between the given start and end
       * dates, in years, months and days. Calculation includes both start and
       * end dates in duration.
       *
       * Special handling for month and year diffs is used by moment.js, 
       * optimized to ensure that duration from 1st day of month to last day of 
       * month is calculated as "1 month".  This implies that duration between 
       * nth day of a month and (n-1)th day of next month is exactly "1 month" 
       * (eg. 2017-01-15 => 2017-02-14 = "1 month").  The analogue is expected
       * of years calculation (ie. duration between nth day of a month and
       * (n-1)th day of that same month in next year is calculated as "1 year", 
       * even on leap years). 
       * 
       * @param  {Date} dateStart
       * @param  {Date} dateEnd
       * 
       * @return {string}
       *   Duration in years, months and days
       */
      function duration(dateStart, dateEnd){
        if (!dateStart || !dateEnd) {
          return null;
        }

        var days, months, m, years;

        m = moment(dateEnd);
        m.add(1, 'days');
        years = m.diff(dateStart, 'years');

        m.add(-years, 'years');
        months = m.diff(dateStart, 'months');

        m.add(-months, 'months');
        days = m.diff(dateStart, 'days');

        years = years > 0  ? (years > 1 ? years + ' years ' : years + ' year ') :  '';
        months = months > 0 ? (months > 1 ? months + ' months ' : months + ' month ') :  '';
        days = days > 0 ? (days > 1 ? days + ' days' : days + ' day') : '';

        return (years + months + days) || '0 days';
      }

      /**
       * Return the max or min date allowed based on the given date
       * (basically adds a day for `min`, subtracts it for `max`)
       *
       * @param  {Date} date
       * @param  {string} type either 'max' or 'min'
       * @return {[Date]}
       */
      function getLimitDate(date, type) {
        type = type || 'min';

        return moment(date)[(type === 'max' ? 'subtract' : 'add')](1, 'day').toDate();
      }

      /**
       * Initializes the datepicker options
       *
       * @return {Object}
       */
      function initDatepickerOptions() {
        return {
          start: {
            maxDate: entityDetails.period_end_date ? getLimitDate(entityDetails.period_end_date, 'max') : null
          },
          end: {
            minDate: entityDetails.period_start_date ? getLimitDate(entityDetails.period_start_date, 'min') : null
          }
        };
      }
  }]);
});
