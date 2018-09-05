/* eslint-env amd, jasmine */

define([
  'job-contract/job-contract.module'
], function () {
  'use strict';

  describe('FormLeaveController', function () {
    var ctrl, $controller, $httpBackend, $rootScope, $scope, utilsService;

    beforeEach(module('job-contract', 'job-contract.templates'));

    beforeEach(inject(function (_$controller_, _$httpBackend_, _$rootScope_, _utilsService_, $q) {
      $controller = _$controller_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
      utilsService = _utilsService_;

      spyOn(utilsService, 'getNumberOfPublicHolidaysInCurrentPeriod').and.callFake(function () {
        var deferred = $q.defer();

        deferred.resolve(2);

        return deferred.promise;
      });

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(200);
      initController();
    }));

    describe('init', function () {
      beforeEach(function () {
        $scope.$digest();
      });

      it('loads the number of public holidays', function () {
        expect(utilsService.getNumberOfPublicHolidaysInCurrentPeriod).toHaveBeenCalled();
        expect(ctrl.numberOfPublicHolidays).toBe(2);
      });
    });

    describe('Set a leave type to add public holidays', function () {
      beforeEach(function () {
        $scope.entity = {
          leave: [
            { add_public_holidays: false, leave_type: '1' },
            { add_public_holidays: false, leave_type: '2' },
            { add_public_holidays: true, leave_type: '3' }
          ]
        };

        $scope.$digest();
      });

      describe('when a leave type is set to add public holidays', function () {
        beforeEach(function () {
          $scope.entity.leave[1].add_public_holidays = true;

          $scope.$digest();
        });

        it('sets the other leave types to not add public holidays', function () {
          expect($scope.entity.leave[0].add_public_holidays).toBe(false);
          expect($scope.entity.leave[1].add_public_holidays).toBe(true);
          expect($scope.entity.leave[2].add_public_holidays).toBe(false);
        });
      });
    });

    /**
     * Initializes the form controller
     */
    function initController () {
      $scope = $rootScope.$new();
      ctrl = $controller('FormLeaveController', { $scope: $scope });
    }
  });
});
