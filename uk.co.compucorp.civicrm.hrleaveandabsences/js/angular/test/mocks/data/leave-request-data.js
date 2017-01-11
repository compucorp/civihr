define([
  'common/lodash',
  'mocks/data/option-group-mock-data',
  'mocks/data/absence-type-data',
], function (_, optionGroupMock, absenceTypeData) {
  var mockData = {
    allData: {
      'is_error': 0,
      'version': 3,
      'count': 6,
      'values': [{
        'id': '17',
        'type_id': absenceTypeData.all().values[0]['id'],
        'contact_id': '202',
        'status_id': optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1'),
        'from_date': '2016-02-01',
        'from_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'to_date': '2016-02-03',
        'to_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'balance_change': -3
      }, {
        'id': '18',
        'type_id': absenceTypeData.all().values[0]['id'],
        'contact_id': '202',
        'status_id': optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1'),
        'from_date': '2016-08-17',
        'from_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'to_date': '2016-08-25',
        'to_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'balance_change': -1.5
      }, {
        'id': '19',
        'type_id': absenceTypeData.all().values[0]['id'],
        'contact_id': '202',
        'status_id': optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '6'),
        'from_date': '2016-01-30',
        'from_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'to_date': '2016-02-01',
        'to_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'balance_change': -1
      }, {
        'id': '20',
        'type_id': absenceTypeData.all().values[0]['id'],
        'contact_id': '202',
        'status_id': optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3'),
        'from_date': '2016-11-23',
        'from_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'to_date': '2016-11-28',
        'to_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'balance_change': -5
      }, {
        'id': '21',
        'type_id': absenceTypeData.all().values[2]['id'],
        'contact_id': '202',
        'status_id': optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '5'),
        'from_date': '2016-06-03',
        'from_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'to_date': '2016-06-13',
        'to_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'balance_change': -10
      }, {
        'id': '22',
        'type_id': absenceTypeData.all().values[1]['id'],
        'contact_id': '202',
        'status_id': optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '4'),
        'from_date': '2016-01-01',
        'from_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'to_date': '2016-01-01',
        'to_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'balance_change': -1
      }]
    },
    singleDataSuccess: {
      'is_error': 0,
      'version': 3,
      'count': 1,
      'values': [{
        'id': '17',
        'type_id': absenceTypeData.all().values[0]['id'],
        'contact_id': '202',
        'status_id': optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1'),
        'from_date': '2016-02-01',
        'from_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1'),
        'to_date': '2016-02-03',
        'to_date_type': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1')
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
      'values': (function () {
        var values = {};

        absenceTypeData.all().values.forEach(function (absenceType) {
          values[absenceType.id] = -1 * _.random(0, 25);
        });

        return values;
      })()
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
            'label': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'label', 'Weekend')
          }
        }, {
          'date': '2016-11-06',
          'amount': 0,
          'type': {
            'id': 4,
            'value': 4,
            'label': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'label', 'Weekend')
          }
        }, {
          'date': '2016-11-07',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'label', 'All Day')
          }
        }, {
          'date': '2016-11-08',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'label', 'All Day')
          }
        }, {
          'date': '2016-11-09',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'label', 'All Day')
          }
        }, {
          'date': '2016-11-10',
          'amount': 1,
          'type': {
            'id': 1,
            'value': 1,
            'label': optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'label', 'All Day')
          }
        }]
      }
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
    },
    isManagedByData: {
      "is_error": 0,
      "version": 3,
      "count": 1,
      "values": true
    },
    half_day_am_calculateBalanceChange: {
      "is_error": 0,
      "version": 3,
      "count": 2,
      "values": {
        "amount": -0.5,
        "breakdown": [{
          "date": "2017-01-04",
          "amount": 0.5,
          "type": {
            "id": 2,
            "value": 2,
            "label": "1/2 AM"
          }
        }]
      }
    },
    all_day_calculateBalanceChange: {
      "is_error": 0,
      "version": 3,
      "count": 2,
      "values": {
        "amount": -2,
        "breakdown": [{
          "date": "2017-01-04",
          "amount": 1,
          "type": {
            "id": 1,
            "value": 1,
            "label": "All Day"
          }
        }, {
          "date": "2017-01-05",
          "amount": 1,
          "type": {
            "id": 1,
            "value": 1,
            "label": "All Day"
          }
        }]
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
    calculateBalanceChange: function () {
      return mockData.calculateBalanceChangeData;
    },
    getisValid: function () {
      return mockData.isValidData;
    },
    getNotIsValid: function () {
      return mockData.isNotValidData;
    },
    isManagedBy: function () {
      return mockData.isManagedByData;
    },
    singleDayCalculateBalanceChange: function () {
      return mockData.half_day_am_calculateBalanceChange;
    },
    multipleDayCalculateBalanceChange: function () {
      return mockData.all_day_calculateBalanceChange;
    }
  };
});
