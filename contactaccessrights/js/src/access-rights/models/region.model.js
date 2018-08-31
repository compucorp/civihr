/* eslint-env amd */

define([
  'common/services/api/option-group'
], function () {
  'use strict';

  Region.$inject = ['Model', 'api.optionGroup'];

  function Region (Model, OptionGroup) {
    return Model.extend({
      getAll: function () {
        return OptionGroup.valuesOf('hrjc_region');
      }
    });
  }

  return { Region: Region };
});
