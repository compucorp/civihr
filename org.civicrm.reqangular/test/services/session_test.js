/* eslint-env amd, jasmine */
/* global Drupal */

(function (CRM, Drupal) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/session'
  ], function () {
    'use strict';

    describe('Session', function () {
      var $scope, Session, value;
      var contactId = 999;

      beforeEach(module('common.services'));

      describe('get method', function () {
        describe('when logged in at the Admin Portal', function () {
          beforeEach(function () {
            CRM.vars = {
              session: {
                contact_id: contactId
              }
            };
          });

          beforeEach(initSessionService);

          it('should return the contact id for the logged in contact', function () {
            expect(value).toBe(contactId);
          });
        });

        describe('when logged in at the Self Serving Portal', function () {
          beforeEach(function () {
            CRM.vars = {};
            Drupal.settings.currentCiviCRMUserId = contactId;
          });

          beforeEach(initSessionService);

          it('should return the contact id for the logged in contact', function () {
            expect(value).toBe(contactId);
          });
        });

        describe('when the logged in contact id is not defined', function () {
          beforeEach(function () {
            CRM.vars = {};
            Drupal.settings = {};
          });

          it('should throw an error', function () {
            /**
             * Note: this version of Jasmine .toThrowError does not
             * work as intended.
             */
            expect(initSessionService).toThrow();
          });
        });
      });

      function initSessionService () {
        inject(function (_$rootScope_, _Session_) {
          $scope = _$rootScope_;
          Session = _Session_;

          Session.get().then(function (session) {
            value = session.contactId;
          });

          $scope.$digest();
        });
      }
    });
  });
})(CRM, Drupal);
