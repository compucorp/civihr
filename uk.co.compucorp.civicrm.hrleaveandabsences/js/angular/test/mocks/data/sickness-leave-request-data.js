define([
  'common/lodash',
  'mocks/data/option-group-mock-data',
  'mocks/data/absence-type-data',
], function (_, optionGroupMock, absenceTypeData) {
  var mockData = {
    allData: {
      "is_error": 0,
      "version": 3,
      "count": 3,
      "values": [{
          "id": "1",
          "type_id": "3",
          "contact_id": "202",
          "status_id": "5",
          "from_date": "2016-06-03",
          "from_date_type": "1",
          "to_date": "2016-06-13",
          "to_date_type": "1",
          "leave_request_id": "21",
          "reason": "1"
        },
        {
          "id": "2",
          "type_id": "3",
          "contact_id": "202",
          "status_id": "6",
          "from_date": "2017-02-01",
          "from_date_type": "1",
          "to_date": "2017-02-01",
          "to_date_type": "1",
          "leave_request_id": "27",
          "reason": "2"
        },
        {
          "id": "3",
          "type_id": "3",
          "contact_id": "202",
          "status_id": "1",
          "from_date": "2017-03-22",
          "from_date_type": "1",
          "to_date": "2017-03-24",
          "to_date_type": "1",
          "leave_request_id": "31",
          "reason": "1"
        }
      ]
    }
  };

  return {
    all: function () {
      return mockData.allData;
    }
  };
});
