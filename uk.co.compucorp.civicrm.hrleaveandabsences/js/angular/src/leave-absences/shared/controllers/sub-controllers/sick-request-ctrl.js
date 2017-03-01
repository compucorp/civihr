define([
  'common/lodash',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request-ctrl',
  'leave-absences/shared/models/instances/sickness-leave-request-instance',
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
       * Checks if given value is set for leave request list of document value ie., field required_documents
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
       * Resets data in dates, types, balance in parent and reason here.
       */
      vm._reset = function () {
        parentRequestCtrl._reset.call(this);
        vm.request.sickness_reason = null;
      };

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController() {
        vm.loading.absenceTypes = true;
        initRequest();

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
       * Initialize leaverequest based on attributes that come from directive
       */
      function initRequest() {
        var attributes = vm._initRequestAttributes();

        vm.request = SicknessRequestInstance.init(attributes);
      }

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
