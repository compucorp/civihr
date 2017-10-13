/* eslint-env amd */

define([
  'common/mocks/data/contact.data',
  'mocks/data/absence-type-data'
], function (contactsMock, absenceTypeMock) {
  var records = createMockRecords();

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
    }
  };

  /**
   * Returns an array of contact records with absence types balances.
   *
   * @return {Array}
   */
  function createMockRecords () {
    return contactsMock.all.values.map(function (contact) {
      return {
        contact_id: contact.contact_id,
        contact_display_name: contact.display_name,
        absence_types: absenceTypeMock.all().values.map(function (type) {
          return { id: type.id, entitlement: 4, used: 3, balance: 1, requested: 1 };
        })
      };
    });
  }
});
