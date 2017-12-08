/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers'
], function (_, controllers) {
  controllers.controller('SicknessRequestCtrl', SicknessRequestCtrl);

  SicknessRequestCtrl.$inject = ['$controller', '$log', '$q', 'api.optionGroup', 'parentCtrl'];

  function SicknessRequestCtrl ($controller, $log, $q, OptionGroup, parentCtrl) {
    $log.debug('SicknessRequestCtrl');
    // We need to extend parent controller with
    // calculateBalanceChange() function from Leave Request Controller
    $controller('LeaveRequestCtrl', { parentCtrl: parentCtrl });

    parentCtrl.checkSubmitConditions = checkSubmitConditions;
    parentCtrl.initChildController = initChildController;
    parentCtrl.isChecked = isChecked;
    parentCtrl.isDocumentInRequest = isDocumentInRequest;

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function checkSubmitConditions () {
      return parentCtrl.canCalculateChange() && !!parentCtrl.request.sickness_reason;
    }

    /**
     * Initialize the controller
     *
     * @return {Promise}
     */
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
      var docsArray = parentCtrl.request.getDocumentArray();

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
      return !!_.find(parentCtrl.sicknessDocumentTypes, function (document) {
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
          parentCtrl.sicknessDocumentTypes = documentTypes;
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
          parentCtrl.sicknessReasons = _.indexBy(reasons, 'name');
        });
    }
  }
});
