define([
  'common/angularMocks',
  'job-contract/app'
], function (angular) {
  'use strict';

  describe('FormHourCtrl', function () {
    var ctrl, $controller, $rootScope, $scope;

    beforeEach(module('hrjc'));
    beforeEach(function () {
      inject(function (_$controller_, _$rootScope_) {
        $controller = _$controller_;
        $rootScope = _$rootScope_;
      });
      initController();
    });

    describe('FormHourCtrl', function() {
      describe('updateHours', function() {
        it('asdsad', function() {
          expect($scope.entity.hour.hours_amount).toBe('');

          $scope.entity.hour.hours_type = '8';
          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe('40.00');

          $scope.entity.hour.hours_type = '4';
          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe(20);

          $scope.entity.hour.hours_type = '8';
          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe('40.00');

          $scope.entity.hour.hours_type = '0';
          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe(0);

          $scope.entity.hour.hours_type = '4';
          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe(20);

          $scope.entity.hour.hours_type = '0';
          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe(0);
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
        hour: {
          'id': '1',
          'location_standard_hours': '1',
          'hours_type': '',
          'hours_amount': '',
          'hours_unit': '',
          'hours_fte': '0',
          'fte_num': '0',
          'fte_denom': '0',
          'jobcontract_revision_id': '1'
        }
      };
      $scope.utils = {
        hoursLocation: [
          {
            'id':'1',
            'location': 'Head office',
            'standard_hours': '40.00',
            'periodicity': 'Week',
            'is_active': '1'
          },
          {
            'id':'2',
            'location': 'Other office',
            'standard_hours': '8.00',
            'periodicity': 'Day',
            'is_active': '1'
          },
          {
            'id':'3',
            'location': 'Small office',
            'standard_hours': '36.00',
            'periodicity': 'Week',
            'is_active': '1'
          }
        ]
      };

      ctrl = $controller('FormHourCtrl', { $scope: $scope });
    }
  });
});
