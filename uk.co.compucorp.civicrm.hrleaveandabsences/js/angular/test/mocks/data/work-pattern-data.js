/* eslint-env amd */

define([
  'common/lodash',
  'mocks/data/option-group-mock-data'
], function (_, optionGroupMock) {
  var dayTypes = optionGroupMock.getCollection('hrleaveandabsences_work_day_type');

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
              'type': dayTypeByName('working_day').value
            },
            {
              'date': '2016-01-06',
              'type': dayTypeByName('working_day').value
            },
            {
              'date': '2016-01-07',
              'type': dayTypeByName('working_day').value
            },
            {
              'date': '2016-02-02',
              'type': dayTypeByName('non_working_day').value
            },
            {
              'date': '2016-02-03',
              'type': dayTypeByName('non_working_day').value
            },
            {
              'date': '2016-03-03',
              'type': dayTypeByName('weekend').value
            },
            {
              'date': '2016-03-04',
              'type': dayTypeByName('weekend').value
            }
          ]
        },
        {
          'contact_id': 2,
          'calendar': [
            {
              'date': '2016-01-05',
              'type': dayTypeByName('weekend').value
            },
            {
              'date': '2016-02-02',
              'type': dayTypeByName('working_day').value
            },
            {
              'date': '2016-03-03',
              'type': dayTypeByName('non_working_day').value
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

  /**
   * Finds a day type Option Value based on its name
   *
   * @param  {string} name
   * @return {object}
   */
  function dayTypeByName (name) {
    return _.find(dayTypes, function (dayType) {
      return dayType.name === name;
    });
  }
});
