define(['access-rights/modules/controllers'], function (controllers) {
	'use strict';

	controllers.controller('AccessRightsModalCtrl', ['Region', 'Location', 'Right', '$q', '$modalInstance',
		function (Region, Location, Right, $q, $modalInstance) {
			var vm = this;

			vm.availableData = {
				regions: [],
				locations: []
			};

			vm.selectedData = {
				locations: [],
				regions: []
			};

			vm.originalData = {
				locations: [],
				regions: []
			};

			vm.errorMsg = '';

			vm.cancel = function () {
				$modalInstance.dismiss('cancel');
			};

			vm.submit = function () {
				$q.all([persistRegions(), persistLocations()])
					.then(function () {
						$modalInstance.dismiss('cancel');
					})
					.catch(function (err) {
						vm.errorMsg = 'Error while saving data';
					});
			};

			function persistRegions() {
				return persistValues(vm.originalData.regions, vm.selectedData.regions,
					Right.saveRegions.bind(Right));
			}

			function persistLocations() {
				return persistValues(vm.originalData.locations, vm.selectedData.locations,
					Right.saveLocations.bind(Right));
			}

			function persistValues(originalData, selectedData, fnSave) {
				var originalEntityIds = originalData.map(function (i) {
					return i.entity_id;
				});
				var newEntityIds = _.difference(selectedData, originalEntityIds);
				var removedRightIds = _.difference(originalEntityIds, selectedData)
					.map(function (entityId) {
						return _.find(originalData, function (i) {
							return i.entity_id === entityId;
						}).id;
					});
				var promises = [];
				if(newEntityIds.length > 0)
					promises.push(fnSave(newEntityIds));
				if(removedRightIds.length > 0)
					promises.push(Right.deleteByIds(removedRightIds));
				return $q.all(promises);
			}

			Region.getAll().then(function (regions) {
				vm.availableData.regions = regions;
				return Right.getRegions();
			}).then(function (regionRights) {
				vm.originalData.regions = regionRights;
				vm.selectedData.regions = regionRights.map(function (regionRight) {
					return regionRight.entity_id;
				});
			});

			Location.getAll().then(function (locations) {
				vm.availableData.locations = locations;
				return Right.getLocations();
			}).then(function (locationRights) {
				vm.originalData.locations = locationRights;
				vm.selectedData.locations = locationRights.map(function (locationRight) {
					return locationRight.entity_id;
				});
			});
		}
	]);
});
