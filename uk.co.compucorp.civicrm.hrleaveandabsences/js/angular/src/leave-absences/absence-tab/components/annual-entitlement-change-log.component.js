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
    controller: AnnualEntitlementChangeLog
  });

  AnnualEntitlementChangeLog.$inject = ['AbsencePeriod', 'AbsenceType',
    'Entitlement', 'OptionGroup', 'shared-settings'];

  function AnnualEntitlementChangeLog (AbsencePeriod, AbsenceType, Entitlement,
  OptionGroup, sharedSettings) {
    var entitlementsChangeLog = [];
    var vm = this;

    vm.absencePeriod = null;
    vm.absenceTypes = [];
    vm.changeLogRows = [];
    vm.loading = { component: true };

    (function init () {
      loadAbsencePeriod()
      .then(loadAbsenceTypes)
      .then(loadChangeLog)
      .then(appendCurrentEntitlementsToChangeLog)
      .then(createChangeLogRows)
      .then(removeRepeatedComments)
      .then(highlightEntitlementWithComments)
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
     * Iterates over each one of the log row's entitlements providing
     * the previous entitlement values from the previous row and the updated
     * ones from the current row. This function can be used to display
     * the change between one row and the one below.
     *
     * @param {Object} changeLogRow - a row inside the changeLogRows array.
     * @param {Function} iterationFunction - the function that will be used
     * to iterate over each one of the entitlements. The signatura for this
     * function is:
     * - {EntitlementInstance} entitlement - the current entitlement being iterated.
     * - {Array} previousEntitlementValues - An array with the previous row's
     * entitlements before the current entitlement.
     * - {Array} updatedEntitlementValues - An array with the current row's
     * entitlements after the current entitlement.
     */
    function forEachEntitlementOfRow (changeLogRow, iterationFunction) {
      var previousEntitlementValues, updatedEntitlementValues;
      var nextRow = getNextLogRowOf(changeLogRow);

      changeLogRow.entitlements.forEach(function (entitlement, i) {
        previousEntitlementValues = nextRow.entitlements.slice(0, i);
        updatedEntitlementValues = changeLogRow.entitlements.slice(i + 1);

        iterationFunction(entitlement, previousEntitlementValues,
          updatedEntitlementValues);
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
      var entitlement, indexedEntitlements, sortedEntitlements;

      indexedEntitlements = _.indexBy(entitlements, 'entitlement_id.type_id');
      sortedEntitlements = vm.absenceTypes.map(function (absenceType) {
        entitlement = indexedEntitlements[absenceType.id];

        return _.extend({
          calculation_unit: absenceType['calculation_unit_name']
        }, entitlement);
      });

      return {
        date: moment(createdDate),
        entitlements: sortedEntitlements
      };
    }

    /**
     * Returns the row below the one provided. This can be used to access the
     * previous entitlement values. In case this is the last row, a row with
     * empty values is returned so it can be used for reference.
     *
     * @param {Object} changeLogRow - The change log row to use as reference
     * for finding the row below.
     * @return {Object}
     */
    function getNextLogRowOf (changeLogRow) {
      var nextRow, rowIndex;

      rowIndex = vm.changeLogRows.indexOf(changeLogRow);
      nextRow = vm.changeLogRows[rowIndex + 1];

      return nextRow || {
        date: changeLogRow.date.clone(),
        entitlements: changeLogRow.entitlements.map(function () {
          return {};
        })
      };
    }

    /**
     * Selects the entitlement rows to be highlighlighted based on the one
     * that has comments.
     */
    function highlightEntitlementWithComments () {
      var changeLogRow, entitlementComments, validEntitlementComments;

      for (var i = vm.changeLogRows.length - 1; i >= 0; i--) {
        changeLogRow = vm.changeLogRows[i];
        entitlementComments = _.pluck(changeLogRow.entitlements, 'comment');
        validEntitlementComments = _.compact(entitlementComments).length;

        if (validEntitlementComments === 1) {
          var commentIndex = _.findIndex(entitlementComments, 'length');

          changeLogRow.highlightedEntitlement = changeLogRow
            .entitlements[commentIndex];
        } else if (validEntitlementComments > 1) {
          splitEntitlementCommentsIntoMultipleRows(i);
        }
      }
    }

    /**
     * Inserts a new log row at the provided index.
     *
     * @param {Object} logRow - the log row to insert.
     * @param {Number} rowIndex - the index where the new row should be inserted.
     */
    function insertLogRowAtIndex (logRow, rowIndex) {
      vm.changeLogRows.splice(rowIndex, 0, logRow);
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
      return AbsenceType.all()
        .then(AbsenceType.loadCalculationUnits)
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
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

    /**
     * Removes comments that remain the same from one row to the other.
     * This reduces duplication of rows when displaying one row per comment.
     */
    function removeRepeatedComments () {
      var currentLogRowPointer, nextLogRowPointer;
      var logHasOneOrZeroRows = vm.changeLogRows.length <= 1;

      // There is no chance of repetition when there are one or zero rows
      if (logHasOneOrZeroRows) {
        return;
      }

      currentLogRowPointer = vm.changeLogRows.length - 2;
      nextLogRowPointer = vm.changeLogRows.length - 1;

      while (currentLogRowPointer >= 0) {
        var currentLogRow = vm.changeLogRows[currentLogRowPointer];
        var nextLogRow = vm.changeLogRows[nextLogRowPointer];

        currentLogRow.entitlements.forEach(function (currentLogRowEntitlement, i) {
          var nextLogRowEntitlement = nextLogRow.entitlements[i];

          if (currentLogRowEntitlement.comment === nextLogRowEntitlement.comment) {
            delete currentLogRowEntitlement.comment;
          }
        });

        currentLogRowPointer--;
        nextLogRowPointer--;
      }
    }

    /**
     * When a row has multiple entitlements with comments this function can be
     * used to create a new row for each of the comments. The new rows created
     * will display the change between comments and highlight the comment's
     * entitlement.
     *
     * @param {Number} rowIndex - The row index with multiple entitlement
     * comments that will be split into different rows.
     */
    function splitEntitlementCommentsIntoMultipleRows (rowIndex) {
      var entitlements, newRowWithSingleComment;
      var changeLogRow = vm.changeLogRows[rowIndex];

      forEachEntitlementOfRow(changeLogRow, function (entitlement,
      previousEntitlementValues, updatedEntitlementValues) {
        // Doesn't create a new row if the entitlement has no comments:
        if (!entitlement.comment) { return; }

        entitlements = previousEntitlementValues.concat(entitlement)
          .concat(updatedEntitlementValues);
        newRowWithSingleComment = {
          date: changeLogRow.date.clone(),
          entitlements: entitlements
        };
        newRowWithSingleComment.highlightedEntitlement = entitlement;

        insertLogRowAtIndex(newRowWithSingleComment, rowIndex++);
      });

      // Removes the original row to avoid repetition of information:
      removeLogRow(rowIndex);
    }

    /**
     * Removes the log row at the provided index.
     * @param {Number} rowIndex - the index of the row to be removed from the log.
     */
    function removeLogRow (rowIndex) {
      vm.changeLogRows.splice(rowIndex, 1);
    }
  }
});
