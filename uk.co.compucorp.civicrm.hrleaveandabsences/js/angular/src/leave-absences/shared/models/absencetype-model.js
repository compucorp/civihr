define([
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/absencetype-instance',
  'leave-absences/shared/apis/absencetype-api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('AbsenceType', [
    '$log', 'Model', 'AbsenceTypeAPI', 'AbsenceTypeInstance',
    function ($log, Model, absencetypeAPI, instance) {
      $log.debug('AbsenceType');

      return Model.extend({
        /**
         * Calls the all() method of the AbsenceType API, and returns an
         * AbsenceTypeInstance for each absencetype.
         *
         * @param  {Object} params  matches the api endpoint params (title, weight etc)
         * @return {Promise}
         */
        all: function (params) {
          return absencetypeAPI.all(params)
            .then(function (absencetypes) {
              return absencetypes.map(function (absencetype) {
                return instance.init(absencetype, true);
              });
            });
        }
      });
    }
  ]);
});
