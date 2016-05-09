define([
  'access-rights/modules/models',
  'access-rights/services/region',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Region', ['Model', 'api.region', function (Model, api) {
    return Model.extend({
      getAll: api.query.bind(api)
    });
  }]);
});
