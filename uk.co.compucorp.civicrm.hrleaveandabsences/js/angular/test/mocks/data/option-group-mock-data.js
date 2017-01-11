define([
  'common/lodash',
  'mocks/module',
], function (_, mocks) {
  'use strict';

  /**
   * Mocked option values, grouped by option group
   */
  var mockedOptionValues = {
    hrleaveandabsences_leave_request_day_type: [{
      "id": "1113",
      "option_group_id": "142",
      "label": "All Day",
      "value": "1",
      "name": "all_day",
      "is_default": "0",
      "weight": "0",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1114",
      "option_group_id": "142",
      "label": "1/2 AM",
      "value": "2",
      "name": "half_day_am",
      "is_default": "0",
      "weight": "1",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1115",
      "option_group_id": "142",
      "label": "1/2 PM",
      "value": "3",
      "name": "half_day_pm",
      "is_default": "0",
      "weight": "2",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1116",
      "option_group_id": "142",
      "label": "Weekend",
      "value": "4",
      "name": "weekend",
      "is_default": "0",
      "weight": "3",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1117",
      "option_group_id": "142",
      "label": "Non Working Day",
      "value": "5",
      "name": "non_working_day",
      "is_default": "0",
      "weight": "4",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1118",
      "option_group_id": "142",
      "label": "Public Holiday",
      "value": "6",
      "name": "public_holiday",
      "is_default": "0",
      "weight": "5",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }],
    hrleaveandabsences_leave_request_status: [{
      "id": "1119",
      "option_group_id": "143",
      "label": "Approved",
      "value": "1",
      "name": "approved",
      "is_default": "0",
      "weight": "0",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1120",
      "option_group_id": "143",
      "label": "Admin Approved",
      "value": "2",
      "name": "admin_approved",
      "is_default": "0",
      "weight": "1",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1121",
      "option_group_id": "143",
      "label": "Waiting Approval",
      "value": "3",
      "name": "waiting_approval",
      "is_default": "0",
      "weight": "2",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1122",
      "option_group_id": "143",
      "label": "More Information Requested",
      "value": "4",
      "name": "more_information_requested",
      "is_default": "0",
      "weight": "3",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1123",
      "option_group_id": "143",
      "label": "Rejected",
      "value": "5",
      "name": "rejected",
      "is_default": "0",
      "weight": "4",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1124",
      "option_group_id": "143",
      "label": "Cancelled",
      "value": "6",
      "name": "cancelled",
      "is_default": "0",
      "weight": "5",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }],
    hrleaveandabsences_leave_days_amounts: [{
      "id": "1105",
      "option_group_id": "141",
      "label": "0 Days",
      "value": "0",
      "name": "zero_days",
      "is_default": "0",
      "weight": "1",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }, {
      "id": "1106",
      "option_group_id": "141",
      "label": "1/4 Days",
      "value": "0.25",
      "name": "quarter_day",
      "is_default": "0",
      "weight": "2",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }, {
      "id": "1107",
      "option_group_id": "141",
      "label": "1/2 Days",
      "value": "0.5",
      "name": "half_day",
      "is_default": "0",
      "weight": "3",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }, {
      "id": "1108",
      "option_group_id": "141",
      "label": "3/4 Days",
      "value": "0.75",
      "name": "three_quarters_day",
      "is_default": "0",
      "weight": "4",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }, {
      "id": "1109",
      "option_group_id": "141",
      "label": "1 Day",
      "value": "1",
      "name": "one_day",
      "is_default": "0",
      "weight": "5",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }, {
      "id": "1110",
      "option_group_id": "141",
      "label": "1 1/4 Days",
      "value": "1.25",
      "name": "one_and_a_quarter_days",
      "is_default": "0",
      "weight": "6",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }, {
      "id": "1111",
      "option_group_id": "141",
      "label": "1 1/2 Days",
      "value": "1.5",
      "name": "one_and_a_half_days",
      "is_default": "0",
      "weight": "7",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }, {
      "id": "1112",
      "option_group_id": "141",
      "label": "1 3/4 Days",
      "value": "1.75",
      "name": "one_and_three_quarters_days",
      "is_default": "0",
      "weight": "8",
      "is_optgroup": "0",
      "is_reserved": "0",
      "is_active": "1"
    }],
    hrleaveandabsences_leave_balance_change_type: [{
      "id": "1099",
      "option_group_id": "140",
      "label": "Leave",
      "value": "1",
      "name": "leave",
      "is_default": "0",
      "weight": "0",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1100",
      "option_group_id": "140",
      "label": "Brought Forward",
      "value": "2",
      "name": "brought_forward",
      "is_default": "0",
      "weight": "1",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1101",
      "option_group_id": "140",
      "label": "Public Holiday",
      "value": "3",
      "name": "public_holiday",
      "is_default": "0",
      "weight": "2",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1102",
      "option_group_id": "140",
      "label": "Credit",
      "value": "4",
      "name": "credit",
      "is_default": "0",
      "weight": "3",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1103",
      "option_group_id": "140",
      "label": "Debit",
      "value": "5",
      "name": "debit",
      "is_default": "0",
      "weight": "4",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }, {
      "id": "1104",
      "option_group_id": "140",
      "label": "Overridden",
      "value": "6",
      "name": "overridden",
      "is_default": "0",
      "weight": "5",
      "is_optgroup": "0",
      "is_reserved": "1",
      "is_active": "1"
    }]
  };

  return {
    /**
     * Pick a random value out of a collection
     *
     * @param {array} the option group collection key
     * @param {string} key - The sub-collection key
     * @return {object}
     */
    randomValue: function (collection, key) {
      var whichOptionGroup = mockedOptionValues[collection];
      var randomOptionValue = _.sample(whichOptionGroup);
      return randomOptionValue[key];
    },
    /**
     * Pick a specific value out of a collection
     *
     * @param {array} the option group collection key
     * @param {string} key - The sub-collection key
     * @param {string} value - The sub-collection key's value to match
     * @return {object}
     */
    specificValue: function (collection, key, value) {
      var whichOptionGroup = mockedOptionValues[collection];
      var specificObject = _.find(whichOptionGroup, function (item) {
        return item[key] === value;
      });
      return specificObject[key];
    },
    /**
     * Gets all values for given key in collection object
     *
     * @param {array} the option group collection key
     * @param {string} key - The sub-collection key
     * @return {Array} of values
     */
    getAllValuesForKey: function (collection, key) {
      return mockedOptionValues[collection].map(function (item) {
        return item[key];
      });
    },
    /**
     * Gets all leave request day types values
     *
     * @return {Array} of values of leave request day types
     */
    getAllRequestDayValues: function () {
      return this.getAllValuesForKey('hrleaveandabsences_leave_request_day_type', 'value');
    },
    /**
     * Gets all leave request statuses values
     *
     * @return {Array} of values of leave request statuses
     */
    getAllRequestStatusesValues: function () {
      return this.getAllValuesForKey('hrleaveandabsences_leave_request_status', 'value');
    },
    /**
     * Returns the specified collection
     *
     * @param  {string} collection
     * @return {Array}
     */
    getCollection: function (collection) {
      return mockedOptionValues[collection];
    },
    /**
     * Pick a specific object out of a collection
     *
     * @param {array} collection - the option group collection key
     * @param {string} key - The sub-collection key
     * @param {string} value - The sub-collection key's value to match
     * @return {object}
     */
    specificObject: function (collection, key, value) {
      var whichOptionGroup = mockedOptionValues[collection];
      
      return _.find(whichOptionGroup, function (item) {
        return item[key] === value;
      });
    },
  }
});
