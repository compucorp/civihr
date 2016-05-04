define([
	'access-rights/modules/models',
	'access-rights/services/resource-builder',
], function (models) {
	'use strict';

	models.factory('apiBuilder', ['resourceBuilder', function (resourceBuilder) {
		var methods = {
			/**
			 * Sends the entity to the API via POST
			 *
			 * @param  {object} entityToSave The entity to send to the API
			 * @param  {string} entityName   The entity name
			 * @return {Promise}
			 */
			saveEntity: function (entityToSave, entityName) {
				return this.resourceApi.save({
					entity: entity,
					action: 'create'
				}, obj).$promise;
			},

			/**
			 * Lists entities, eventually filtered / paginated / sorted
			 *
			 * @param  {string} optionGroupName  The name o the option group
			 * @param  {object} filters          Values the full list should be filtered by
			 * @param  {object} pagination       `page` for the current page, `size` for number of items per page
			 * @param  {object} sort             The field and direction to order by
			 * @param  {object} additionalParams Additional params to pass to the api
			 * @return {Promise}                 Resolves to an object with `list`
			 */
			getAllEntities: function (optionGroupName, filters, pagination, sort) {
				var params = _.assign({
					action: 'get',
				}, (filters || {}), {
					options: {
						sort: sort || 'id DESC'
					}
				});
				if (pagination) {
					params.options.offset = (pagination.page - 1) * pagination.size;
					params.options.limit = pagination.size;
				}
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
