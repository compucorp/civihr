/* eslint-env amd */

define(function () {
  var recordCounter = 0;
  var records = [
    recordFor('Albert Albatross'),
    recordFor('Ben Bengal'),
    recordFor('Coral Crocodile'),
    recordFor('Diana Dingo'),
    recordFor('Earl Eagle'),
    recordFor('Favio Fox'),
    recordFor('Giselle Gazella'),
    recordFor('Sonic Hedgehog')
  ];

  /**
   * Returns a balance report for a contact's absence types.
   *
   * @param {String} contactName the name for the contact.
   * @return {Object}
   */
  function recordFor (contactName) {
    return {
      contact_id: ++recordCounter,
      contact_display_name: contactName,
      absence_types: [
        { id: 1, entitlement: 4, used: 3, balance: 1, requested: 1 },
        { id: 2, entitlement: 4, used: 3, balance: 1, requested: 1 },
        { id: 3, entitlement: 4, used: 3, balance: 1, requested: 1 }
      ]
    };
  }

  return {
    /**
     * Returns all records
     *
     * @return {Object}
     */
    all: function () {
      return {
        is_error: 0,
        version: 123,
        count: records.length,
        values: records
      };
    },

    /**
     * Returns a limited list of records starting from offset.
     *
     * @param {Number} count the number of values to return.
     * @param {Number} offset the starting point to return values from.
     * @return {Object}
     */
    limit: function (count, offset) {
      count = count || records.length;
      offset = offset || 0;

      return {
        is_error: 0,
        version: 123,
        count: count,
        values: records.slice(offset, offset + count)
      };
    }
  };
});
