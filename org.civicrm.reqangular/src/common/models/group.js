define([
    'common/modules/models',
    'common/models/model',
    'common/models/instances/group-instance',
    'common/mocks/services/api/group-mock',        // Temporary, necessary to use the mocked API data
    'common/mocks/services/api/group-contact-mock' // Temporary, necessary to use the mocked API data
], function (models) {
    'use strict';

    models.factory('Group', [
        'Model', 'api.group.mock', 'api.group-contact.mock', 'GroupInstance',
        function (Model, groupAPI, groupContactAPI, instance) {

            return Model.extend({

                /**
                 * Returns a list of groups, each converted to a model instance
                 *
                 * @param {object} filters - Values the full list should be filtered by
                 * @param {object} pagination
                 *   `page` for the current page, `size` for number of items per page
                 * @return {Promise}
                 */
                all: function (filters, pagination) {
                    return groupAPI.all(this.processFilters(filters), pagination).then(function (response) {
                        response.list = response.list.map(function (group) {
                            return instance.init(group, true);
                        });

                        return response;
                    });
                },

                /**
                 * Finds a group by id
                 *
                 * @param {string} id
                 * @return {Promise} - Resolves with found group
                 */
                find: function (id) {
                    return groupAPI.find(id).then(function (group) {
                        return instance.init(group, true);
                    });
                },

                /**
                 * Returns the list of contact ids of the group with the given id
                 *
                 * @param {string} id
                 * @return {Array}
                 */
                contactIdsOf: function (id) {
                    return groupContactAPI.all({ group_id: id }).then(function (response) {
                        return response.list.map(function (groupContact) {
                            return groupContact.contact_id;
                        });
                    });
                }
            });
        }
    ]);
})
