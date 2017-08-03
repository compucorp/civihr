/* eslint-env amd */

(function (CRM, Drupal) {
  define([
    'common/modules/models',
    'common/models/model'
  ], function (models) {
    'use strict';

    models.factory('Session', [
      '$log', '$q', 'Model',
      function ($log, $q, Model) {
        $log.debug('Session');

        return Model.extend({
          /**
           * Returns the session data of the currently logged in user (contact)
           *
           * @NOTE This model is built on the following assumptions:
           * 1. Global "CRM" object always exists with "vars" property that is object
           * 2. "CRM.vars" object _might_ have "session" property that contains user ID
           * 3. If not, global "Drupal" object must exist with "settings" property containing user ID
           * In any other cases, exception will be thrown and the service will not work
           *
           * @TODO Even though the session values are taken from the global CRM (Admin Portal)
           * and Drupal (SSP) variables, this should be amended in the future once a Session
           * endpoint in the API is ready.
           *
           * @return {Promise} resolves with {Object}
           */
          get: function () {
            var session = {
              contactId: CRM.vars.session
                ? CRM.vars.session.contact_id
                : Drupal.settings.currentCiviCRMUserId
            };

            // Ensure that user ID is set, otherwise throw error
            if (!session.contactId) {
              throw new Error('Session Error: *Logged In Contact Id* is not defined');
            }

            return $q.resolve(session);
          }
        });
      }
    ]);
  });
})(CRM, window.Drupal);
