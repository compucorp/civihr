define([
    'common/modules/models-instances',
    'common/models/instances/instance'
], function (instances) {
    'use strict';

    instances.factory('GroupInstance', ['ModelInstance', function (ModelInstance) {
        var whiteList = ['id', 'name', 'title', 'description'];

        return ModelInstance.extend({

            /**
             * Override of parent method
             *
             * @param {object} result - The accumulator object
             * @param {string} key - The property name
             */
            fromAPIFilter: function (result, __, key) {
                if (_.includes(whiteList, key)) {
                    result[key] = this[key];
                }
            },
        });
    }]);
});
