/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/absence-tab/modules/components',
  'leave-absences/absence-tab/components/absence-tab-custom-work-pattern-modal.component'
], function (_, moment, components) {
  components.component('absenceTabWorkPatterns', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-work-patterns.html';
    }],
    controllerAs: 'workpatterns',
    controller: [
      '$log', '$q', '$rootElement', '$rootScope', '$uibModal', 'dialog', 'DateFormat', 'HR_settings',
      'OptionGroup', 'WorkPattern', controller]
  });

  function controller ($log, $q, $rootElement, $rootScope, $uibModal, dialog, DateFormat, HRSettings, OptionGroup, WorkPattern) {
    $log.debug('Component: absence-tab-work-patterns');

    var changeReasons = [];
    var vm = Object.create(this);

    vm.customWorkPatterns = [];
    vm.defaultWorkPattern = null;
    vm.linkToWorkPatternListingPage = getWorkPatternListingPageURL();
    vm.loading = {
      workPattern: true
    };

    (function init () {
      refresh([
        loadJobContractRevisionChangeReasons(),
        getDefaultWorkPattern(),
        DateFormat.getDateFormat()
      ]);

      $rootScope.$on('CustomWorkPattern::Added', function () {
        refresh();
      });
    })();

    /**
     * Shows a delete work pattern confirm dialog before deleting the contact work pattern
     * @param {string} contactWorkPatternID
     */
    vm.deleteWorkPattern = function (contactWorkPatternID) {
      dialog.open({
        title: 'Confirm Deletion?',
        copyCancel: 'Cancel',
        copyConfirm: 'Confirm',
        classConfirm: 'btn-danger',
        msg: 'This cannot be undone',
        onConfirm: function () {
          WorkPattern.unassignWorkPattern(contactWorkPatternID)
            .then(function () {
              refresh();
            });
        }
      });
    };

    /**
     * Opens the Custom Work Pattern Modal
     */
    vm.openModal = function () {
      $uibModal.open({
        appendTo: $rootElement.children().eq(0),
        template: '<absence-tab-custom-work-pattern-modal dismiss="$ctrl.dismiss()" contact-id="' + vm.contactId + '"/>',
        controller: ['$uibModalInstance', function ($modalInstance) {
          this.dismiss = $modalInstance.dismiss;
        }],
        controllerAs: '$ctrl'
      });
    };

    /**
     * Loads the custom work patterns,
     * if no custom work pattern is found, calls the getDefaultWorkPattern()
     *
     * @return {Promise}
     */
    function getCustomWorkPatterns () {
      return WorkPattern.workPatternsOf(vm.contactId, {}, false)
        .then(function (workPatterns) {
          vm.customWorkPatterns = workPatterns;
        });
    }

    /**
     * Loads the default work pattern
     *
     * @return {Promise}
     */
    function getDefaultWorkPattern () {
      return WorkPattern.default()
        .then(function (defaultWorkPattern) {
          vm.defaultWorkPattern = defaultWorkPattern;
        });
    }

    /**
     * Loads the Job Contract Revision Change Reasons and indexes by `value`
     *
     * @return {Promise}
     */
    function loadJobContractRevisionChangeReasons () {
      return OptionGroup.valuesOf('hrjc_revision_change_reason')
        .then(function (reasons) {
          changeReasons = _.indexBy(reasons, 'value');
        });
    }

    /**
     * Refreshes the view by loading work patterns
     * @param {Promise/Array} promise
     * @return {Promise}
     */
    function refresh (promise) {
      var allPromises = [getCustomWorkPatterns()];
      if (promise) {
        allPromises = allPromises.concat(promise);
      }

      vm.loading.workPattern = true;
      return $q.all(allPromises)
        .then(function () {
          setCustomWorkPatternProperties();
        })
        .finally(function () {
          vm.loading.workPattern = false;
        });
    }

    /**
     * Sets the change reason label, and formats effective date to the custom work patterns
     */
    function setCustomWorkPatternProperties () {
      var changeReason;
      var dateFormat = HRSettings.DATE_FORMAT.toUpperCase();

      vm.customWorkPatterns = _(vm.customWorkPatterns)
        .map(function (workPattern) {
          changeReason = changeReasons[workPattern.change_reason];
          workPattern.change_reason_label = changeReason ? changeReason.label : '';

          workPattern.effective_date = workPattern.effective_date
            ? moment(workPattern.effective_date).format(dateFormat) : '';

          return workPattern;
        })
        .sortBy(function (customWorkpattern) {
          return -moment(customWorkpattern.effective_date, dateFormat).valueOf();
        })
        .value();
    }

    /**
     * Returns the URL to the Work Pattern Listing Page.
     *
     * The given contact ID is added to the URL, as the cid parameter.
     *
     * @return {string}
     */
    function getWorkPatternListingPageURL () {
      var path = 'civicrm/admin/leaveandabsences/work_patterns';
      var returnPath = 'civicrm/contact/view';
      var returnUrl = CRM.url(returnPath, { cid: vm.contactId, selectedChild: 'absence' });

      return CRM.url(path, { cid: vm.contactId, returnUrl: returnUrl });
    }

    return vm;
  }
});
