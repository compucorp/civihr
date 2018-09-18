/* eslint-env amd */

define([
  'common/services/api/option-group'
], function () {
  'use strict';

  Location.$inject = ['Model', 'api.optionGroup'];

  function Location (Model, OptionGroup) {
    return Model.extend({
      getAll: function () {
        return OptionGroup.valuesOf('hrjc_location');
      }
    });
  }

  return { Location: Location };
});
