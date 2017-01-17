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
        it('should always change the hours_amount based on hours_type', function() {
          expect($scope.entity.hour.hours_amount).toBe('');
          $scope.entity.hour.hours_type = '8';
          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe('40.00');

        });
        
        it('should not change the hours_amount if it had been set', function() {
          expect($scope.entity.hour.hours_amount).toBe('');

          $scope.entity.hour.hours_amount = '25';
          $scope.entity.hour.hours_type = '20';

          $scope.$digest();
          expect($scope.entity.hour.hours_amount).toBe('25');
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
