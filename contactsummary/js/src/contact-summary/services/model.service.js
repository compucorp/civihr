/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  modelService.$inject = ['itemService'];

  function modelService (Item) {
    var factory = {};

    factory.data = {};
    factory.createInstance = createInstance;
    factory.getData = getData;
    factory.setData = setData;
    factory.setDataKey = setDataKey;

    return factory;

    /**
     * @returns {(modelService|Object)}
     */
    function createInstance () {
      var instance = Object.create(factory);
      instance.data = Item.createInstance();

      return instance;
    }

    /**
     * @returns {Object}
     */
    function getData () {
      return this.data.get();
    }

    /**
     * @param value
     */
    function setData (value) {
      this.data.set(value);
    }

    /**
     * @param key
     * @param value
     */
    function setDataKey (key, value) {
      this.data.setKey(key, value);
    }
  }

  return { modelService: modelService };
});
