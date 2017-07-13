/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/controllers/calendar-ctrl'
], function (_, moment, components) {
  components.component('staffLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/staff-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$controller', '$log', '$q', '$rootScope', 'Calendar', 'Contact', controller]
  });

  function controller ($controller, $log, $q, $rootScope, Calendar, Contact) {
    $log.debug('Component: staff-leave-calendar');

    var vm = _.assign(Object.create($controller('CalendarCtrl')), this);

    /**
     * [_contacts description]
     * @return {[type]} [description]
     */
    vm._contacts = function () {
      return Contact.all({
        id: { in: [vm.contactId] }
      })
      .then(function (contacts) {
        return contacts.list;
      });
    };

    (function init () {
      vm._init();

      $rootScope.$on('LeaveRequest::new', vm.refresh);
      $rootScope.$on('LeaveRequest::edit', vm.refresh);
      $rootScope.$on('LeaveRequest::deleted', deleteLeaveRequest);
    })();

    /**
     * Event handler for Delete event of Leave Request
     *
     * @param  {object} event
     * @param  {object} leaveRequest
     */
    function deleteLeaveRequest (event, leaveRequest) {
      vm.leaveRequests = _.omit(vm.leaveRequests, function (leaveRequestObj) {
        return leaveRequestObj.id === leaveRequest.id;
      });
      vm._setCalendarProps();
    }

    return vm;
  }
});
