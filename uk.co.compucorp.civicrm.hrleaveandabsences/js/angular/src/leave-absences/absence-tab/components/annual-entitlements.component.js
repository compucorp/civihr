/* eslint-env amd */

(function (CRM) {
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
      controller: ['$log', '$q', 'AbsenceType', 'AbsencePeriod', 'Entitlement', 'Contact', 'notificationService', controller]
    });

    function controller ($log, $q, AbsenceType, AbsencePeriod, Entitlement, Contact, notification) {
      $log.debug('Component: annual-entitlements');

      var vm = this;
      var contacts = [];
      var allEntitlements = [];

      vm.absencePeriods = [];
      vm.absenceTypes = [];
      vm.loading = { absencePeriods: true };
      vm.editEntitlementsPageUrl = getEditEntitlementsPageURL(vm.contactId);

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
        })
        .finally(function () {
          vm.loading.absencePeriods = false;
        });
      })();

      /**
       * Shows a comment to the entitlement
       *
       * @param {Object} comment
       */
      vm.showComment = function (comment) {
        /*
         * @TODO There is no support for footer in notificationService at the moment.
         * This code should be refactored as soon as notificationService supports footer.
         * At the moment the footer is constructed via rich HTML directly via body text
         */
        var text = comment.message +
          '<br/><br/><strong>Last updated:' +
          '<br/>By: ' + comment.author_name +
          '<br/>Date: ' + moment.utc(comment.date).local().format('DD/M/YYYY HH:mm') +
          '</strong>';

        notification.info('Calculation comment:', text);
      };

      /**
       * Loads absence periods
       *
       * @return {Promise}
       */
      function loadAbsencePeriods () {
        return AbsencePeriod.all().then(setAbsencePeriodsProps);
      }

      /**
       * Loads absence types
       *
       * @return {Promise}
       */
      function loadAbsenceTypes () {
        return AbsenceType.all()
          .then(function (data) {
            vm.absenceTypes = data;
          });
      }

      /**
       * Loads entitlements comments authors
       *
       * @return {Promise}
       */
      function loadCommentsAuthors () {
        var authorsIDs = _.uniq(_.map(allEntitlements, function (entitlement) {
          return entitlement.editor_id;
        }));

        return Contact.all({ id: { 'IN': authorsIDs } })
          .then(function (data) {
            contacts = _.indexBy(data.list, 'contact_id');
          });
      }

      /**
       * Loads entitlements
       *
       * @return {Promise}
       */
      function loadEntitlements () {
        return Entitlement.all({ contact_id: vm.contactId })
          .then(function (data) {
            allEntitlements = data;
          });
      }

      /**
       * Processes entitlements from data and sets them to the controller
       *
       * @param {Object} absencePeriods
       */
      function setAbsencePeriodsProps (absencePeriods) {
        // Get all periods as per entitlements
        var periods = _.uniq(_.map(allEntitlements, function (entitlement) {
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
          var entitlements = _.map(vm.absenceTypes, function (absenceType) {
            var leave = _.filter(allEntitlements, function (entitlement) {
              return entitlement.type_id === absenceType.id && entitlement.period_id === absencePeriod.id;
            })[0];

            return leave ? {
              amount: leave.value,
              comment: leave.comment ? {
                message: leave.comment,
                author_name: contacts[leave.editor_id].display_name,
                date: leave.created_date
              } : null
            } : null;
          });

          return {
            period: absencePeriod.title,
            entitlements: entitlements
          };
        });
      }

      /**
        * Returns the URL to the Manage Entitlement page.
        *
        * The given contact ID is added to the URL, as the cid parameter.
        *
        * @param {number} contactId
        * @return {string}
        */
      function getEditEntitlementsPageURL (contactId) {
        var path = 'civicrm/admin/leaveandabsences/periods/manage_entitlements';
        var returnPath = 'civicrm/contact/view';
        var returnUrl = CRM.url(returnPath, { cid: contactId, selectedChild: 'absence' });
        return CRM.url(path, { cid: contactId, returnUrl: returnUrl });
      }

      return vm;
    }
  });
})(CRM);
