define([
  'mocks/data/option-group-mock-data',
  'mocks/data/absence-type-data',
  'mocks/data/work-pattern-data',
], function (optionGroupMock, absenceTypeData, workPatternMock) {

  return {
    /**
     * Creates a LeaveRequest with random values
     *
     * @return {object} containing valid leaverequest data
     **/
    createRandomLeaveRequest: function () {
      return {
        'type_id': absenceTypeData.getRandomAbsenceType('id'),
        'contact_id': '202',
        'status_id': optionGroupMock.randomValue('hrleaveandabsences_leave_request_status', 'value'),
        'from_date': '2016-02-01',
        'from_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
        'to_date': '2016-02-03',
        'to_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
      };
    },
    /**
     * Creates a Sickness LeaveRequest with random values
     *
     * @return {object} containing valid leaverequest data
     **/
    createRandomSicknessRequest: function () {
      return {
        'type_id': absenceTypeData.getRandomAbsenceType('id'),
        'contact_id': '202',
        'status_id': optionGroupMock.randomValue('hrleaveandabsences_leave_request_status', 'value'),
        'from_date': '2016-02-01',
        'from_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
        'to_date': '2016-02-03',
        'to_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
        "reason": optionGroupMock.randomValue('hrleaveandabsences_sickness_reason', 'name'),
      };
    },
    /**
     * Creates a TOIL LeaveRequest with random values
     *
     * @return {object} containing valid leaverequest data
     **/
    createRandomTOILRequest: function () {
      return {
        'type_id': absenceTypeData.getRandomAbsenceType('id'),
        'contact_id': '202',
        'status_id': optionGroupMock.randomValue('hrleaveandabsences_leave_request_status', 'value'),
        'from_date': '2016-02-01',
        'from_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
        'to_date': '2016-02-03',
        'to_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
        "duration": 180
      };
    },
    /**
     * Find if HTTP POST is for entity LeaveRequest and action create/update
     *
     * @param {object} data - contains the data sent along with http request
     * @param {string} entity - civicrm data entity like LeaveRequest
     * @param {string} action - civicrm action like create, get etc.,
     *
     * @return {boolean} true if request filter succeeds else false
     **/
    isEntityActionInPost: function (data, entity, action) {
      var uriParts = decodeURIComponent(data).split('&');
      var uriEntityAction = {};

      var uriFilter = uriParts.filter(function (item) {
        var itemSplit = item.split('=');
        if (itemSplit[0] === 'entity' || itemSplit[0] === 'action') {
          uriEntityAction[itemSplit[0]] = itemSplit[1];
          return true;
        }
      });

      //'update' is 'create' call with id set
      if (uriEntityAction.entity === entity && uriEntityAction.action === action) {
        return true;
      }

      return false;
    },
    /**
     * Gets a date for given day type
     *
     * @param {string} dayType - like working_day, non_working_day, weekend
     * @return {string} date
     **/
    getDate: function (dayType) {
      return workPatternMock.daysData().values.find(function (data) {
        return data.type.name === dayType;
      });
    }
  };
});
