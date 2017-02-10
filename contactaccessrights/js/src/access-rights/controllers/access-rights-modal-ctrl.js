define([
  'common/lodash',
  'access-rights/modules/controllers'
], function (_, controllers) {
  'use strict';

  controllers.controller('AccessRightsModalCtrl', [
    '$q', '$uibModalInstance', 'Region', 'Location', 'Right',
    function ($q, $modalInstance, Region, Location, Right) {
      var vm = this;

      vm.submitting = false;

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

      vm.dataLoaded = false;

      /**
       * Closes the modal
       */
      vm.cancel = function () {
        $modalInstance.dismiss('cancel');
      };

      /**
       * Saves data and closes the modal
       */
      vm.submit = function () {
        vm.submitting = true;
        $q.all([persistValues('regions'), persistValues('locations')])
          .then(function () {
            $modalInstance.dismiss('cancel');
          })
          .catch(function (err) {
            vm.errorMsg = 'Error while saving data';
          })
          .finally(function () {
            vm.submitting = true;
          });
      };

      /**
       * Saves the new values, and deletes the removed ones
       *
       * @param  {string} type  Either "regions" or "locations"
       * @return {Promise}      The result of all promises
       */
      function persistValues(type) {
        var originalData = vm.originalData[type];
        var selectedData = vm.selectedData[type];

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

        if (newEntityIds.length > 0) {
          promises.push(Right['save' + _.capitalize(type)](newEntityIds));
        }
        if (removedRightIds.length > 0) {
          promises.push(Right.deleteByIds(removedRightIds));
        }

        return $q.all(promises);
      }

      /**
       * Loads the API data
       */
      function init() {
        $q.all([
            Region.getAll(),
            Location.getAll()
          ])
          .then(function (values) {
            return {
              regions: values[0],
              locations: values[1]
            };
          })
          .then(function (values) {
            return $q.all(_.map(values, function (value, key) {
              vm.availableData[key] = value;
              return Right['get' + _.capitalize(key)]();
            }));
          })
          .then(function (values) {
            return {
              regions: values[0],
              locations: values[1]
            };
          })
          .then(function (values) {
            Object.keys(values).forEach(function (key) {
              vm.originalData[key] = values[key].values;
              vm.selectedData[key] = values[key].values.map(function (entity) {
                return entity.entity_id;
              });
            });
          })
          .then(function () {
            vm.dataLoaded = true;
          });
      }
      init();
    }
  ]);
});
