/* eslint-env amd, jasmine */

define([
  'job-contract/app'
], function () {
  'use strict';

  describe('FormLeaveCtrl', function () {
    var ctrl, $controller, $rootScope, $scope, UtilsService;

    beforeEach(module('hrjc'));

    beforeEach(inject(function (_$controller_, _$rootScope_, _UtilsService_, $q) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      UtilsService = _UtilsService_;

      spyOn(UtilsService, 'getNumberOfPublicHolidaysInCurrentPeriod').and.callFake(function () {
        var deferred = $q.defer();

        deferred.resolve(2);

        return deferred.promise;
      });

      initController();
    }));

    describe('init', function () {
      beforeEach(function () {
        $scope.$digest();
      });

      it('loads the number of public holidays', function () {
        expect(UtilsService.getNumberOfPublicHolidaysInCurrentPeriod).toHaveBeenCalled();
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
      ctrl = $controller('FormLeaveCtrl', { $scope: $scope });
    }
  });
});
