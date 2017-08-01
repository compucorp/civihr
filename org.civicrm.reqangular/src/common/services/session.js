/* eslint-env amd */
/* global CRM */

(function (CRM, Drupal) {
  define([
    'common/modules/services'
  ], function (services) {
    'use strict';

    // Default Drupal value in case it's not defined
    Drupal = Drupal || { settings: {} };

    services.service('Session', ['$q', function ($q) {
      /**
       * `CRM.vars.session.logged_in_contact_id` is only defined on
       * the Admin Portal. `Drupal.settings.currentCiviCRMUserId` is defined
       * everywhere, but it comes from the SSP. Admin Portal should not rely
       * on definitions from SSP.
       */
      var session = {
        contactId: CRM.vars.session
          ? CRM.vars.session.contact_id
          : Drupal.settings.currentCiviCRMUserId
      };

      if (!session.contactId) {
        throw new Error('Session Error: *Logged In Contact Id* is not defined');
      }

      /**
       * Returns the session data of the currently logged in user (contact).
       *
       * @TODO Even though the session values are taken from the global CRM and
       * Drupal variables, this should be amended in the future once a Session
       * endpoint in the API is ready.
       *
       * @return {Promise} resolves with {Object}
       */
      function get () {
        return $q.resolve(session);
      }

      return {
        get: get
      };
    }]);
  });
})(CRM, window.Drupal);
