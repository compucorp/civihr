/* eslint-env amd */

define([
  'common/moment',
  'common/modules/models-instances',
  'common/models/instances/instance'
], function (moment, instances) {
  'use strict';

  instances.factory('RelationshipInstance', RelationshipInstance);

  RelationshipInstance.$inject = ['ModelInstance'];

  function RelationshipInstance (ModelInstance) {
    var extendedModelInstance = ModelInstance.extend({
      isValid: isValid
    });

    /**
     * Determines if the relationship is valid by checking if it's active, and
     * if the dates are defined, verify that the start date is in the past
     * and the end date is in the future.
     *
     * @return {Boolean}
     */
    function isValid () {
      var isActive = !!+this.is_active;
      var startDateIsInThePast = moment().isSameOrAfter(this.start_date, 'day');
      var startDateIsNotDefined = !this.start_date;
      var endDateIsInTheFuture = moment().isSameOrBefore(this.end_date, 'day');
      var endDateIsNotDefined = !this.end_date;

      return isActive && (startDateIsNotDefined || startDateIsInThePast) &&
        (endDateIsNotDefined || endDateIsInTheFuture);
    }

    return extendedModelInstance;
  }
});
