define([
  'access-rights/modules/models',
  'access-rights/services/location',
  'common/services/api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Location', ['Model', 'api.location', function (Model, api) {
    return Model.extend({
      getAll: api.query.bind(api)
    });
  }]);
});
