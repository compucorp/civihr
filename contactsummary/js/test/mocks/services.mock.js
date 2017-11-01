/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/module.mock'
], function (angularMocks, constants, mocks) {
  'use strict';

  /**
   * Base service to extend from.
   *
   * @param $q
   * @returns {Object}
   * @constructor
   */
  BaseServiceMock.$inject = ['$q'];
  function BaseServiceMock ($q) {
    var get = jasmine.createSpy('get').and.callFake(function () {
      var deferred = $q.defer();

      if (this.resolvePromise) {
        deferred.resolve(this.getResponse('get', arguments));
      } else {
        deferred.reject('Something went wrong');
      }

      return deferred.promise;
    });

    var factory = {};

    factory.resolvePromise = true;
    factory.response = {};

    factory.createInstance = function () {
      return Object.create(this);
    };

    factory.get = function () {
      return get.apply(this, arguments);
    };

    factory.getResponse = function (method) {
      return this.response[method];
    };

    factory.respond = function (method, data) {
      this.response[method] = data;
    };

    factory.flush = function () {
      this.resolvePromise = true;
      this.response = {};
    };

    return factory;
  }

  ItemServiceMock.__name = 'ItemService';
  ItemServiceMock.$inject = ['BaseServiceMock'];
  function ItemServiceMock (Base) {
    var factory = Base.createInstance();

    factory.data = {};

    factory.create = jasmine.createSpy('create').and.callFake(function () {
      return Object.create(this);
    });

    factory.get = jasmine.createSpy('get');
    factory.set = jasmine.createSpy('set');
    factory.setKey = jasmine.createSpy('setKey');

    return factory;
  }

  ModelServiceMock.__name = 'ModelService';
  ModelServiceMock.$inject = ['BaseServiceMock'];
  function ModelServiceMock (Base) {
    var factory = Base.createInstance();

    factory.data = {};

    jasmine.createSpy('create').and.callFake(function () {
      return Object.create(this);
    });

    factory.create = Base.createInstance;
    factory.getData = jasmine.createSpy('getData').and.returnValue(factory.data);

    factory.setData = jasmine.createSpy('setData').and.callFake(function (value) {
      factory.data = value;
    });

    factory.setDataKey = jasmine.createSpy('setDataKey').and.callFake(function (key, value) {
      factory.data[key] = value;
    });

    return factory;
  }

  ApiServiceMock.__name = 'ApiService';
  ApiServiceMock.$inject = ['BaseServiceMock', '$q'];
  function ApiServiceMock (Base, $q) {
    var factory = Base.createInstance();

    var post = jasmine.createSpy('post').and.callFake(function () {
      var deferred = $q.defer();

      if (this.resolvePromise) {
        deferred.resolve(this.getResponse(arguments[2], arguments));
      } else {
        deferred.reject('Something went wrong');
      }

      return deferred.promise;
    });

    factory.response = {};

    factory.respondGet = function (entity, data) {
      var method = 'get';

      if (!this.response.hasOwnProperty(method)) {
        this.response[method] = {};
      }

      this.response[method][entity] = data;
    };

    factory.respondPost = function (entity, method, data) {
      if (!this.response.hasOwnProperty(method)) {
        this.response[method] = {};
      }

      this.response[method][entity] = data;
    };

    factory.post = function () {
      return post.apply(this, arguments);
    };

    factory.getResponse = function (method) {
      return this.response[method][arguments[1][0]];
    };

    return factory;
  }

  ContactDetailsServiceMock.__name = 'ContactDetailsService';
  ContactDetailsServiceMock.$inject = ['BaseServiceMock'];
  function ContactDetailsServiceMock (Base) {
    var factory = Base.createInstance();

    factory.response = {};
    factory.data = {
      item: {
        prop: 'val'
      }
    };

    return factory;
  }

  KeyDetailsServiceMock.__name = 'KeyDetailsService';
  KeyDetailsServiceMock.$inject = ['BaseServiceMock'];
  function KeyDetailsServiceMock (Base) {
    var factory = Base.createInstance();

    factory.response = {};

    return factory;
  }

  KeyDatesServiceMock.__name = 'KeyDatesService';
  KeyDatesServiceMock.$inject = ['BaseServiceMock'];
  function KeyDatesServiceMock (Base) {
    var factory = Base.createInstance();

    factory.response = {};

    return factory;
  }

  ContractServiceMock.__name = 'ContractService';
  ContractServiceMock.$inject = ['BaseServiceMock'];
  function ContractServiceMock (Base) {
    var factory = Base.createInstance();

    factory.response = {};
    factory.resetContracts = jasmine.createSpy('');

    return factory;
  }

  function PubSubMock () {
    var factory = {
      publish: jasmine.createSpy(''),
      subscribe: jasmine.createSpy('')
    };
    factory.subscribe.and.callFake(function (topic, listener) {
      listener();
    });
    factory.publish.and.callFake(factory.subscribe);
    return factory;
  }

  mocks
    .factory('BaseServiceMock', BaseServiceMock)
    .factory('ItemServiceMock', ItemServiceMock)
    .factory('ModelServiceMock', ModelServiceMock)
    .factory('ApiServiceMock', ApiServiceMock)
    .factory('ContactDetailsServiceMock', ContactDetailsServiceMock)
    .factory('KeyDetailsServiceMock', 'settingsMock', KeyDetailsServiceMock)
    .factory('KeyDatesServiceMock', KeyDatesServiceMock)
    .factory('ContractServiceMock', ContractServiceMock)
    .factory('PubSubMock', PubSubMock);
});
