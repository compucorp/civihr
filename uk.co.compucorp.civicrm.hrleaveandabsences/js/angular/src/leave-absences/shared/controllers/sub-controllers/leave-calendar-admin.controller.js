/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarAdminController', ['$log', 'Contact', controller]);

  function controller ($log, Contact) {
    $log.debug('LeaveCalendarAdminController');

    var vm;

    return {
      /**
       * Initializes the sub-controller, passing the context (and thus the interface)
       * of the leave-calendar component's controller
       */
      init: function (_vm_) {
        vm = _vm_;
        vm.showContactName = true;
        vm.showFilters = true;

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
         * Returns all contacts
         * @return {Promise} resolves as an {Array}
         */
        loadContacts: function () {
          return Contact.all()
            .then(function (contacts) {
              vm.lookupContacts = contacts.list;

              return contacts.list;
            });
        }
      };
    }
  }
});
