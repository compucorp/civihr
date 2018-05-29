/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/contact.data'
], function (_, contactsMockData) {
  var LeaveCalendarService = jasmine.createSpyObj('LeaveCalendarService', ['init']);
  var leaveCalendarInstance = jasmine.createSpyObj('leaveCalendarInstance', [
    'loadAllLookUpContacts', 'loadContactIdsToReduceTo',
    'loadFilteredContacts']);
  var data = {
    contactIdsToReduceTo: [_.uniqueId(), _.uniqueId(), _.uniqueId()],
    filteredContacts: _.clone(contactsMockData.all.values.slice(0, 2)),
    lookedUpContacts: _.clone(contactsMockData.all.values)
  };

  function setup ($q) {
    LeaveCalendarService.init.and.returnValue(leaveCalendarInstance);
    leaveCalendarInstance.loadAllLookUpContacts.and.returnValue($q.resolve(
      data.lookedUpContacts));
    leaveCalendarInstance.loadContactIdsToReduceTo.and.returnValue($q.resolve(
      data.contactIdsToReduceTo));
    leaveCalendarInstance.loadFilteredContacts.and.returnValue($q.resolve(
      data.filteredContacts));
  }

  return {
    data: data,
    instance: leaveCalendarInstance,
    service: LeaveCalendarService,
    setup: setup
  };
});
