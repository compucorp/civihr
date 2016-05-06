define([
	'common/angularMocks',
	'access-rights/controllers/access-rights-modal-ctrl',
	'access-rights/models/region',
	'access-rights/models/location',
	'access-rights/models/right'
], function () {
	'use strict';

	describe('AccessRightsModalCtrl', function () {
		var ctrl, $scope, $q, modalInstanceSpy, regionSpy, locationSpy, rightSpy;

		beforeEach(module('access-rights.models', 'access-rights.controllers'));
		beforeEach(function () {
			modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss']);
			regionSpy = jasmine.createSpyObj('regionSpy', ['getAll']);
			locationSpy = jasmine.createSpyObj('locationSpy', ['getAll']);
			rightSpy = jasmine.createSpyObj('rightSpy', ['getRegions', 'getLocations',
				'saveRegions', 'saveLocations', 'deleteByIds'
			]);
		});
		beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
			CRM = {
				vars: {
					summaryPage: {
						summaryPage: '1'
					}
				}
			};
			$scope = _$rootScope_.$new();
			$q = _$q_;

			function getEntityMock(sequential, isRegion) {
				var difference = isRegion ? 0 : 10;
				return {
					id: sequential + difference,
					entity_id: sequential * 100 + difference
				};
			}

			[regionSpy.getAll, locationSpy.getAll]
			.forEach(function (fn, idx) {
				fn.and.returnValue($q.resolve([1, 2, 3].map(function (i) {
					return getEntityMock(i, idx === 0);
				})));
			});

			[rightSpy.getRegions, rightSpy.getLocations].forEach(function (fn, idx) {
				fn.and.returnValue($q.resolve([getEntityMock(1, idx === 0)]));
			});

			[rightSpy.saveRegions, rightSpy.saveLocations].forEach(function (fn) {
				fn.and.returnValue($q.resolve());
			});

			ctrl = _$controller_('AccessRightsModalCtrl', {
				$scope: $scope,
				$modalInstance: modalInstanceSpy,
				Region: regionSpy,
				Location: locationSpy,
				Right: rightSpy
			});
			$scope.$digest();
		}));

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

				it('saves the new Locations', function () {
					ctrl.submit();
					expect(rightSpy.saveLocations.calls.count()).toBe(1);
					expect(rightSpy.saveLocations).toHaveBeenCalledWith(newIds);
				});

				it('sets the error message', function () {
					rightSpy.saveLocations.and.returnValue($q.reject());
					ctrl.submit();
					$scope.$digest();
					expect(rightSpy.saveLocations.calls.count()).toBe(1);
					expect(ctrl.errorMsg.length).not.toBe(0);
				});
			});

			describe('new Regions', function () {
				var newIds;
				beforeEach(function () {
					newIds = [200, 300];
					ctrl.selectedData.regions = ctrl.selectedData.regions.concat(newIds);
				});

				it('saves the new Regions', function () {
					ctrl.submit();
					expect(rightSpy.saveRegions.calls.count()).toBe(1);
					expect(rightSpy.saveRegions).toHaveBeenCalledWith(newIds);
				});

				it('sets the error message', function () {
					rightSpy.saveRegions.and.returnValue($q.reject());
					ctrl.submit();
					$scope.$digest();
					expect(rightSpy.saveRegions.calls.count()).toBe(1);
					expect(ctrl.errorMsg.length).not.toBe(0);
				});
			});

			describe('removed Locations', function(){
				beforeEach(function(){
					ctrl.selectedData.locations = [];
				});

				it('deletes the removed Locations', function () {
					ctrl.submit();
					expect(rightSpy.deleteByIds.calls.count()).toBe(1);
					expect(rightSpy.deleteByIds).toHaveBeenCalledWith([11]);
				});

				it('sets the error message', function () {
					rightSpy.deleteByIds.and.returnValue($q.reject());
					ctrl.submit();
					$scope.$digest();
					expect(rightSpy.deleteByIds.calls.count()).toBe(1);
					expect(ctrl.errorMsg.length).not.toBe(0);
				});
			});

			describe('removed Regions', function(){
				beforeEach(function(){
					ctrl.selectedData.regions = [];
				});

				it('deletes the removed Regions', function () {
					ctrl.submit();
					expect(rightSpy.deleteByIds.calls.count()).toBe(1);
					expect(rightSpy.deleteByIds).toHaveBeenCalledWith([1]);
				});

				it('sets the error message', function () {
					rightSpy.deleteByIds.and.returnValue($q.reject());
					ctrl.submit();
					$scope.$digest();
					expect(rightSpy.deleteByIds.calls.count()).toBe(1);
					expect(ctrl.errorMsg.length).not.toBe(0);
				});
			});
		});

	});
});
