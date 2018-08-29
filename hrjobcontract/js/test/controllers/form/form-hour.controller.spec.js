/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/lodash',
  'job-contract/modules/job-contract.module'
], function (angular, _) {
  'use strict';

  describe('FormHourController', function () {
    var $controller, $httpBackend, $rootScope, $scope;
    var entityData = {
      'hour': {
        'id': '1',
        'location_standard_hours': '3',
        'hours_type': '',
        'hours_amount': '',
        'hours_unit': '',
        'hours_fte': '0',
        'fte_num': '0',
        'fte_denom': '0',
        'jobcontract_revision_id': '1'
      }
    };

    beforeEach(module('job-contract', 'job-contract.templates'));
    beforeEach(inject(function (_$controller_, _$httpBackend_, _$rootScope_) {
      $controller = _$controller_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(200);
    }));

    describe('init', function () {
      describe('when no existing data is passed to the form', function () {
        beforeEach(function () {
          initController();
        });

        it('defaults the "Location/Standard hours" value to the first available option', function () {
          expect($scope.entity.hour.location_standard_hours).toBe($scope.utils.hoursLocation[0].id);
        });
      });

      describe('when existing data is passed to the form', function () {
        beforeEach(function () {
          initController(entityData);
        });

        it('sets the "Location/Standard hours" value equal to the existing data', function () {
          expect($scope.entity.hour.location_standard_hours).toBe(entityData.hour.location_standard_hours);
        });
      });
    });

    describe('updateHours', function () {
      beforeEach(function () {
        initController(entityData);
      });

      it('always changes the hours_amount based on hours_type', function () {
        expect($scope.entity.hour.hours_amount).toBe('');
        $scope.entity.hour.hours_type = '8';
        $scope.$digest();
        expect($scope.entity.hour.hours_amount).toBe('8.00');
      });

      it('does not change the hours_amount if it had been set', function () {
        expect($scope.entity.hour.hours_amount).toBe('');

        $scope.entity.hour.hours_amount = '25';
        $scope.entity.hour.hours_type = '20';

        $scope.$digest();
        expect($scope.entity.hour.hours_amount).toBe('25');
      });
    });

    /**
     * Initializes the form controller
     *
     * @param  {Object} entityData additional data to put in the entity details
     */
    function initController (entityData) {
      $scope = $rootScope.$new();
      $scope.entity = _.assign({
        hour: {}
      }, _.cloneDeep(entityData));
      $scope.utils = {
        hoursLocation: [
          {
            'id': '2',
            'location': 'Head office',
            'standard_hours': '40.00',
            'pay_frequency': 'Week',
            'is_active': '1'
          },
          {
            'id': '3',
            'location': 'Other office',
            'standard_hours': '8.00',
            'pay_frequency': 'Day',
            'is_active': '1'
          },
          {
            'id': '4',
            'location': 'Small office',
            'standard_hours': '36.00',
            'pay_frequency': 'Week',
            'is_active': '1'
          }
        ]
      };

      $controller('FormHourController', { $scope: $scope });
    }
  });
});
