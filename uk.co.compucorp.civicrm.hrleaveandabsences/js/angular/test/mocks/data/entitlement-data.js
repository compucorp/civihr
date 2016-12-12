define(function () {
  var mockData = {
    all_data: {
      "is_error": 0,
      "version": 3,
      "count": 6,
      "values": [{
        "id": "1",
        "period_id": "1",
        "type_id": "1",
        "contact_id": "202",
        "overridden": "0"
      }, {
        "id": "2",
        "period_id": "1",
        "type_id": "2",
        "contact_id": "202",
        "overridden": "0"
      }, {
        "id": "3",
        "period_id": "1",
        "type_id": "3",
        "contact_id": "202",
        "overridden": "0"
      }, {
        "id": "4",
        "period_id": "2",
        "type_id": "1",
        "contact_id": "202",
        "overridden": "0"
      }, {
        "id": "5",
        "period_id": "2",
        "type_id": "2",
        "contact_id": "202",
        "overridden": "0"
      }, {
        "id": "6",
        "period_id": "2",
        "type_id": "3",
        "contact_id": "202",
        "overridden": "0"
      }]
    },
    all_data_with_remainder: {
      "is_error": 0,
      "version": 3,
      "count": 3,
      "values": [{
        "id": "1",
        "remainder": {
          "current": 11,
          "future": 5
        }
      }, {
        "id": "2",
        "remainder": {
          "current": 0,
          "future": -1
        }
      }, {
        "id": "3",
        "remainder": {
          "current": 5,
          "future": 5
        }
      }]
    },
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
    entitlements_chained_with_remainder: {
      "is_error": 0,
      "version": 3,
      "count": 6,
      "values": [{
        "id": "1",
        "period_id": "1",
        "type_id": "1",
        "contact_id": "202",
        "overridden": "0",
        "api.LeavePeriodEntitlement.getremainder": {
          "is_error": 0,
          "version": 3,
          "count": 1,
          "id": 0,
          "values": [{
            "id": "1",
            "remainder": {
              "current": 11,
              "future": 5
            }
          }]
        }
      }, {
        "id": "2",
        "period_id": "1",
        "type_id": "2",
        "contact_id": "202",
        "overridden": "0",
        "api.LeavePeriodEntitlement.getremainder": {
          "is_error": 0,
          "version": 3,
          "count": 1,
          "id": 0,
          "values": [{
            "id": "2",
            "remainder": {
              "current": 0,
              "future": -1
            }
          }]
        }
      }, {
        "id": "3",
        "period_id": "1",
        "type_id": "3",
        "contact_id": "202",
        "overridden": "0",
        "api.LeavePeriodEntitlement.getremainder": {
          "is_error": 0,
          "version": 3,
          "count": 1,
          "id": 0,
          "values": [{
            "id": "3",
            "remainder": {
              "current": 5,
              "future": 5
            }
          }]
        }
      }, {
        "id": "4",
        "period_id": "2",
        "type_id": "1",
        "contact_id": "202",
        "overridden": "0",
        "api.LeavePeriodEntitlement.getremainder": {
          "is_error": 0,
          "version": 3,
          "count": 1,
          "id": 0,
          "values": [{
            "id": "4",
            "remainder": {
              "current": -8,
              "future": -8
            }
          }]
        }
      }, {
        "id": "5",
        "period_id": "2",
        "type_id": "2",
        "contact_id": "202",
        "overridden": "0",
        "api.LeavePeriodEntitlement.getremainder": {
          "is_error": 0,
          "version": 3,
          "count": 1,
          "id": 0,
          "values": [{
            "id": "5",
            "remainder": {
              "current": 0,
              "future": 0
            }
          }]
        }
      }, {
        "id": "6",
        "period_id": "2",
        "type_id": "3",
        "contact_id": "202",
        "overridden": "0",
        "api.LeavePeriodEntitlement.getremainder": {
          "is_error": 0,
          "version": 3,
          "count": 1,
          "id": 0,
          "values": [{
            "id": "6",
            "remainder": {
              "current": 0,
              "future": 0
            }
          }]
        }
      }]
    }
  }
  return {
    all: function (params, withBalance) {
      if (withBalance) {
        return mockData.entitlements_chained_with_remainder;
      }
      return mockData.all_data;
    },
    breakdown: function () {
      return mockData.breakdown_data;
    }
  }
});
