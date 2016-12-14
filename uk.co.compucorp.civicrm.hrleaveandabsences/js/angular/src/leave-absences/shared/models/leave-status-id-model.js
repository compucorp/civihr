define([
  'leave-absences/shared/modules/models',
  'common/services/api/option-group',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('LeaveStatusID', [
    'Model',
    'api.optionGroup',
    function (Model, OptionGroup) {
      return Model.extend({

        /**
         * This method is used to get all optiongroup values for leave request
         *
         * @return {Promise}
         */
        getAll: function () {
          return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status');
        },

        /**
         * This method is used to get ID of an option value
         *
         * @param {string} name - name of the option value
         * @return {Promise}
         */
        getOptionIDByName: function (name) {
          return this.getAll()
            .then(function (data) {
              return data.find(function (statusObj) {
                return statusObj.name === name;
              })
            })
        }
      });
    }]);
});
