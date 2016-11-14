define([
  'mocks/module',
  'mocks/constants',
  'common/angularMocks'
], function (mocks) {
  'use strict';

  /**
   * Base service to extend from.
   *
   * @param $q
   * @returns {Object}
   * @constructor
   */
  function BaseServiceMock($q) {
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

  function ItemServiceMock(Base) {
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

  function ModelServiceMock(Base) {
    var factory = Base.createInstance();

    factory.data = {};

    var create = jasmine.createSpy('create').and.callFake(function () {
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

  function ApiServiceMock(Base, $q) {
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

  function ContactDetailsServiceMock(Base) {
    var factory = Base.createInstance();

    factory.response = {};
    factory.data = {
      item: {
        prop: "val"
      }
    };

    return factory;
  }

  function LeaveServiceMock(Base) {
    var factory = Base.createInstance();

    factory.response = {};

    return factory;
  }

  function KeyDetailsServiceMock(Base) {
    var factory = Base.createInstance();

    factory.response = {};

    return factory;
  }

  function KeyDatesServiceMock(Base) {
    var factory = Base.createInstance();

    factory.response = {};

    return factory;
  }

  function ContractServiceMock(Base) {
    var factory = Base.createInstance();

    factory.response = {};
    factory.resetContracts = jasmine.createSpy("");

    return factory;
  }

  function PubSubMock() {
    var factory = {
      publish: jasmine.createSpy(""),
      subscribe: jasmine.createSpy("")
    };
    factory.subscribe.and.callFake(function(topic, listener){
      listener();
    });
    factory.publish.and.callFake(factory.subscribe);
    return factory;
  }

  mocks
    .factory('BaseServiceMock', ['$q', BaseServiceMock])
    .factory('ItemServiceMock', ['BaseServiceMock', ItemServiceMock])
    .factory('ModelServiceMock', ['ItemServiceMock', 'BaseServiceMock', ModelServiceMock])
    .factory('ApiServiceMock', ['BaseServiceMock', '$q', ApiServiceMock])
    .factory('ContactDetailsServiceMock', ['BaseServiceMock', ContactDetailsServiceMock])
    .factory('LeaveServiceMock', ['BaseServiceMock', LeaveServiceMock])
    .factory('KeyDetailsServiceMock', ['BaseServiceMock', 'settingsMock', KeyDetailsServiceMock])
    .factory('KeyDatesServiceMock', ['BaseServiceMock', KeyDatesServiceMock])
    .factory('ContractServiceMock', ['BaseServiceMock', ContractServiceMock])
    .factory('PubSubMock', [PubSubMock]);
});
