/* eslint-env amd */

define([
  'common/moment',
  'leave-absences/absence-tab/modules/components'
], function (moment, components) {
  components.component('absenceTabCustomWorkPatternModal', {
    bindings: {
      contactId: '<',
      dismiss: '&'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-custom-work-pattern-modal.html';
    }],
    controllerAs: 'workPatternModal',
    controller: ['$log', '$q', '$rootScope', 'crmAngService', 'OptionGroup', 'shared-settings', 'WorkPatternAPI', controller]
  });

  function controller ($log, $q, $rootScope, crmAngService, OptionGroup, sharedSettings, WorkPatternAPI) {
    $log.debug('Component: absence-tab-custom-work-pattern-modal');

    var vm = Object.create(this);

    vm.changeReasons = [];
    vm.errorMessage = '';
    vm.saveInProgress = false;
    vm.workPatterns = [];
    vm.loading = { content: true };
    vm.selected = {
      workPattern: null,
      effectiveDate: null,
      changeReason: null
    };
    vm.uiOptions = {
      effectiveDate: {
        show: false,
        options: {
          startingDay: 1,
          showWeeks: false
        }
      }
    };
    vm.openWorkPatternChangeReasonEditor = openWorkPatternChangeReasonEditor;

    (function init () {
      return $q.all([
        loadWorkPatterns(),
        loadJobContractRevisionChangeReasons(true)
      ])
        .finally(function () {
          vm.loading.content = false;
        });
    })();

    /**
     * Closes the Work Pattern Modal
     */
    vm.closeModal = function () {
      vm.dismiss({ $value: 'cancel' });
    };

    /**
     * Closes the Error Message Alert box
     */
    vm.closeAlert = function () {
      vm.errorMessage = '';
    };

    /**
     * Assigns the Selected Work Pattern to the Contact
     *
     * @return {Promise}
     */
    vm.save = function () {
      var serverFormattedEffectiveDate = moment(vm.selected.effectiveDate).format(sharedSettings.serverDateFormat);
      vm.saveInProgress = true;

      return WorkPatternAPI.assignWorkPattern(
        vm.contactId, vm.selected.workPattern.id, serverFormattedEffectiveDate, null, vm.selected.changeReason)
        .then(function () {
          $rootScope.$broadcast('CustomWorkPattern::Added');
          vm.closeModal();
        })
        .catch(handleError)
        .finally(function () {
          vm.saveInProgress = false;
        });
    };

    /**
     * Handles the error thrown by API
     * @param {String} errorMessage
     */
    function handleError (errorMessage) {
      vm.errorMessage = errorMessage;
    }

    /**
     * Loads all the Work Patterns
     *
     * @return {Promise}
     */
    function loadWorkPatterns () {
      return WorkPatternAPI.get({ is_active: true })
        .then(function (workPatterns) {
          vm.workPatterns = workPatterns;
        });
    }

    /**
     * Loads the Leave and Absences Work Pattern Change Reasons
     *
     * @param  {Boolean} cache if to cache results of the API call, cache by default
     * @return {Promise} resolves with {Array}
     */
    function loadJobContractRevisionChangeReasons (cache) {
      return OptionGroup.valuesOf('hrleaveandabsences_work_pattern_change_reason', cache)
        .then(function (reasons) {
          vm.changeReasons = reasons;
        });
    }

    /**
     * Opens the work pattern change reasons for editing
     */
    function openWorkPatternChangeReasonEditor () {
      crmAngService.loadForm('/civicrm/admin/options/hrleaveandabsences_work_pattern_change_reason?reset=1')
        .on('crmUnload', function () {
          loadJobContractRevisionChangeReasons(false);
        });
    }

    return vm;
  }
});
