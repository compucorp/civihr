/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/contact.data'
], function (_, contactsMockData) {
  var $q, vm;
  var LeaveCalendarService = jasmine.createSpyObj('LeaveCalendarService', ['init']);
  var leaveCalendarInstance = jasmine.createSpyObj('leaveCalendarInstance', [
    'loadContactsForAdmin', 'loadFilteredContacts', 'loadLookUpContacts']);
  var data = {
    contactIdsToReduceTo: [_.uniqueId(), _.uniqueId(), _.uniqueId()],
    filteredContacts: _.clone(contactsMockData.all.values.slice(0, 2)),
    lookedUpContacts: _.clone(contactsMockData.all.values)
  };

  /**
   * Simulates the init function of the leave calendar service. It stores the view
   * model reference and returns a leave calendar instance.
   *
   * @param {Object} vm the view model containing the filters information, selected period, etc.
   * @return {Object} a mock leave calendar instance.
   */
  function LeaveCalendarServiceInit (_vm_) {
    vm = _vm_;

    return leaveCalendarInstance;
  }

  /**
   * Simulates a call to the leave calendar instance's loadContactsForAdmin method.
   * It assigns the contact ids to reduce to and the look up contacts to the previously provided
   * view model. Finally, it returns a list of filtered contacts.
   *
   * @return {Array} a list of contacts.
   */
  function loadContactsForAdmin () {
    vm.contactIdsToReduceTo = data.contactIdsToReduceTo;
    vm.lookupContacts = data.lookedUpContacts;

    return $q.resolve(data.filteredContacts);
  }

  /**
   * Initializes the leave calendar service mock for testing purposes.
   *
   * @param $q the angular $q service.
   */
  function setup (_$q_) {
    $q = _$q_;
    LeaveCalendarService.init.and.callFake(LeaveCalendarServiceInit);
    leaveCalendarInstance.loadContactsForAdmin.and.callFake(loadContactsForAdmin);
    leaveCalendarInstance.loadFilteredContacts.and.returnValue($q.resolve(
      data.filteredContacts));
    leaveCalendarInstance.loadLookUpContacts.and.returnValue($q.resolve(
      data.lookedUpContacts));
  }

  return {
    data: data,
    instance: leaveCalendarInstance,
    service: LeaveCalendarService,
    setup: setup
  };
});
