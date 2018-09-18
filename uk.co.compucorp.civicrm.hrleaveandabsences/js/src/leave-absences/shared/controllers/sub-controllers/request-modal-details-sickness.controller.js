/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/controllers'
], function (_, controllers) {
  controllers.controller('RequestModalDetailsSicknessController', RequestModalDetailsSicknessController);

  RequestModalDetailsSicknessController.$inject = ['$controller', '$log', '$q', '$rootScope',
    'crmAngService', 'api.optionGroup', 'detailsController'];

  function RequestModalDetailsSicknessController ($controller, $log, $q, $rootScope, crmAngService,
    OptionGroup, detailsController) {
    $log.debug('RequestModalDetailsSicknessController');
    // Shares basic logic with the the leave controller
    $controller('RequestModalDetailsLeaveController', { detailsController: detailsController });

    detailsController.canSubmit = canSubmit;
    detailsController.initChildController = initChildController;
    detailsController.isChecked = isChecked;
    detailsController.isDocumentInRequest = isDocumentInRequest;
    detailsController.openSicknessReasonOptionsEditor = openSicknessReasonOptionsEditor;

    /**
     * Checks if submit button can be enabled for user and returns true if successful
     *
     * @return {Boolean}
     */
    function canSubmit () {
      return !!(detailsController.canCalculateChange() && detailsController.request.sickness_reason);
    }

    /**
     * Initialize the controller
     *
     * @return {Promise}
     */
    function initChildController () {
      return $q.all([
        loadDocuments(),
        toggleSicknessReasonsEditorIcon(),
        loadReasons(true)
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
      var docsArray = detailsController.request.getDocumentArray();

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
      return !!_.find(detailsController.sicknessDocumentTypes, function (document) {
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
          detailsController.sicknessDocumentTypes = documentTypes;
        });
    }

    /**
     * Initializes leave request reasons and indexes them by name like accident etc.,
     *
     * @return {Promise}
     */
    function loadReasons (cache) {
      return OptionGroup.valuesOf('hrleaveandabsences_sickness_reason', cache)
        .then(function (reasons) {
          detailsController.sicknessReasons = _.keyBy(reasons, 'name');
        });
    }

    /**
     * Opens editor for sickness reason options editing
     */
    function openSicknessReasonOptionsEditor () {
      crmAngService.loadForm('/civicrm/admin/options/hrleaveandabsences_sickness_reason?reset=1')
        .on('crmUnload', function () {
          loadReasons(false);
        });
    }

    function toggleSicknessReasonsEditorIcon () {
      detailsController.showSicknessOptionsEditorIcon =
        _.includes(['admin-dashboard', 'absence-tab'], $rootScope.section);
    }
  }
});
