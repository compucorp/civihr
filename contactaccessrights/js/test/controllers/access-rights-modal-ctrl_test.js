define([
  'common/angularMocks',
  'access-rights/controllers/access-rights-modal-ctrl',
  'access-rights/models/region',
  'access-rights/models/location',
  'access-rights/models/right'
], function (_) {
  'use strict';

  describe('AccessRightsModalCtrl', function () {
    var ctrl, $scope, $q, modalInstanceSpy, regionSpy, locationSpy, rightSpy;

    beforeEach(module('access-rights.models', 'access-rights.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
      $scope = _$rootScope_.$new();
      $q = _$q_;
      initSpies();
      ctrl = _$controller_('AccessRightsModalCtrl', {
        $scope: $scope,
        $uibModalInstance: modalInstanceSpy,
        Region: regionSpy,
        Location: locationSpy,
        Right: rightSpy
      });
      $scope.$digest();
    }));


    /**
     * Creates a mocked entity
     *
     * @param  {int} sequential     Sequential to base the "id" and "entity_id" properties
     * @param  {bool} isRegion      Whether it's about a region
     * @return {object}             The mocked object
     */
    function getEntityMock(sequential, isRegion) {
      var difference = isRegion ? 0 : 10;
      return {
        id: sequential + difference,
        entity_id: sequential * 100 + difference
      };
    }


    /**
     * Jasmine spies initialization
     */
    function initSpies() {
      modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss']);
      regionSpy = jasmine.createSpyObj('regionSpy', ['getAll']);
      locationSpy = jasmine.createSpyObj('locationSpy', ['getAll']);
      rightSpy = jasmine.createSpyObj('rightSpy', ['getRegions', 'getLocations',
        'saveRegions', 'saveLocations', 'deleteByIds'
      ]);

      [regionSpy.getAll, locationSpy.getAll].forEach(function (fn, idx) {
        fn.and.returnValue($q.resolve({
          values: [1, 2, 3].map(function (i) {
            return getEntityMock(i, idx === 0);
          })
        }));
      });

      [rightSpy.getRegions, rightSpy.getLocations].forEach(function (fn, idx) {
        fn.and.returnValue($q.resolve({
          values: [getEntityMock(1, idx === 0)]
        }));
      });

      [rightSpy.saveRegions, rightSpy.saveLocations].forEach(function (fn) {
        fn.and.returnValue($q.resolve());
      });
    }

    describe('constructor', function () {
      it('calls Region.getAll()', function () {
        expect(regionSpy.getAll).toHaveBeenCalled();
      });

      it('calls Right.getRegions()', function () {
        expect(rightSpy.getRegions).toHaveBeenCalled();
      });

      it('calls Location.getAll()', function () {
        expect(locationSpy.getAll).toHaveBeenCalled();
      });

      it('calls Right.getLocations()', function () {
        expect(rightSpy.getLocations).toHaveBeenCalled();
      });
    });

    describe('cancel', function () {
      it('closes the modal instance', function () {
        ctrl.cancel();
        expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
      });
    });

    describe('submit', function () {

      describe('new Locations', function () {
        var newIds;
        beforeEach(function () {
          newIds = [210, 310];
          ctrl.selectedData.locations = ctrl.selectedData.locations.concat(newIds);
        });

        describe('when there are no errors', function () {
          beforeEach(function () {
            ctrl.submit();
          });

          it('saves the new Locations', function () {
            expect(rightSpy.saveLocations.calls.count()).toBe(1);
            expect(rightSpy.saveLocations).toHaveBeenCalledWith(newIds);
          });
        });

        describe('when there are errors', function () {
          beforeEach(function () {
            rightSpy.saveLocations.and.returnValue($q.reject());
            ctrl.submit();
            $scope.$digest();
          });

          it('sets the error message', function () {
            expect(rightSpy.saveLocations.calls.count()).toBe(1);
            expect(ctrl.errorMsg.length).not.toBe(0);
          });
        });
      });

      describe('new Regions', function () {
        var newIds;
        beforeEach(function () {
          newIds = [200, 300];
          ctrl.selectedData.regions = ctrl.selectedData.regions.concat(newIds);
        });

        describe('when there are no errors', function () {
          beforeEach(function () {
            ctrl.submit();
          });

          it('saves the new Regions', function () {
            expect(rightSpy.saveRegions.calls.count()).toBe(1);
            expect(rightSpy.saveRegions).toHaveBeenCalledWith(newIds);
          });
        });

        describe('when there are errors', function () {
          beforeEach(function () {
            rightSpy.saveRegions.and.returnValue($q.reject());
            ctrl.submit();
            $scope.$digest();
          });

          it('sets the error message', function () {
            expect(rightSpy.saveRegions.calls.count()).toBe(1);
            expect(ctrl.errorMsg.length).not.toBe(0);
          });
        });
      });

      describe('removed Locations', function () {
        beforeEach(function () {
          ctrl.selectedData.locations = [];
        });

        describe('when there are no errors', function () {
          beforeEach(function () {
            ctrl.submit();
          });

          it('deletes the removed Locations', function () {
            expect(rightSpy.deleteByIds.calls.count()).toBe(1);
            expect(rightSpy.deleteByIds).toHaveBeenCalledWith([11]);
          });
        });

        describe('when there are errors', function () {
          beforeEach(function () {
            rightSpy.deleteByIds.and.returnValue($q.reject());
            ctrl.submit();
            $scope.$digest();
          });

          it('sets the error message', function () {
            expect(rightSpy.deleteByIds.calls.count()).toBe(1);
            expect(ctrl.errorMsg.length).not.toBe(0);
          });
        });

      });

      describe('removed Regions', function () {
        beforeEach(function () {
          ctrl.selectedData.regions = [];
        });

        describe('when there no are errors', function () {
          beforeEach(function () {
            ctrl.submit();
          });

          it('deletes the removed Regions', function () {
            expect(rightSpy.deleteByIds.calls.count()).toBe(1);
            expect(rightSpy.deleteByIds).toHaveBeenCalledWith([1]);
          });
        });

        describe('when there are errors', function () {
          beforeEach(function () {
            rightSpy.deleteByIds.and.returnValue($q.reject());
            ctrl.submit();
            $scope.$digest();
          });

          it('sets the error message', function () {
            expect(rightSpy.deleteByIds.calls.count()).toBe(1);
            expect(ctrl.errorMsg.length).not.toBe(0);
          });
        });

        describe('when location and region is not set', function () {
          it("cannot submit without selecting location", function () {
            expect(ctrl.hasSelected).toBe(false);
          });
        });

        describe('when only region is set', function () {
          beforeEach(function () {
            ctrl.selectedData.locations = [];

            ctrl.selectedData.regions.push({
              name: "Home",
              label: "Home"
            });

            ctrl.selection();
          });

          it("cannot submit when only region is set", function () {
            expect(ctrl.hasSelected).toBe(false);
          });
        });

        describe('when only location is set', function () {
          beforeEach(function () {
            ctrl.selectedData.locations.push({
              name: "Home",
              label: "Home"
            });

            ctrl.selectedData.regions.push = [];

            ctrl.selection();
          });

          it("cannot submit when only location is set", function () {
            expect(ctrl.hasSelected).toBe(false);
          });
        });

        describe('when location and region is set', function () {
          beforeEach(function () {
            ctrl.selectedData.locations.push({
              name: "Home",
              label: "Home"
            });

            ctrl.selectedData.regions.push({
              name: "Honme",
              label: "Home"
            });

            ctrl.selection();
          });

          it("can submit when location and region is set", function () {
            expect(ctrl.hasSelected).toBe(true);
          });
        });

        describe('prevent multiple save', function () {
          beforeEach(function () {
            ctrl.submit();
          });

          it('submitting should be true', function () {
            expect(ctrl.submitting).toBe(true);
          });
        });
      });
    });

  });
});
