define([
  'mocks/data/option-group-mock-data',
  'mocks/data/absence-type-data',
], function (optionGroupMock, absenceTypeData) {
  var mockData = {
    allData: {
      'is_error': 0,
      'version': 3,
      'count': 6,
      'values': [{
        'id': '17',
        'type_id': '1',
        'contact_id': '202',
        'status_id': '1',
        'from_date': '2016-02-01',
        'from_date_type': '1',
        'to_date': '2016-02-03',
        'to_date_type': '1'
      }, {
        'id': '18',
        'type_id': '1',
        'contact_id': '202',
        'status_id': '1',
        'from_date': '2016-08-17',
        'from_date_type': '1',
        'to_date': '2016-08-25',
        'to_date_type': '1'
      }, {
        'id': '19',
        'type_id': '1',
        'contact_id': '202',
        'status_id': '6',
        'from_date': '2016-01-30',
        'from_date_type': '1',
        'to_date': '2016-02-01',
        'to_date_type': '1'
      }, {
        'id': '20',
        'type_id': '1',
        'contact_id': '202',
        'status_id': '3',
        'from_date': '2016-11-23',
        'from_date_type': '1',
        'to_date': '2016-11-28',
        'to_date_type': '1'
      }, {
        'id': '21',
        'type_id': '3',
        'contact_id': '202',
        'status_id': '5',
        'from_date': '2016-06-03',
        'from_date_type': '1',
        'to_date': '2016-06-13',
        'to_date_type': '1'
      }, {
        'id': '22',
        'type_id': '2',
        'contact_id': '202',
        'status_id': '4',
        'from_date': '2016-01-01',
        'from_date_type': '1',
        'to_date': '2016-01-01',
        'to_date_type': '1'
      }]
    },
    singleDataSuccess: {
      'is_error': 0,
      'version': 3,
      'count': 1,
      'values': [{
        'id': '17',
        'type_id': '1',
        'contact_id': '202',
        'status_id': '1',
        'from_date': '2016-02-01',
        'from_date_type': '1',
        'to_date': '2016-02-03',
        'to_date_type': '1'
      }]
    },
    singleDataError: {
      'is_error': 1,
      'error_message': jasmine.any(String)
    },
    balanceChangeByAbsenceTypeData: {
      'is_error': 0,
      'version': 3,
      'count': 3,
      'values': {
        '1': -21,
        '2': -1,
        '3': -11
      }
    },
    calculateBalanceChangeRequestData: {
      'contact_id': 109,
      'from_date': '2016-11-05',
      'from_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
      'to_date': '2016-11-10',
      'to_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name')
    },
    calculateBalanceChangeData: {
      'is_error': 0,
      'version': 3,
      'count': 2,
      'values': {
        'amount': -4,
        'breakdown': [{
          'date': '2016-11-05',
          'amount': 0,
          'type': {
            'id': 4,
            'value': 4,
            'label': 'Weekend'
          }
        }, {
          'date': '2016-11-06',
          'amount': 0,
          'type': {
            'id': 4,
            'value': 4,
            'label': 'Weekend'
          }
        }, {
          'date': '2016-11-07',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': 'All Day'
          }
        }, {
          'date': '2016-11-08',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': 'All Day'
          }
        }, {
          'date': '2016-11-09',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': 'All Day'
          }
        }, {
          'date': '2016-11-10',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': 'All Day'
          }
        }]
      }
    },
    randomLeaveRequestFromServer: {
      'is_error': 0,
      'version': 3,
      'count': 1,
      'values': [{
        'id': '17',
        'type_id': absenceTypeData.getRandomAbsenceType('id'),
        'contact_id': '202',
        'status_id': optionGroupMock.randomValue('hrleaveandabsences_leave_request_status', 'value'),
        'from_date': '2016-02-01',
        'from_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'value'),
        'to_date': '2016-02-03',
        'to_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'value'),
      }]
    },
    randomLeaveRequestToServer: {
      'type_id': absenceTypeData.getRandomAbsenceType('id'),
      'contact_id': '202',
      'status_id': optionGroupMock.randomValue('hrleaveandabsences_leave_request_status', 'name'),
      'from_date': '2016-02-01',
      'from_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
      'to_date': '2016-02-03',
      'to_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
    },
    randomUpdateLeaveRequestToServer: {
      'id': '17',
      'type_id': absenceTypeData.getRandomAbsenceType('id'),
      'contact_id': '202',
      'status_id': optionGroupMock.randomValue('hrleaveandabsences_leave_request_status', 'name'),
      'from_date': '2016-02-01',
      'from_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
      'to_date': '2016-02-03',
      'to_date_type': optionGroupMock.randomValue('hrleaveandabsences_leave_request_day_type', 'name'),
    },
    isValidData: {
      "is_error": 0,
      "count": 0,
      "values": []
    },
    isNotValidData: {
      "is_error": 0,
      "count": 1,
      "values": {
        "from_date": [
          "overlaps_existing_leave_request"
        ]
      }
    }
  };
  return {
    all: function () {
      return mockData.allData;
    },
    singleDataSuccess: function () {
      return mockData.singleDataSuccess;
    },
    singleDataError: function () {
      return mockData.singleDataError;
    },
    balanceChangeByAbsenceType: function () {
      return mockData.balanceChangeByAbsenceTypeData;
    },
    getRandomLeaveRequest: function () {
      return mockData.randomLeaveRequestFromServer;
    },
    createRandomLeaveRequest: function () {
      return mockData.randomLeaveRequestToServer;
    },
    updateRandomLeaveRequest: function () {
      return mockData.randomUpdateLeaveRequestToServer;
    },
    calculateBalanceChangeRequest: function () {
      return mockData.calculateBalanceChangeRequestData;
    },
    calculateBalanceChange: function () {
      return mockData.calculateBalanceChangeData;
    },
    getAllRequestDayTypes: function () {
      return optionGroupMock.getAllValuesForKey('hrleaveandabsences_leave_request_day_type', 'name');
    },
    getAllRequestDayValues: function () {
      return optionGroupMock.getAllValuesForKey('hrleaveandabsences_leave_request_day_type', 'value');
    },
    getAllRequestStatusesNames: function () {
      return optionGroupMock.getAllValuesForKey('hrleaveandabsences_leave_request_status', 'name');
    },
    getAllRequestStatusesLabels: function () {
      return optionGroupMock.getAllValuesForKey('hrleaveandabsences_leave_request_status', 'label');
    },
    getAllRequestStatusesValues: function () {
      return optionGroupMock.getAllValuesForKey('hrleaveandabsences_leave_request_status', 'value');
    },
    getAllAbsenceTypesTitles: function () {
      return absenceTypeData.getAllAbsenceTypesByKey('title');
    },
    getAllAbsenceTypesIds: function () {
      return absenceTypeData.getAllAbsenceTypesByKey('id');
    },
    getisValid: function () {
      return mockData.isValidData;
    },
    getNotIsValid: function () {
      return mockData.isNotValidData;
    }
  };
});
