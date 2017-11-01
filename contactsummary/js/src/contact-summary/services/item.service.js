/* eslint-env amd */

define([
  'common/angular',
  'common/moment'
], function (angular, moment) {
  'use strict';

  itemService.__name = 'itemService';

  function itemService () {
    var factory = {};

    /**
     * @returns {(Object|itemService)}
     */
    factory.createInstance = function () {
      var instance = Object.create(this);
      instance.item = {};

      return instance;
    };

    /**
     * @returns {Object}
     */
    factory.get = function () {
      return this.item;
    };

    /**
     * @param data
     */
    factory.set = function (data) {
      if (!angular.isObject(data)) {
        throw new TypeError('Data must be of type Object');
      }

      this.item = data;
    };

    /**
     * @param key
     * @param value
     */
    factory.setKey = function (key, value) {
      this.item[key] = value;
    };

    return factory;
  }

  return itemService;
});
