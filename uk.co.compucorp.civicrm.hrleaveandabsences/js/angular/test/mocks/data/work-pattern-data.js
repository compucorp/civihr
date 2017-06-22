/* eslint-env amd */

define(function () {
  return {
    getCalendar: {
      'is_error': 0,
      'version': 3,
      'count': 2,
      'values': [
        {
          'contact_id': 1,
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
          'contact_id': 2,
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
    },
    getAllWorkPattern: {
      'is_error': 0,
      'version': 3,
      'count': 1,
      'id': 1,
      'values': [
        {
          'id': '1',
          'label': 'Default 5 day week (London)',
          'description': 'A standard 37.5 week',
          'is_default': '1',
          'is_active': '1',
          'weight': '1'
        }
      ]
    },
    workPatternsOf: {
      'is_error': 0,
      'version': 3,
      'count': 1,
      'id': 1,
      'values': [
        {
          'id': '1',
          'contact_id': '204',
          'pattern_id': '1',
          'effective_date': '2017-06-22',
          'effective_end_date': '2018-06-22',
          'change_reason': '1',
          'api.WorkPattern.get': {
            'is_error': 0,
            'version': 3,
            'count': 1,
            'id': 1,
            'values': [
              {
                'id': '1',
                'label': 'Default 5 day week (London)',
                'description': 'A standard 37.5 week',
                'is_default': '1',
                'is_active': '1',
                'weight': '1'
              }
            ]
          }
        }
      ]
    }
  };
});
