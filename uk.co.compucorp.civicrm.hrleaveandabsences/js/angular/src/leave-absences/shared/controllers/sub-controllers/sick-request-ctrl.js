define([
  'common/lodash',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request-ctrl',
  'leave-absences/shared/models/instances/sickness-request-instance',
], function (_, controllers) {
  controllers.controller('SicknessRequestCtrl', [
    '$controller', '$log', '$q', '$uibModalInstance', 'api.optionGroup', 'directiveOptions', 'SicknessRequestInstance',
    function ($controller, $log, $q, $modalInstance, OptionGroup, directiveOptions, SicknessRequestInstance) {
      $log.debug('SicknessRequestCtrl');

      var parentRequestCtrl = $controller('RequestCtrl'),
        vm = Object.create(parentRequestCtrl);

      vm.directiveOptions = directiveOptions;
      vm.$modalInstance = $modalInstance;
      vm.initParams = {
        absenceType: {
          is_sick: true
        }
      };

      /**
       * Checks if submit button can be enabled for user and returns true if successful
       *
       * @return {Boolean}
       */
      vm.canSubmit = function () {
        return parentRequestCtrl.canSubmit.call(this) && !!vm.request.sickness_reason;
      };

      /**
       * Checks if given value is set for leave request list of document value ie., field sickness_required_documents
       *
       * @param {String} value
       * @return {Boolean}
       */
      vm.isDocumentInRequest = function (value) {
        return !!_.find(vm.sicknessDocumentTypes, function (document) {
          return document.value == value;
        });
      };

      /**
       * During initialization it will check if given value is set for leave request list
       * of document value ie., field sickness_required_documents in existing leave request
       *
       * @param {String} value
       * @return {Boolean}
       */
      vm.isChecked = function (value) {
        var docsArray = vm.request.getDocumentArray();

        return !!_.find(docsArray, function (document) {
          return document == value;
        });
      };

      /**
       * Initialize leaverequest based on attributes that come from directive
       */
      vm._initRequest = function () {
        var attributes = vm._initRequestAttributes();

        vm.request = SicknessRequestInstance.init(attributes);
      };

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController() {
        vm.loading.absenceTypes = true;

        vm._init()
          .then(function () {
            return $q.all([
              loadDocuments(),
              loadReasons()
            ]);
          })
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();

      /**
       * Initializes leave request documents types required for submission
       *
       * @return {Promise}
       */
      function loadDocuments() {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_required_document')
          .then(function (documentTypes) {
            vm.sicknessDocumentTypes = documentTypes;
          });
      }

      /**
       * Initializes leave request reasons and indexes them by name like accident etc.,
       *
       * @return {Promise}
       */
      function loadReasons() {
        return OptionGroup.valuesOf('hrleaveandabsences_sickness_reason')
          .then(function (reasons) {
            vm.sicknessReasons = _.indexBy(reasons, 'name');
          });
      }

      return vm;
    }
  ]);
});
