define([
	'common/modules/apis',
	'common/services/api',
	'common/services/api/resource-builder'
], function (apis) {
	'use strict';

	apis.factory('apiBuilder', ['resourceBuilder', function (resourceBuilder) {
		var methods = {
			/**
			 * Sends the entity to the API via POST
			 *
			 * @param  {object} entityToSave The entity to send to the API
			 * @param  {object} additionalParams Additional params to pass to the api
			 * @return {Promise}
			 */
			saveEntity: function (entityToSave, additionalParams) {
				return this.resourceApi.save(additionalParams || {}, entityToSave).$promise;
			},

			/**
			 * Removes the entity
			 *
			 * @param  {object} entityToRemove The entity to remove
			 * @param  {object} additionalParams Additional params to pass to the api
			 * @return {Promise}
			 */
			removeEntity: function (entityToRemove, additionalParams) {
				return this.resourceApi.remove(additionalParams || {}, entityToRemove).$promise;
			},

			/**
			 * Lists entities, eventually filtered / paginated / sorted
			 *
			 * @param  {object} filters          Values the full list should be filtered by
			 * @param  {object} pagination       `page` for the current page, `size` for number of items per page
			 * @param  {object} sort             The field and direction to order by
			 * @param  {object} additionalParams Additional params to pass to the api
			 * @return {Promise}                 Resolves to an object with `list`
			 */
			getAllEntities: function (filters, pagination, sort, additionalParams) {
				var params = _.assign({}, (filters || {}), (additionalParams || {}), {
					options: {
						sort: sort || 'id DESC'
					}
				});
				if (pagination) {
					params.options.offset = (pagination.page - 1) * pagination.size;
					params.options.limit = pagination.size;
				}
				if(!params.action)
					params.action = 'get';
				return this.resourceApi.getAll(params).$promise;
			}
		};

		return {
			/**
			 * Extends the api with the given child api
			 *
			 * @param  {object} childAPI
			 * @return {object} the child api with the basic api as prototype
			 */
			build: function (childAPI, entityName, additionalParams, dataTransformations, entityPrototype) {
				return angular.extend(Object.create(methods, {
					resourceApi: {
						writable: false,
						enumerable: false,
						configurable: false,
						value: resourceBuilder.build(entityName, additionalParams, dataTransformations, entityPrototype)
					}
				}), childAPI);
			}
		};

	}]);
});
