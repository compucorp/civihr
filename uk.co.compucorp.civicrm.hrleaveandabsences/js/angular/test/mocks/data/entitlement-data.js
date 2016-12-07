//contains data for the mock api calls for entitlement
define(function() {
  return {
    all_data: {
    "is_error": 0,
    "version": 3,
    "count": 6,
    "values": [
        {
            "id": "1",
            "period_id": "1",
            "type_id": "1",
            "contact_id": "202",
            "overridden": "0"
        },
        {
            "id": "2",
            "period_id": "1",
            "type_id": "2",
            "contact_id": "202",
            "overridden": "0"
        },
        {
            "id": "3",
            "period_id": "1",
            "type_id": "3",
            "contact_id": "202",
            "overridden": "0"
        },
        {
            "id": "4",
            "period_id": "2",
            "type_id": "1",
            "contact_id": "202",
            "overridden": "0"
        },
        {
            "id": "5",
            "period_id": "2",
            "type_id": "2",
            "contact_id": "202",
            "overridden": "0"
        },
        {
            "id": "6",
            "period_id": "2",
            "type_id": "3",
            "contact_id": "202",
            "overridden": "0"
        }
    ]
    },
    all_data_with_remainder: {
    "is_error": 0,
    "version": 3,
    "count": 3,
    "values": [
        {
            "id": "1",
            "remainder": {
                "current": 11,
                "future": 5
            }
        },
        {
            "id": "2",
            "remainder": {
                "current": 0,
                "future": -1
            }
        },
        {
            "id": "3",
            "remainder": {
                "current": 5,
                "future": 5
            }
        }
    ]
    },
    breakdown_data: {
    "is_error": 0,
    "version": 3,
    "count": 3,
    "values": [
        {
            "id": "1",
            "breakdown": [
                {
                    "amount": "20.00",
                    "expiry_date": null,
                    "type": {
                        "id": 1,
                        "value": "leave",
                        "label": "Leave"
                    }
                },
                {
                    "amount": "8.00",
                    "expiry_date": null,
                    "type": {
                        "id": 3,
                        "value": "public_holiday",
                        "label": "Public Holiday"
                    }
                },
                {
                    "amount": "5.00",
                    "expiry_date": "2016-04-01",
                    "type": {
                        "id": 2,
                        "value": "brought_forward",
                        "label": "Brought Forward"
                    }
                }
            ]
        },
        {
            "id": "2",
            "breakdown": [

            ]
        },
        {
            "id": "3",
            "breakdown": [
                {
                    "amount": "5.00",
                    "expiry_date": null,
                    "type": {
                        "id": 1,
                        "value": "leave",
                        "label": "Leave"
                    }
                }
            ]
        }
    ]
    }
  }
});
