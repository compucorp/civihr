define([
  'common/lodash',
  'mocks/data/absence-period-data',
  'mocks/data/absence-type-data',
], function (_, absencePeriodData, absenceTypeData) {
  var mockData = {
    all_data: (function () {
      var entitlements = generateEntitlements();

      return {
        "is_error": 0,
        "version": 3,
        "count": entitlements.length,
        "values": entitlements
      };
    })(),
    breakdown_data: {
      "is_error": 0,
      "version": 3,
      "count": 3,
      "values": [{
        "id": "1",
        "breakdown": [{
          "amount": "20.00",
          "expiry_date": null,
          "type": {
            "id": 1,
            "value": "leave",
            "label": "Leave"
          }
        }, {
          "amount": "8.00",
          "expiry_date": null,
          "type": {
            "id": 3,
            "value": "public_holiday",
            "label": "Public Holiday"
          }
        }, {
          "amount": "5.00",
          "expiry_date": "2016-04-01",
          "type": {
            "id": 2,
            "value": "brought_forward",
            "label": "Brought Forward"
          }
        }]
      }, {
        "id": "2",
        "breakdown": []
      }, {
        "id": "3",
        "breakdown": [{
          "amount": "5.00",
          "expiry_date": null,
          "type": {
            "id": 1,
            "value": "leave",
            "label": "Leave"
          }
        }]
      }]
    },
    entitlements_chained_with_remainder: (function () {
      var entitlements = generateEntitlements(true);

      return {
        "is_error": 0,
        "version": 3,
        "count": entitlements.length,
        "values": entitlements
      };
    })(),
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
  }

  /**
   * Generates an entitlement for each absence type in each period
   *
   * @param {Boolean} withRemainder if the remainder data should be attached
   * @return {Array}
   */
  function generateEntitlements(withRemainder) {
    var i = 1, values = [];

    absencePeriodData.all().values.forEach(function (absencePeriod) {
      absenceTypeData.all().values.forEach(function (absenceType) {
        var id = i++;
        var entitlement = {
          "id": id,
          "period_id": absencePeriod.id,
          "type_id": absenceType.id,
          "contact_id": "202",
          "overridden": "0",
        };

        if (withRemainder) {
          entitlement["api.LeavePeriodEntitlement.getremainder"] = {
            "is_error": 0,
            "version": 3,
            "count": 1,
            "id": 0,
            "values": [{
              "id": id,
              "remainder": {
                "current": _.random(-10, 10),
                "future": _.random(-10, 10)
              }
            }]
          };
        }

        values.push(entitlement);
      });
    });

    return values;
  }
});
