/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/absence-tab/modules/components',
  'common/models/contact'
], function (_, moment, components) {
  components.component('annualEntitlements', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/annual-entitlements.html';
    }],
    controllerAs: 'entitlements',
    controller: ['$log', '$q', 'AbsenceType', 'AbsencePeriod', 'Entitlement', 'Contact', controller]
  });

  function controller ($log, $q, AbsenceType, AbsencePeriod, Entitlement, Contact) {
    $log.debug('Component: annual-entitlements');

    var vm = {};

    vm.absenceTypes = [];
    vm.absencePeriods = [];
    vm.contactId = this.contactId;
    vm.contacts = [];
    vm.entitlements = [];
    vm.loaded = { absencePeriods: false };

    (function init () {
      return $q.all([
        loadAbsenceTypes(),
        loadEntitlements()
      ])
      .then(function () {
        return loadCommentsAuthors();
      })
      .then(function () {
        return loadAbsencePeriods();
      });
    })();

    /**
     * Shows a comment to the entitlement
     *
     * @param {object} comment
     */
    vm.showComment = function (comment) {
      /*
       * @TODO There is no support for footer in CRM.alert at the moment.
       * This code should be refactored as soon as CRM.alert supports footer.
       * At the moment the footer is constructed via rich HTML directly via body text
       */
      var text = comment.message +
        '<br/><br/><strong>Last updated:' +
        '<br/>By: ' + comment.author_name +
        '<br/>Date: ' + moment(comment.date).format('DD/M/YYYY HH.mm') +
        '</strong>';

      CRM.alert(text, 'Calculation comment:', 'error');
    };

    /**
     * Loads absence periods
     *
     * @return {Promise}
     */
    function loadAbsencePeriods () {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          setAbsencePeriodsProps(absencePeriods);
        });
    }

    /**
     * Loads absence types
     *
     * @return {Promise}
     */
    function loadAbsenceTypes () {
      return AbsenceType.all()
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
        });
    }

    /**
     * Loads entitlements comments authors
     *
     * @return {Promise}
     */
    function loadCommentsAuthors () {
      var authorsIDs = _.uniq(_.map(vm.entitlements, function (entitlement) {
        return entitlement.comment_author_id;
      }));

      return Contact.all({ id: { 'IN': authorsIDs } })
        .then(function (contacts) {
          vm.contacts = contacts.list;
        });
    }

    /**
     * Loads entitlements
     *
     * @return {Promise}
     */
    function loadEntitlements () {
      return Entitlement.all({contact_id: vm.contactId})
        .then(function (entitlements) {
          vm.entitlements = entitlements;
        });
    }

    /**
     * Processes entitlements from data and sets them to a controller
     *
     * @param {object} data
     */
    function setAbsencePeriodsProps (absencePeriods) {
      // Get all periods as per entitlements
      var periods = _.uniq(_.map(vm.entitlements, function (entitlement) {
        return entitlement.period_id;
      }));

      // Filter periods needed for loaded entitlements only
      absencePeriods = _.filter(absencePeriods, function (absencePeriod) {
        return periods.indexOf(absencePeriod.id) !== -1;
      });
      absencePeriods = _.sortBy(absencePeriods, function (absencePeriod) {
        return -moment(absencePeriod.start_date).valueOf();
      });
      vm.absencePeriods = _.map(absencePeriods, function (absencePeriod) {
        var absences = _.map(vm.absenceTypes, function (absenceType) {
          var leave = _.filter(vm.entitlements, function (entitlement) {
            return entitlement.type_id === absenceType.id && entitlement.period_id === absencePeriod.id;
          })[0];

          return leave ? {
            amount: leave.value,
            comment: leave.comment ? {
              message: leave.comment,
              author_name: _.filter(vm.contacts, function (contact) {
                return contact.contact_id === leave.comment_author_id;
              })[0].display_name,
              date: leave.comment_date
            } : null
          } : null;
        });

        return {
          period: absencePeriod.title,
          absences: absences
        };
      });
      vm.loaded.absencePeriods = true;
    }

    return vm;
  }
});
