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
    controller: ['$log', '$q', '$rootScope', 'OptionGroup', 'shared-settings', 'WorkPatternAPI', controller]
  });

  function controller ($log, $q, $rootScope, OptionGroup, sharedSettings, WorkPatternAPI) {
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

    (function init () {
      return $q.all([
        loadWorkPatterns(),
        loadJobContractRevisionChangeReasons()
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
      return WorkPatternAPI.get()
        .then(function (workPatterns) {
          vm.workPatterns = workPatterns;
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
          vm.changeReasons = reasons;
        });
    }

    return vm;
  }
});
