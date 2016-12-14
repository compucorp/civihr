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
  var past_all_data = {
    "is_error": 0,
    "version": 3,
    "count": 2,
    "values": [{
      "id": "1",
      "title": "2013",
      "start_date": "2013-01-01",
      "end_date": "2013-12-31",
      "weight": "1"
    }, {
      "id": "2",
      "title": "2014",
      "start_date": "2014-01-01",
      "end_date": "2014-12-31",
      "weight": "2"
    }]
  };

  return {
    all: function () {
      return all_data;
    },
    past_all: function () {
      return past_all_data;
    }
  }
});
