/* eslint-env amd */

(function (CRM, Drupal) {
  define([
    'common/modules/services'
  ], function (services) {
    'use strict';

    services.service('Session', ['$q', function ($q) {
      /**
       * `CRM.vars.session.logged_in_contact_id` is only defined on
       * the Admin Portal. `Drupal.settings.currentCiviCRMUserId` is defined
       * everywhere, but it comes from the SSP. Admin Portal should not rely
       * on definitions from SSP.
       */
      var session = {
        contact_id: CRM.vars.session
          ? CRM.vars.session.contact_id
          : Drupal.settings.currentCiviCRMUserId
      };

      if (!session.contact_id) {
        throw new Error('Session Error: *Logged In Contact Id* is not defined');
      }

      /**
       * This method returns the sessions as a promise. Even though the sessions
       * is a value this will change in the future as there will be a Session
       * endpoint on the API.
       *
       * @returns {Promise}
       */
      function get () {
        return $q.resolve(session);
      }

      return {
        get: get
      };
    }]);
  });
})(CRM, Drupal);
