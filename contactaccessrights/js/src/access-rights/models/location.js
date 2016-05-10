define([
  'access-rights/modules/models',
  'access-rights/services/api/location',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Location', ['Model', 'locationApi', function (Model, api) {
    return Model.extend({
      getAll: api.query.bind(api)
    });
  }]);
});
