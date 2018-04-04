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
    return ModelInstance.extend({
      /**
       * Determines if the relationship is valid by checking if it's active,
       * the start date is in the past, and the end date is in the future.
       *
       * @return {Boolean}
       */
      isValid: function () {
        var isActive = parseInt(this.is_active) === 1;
        var startDateIsInThePast = moment().isSameOrAfter(this.start_date);
        var startDateIsNotDefined = !this.start_date;
        var endDateIsInTheFuture = moment().isSameOrBefore(this.end_date);
        var endDateIsNotDefined = !this.end_date;

        return isActive && (startDateIsNotDefined || startDateIsInThePast) &&
          (endDateIsNotDefined || endDateIsInTheFuture);
      }
    });
  }
});
