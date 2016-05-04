define([
	'access-rights/modules/models'
], function (models) {
	'use strict';

	models.factory('baseApi', ['$resource', '$httpParamSerializerJQLike', '$log', function ($resource, $httpParamSerializerJQLike, $log) {
		var api = $resource('/civicrm/ajax/rest', null, {
			save: {
				url: 'test.test',
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
				},
				responseType: 'json',
				params: {
					json: true,
					sequential: true
				},
				transformRequest: function (data) {
					data.testing = 'adicionalTestParameter!!!';
					return $httpParamSerializerJQLike(data);
				}
			},
			getAll: {
				method: 'GET',
				cache: false,
				responseType: 'json',
				transformResponse: function (data, headers) {
					return {
						list: data.values,
						total: data.count,
						allIds: Object.keys(data.values)
					};
				},
				params: {
					'return': 'id,name',
					json: true,
					sequential: true
				}
			}
		});

		return {

			/**
			 * Extends the api with the given child api
			 *
			 * @param  {object} childAPI
			 * @return {object} the child api with the basic api as prototype
			 */
			extend: function (childAPI) {
				return angular.extend(Object.create(this), childAPI);
			},

			/**
			 * Sends the entity to the API via POST
			 *
			 * @param  {object} entityToSave The entity to send to the API
			 * @param  {string} entityName   The entity name
			 * @return {Promise}
			 */
			saveEntity: function (entityToSave, entityName) {
				return api.save({
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
			getAllEntities: function (optionGroupName, filters, pagination, sort, additionalParams) {
				filters = filters || {};
				var params = _.assign({
					action: 'get',
					entity: 'OptionValue',
					option_group_name: optionGroupName
				}, filters, (additionalParams || {}), {
					options: {
						sort: sort || 'id DESC'
					}
				});
				if (pagination) {
					params.options.offset = (pagination.page - 1) * pagination.size;
					params.options.limit = pagination.size;
				}
				return api.getAll(params).$promise;
			}
		};
	}]);
});
