define([
	'access-rights/modules/models'
], function (models) {
	'use strict';

	models.factory('resourceBuilder', ['$resource', '$httpParamSerializerJQLike', function ($resource, $httpParamSerializerJQLike) {
		return {
      build: function(entityName, additionalParams, dataTransformations, entityPrototype) {
  			var entityResource = $resource('/civicrm/ajax/rest', _.assign({
  				entity: entityName,
  				json:{
            sequential: 1
          }
  			}, (additionalParams || {})), {
  				save: {
  					url: 'test.test',
  					method: 'POST',
  					headers: {
  						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
  					},
  					responseType: 'json',
  					params: {
  						action: 'create'
  					},
  					transformRequest: function (data) {
  						return $httpParamSerializerJQLike(dataTransformations ? dataTransformations.toApi(data): data);
  					}
  				},
  				getAll: {
  					method: 'GET',
            responseType: 'json',
  					cache: false,
            isArray: true,
  					transformResponse: function (data, headers) {
  						if(dataTransformations)
  							return dataTransformations.fromApi(data);
  						return data.values;
  					}
  				}
  			});
  			if(entityPrototype)
  				entityResource.prototype = entityPrototype;
        return entityResource;
  		}
    };
	}]);
});
