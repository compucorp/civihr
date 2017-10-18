/* eslint-env amd */

define([
  'common/lodash',
  'mocks/data/absence-period-data',
  'mocks/data/absence-type-data',
  'mocks/data/option-group-mock-data'
], function (_, absencePeriodData, absenceTypeData, optionGroupData) {
  var mockData = {
    all_data: (function () {
      var entitlements = generateEntitlements();

      return {
        'is_error': 0,
        'version': 3,
        'count': entitlements.length,
        'values': entitlements
      };
    })(),
    breakdown_data: (function () {
      var entitlements = generateEntitlements();
      var dayTypes = optionGroupData.getCollection('hrleaveandabsences_leave_request_day_type');

      return {
        'is_error': 0,
        'version': 3,
        'count': entitlements.length,
        'values': (function () {
          return entitlements.map(function (entitlement) {
            return {
              'id': entitlement.id,
              'breakdown': _.times(_.random(1, 10)).map(function () {
                var dayType = _.sample(dayTypes);

                return {
                  'amount': _.random(-30, 30) + '.00',
                  'expiry_date': null,
                  'type': {
                    'id': dayType.id,
                    'value': dayType.value,
                    'label': dayType.label
                  }
                };
              })
            };
          });
        })()
      };
    })(),
    entitlements_chained_with_remainder: (function () {
      var entitlements = generateEntitlements(true);

      return {
        'is_error': 0,
        'version': 3,
        'count': entitlements.length,
        'values': entitlements
      };
    })()
  };
  return {
    all: function (withBalance) {
      if (withBalance) {
        return mockData.entitlements_chained_with_remainder;
      }
      return mockData.all_data;
    },
    breakdown: function () {
      return mockData.breakdown_data;
    }
  };

  /**
   * Generates an entitlement for each absence type in each period
   *
   * @param {Boolean} withRemainder if the remainder data should be attached
   * @return {Array}
   */
  function generateEntitlements (withRemainder) {
    var i = 1;
    var values = [];

    absencePeriodData.all().values.forEach(function (absencePeriod) {
      absenceTypeData.all().values.forEach(function (absenceType, index) {
        if (index < absenceTypeData.all().values.length - 1) {
          var id = i++;
          var entitlement = {
            'id': id,
            'period_id': absencePeriod.id,
            'type_id': absenceType.id,
            'contact_id': '202',
            'overridden': '0',
            'comment': 'Test comment',
            'comment_author_id': '202',
            'comment_date': '2017-06-21 14:28:46',
            'api.LeavePeriodEntitlement.getentitlement': {
              'is_error': 0,
              'version': 3,
              'count': 1,
              'values': [{
                'id': id,
                'entitlement': _.random(0, 30)
              }]
            }
          };

          if (withRemainder) {
            entitlement['api.LeavePeriodEntitlement.getremainder'] = {
              'is_error': 0,
              'version': 3,
              'count': 1,
              'id': 0,
              'values': [{
                'id': id,
                'remainder': {
                  'current': _.random(0, 10),
                  'future': _.random(-10, 10)
                }
              }]
            };
          }

          values.push(entitlement);
        }
      });
    });

    return values;
  }
});
