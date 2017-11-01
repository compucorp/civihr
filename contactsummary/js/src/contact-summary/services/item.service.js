/* eslint-env amd */

define([
  'common/angular',
  'common/moment'
], function (angular, moment) {
  'use strict';

  ItemService.__name = 'ItemService';

  function ItemService () {
    /**
     * @ngdoc service
     * @name ItemService
     */
    var factory = {};

    /**
     * @ngdoc method
     * @name createInstance
     * @methodOf ItemService
     * @returns {(Object|ItemService)}
     */
    factory.createInstance = function () {
      var instance = Object.create(this);
      instance.item = {};

      return instance;
    };

    /**
     * @ngdoc method
     * @name get
     * @methodOf ItemService
     * @returns {Object}
     */
    factory.get = function () {
      return this.item;
    };

    /**
     * @ngdoc method
     * @name set
     * @methodOf ItemService
     * @param data
     */
    factory.set = function (data) {
      if (!angular.isObject(data)) {
        throw new TypeError('Data must be of type Object');
      }

      this.item = data;
    };

    /**
     * @ngdoc method
     * @name setKey
     * @methodOf ItemService
     * @param key
     * @param value
     */
    factory.setKey = function (key, value) {
      this.item[key] = value;
    };

    return factory;
  }

  return ItemService;
});
