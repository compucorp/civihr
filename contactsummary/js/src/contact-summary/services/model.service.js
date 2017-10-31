define([
    'contact-summary/modules/contact-summary.services',
    'contact-summary/services/item.service'
], function (services) {
    'use strict';

    /**
     * Base service to extend.
     *
     * This will have common functionality to avoid duplication of functionality between services.
     *
     * @param {ItemService} Item
     * @constructor
     */
    function ModelService(Item) {

        ////////////////////
        // Public Members //
        ////////////////////

        /**
         * @ngdoc service
         * @name ModelService
         * @type {Object}
         */
        var factory = {};

        /**
         * @name data
         * @propertyOf ModelService
         * @type {ItemService}
         */
        factory.data = {};

        /**
         * @ngdoc method
         * @name createInstance
         * @methodOf ModelService
         * @returns {(ModelService|Object)}
         * @constructs
         */
        factory.createInstance = function () {
            var instance = Object.create(this);
            instance.data = Item.createInstance();

            return instance;
        };

        /**
         * @ngdoc method
         * @name getData
         * @methodOf ModelService
         * @this factory
         * @returns {Object}
         */
        factory.getData = function () {
            return this.data.get();
        };

        /**
         * @ngdoc method
         * @name setData
         * @methodOf ModelService
         * @this factory
         * @param value
         */
        factory.setData = function (value) {
            this.data.set(value);
        };

        /**
         * @ngdoc method
         * @name setDataKey
         * @methodOf ModelService
         * @this factory
         * @param key
         * @param value
         */
        factory.setDataKey = function (key, value) {
            this.data.setKey(key, value);
        };

        return factory;
    }

    services.factory('ModelService', ['ItemService', ModelService]);
});
