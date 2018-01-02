/* eslint-env amd */

define(function () {
  var allData = {
    'is_error': 0,
    'version': 3,
    'count': 2,
    'values': [{
      'id': '1',
      'title': '2016',
      'start_date': '2016-01-01',
      'end_date': '2016-12-31',
      'weight': '1'
    }, {
      'id': '2',
      'title': '2017',
      'start_date': '2017-01-01',
      'end_date': '2017-12-31',
      'weight': '2'
    }]
  };

  addCurrentYear();

  return {
    all: function () {
      return allData;
    }
  };

  function addCurrentYear () {
    var currentDate = new Date();
    var title = currentDate.getFullYear().toString();
    var startDate = title + '-01-01';
    var endDate = title + '-12-31';

    allData.values = allData.values.concat({
      'id': '3',
      'title': title,
      'start_date': startDate,
      'end_date': endDate,
      'weight': '3'
    });
  }
});
