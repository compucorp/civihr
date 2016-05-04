define([
  'access-rights/modules/models',
  'common/services/api/api-builder'
], function (models) {
	'use strict';
  models.factory('Location', ['apiBuilder', function (apiBuilder) {
    var methods = {
      getAll: function (filters, pagination, sort) {
        return this.getAllEntities(filters, pagination, sort);
			}
    };
    var dataTransformations = {
      toApi: function(data){
        data.additionalCustomParameter = 'TESTING TO API!';
        return data;
      },
      fromApi: function(data){
        return data.values;
      }
    };
    var entityPrototype = {
      hello: function(){
        alert(this.name);
      }
    };
		return apiBuilder.build(methods, 'OptionValue', {
      'option_group_name': 'hrjc_location'
    }, dataTransformations, entityPrototype);
	}]);
});
