define(['services/services'], function (services) {
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
     * @ngdoc method
     * @name createInstance
     * @methodOf ModelService
     * @returns {ModelService|Object}
     * @constructs
     */
    factory.createInstance = function () {
      data = Item.create();
      return Object.create(this);
    };

    /**
     * @ngdoc method
     * @name getData
     * @methodOf ModelService
     * @returns {Object}
     */
    factory.getData = function () {
      return data.get();
    };

    /**
     * @ngdoc method
     * @name setDataKey
     * @methodOf ModelService
     * @param key
     * @param value
     */
    factory.setDataKey = function (key, value) {
      data.setKey(key, value);
    };

    /////////////////////
    // Private Members //
    /////////////////////

    /**
     * @type {ItemService}
     */
    var data;

    return factory;
  }

  services.factory('ModelService', ['ItemService', ModelService]);
});
