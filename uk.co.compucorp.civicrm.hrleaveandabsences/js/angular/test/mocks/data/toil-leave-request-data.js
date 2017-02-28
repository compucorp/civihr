define([
  'common/lodash',
  'mocks/data/option-group-mock-data',
  'mocks/data/absence-type-data',
], function (_, optionGroupMock, absenceTypeData) {
  var mockData = {
    allData: {
      "is_error": 0,
      "version": 3,
      "count": 4,
      "values": [{
          "id": "1",
          "type_id": "2",
          "contact_id": "202",
          "status_id": "1",
          "from_date": "2016-06-01",
          "from_date_type": "1",
          "to_date": "2016-06-01",
          "to_date_type": "1",
          "leave_request_id": "22",
          "duration": "180"
        },
        {
          "id": "2",
          "type_id": "2",
          "contact_id": "202",
          "status_id": "1",
          "from_date": "2016-10-20",
          "from_date_type": "1",
          "to_date": "2016-10-20",
          "to_date_type": "1",
          "leave_request_id": "24",
          "duration": "200"
        },
        {
          "id": "3",
          "type_id": "2",
          "contact_id": "202",
          "status_id": "4",
          "from_date": "2016-12-15",
          "from_date_type": "1",
          "to_date": "2016-12-15",
          "to_date_type": "1",
          "leave_request_id": "25",
          "duration": "360"
        },
        {
          "id": "4",
          "type_id": "2",
          "contact_id": "202",
          "status_id": "1",
          "from_date": "2017-04-25",
          "from_date_type": "1",
          "to_date": "2017-04-25",
          "to_date_type": "1",
          "leave_request_id": "32",
          "duration": "180"
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
