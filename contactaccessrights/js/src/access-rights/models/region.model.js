define([
  'access-rights/modules/access-rights.models',
  'common/services/api/option-group',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Region', ['Model', 'api.optionGroup', function (Model, OptionGroup) {
    return Model.extend({
      getAll: function () {
        return OptionGroup.valuesOf('hrjc_region');
      }
    });
  }]);
});
