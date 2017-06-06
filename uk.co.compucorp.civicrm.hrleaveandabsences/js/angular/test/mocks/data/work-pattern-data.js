/* eslint-env amd */

define(function () {
  var mockData = {
    daysData: {
      'is_error': 0,
      'version': 3,
      'count': 2,
      'values': [
        {
          'contact_id': 204,
          'calendar': [
            {
              'date': '2016-01-05',
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
        },
        {
          'contact_id': 205,
          'calendar': [
            {
              'date': '2016-01-05',
              'type': {
                'value': 3,
                'name': 'weekend',
                'label': 'Weekend'
              }
            },
            {
              'date': '2016-02-02',
              'type': {
                'value': 2,
                'name': 'working_day',
                'label': 'Working day'
              }
            },
            {
              'date': '2016-03-03',
              'type': {
                'value': 1,
                'name': 'non_working_day',
                'label': 'Non-working day'
              }
            }
          ]
        }
      ]
    }
  };

  return {
    daysData: function () {
      return mockData.daysData;
    }
  };
});
