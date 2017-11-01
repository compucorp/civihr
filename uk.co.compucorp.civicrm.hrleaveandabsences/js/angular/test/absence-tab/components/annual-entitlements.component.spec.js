/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'common/angular',
  'mocks/data/absence-period-data',
  'mocks/data/absence-type-data',
  'mocks/data/entitlement-data',
  'leave-absences/shared/config',
  'leave-absences/absence-tab/app',
  'mocks/apis/absence-type-api-mock',
  'mocks/apis/absence-period-api-mock',
  'mocks/apis/entitlement-api-mock',
  'common/mocks/services/api/contact-mock',
  'common/mocks/services/hr-settings-mock'
], function (_, moment, angular, absencePeriodMocked, absenceTypeMocked, absenceEntitlementMocked) {
  'use strict';

  describe('annualEntitlements', function () {
    var contactId = 202;
    var $componentController, $log, $provide, $rootScope, absenceTypes,
      controller, ContactAPIMock, notification;

    beforeEach(module('leave-absences.templates', 'absence-tab', 'common.mocks', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (AbsencePeriodAPIMock, EntitlementAPIMock) {
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
      $provide.value('EntitlementAPI', EntitlementAPIMock);
    }));

    beforeEach(inject(['api.contact.mock',
      function (_ContactAPIMock_) {
        $provide.value('api.contact', _ContactAPIMock_);

        ContactAPIMock = _ContactAPIMock_;
      }]));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _notificationService_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      notification = _notificationService_;
      window.alert = function () {}; // prevent alert from being logged in console

      spyOn($log, 'debug');
      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    it('has a contact to load for', function () {
      expect(controller.contactId).toEqual(contactId);
    });

    it('has absence periods', function () {
      expect(controller.absencePeriods).toEqual(jasmine.any(Array));
    });

    it('has loaded absence periods', function () {
      expect(controller.loading.absencePeriods).toEqual(false);
    });

    it('navigates to a correct edit entitlements page', function () {
      expect(controller.editEntitlementsPageUrl).toEqual('/index.php?q=civicrm/admin/leaveandabsences/periods/manage_entitlements&cid=' + contactId + '&returnUrl=%2Findex.php%3Fq%3Dcivicrm%2Fcontact%2Fview%26cid%3D202%26selectedChild%3Dabsence');
    });

    describe('absence period', function () {
      var absencePeriod, mockedAbsencePeriod, mockedEntitlements;

      beforeEach(function () {
        absencePeriod = controller.absencePeriods[0];
        mockedAbsencePeriod = _.sortBy(absencePeriodMocked.all().values, function (absencePeriod) {
          return -moment(absencePeriod.start_date).valueOf();
        })[0];
        mockedEntitlements = absenceEntitlementMocked.all().values.map(storeEntitlementValue);
      });

      it('has period', function () {
        expect(absencePeriod.period).toEqual(moment(mockedAbsencePeriod.start_date).format('YYYY'));
      });

      it('has entitlements', function () {
        expect(absencePeriod.entitlements).toEqual(jasmine.any(Array));
      });

      describe('entitlement', function () {
        var entitlement, mockedEntitlement, mockedContact;

        beforeEach(function () {
          entitlement = absencePeriod.entitlements[0];
          mockedEntitlement = _.filter(mockedEntitlements, function (mockedEntitlement) {
            return mockedEntitlement.period_id === mockedAbsencePeriod.id;
          })[0];
          mockedContact = _.filter(ContactAPIMock.mockedContacts().list, function (mockedContact) {
            return mockedContact.id === contactId.toString();
          })[0];
        });

        it('has amount', function () {
          expect(entitlement.amount).toEqual(mockedEntitlement.value);
        });

        it('has the calculation unit name', function () {
          expect(entitlement.calculation_unit).toBe('days');
        });

        it('has comment', function () {
          expect(entitlement.comment).toEqual({
            message: mockedEntitlement.comment,
            author_name: mockedContact.display_name,
            date: mockedEntitlement.created_date
          });
        });
      });
    });

    describe('when user wants to see a comment to an entitlement', function () {
      beforeEach(function () {
        spyOn(notification, 'info').and.callThrough();
        controller.showComment('Sample comment');
      });

      it('shows the notification with a comment', function () {
        expect(notification.info).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(String));
      });
    });

    /**
     * Compiles the controller
     */
    function compileComponent () {
      absenceTypes = getAbsenceTypeMocks();

      controller = $componentController('annualEntitlements', null, {
        contactId: contactId,
        absenceTypes: absenceTypes
      });
      $rootScope.$digest();
    }

    /**
     * Returns a list of absence types mocks that have a calculation unit name.
     *
     * @return {Array}
     */
    function getAbsenceTypeMocks () {
      return absenceTypeMocked.all().values.map(function (absenceType) {
        return _.extend({
          'calculation_unit.name': 'days'
        }, absenceType);
      });
    }

    /**
     * A copy of part of the implementation of the real Entitlement API
     */
    function storeEntitlementValue (entitlement) {
      var clone = _.clone(entitlement);
      var value = clone['api.LeavePeriodEntitlement.getentitlement'].values[0].entitlement;

      clone['value'] = value;
      delete clone['api.LeavePeriodEntitlement.getentitlement'];

      return clone;
    }
  });
});
