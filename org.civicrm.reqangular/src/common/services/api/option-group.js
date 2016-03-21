define([
    'common/angular',
    'common/lodash',
    'common/modules/apis',
    'common/services/api'
], function (angular, _, apis) {
    'use strict';

    apis.factory('api.optionGroup', ['$log', 'api', function ($log, api) {
        $log.debug('api.optionGroup');


        /**
         * Groups option values by option group name
         *
         * @param {array} values
         * @param {array} groupIds
         * @return {object} an object formed as such:
         *    {
         *        group_name_1: [ { value_1 }, { ... }, { value_n } ],
         *        ...
         *        group_name_n: [ { value_1 }, { ... }, { value_n } ]
         *    }
         */
        function valuesByGroup(values, groupIds) {
            var groupedValues = {};

            _.forEach(groupIds, function (id, name) {
                groupedValues[name] = _.filter(values, function (value) {
                    return value.option_group_id === id;
                });
            });

            return groupedValues;
        }

        /**
         * Returns the ids of the option groups with the given names
         *
         * @param {string} groupNames
         * @return {Promise}
         *   Resolves to an object with key as the group name and the value
         *   as the group id
         */
        function idsOf(groupNames) {
            return this.sendGET('OptionGroup', 'get', {
                name: { 'IN': groupNames },
                return: ['id', 'name']
            })
            .then(function (data) {
                var idsByName = {};

                data.values.forEach(function (value) {
                    idsByName[value.name] = value.id;
                });

                return idsByName;
            });
        }

        return api.extend({

            /**
             * Returns the values of the option groups with the given names
             *
             * @param {string/array} groupNames
             *   If the value is an array of names, the method will group
             *   the values by option group names
             * @param {object} optional parameters for the query
             * @return {Promise}
             *   Resolves to an array with the values (if `groupNames` is a string)
             *   or an object with keys as the group names and values as the
             *   array of their option values (if `groupNames` is an array)
             */
            valuesOf: function (groupNames, params) {
                var multiple = _.isArray(groupNames);

                return idsOf.call(this, multiple ? groupNames : [groupNames])
                    .then(function (groupIds) {
                        return this.sendGET('OptionValue', 'get', angular.extend({
                            option_group_id: { 'IN': _.values(groupIds) },
                            is_active: '1'
                        }, params))
                        .then(function (data) {
                            if (multiple) {
                                return valuesByGroup(data.values, groupIds);
                            } else {
                                return data.values;
                            }
                        });
                    }.bind(this));
            }
        });
    }]);
});
