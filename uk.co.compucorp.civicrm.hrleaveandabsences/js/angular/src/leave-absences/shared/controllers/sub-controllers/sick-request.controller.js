/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request.controller',
  'leave-absences/shared/instances/sickness-request.instance'
], function (_, controllers) {
  controllers.controller('SicknessRequestCtrl', SicknessRequestCtrl);

  SicknessRequestCtrl.$inject = ['$log', '$q', 'api.optionGroup', 'parentCtrl'];

  function SicknessRequestCtrl ($log, $q, OptionGroup, parentCtrl) {
    $log.debug('SicknessRequestCtrl');
    var vm = parentCtrl;

    vm.checkSubmitConditions = checkSubmitConditions;
    vm.isChecked = isChecked;
    vm.isDocumentInRequest = isDocumentInRequest;

    vm.initChildController = initChildController;

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function checkSubmitConditions () {
      return vm._canCalculateChange() && vm.request.sickness_reason;
    }

    function initChildController () {
      return $q.all([
        loadDocuments(),
        loadReasons()
      ]);
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
  }
});
