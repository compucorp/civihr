define(function () {
  var all_data = {
    "is_error": 0,
    "version": 3,
    "count": 3,
    "values": [{
      "id": "1",
      "title": "Holiday / Vacation",
      "weight": "1",
      "color": "#151D2C",
      "is_default": "1",
      "is_reserved": "1",
      "allow_request_cancelation": "3",
      "allow_overuse": "0",
      "must_take_public_holiday_as_leave": "1",
      "default_entitlement": "20",
      "add_public_holiday_to_entitlement": "1",
      "is_active": "1",
      "allow_accruals_request": "0",
      "allow_accrue_in_the_past": "0",
      "allow_carry_forward": "1",
      "max_number_of_days_to_carry_forward": "5",
      "carry_forward_expiration_duration": "12",
      "carry_forward_expiration_unit": "2"
    }, {
      "id": "2",
      "title": "TOIL",
      "weight": "2",
      "color": "#056780",
      "is_default": "0",
      "is_reserved": "1",
      "allow_request_cancelation": "3",
      "allow_overuse": "0",
      "must_take_public_holiday_as_leave": "0",
      "default_entitlement": "0",
      "add_public_holiday_to_entitlement": "0",
      "is_active": "1",
      "allow_accruals_request": "1",
      "max_leave_accrual": "5",
      "allow_accrue_in_the_past": "0",
      "accrual_expiration_duration": "3",
      "accrual_expiration_unit": "2",
      "allow_carry_forward": "0"
    }, {
      "id": "3",
      "title": "Sick",
      "weight": "3",
      "color": "#B32E2E",
      "is_default": "0",
      "is_reserved": "1",
      "allow_request_cancelation": "1",
      "allow_overuse": "1",
      "must_take_public_holiday_as_leave": "0",
      "default_entitlement": "0",
      "add_public_holiday_to_entitlement": "0",
      "is_active": "1",
      "allow_accruals_request": "0",
      "allow_accrue_in_the_past": "0",
      "allow_carry_forward": "0"
    }]
  }
  return {
    all: function () {
      return all_data;
    }
  }
});
