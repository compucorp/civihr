define([
  'access-rights/modules/models',
  'access-rights/services/api/region',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Region', ['Model', 'regionApi', function (Model, api) {
    return Model.extend({
      getAll: api.query.bind(api)
    });
  }]);
});
