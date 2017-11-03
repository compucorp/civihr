/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/absence-tab/modules/components',
  'common/models/contract'
], function (_, moment, components) {
  components.component('annualEntitlementChangeLog', {
    bindings: {
      contactId: '<',
      periodId: '<',
      dismissModal: '&'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/annual-entitlement-change-log.html';
    }],
    controllerAs: 'changeLog',
    controller: AnualEntitlementChangeLog
  });

  AnualEntitlementChangeLog.$inject = ['AbsencePeriod', 'AbsenceType',
    'Entitlement', 'OptionGroup', 'shared-settings'];

  function AnualEntitlementChangeLog (AbsencePeriod, AbsenceType, Entitlement,
  OptionGroup, sharedSettings) {
    var entitlementsChangeLog = [];
    var vm = this;

    vm.absencePeriod = null;
    vm.absenceTypes = [];
    vm.changeLogRows = [];
    vm.loading = { component: true };

    (function init () {
      loadAbsencePeriod()
      .then(loadCalculationUnits)
      .then(loadAbsenceTypes)
      .then(loadChangeLog)
      .then(appendCurrentEntitlementsToChangeLog)
      .then(createChangeLogRows)
      .finally(function () {
        vm.loading.component = false;
      });
    })();

    /**
     * Appends the current entitlements for the user and period into the change
     * log's list. This is done because the logs don't return the current
     * entitlement values, just the previous ones.
     *
     * @return {Promise}
     */
    function appendCurrentEntitlementsToChangeLog () {
      return getCurrentEntitlementsLog().then(function (currentEntitlements) {
        entitlementsChangeLog = entitlementsChangeLog.concat(currentEntitlements);
      });
    }

    /**
     * Creates the structure used to display entitlement logs by grouping
     * changes using their creation date and sorting them in a descending order.
     */
    function createChangeLogRows () {
      var groupedEntitlements = _.groupBy(entitlementsChangeLog, 'created_date');

      vm.changeLogRows = _.map(groupedEntitlements, getChangeLogRow)
        .sort(function (previousRow, currentRow) {
          return currentRow.date.diff(previousRow.date);
        });
    }

    /**
     * Returns the current entitlements for the contact and period and maps
     * them into entitlement logs format.
     *
     * @return {Promise}
     */
    function getCurrentEntitlementsLog () {
      return Entitlement.all({
        contact_id: vm.contactId,
        period_id: vm.periodId
      }, false)
      .then(function (currentEntitlements) {
        return currentEntitlements.map(function (entitlement) {
          return {
            'comment': entitlement.comment,
            'contact_id': entitlement.contact_id,
            'created_date': entitlement.created_date,
            'editor_id': entitlement.editor_id,
            'entitlement_amount': entitlement.value,
            'entitlement_id': entitlement.id,
            'entitlement_id.type_id': entitlement.type_id
          };
        });
      });
    }

    /**
     * Each change log row consist of their creation date and a list of
     * entitlements that are sorted in the same order as the absence types that
     * are displayed in the table's header. The calculation units are also added
     * to the entitlements in order to display if the change was in hours or days.
     *
     * @param {Array} entitlements - List of entitlement changes for a specific date.
     * @param {String} createdDate - The date of creation for the entitlements
     * provided.
     * @return {Object}
     */
    function getChangeLogRow (entitlements, createdDate) {
      var indexedEntitlements = _.indexBy(entitlements, 'entitlement_id.type_id');
      var sortedEntitlements = vm.absenceTypes.map(function (absenceType) {
        var entitlement = indexedEntitlements[absenceType.id];

        return _.extend({
          calculation_unit: absenceType['calculation_unit.name']
        }, entitlement);
      });

      return {
        date: moment(createdDate),
        entitlements: sortedEntitlements
      };
    }

    /**
     * Loads the absence period that corresponds to the period id given to
     * the component.
     *
     * @return {Promise}
     */
    function loadAbsencePeriod () {
      return AbsencePeriod.all({
        id: vm.periodId
      })
      .then(function (absencePeriods) {
        vm.absencePeriod = absencePeriods[0];
      });
    }

    /**
     * Loads a list of absence types and maps their calculation unit's names and
     * labels.
     *
     * @param {Object} calculationUnits - A map of calculation units indexed by
     * their value.
     * @return {Promise}
     */
    function loadAbsenceTypes (calculationUnits) {
      return AbsenceType.all().then(function (absenceTypes) {
        return absenceTypes.map(function (absenceType) {
          var unit = calculationUnits[absenceType.calculation_unit];

          return _.extend({
            'calculation_unit.name': unit.name,
            'calculation_unit.label': unit.label
          }, absenceType);
        });
      })
      .then(function (absenceTypes) {
        vm.absenceTypes = absenceTypes;
      });
    }

    /**
     * Returns a map of absence type calculation units indexed by their
     * values.
     *
     * @return {Promise}
     */
    function loadCalculationUnits () {
      return OptionGroup.valuesOf('hrleaveandabsences_absence_type_calculation_unit')
      .then(function (calculationUnits) {
        return _.indexBy(calculationUnits, 'value');
      });
    }

    /**
     * Loads and stores the Leave Entitlements change log for the contact and
     * period provided.
     *
     * @return {Promise}
     */
    function loadChangeLog () {
      return Entitlement.logs({
        contact_id: vm.contactId,
        period_id: vm.periodId
      })
      .then(function (changeLog) {
        entitlementsChangeLog = changeLog;
      });
    }
  }
});
