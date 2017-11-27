/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment'
], function (angular, _, moment) {
  'use strict';

  itemService.__name = 'itemService';

  function itemService () {
    var factory = {};

    factory.createInstance = createInstance;
    factory.get = get;
    factory.set = set;
    factory.setKey = setKey;

    return factory;

    /**
     * @returns {(Object|itemService)}
     */
    function createInstance () {
      var instance = Object.create(factory);
      instance.item = {};

      return instance;
    }

    /**
     * @returns {Object}
     */
    function get () {
      return this.item;
    }

    /**
     * @param data
     */
    function set (data) {
      if (!angular.isObject(data)) {
        throw new TypeError('Data must be of type Object');
      }

      this.item = data;
    }

    /**
     * @param key
     * @param value
     */
    function setKey (key, value) {
      this.item[key] = value;
    }
  }

  return itemService;
});
