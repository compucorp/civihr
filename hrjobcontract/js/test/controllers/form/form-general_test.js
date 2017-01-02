define([
  'common/lodash',
  'common/moment',
  'common/angularMocks',
  'common/mocks/services/hr-settings-mock',
  'job-contract/app'
], function (_, moment) {
  'use strict';

  describe('FormGeneralCtrl', function () {
    var ctrl, $controller, $provide, $rootScope, $scope;

    beforeEach(function () {
      module('hrjc', 'common.mocks', function (_$provide_) {
          $provide = _$provide_;
      });
      inject(['HR_settingsMock', function (HR_settingsMock) {
          $provide.value('HR_settings', HR_settingsMock);
      }]);
    });

    beforeEach(inject(function (_$controller_, _$rootScope_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
    }));

    describe('Min and max allowed dates', function () {
      describe('when there are no period dates already set', function () {
        beforeEach(function () {
          initController();
        });

        it('sets the min/max dates as null', function () {
          expect($scope.datepickerOptions.start.maxDate).toBe(null);
          expect($scope.datepickerOptions.end.minDate).toBe(null);
        });
      });

      describe('when the period dates are already set', function () {
        beforeEach(function () {
          initController({
            period_start_date: moment('2016-01-01').toDate(),
            period_end_date: moment('2016-02-11').toDate(),
          });
        });

        it('sets the min date as the start date + 1', function () {
          expect($scope.datepickerOptions.end.minDate).toEqual(moment('2016-01-02').toDate());
        });

        it('sets the max date as the end date - 1', function () {
          expect($scope.datepickerOptions.start.maxDate).toEqual(moment('2016-02-10').toDate());
        });
      });

      describe('when the period dates change', function () {
        beforeEach(function () {
          initController();

          _.assign($scope.entity.details, {
            period_start_date: moment('2015-11-21').toDate(),
            period_end_date: moment('2015-12-07').toDate()
          });

          $scope.$digest();
        });

        it('changes the min/max dates accordingly', function () {
          expect($scope.datepickerOptions.start.maxDate).toEqual(moment('2015-12-06').toDate());
          expect($scope.datepickerOptions.end.minDate).toEqual(moment('2015-11-22').toDate());
        });

        it('sets duration calculating number of days including start and end dates', function() {
          expect($scope.duration).toEqual('17 days');
        });

        it('calculates duration including first and last days of months as absolute number of months with no day fraction', function() {
          _.assign($scope.entity.details, {
            period_start_date: moment('2017-02-01').toDate(),
            period_end_date: moment('2017-03-31').toDate()
          });
          $scope.$digest();
          expect($scope.duration.trim()).toEqual('2 months');
        });
      });
    });

    /**
     * Initializes the form controller
     *
     * @param  {Object} scopeData additional data to put in the entity details
     */
    function initController(scopeData) {
      $scope = $rootScope.$new();
      $scope.entity = {
        details: _.assign({}, scopeData)
      };

      ctrl = $controller('FormGeneralCtrl', { $scope: $scope });
    }
  });
});
