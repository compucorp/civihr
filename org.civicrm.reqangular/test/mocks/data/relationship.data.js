/* eslint-env amd */

define([
  'common/lodash',
  'common/moment'
], function (_, moment) {
  var nextWeek = moment().add(7, 'day').format('YYYY-MM-DD');
  var previousWeek = moment().subtract(7, 'day').format('YYYY-MM-DD');
  var namedRelationships = {
    isNotActive: { id: '1', is_active: '0' },
    isInThePast: { id: '2', is_active: '1', end_date: previousWeek },
    isInTheFuture: { id: '3', is_active: '1', start_date: nextWeek },
    isActive: { id: '4', is_active: '1' },
    hasStarted: { id: '5', is_active: '1', start_date: previousWeek },
    hasNotFinished: { id: '6', is_active: '1', end_date: nextWeek }
  };

  return {
    named: namedRelationships,
    all: {
      'is_error': 0,
      'version': 3,
      'count': Object.keys(namedRelationships).length,
      'values': _.values(namedRelationships)
    }
  };
});
