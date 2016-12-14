define([
  'leave-absences/shared/modules/models-instances',
  'common/moment',
  'common/models/instances/instance',
], function (instances, moment) {
  'use strict';

  instances.factory('AbsencePeriodInstance', ['$log', 'ModelInstance',
    function ($log, ModelInstance) {
      $log.debug('AbsencePeriodInstance');

      return ModelInstance.extend({
        /**
         * Sets the current property of this absence period on instantiation
         * and then creates a new instance.
         * As its overriding the ModelInstance.init method it needs to include isInPeriod
         * function as part of its object definition.
         *
         * @param {object} attributes - The instance data
         * @param {boolean} fromAPI - If the data comes from the API and needs to be normalized
         * @return {object}
         */
        init: function (attributes, fromAPI) {
          var currentDate = moment();

          attributes.current = false;
          if (moment(attributes.start_date).isSameOrBefore(currentDate) &&
            moment(attributes.end_date).isSameOrAfter(currentDate)) {
            attributes.current = true;
          }
          attributes.isInPeriod = this.isInPeriod;

          return ModelInstance.init(attributes, fromAPI);
        },
        /**
         *  Finds out if given date is in this object's absence period.
         *
         * @param  {String} whichDate given date
         * @return true if whichDate is in this instance's period range, else false
         */
        isInPeriod: function (whichDate) {
          var checkDate = moment(whichDate);

          return moment(this.start_date).isSameOrBefore(checkDate) &&
            moment(this.end_date).isSameOrAfter(checkDate);
        }
      });
    }
  ]);
});
