define(function () {
  var mockData = {
    calenderData: {
      "is_error": 0,
      "version": 3,
      "count": 3,
      "values": [
        {
          "date": "2016-01-01",
          "type": {
            "value": 2,
            "name": "working_day",
            "label": "Working day"
          }
        },
        {
          "date": "2016-01-02",
          "type": {
            "value": 1,
            "name": "non_working_day",
            "label": "Non-working day"
          }
        },
        {
          "date": "2016-01-03",
          "type": {
            "value": 3,
            "name": "weekend",
            "label": "Weekend"
          }
        }
      ]
    },
    errorData: {
      "is_error": 1,
      "version": 3,
      "error_message": jasmine.any(String)
    }
  };

  return {
    calenderData: function () {
      return mockData.calenderData;
    },
    errorData: function () {
      return mockData.errorData;
    }
  }
});
