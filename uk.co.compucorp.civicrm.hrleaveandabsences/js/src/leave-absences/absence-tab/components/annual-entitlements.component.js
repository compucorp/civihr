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
        absenceTypes: '<',
        contactId: '<'
      },
      templateUrl: ['settings', function (settings) {
        return settings.pathTpl + 'components/annual-entitlements.html';
      }],
      controllerAs: 'entitlements',
      controller: AnnualEntitlementsController
    });

    AnnualEntitlementsController.$inject = ['$log', '$q', '$rootElement',
      '$uibModal', 'AbsenceType', 'AbsencePeriod', 'Entitlement', 'Contact',
      'notificationService'];

    function AnnualEntitlementsController ($log, $q, $rootElement,
      $uibModal, AbsenceType, AbsencePeriod, Entitlement, Contact,
      notification) {
      $log.debug('Component: annual-entitlements');

      var allEntitlements = [];
      var contacts = [];
      var vm = this;

      vm.absencePeriods = [];
      vm.loading = { absencePeriods: true };

      vm.$onInit = $onInit;
      vm.getEditEntitlementsPageURL = getEditEntitlementsPageURL;
      vm.openAnnualEntitlementChangeLog = openAnnualEntitlementChangeLog;
      vm.showComment = showComment;

      function $onInit () {
        loadEntitlements()
          .then(loadCommentsAuthors)
          .then(loadAbsencePeriods)
          .then(filterAbsencePeriods)
          .then(filterAbsenceTypes)
          .then(setAbsencePeriodsProps)
          .finally(function () {
            vm.loading.absencePeriods = false;
          });
      }

      /**
       * Filters absence periods basing on loaded entitlements
       */
      function filterAbsencePeriods () {
        vm.absencePeriods = _.chain(vm.absencePeriods)
          .filter(function (absencePeriod) {
            return _.find(allEntitlements, function (entitlement) {
              return entitlement.period_id === absencePeriod.id;
            });
          })
          .sortBy(function (absencePeriod) {
            return -moment(absencePeriod.start_date).valueOf();
          })
          .value();
      }

      /**
       * Filters absence types basing on loaded entitlements
       */
      function filterAbsenceTypes () {
        vm.absenceTypes = _.filter(vm.absenceTypes, function (absenceType) {
          return _.find(allEntitlements, function (entitlement) {
            return entitlement.type_id === absenceType.id;
          });
        });
      }

      /**
       * Gets the URL to the Manage Entitlement page
       * for the contact and absence period provided
       *
       * @param  {Number|String} absencePeriodId
       * @return {String}
       */
      function getEditEntitlementsPageURL (absencePeriodId) {
        var path = 'civicrm/admin/leaveandabsences/periods/manage_entitlements';
        var returnPath = 'civicrm/contact/view';
        var returnUrl = CRM.url(returnPath, {
          cid: vm.contactId,
          selectedChild: 'absence'
        });

        return CRM.url(path, {
          id: absencePeriodId,
          cid: vm.contactId,
          returnUrl: returnUrl
        });
      }

      /**
       * Loads absence periods
       *
       * @return {Promise}
       */
      function loadAbsencePeriods () {
        return AbsencePeriod.all()
          .then(function (absencePeriods) {
            vm.absencePeriods = absencePeriods;
          });
      }

      /**
       * Loads authors of comments to the entitlements
       *
       * @return {Promise}
       */
      function loadCommentsAuthors () {
        var authorsIDs = _.uniq(_.map(allEntitlements, function (entitlement) {
          return entitlement.editor_id;
        }));

        return Contact.all({ id: { 'IN': authorsIDs } })
          .then(function (data) {
            contacts = _.keyBy(data.list, 'contact_id');
          });
      }

      /**
       * Loads entitlements
       *
       * @return {Promise}
       */
      function loadEntitlements () {
        return Entitlement.all({ contact_id: vm.contactId })
          .then(function (entitlements) {
            allEntitlements = entitlements;
          });
      }

      /**
       * Opens the Annual entitlement change log modal
       * for the current contact and the given period
       */
      function openAnnualEntitlementChangeLog (periodId) {
        $uibModal.open({
          appendTo: $rootElement.children().eq(0),
          templateUrl: 'annual-entitlement-change-log-modal',
          controller: ['$uibModalInstance', function ($modalInstance) {
            this.contactId = vm.contactId;
            this.dismiss = $modalInstance.dismiss;
            this.periodId = periodId;
          }],
          controllerAs: 'modal'
        });
      }

      /**
       * Processes entitlements from data and sets them to the controller
       */
      function setAbsencePeriodsProps () {
        vm.absencePeriods = _.map(vm.absencePeriods, function (absencePeriod) {
          var entitlements = _.map(vm.absenceTypes, function (absenceType) {
            var leave = _.find(allEntitlements, function (entitlement) {
              return entitlement.type_id === absenceType.id &&
                entitlement.period_id === absencePeriod.id;
            });

            return leave ? {
              amount: leave.value,
              calculation_unit: absenceType['calculation_unit_name'],
              comment: leave.comment ? {
                message: leave.comment,
                author_name: contacts[leave.editor_id].display_name,
                date: leave.created_date
              } : null
            } : null;
          });

          return {
            id: absencePeriod.id,
            title: absencePeriod.title,
            entitlements: entitlements
          };
        });
      }

      /**
       * Shows a comment to the entitlement
       *
       * @NOTE There is no support for footer in notificationService at the moment.
       * This code should be refactored as soon as notificationService supports footer.
       * At the moment the footer is constructed via rich HTML directly via body text
       *
       * @param {Object} comment
       */
      function showComment (comment) {
        var text = comment.message +
          '<br/><br/><strong>Last updated:' +
          '<br/>By: ' + comment.author_name +
          '<br/>Date: ' + moment.utc(comment.date).local().format('DD/M/YYYY HH:mm') +
          '</strong>';

        notification.info('Calculation comment:', text);
      }
    }
  });
})(CRM);
