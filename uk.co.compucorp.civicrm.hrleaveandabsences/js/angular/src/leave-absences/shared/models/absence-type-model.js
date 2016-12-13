define([
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/absence-type-instance',
  'leave-absences/shared/apis/absence-type-api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('AbsenceType', [
    '$log', 'Model', 'AbsenceTypeAPI', 'AbsenceTypeInstance',
    function ($log, Model, absenceTypeAPI, instance) {
      $log.debug('AbsenceType');

      return Model.extend({
        /**
         * Calls the all() method of the AbsenceType API, and returns an
         * AbsenceTypeInstance for each absenceType.
         *
         * @param  {Object} params  matches the api endpoint params (title, weight etc)
         * @return {Promise}
         */
        all: function (params) {
          return absenceTypeAPI.all(params)
            .then(function (absenceTypes) {
              return absenceTypes.map(function (absenceType) {
                return instance.init(absenceType, true);
              });
            });
        }
      });
    }
  ]);
});
