/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request.controller',
  'leave-absences/shared/instances/sickness-request.instance'
], function (_, controllers) {
  controllers.controller('SicknessRequestCtrl', SicknessRequestCtrl);

  SicknessRequestCtrl.$inject = ['$controller', '$log', '$q', '$uibModalInstance',
    'api.optionGroup', 'directiveOptions', 'SicknessRequestInstance'];

  function SicknessRequestCtrl ($controller, $log, $q, $modalInstance,
    OptionGroup, directiveOptions, SicknessRequestInstance) {
    $log.debug('SicknessRequestCtrl');

    var parentRequestCtrl = $controller('RequestCtrl');
    var vm = Object.create(parentRequestCtrl);

    vm.directiveOptions = directiveOptions;
    vm.$modalInstance = $modalInstance;
    vm.initParams = {
      absenceType: {
        is_sick: true
      }
    };

    vm.canSubmit = canSubmit;
    vm.isChecked = isChecked;
    vm.isDocumentInRequest = isDocumentInRequest;
    vm._initRequest = _initRequest;

    (function init () {
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
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function canSubmit () {
      return parentRequestCtrl.canSubmit.call(this) && !!vm.request.sickness_reason;
    }

    /**
     * During initialization it will check if given value is set for leave request list
     * of document value ie., field sickness_required_documents in existing leave request
     *
     * @param {String} value
     * @return {Boolean}
     */
    function isChecked (value) {
      var docsArray = vm.request.getDocumentArray();

      return !!_.find(docsArray, function (document) {
        return document === value;
      });
    }

    /**
     * Checks if given value is set for leave request list of document value
     * i.e. field sickness_required_documents
     *
     * @param {String} value
     * @return {Boolean}
     */
    function isDocumentInRequest (value) {
      return !!_.find(vm.sicknessDocumentTypes, function (document) {
        return document.value === value;
      });
    }

    /**
     * Initializes leave request documents types required for submission
     *
     * @return {Promise}
     */
    function loadDocuments () {
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
    function loadReasons () {
      return OptionGroup.valuesOf('hrleaveandabsences_sickness_reason')
        .then(function (reasons) {
          vm.sicknessReasons = _.indexBy(reasons, 'name');
        });
    }

    /**
     * Initialize leaverequest based on attributes that come from directive
     */
    function _initRequest () {
      var attributes = vm._initRequestAttributes();

      vm.request = SicknessRequestInstance.init(attributes);
    }

    return vm;
  }
});
