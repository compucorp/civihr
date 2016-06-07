define([
    'common/lodash',
    'common/modules/models-instances'
], function (_, instances) {
    'use strict';

    instances.factory('ModelInstance', function () {

        return {

            /**
             * Creates a plain object (w/o prototype) containing
             * only the attributes of this instance
             *
             * @return {object}
             */
            attributes: function () {
                return _.transform(this, function (result, __, key) {
                    !_.isFunction(this[key]) && (result[key] = this[key]);
                }, Object.create(null), this);
            },

            /**
             * Returns the default custom data (as in, not given by the API)
             * with its default values
             *
             * @return {object}
             */
            defaultCustomData: function () {
                return {};
            },

            /**
             * Creates a new instance type by extending this base type with
             * additional methods
             *
             * @param {object} instance
             * @return {object} the new type with the basic instance as prototype
             */
            extend: function (instance) {
                return _.assign(Object.create(this), instance);
            },

            /**
             * Normalizes the given data in the direction API -> Model
             *
             * @param {object} attributes
             * @return {object}
             */
            fromAPI: function (attributes) {
                return _.transform(
                    attributes,
                    this.fromAPIFilter.bind(attributes),
                    Object.create(null)
                );
            },

            /**
             * Function that filters data coming from the API, being called in
             * as the iteratee of a _.transform() call
             *
             * Can be overriden by children api for custom filtering
             *
             * @param {object} result - The accumulator object
             * @param {string} key - The property name
             */
            fromAPIFilter: function (result, __, key) {
                result[key] = this[key];
            },

            /**
             * Creates a new instance, optionally with its data normalized
             *
             * @param {object} attributes - The instance data
             * @param {boolean} fromAPI - If the data comes from the API and needs to be normalized
             * @return {object}
             */
            init: function (attributes, fromAPI) {
                attributes = _.assign(this.defaultCustomData(), attributes);

                if (typeof fromAPI !== 'undefined' && fromAPI) {
                    attributes = this.fromAPI(attributes);
                }

                return _.assign(Object.create(this), attributes);
            },

            /**
             * Normalizes the instance data in the direction Model -> API
             *
             * @return {object}
             */
            toAPI: function () {
                var attributes = this.attributes();

                return _.transform(
                    attributes,
                    this.toAPIFilter.bind(attributes),
                    Object.create(null)
                );
            },

            /**
             * Function that filters data meant to be sent to the API, being
             * called in as the iteratee of a _.transform() call
             *
             * Can be overriden by children api for custom filtering
             *
             * @param {object} result - The accumulator object
             * @param {string} key - The property name
             */
            toAPIFilter: function (result, __, key) {
                result[key] = this[key];
            }
        }
    });
});
