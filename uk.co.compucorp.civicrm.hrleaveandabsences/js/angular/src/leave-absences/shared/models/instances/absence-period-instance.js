define([
  'leave-absences/shared/modules/models-instances',
  'common/moment',
  'common/models/instances/instance',
  'common/services/hr-settings',
], function (instances, moment) {
  'use strict';

  instances.factory('AbsencePeriodInstance', ['$log', 'ModelInstance', 'HR_settings',
    function ($log, ModelInstance, HR_settings) {
      $log.debug('AbsencePeriodInstance');

      return ModelInstance.extend({
        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            current: false
          };
        },
        /**
         * Sets the current property of this absence period on instantiation.
         *
         * @return {object} updated attributes object
         */
        transformAttributes: function (attributes) {
          var today = moment();
          attributes.current = false;

          if (moment(attributes.start_date).isSameOrBefore(today) &&
            moment(attributes.end_date).isSameOrAfter(today)) {
            attributes.current = true;
          }

          return attributes;
        },
        /**
         *  Finds out if given date is in this object's absence period.
         *
         * @param  {Date} whichDate given date either as Date object or its string representation
         * @return true if whichDate is in this instance's period range, else false
         */
        isInPeriod: function (whichDate) {
          var dateFormat = HR_settings.DATE_FORMAT.toUpperCase();
          var checkDate = moment(whichDate, dateFormat);

          return moment(this.start_date).isSameOrBefore(checkDate) &&
            moment(this.end_date).isSameOrAfter(checkDate);
        }
      });
    }
  ]);
});
