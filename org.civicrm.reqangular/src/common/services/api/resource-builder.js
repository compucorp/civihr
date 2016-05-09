define([
  'common/modules/apis',
], function (apis) {
  'use strict';

  apis.factory('resourceBuilder', ['$resource', '$httpParamSerializerJQLike', function ($resource, $httpParamSerializerJQLike) {
    return {

      /**
       * Builds the resource API with the given configuration
       *
       * @param  {string} entityName          The name of the entity
       * @param  {object} additionalParams    Additional parameters to pass to the API
       * @param  {object} dataTransformations Object containing "toApi" and "fromApi" functions
       * @param  {object} entityPrototype     The prototype to be used for each entity instance
       * @return {object}                     The built angular resource
       */
      build: function (entityName, additionalParams, dataTransformations, entityPrototype) {
        var entityResource = $resource('/civicrm/ajax/rest', _.assign({
          entity: entityName,
          json: {
            sequential: 1
          }
        }, (additionalParams || {})), {
          save: {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            responseType: 'json',
            params: {
              action: 'create'
            },
            transformRequest: function (data) {
              return $httpParamSerializerJQLike(dataTransformations ? dataTransformations.toApi(data) : data);
            }
          },
          remove: {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            responseType: 'json',
            params: {
              action: 'delete'
            },
            transformRequest: function (data) {
              return $httpParamSerializerJQLike(dataTransformations ? dataTransformations.toApi(data) : data);
            }
          },
          getAll: {
            method: 'GET',
            responseType: 'json',
            cache: false,
            isArray: true,
            transformResponse: function (data, headers) {
              if (dataTransformations)
                return dataTransformations.fromApi(data.values);
              return data.values;
            }
          }
        });
        if (entityPrototype)
          entityResource.prototype = Object.create(entityPrototype);
        return entityResource;
      }
    };
  }]);
});
