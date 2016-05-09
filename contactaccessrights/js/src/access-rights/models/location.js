define([
  'access-rights/modules/models',
  'common/services/api/api-builder'
], function (models) {
  'use strict';

  models.factory('Location', ['apiBuilder', function (apiBuilder) {
    return apiBuilder.build({
      getAll: function (filters, pagination, sort) {
        return this.getAllEntities(filters, pagination, sort);
      }
    }, 'OptionValue', {
      'option_group_name': 'hrjc_location'
    });
  }]);
});
