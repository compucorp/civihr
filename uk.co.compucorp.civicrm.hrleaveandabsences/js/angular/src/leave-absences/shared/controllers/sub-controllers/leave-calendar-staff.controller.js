/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarStaffController', ['$log', 'Contact', controller]);

  function controller ($log, Contact) {
    $log.debug('LeaveCalendarStaffController');

    var vm;

    return {
      /**
       * Initializes the sub-controller, passing the context (and thus the interface)
       * of the leave-calendar component's controller
       */
      init: function (_vm_) {
        vm = _vm_;
        vm.filters.userSettings.contacts_with_leaves = false;

        return api();
      }
    };

    /**
     * Returns the api of the sub-controller
     *
     * @return {Object}
     */
    function api () {
      return {
        /**
         * Returns the data of the current contact
         *
         * It returns it as a single-item array to comply with the standard
         * structure leave-calendar expect to receive the contacts as
         *
         * @return {Promise} resolves as an {Array}
         */
        loadContacts: function () {
          return Contact.all({
            id: { in: [vm.contactId] }
          })
          .then(function (contacts) {
            return contacts.list;
          });
        }
      };
    }
  }
});
