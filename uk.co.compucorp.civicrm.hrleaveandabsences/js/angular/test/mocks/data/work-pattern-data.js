define(function () {
  var mockData = {
    daysData: {
      'is_error': 0,
      'version': 3,
      'count': 3,
      'values': [
        {
          'date': '2016-01-01',
          'type': {
            'value': 2,
            'name': 'working_day',
            'label': 'Working day'
          }
        },
        {
          'date': '2016-02-02',
          'type': {
            'value': 1,
            'name': 'non_working_day',
            'label': 'Non-working day'
          }
        },
        {
          'date': '2016-03-03',
          'type': {
            'value': 3,
            'name': 'weekend',
            'label': 'Weekend'
          }
        }
      ]
    }
  };

  return {
    daysData: function () {
      return mockData.daysData;
    }
  }
});
