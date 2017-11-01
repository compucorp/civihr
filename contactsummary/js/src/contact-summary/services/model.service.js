/* eslint-env amd */

define([
  'contact-summary/modules/contact-summary.services'
], function (services) {
  'use strict';

  modelService.__name = 'modelService';
  modelService.$inject = ['itemService'];

  function modelService (Item) {
    var factory = {};

    factory.data = {};

    /**
     * @returns {(modelService|Object)}
     */
    factory.createInstance = function () {
      var instance = Object.create(this);
      instance.data = Item.createInstance();

      return instance;
    };

    /**
     * @returns {Object}
     */
    factory.getData = function () {
      return this.data.get();
    };

    /**
     * @param value
     */
    factory.setData = function (value) {
      this.data.set(value);
    };

    /**
     * @param key
     * @param value
     */
    factory.setDataKey = function (key, value) {
      this.data.setKey(key, value);
    };

    return factory;
  }

  return modelService;
});
