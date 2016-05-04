define([
  'access-rights/modules/models',
	'access-rights/services/base-api',
], function (models) {
	'use strict';
  models.factory('Region', ['baseApi', '$httpParamSerializerJQLike', function (baseApi, $httpParamSerializerJQLike) {
    var optionGroupName = 'hrjc_region';
    return baseApi.extend({
      save: function(obj){
        return this.saveEntity(obj, optionGroupName);
      },
			getAll: function (filters, pagination, sort, additionalParams) {
        return this.getAllEntities(optionGroupName, filters, pagination, sort, additionalParams);
			}
		});
	}]);
});
