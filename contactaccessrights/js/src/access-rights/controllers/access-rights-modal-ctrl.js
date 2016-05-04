define(['access-rights/modules/controllers'], function (controllers) {
	'use strict';

	controllers.controller('AccessRightsModalCtrl', ['Region', 'Location', '$log', '$rootScope', '$modalInstance',
		function (Region, Location, $log, $rootScope, $modalInstance) {
			$log.debug('AccessRightsModalCtrl');

			Region.getAll().then(function (data) {
				// console.log(data);
				// Region.save(data.list[1046]);
				console.log(data);
				vm.availableData.regions = data.list;
			});

			Location.getAll().then(function (data) {
				// console.log(data);
				// Region.save(data.list[1046]);
				console.log(data);
				vm.availableData.locations = data.list;
			});

			var vm = this;
			vm.cancel = function () {
				$modalInstance.dismiss('cancel');
			};

			vm.availableData = {
				regions: {},
				locations: {}
			};

			vm.selectedData = {
				locations: [],
				regions: []
			};
		}
	]);
});
