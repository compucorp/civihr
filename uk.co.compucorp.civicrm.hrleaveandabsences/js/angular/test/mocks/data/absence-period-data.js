define(function () {
  var all_data = {
    "is_error": 0,
    "version": 3,
    "count": 2,
    "values": [{
      "id": "1",
      "title": "2016",
      "start_date": "2016-01-01",
      "end_date": "2016-12-31",
      "weight": "1"
    }, {
      "id": "2",
      "title": "2017",
      "start_date": "2017-01-01",
      "end_date": "2017-12-31",
      "weight": "2"
    }]
  };

  return {
    all: function () {
      return all_data;
    }
  };
});
