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
  baseServiceMock.$inject = ['$q'];
  function baseServiceMock ($q) {
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

  itemServiceMock.__name = 'itemService';
  itemServiceMock.$inject = ['baseServiceMock'];
  function itemServiceMock (Base) {
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

  modelServiceMock.__name = 'modelService';
  modelServiceMock.$inject = ['baseServiceMock'];
  function modelServiceMock (Base) {
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

  apiServiceMock.__name = 'apiService';
  apiServiceMock.$inject = ['baseServiceMock', '$q'];
  function apiServiceMock (Base, $q) {
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

  contactDetailsServiceMock.__name = 'contactDetailsService';
  contactDetailsServiceMock.$inject = ['baseServiceMock'];
  function contactDetailsServiceMock (Base) {
    var factory = Base.createInstance();

    factory.response = {};
    factory.data = {
      item: {
        prop: 'val'
      }
    };

    return factory;
  }

  jobRoleServiceMock.__name = 'jobRoleService';
  function jobRoleServiceMock () {
    var factory = { get: jasmine.any(Function) };

    factory.jobRoles = [
      {
        'id': '15',
        'title': 'Examle new',
        'start_date': '2017-11-10'
      }
    ];

    return factory;
  }

  keyDetailsServiceMock.__name = 'keyDetailsService';
  keyDetailsServiceMock.$inject = ['baseServiceMock'];
  function keyDetailsServiceMock (Base) {
    var factory = Base.createInstance();

    factory.response = {};

    return factory;
  }

  keyDatesServiceMock.__name = 'keyDatesService';
  keyDatesServiceMock.$inject = ['baseServiceMock'];
  function keyDatesServiceMock (Base) {
    var factory = Base.createInstance();

    factory.response = {};

    return factory;
  }

  contractServiceMock.__name = 'contractService';
  contractServiceMock.$inject = ['baseServiceMock', '$q'];
  function contractServiceMock (Base, $q) {
    var factory = Base.createInstance();

    factory.response = {};
    factory.resetContracts = jasmine.createSpy('');
    factory.getOptions = jasmine.createSpy('');
    factory.removeContract = jasmine.createSpy('');

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
    .factory('baseServiceMock', baseServiceMock)
    .factory('itemServiceMock', itemServiceMock)
    .factory('modelServiceMock', modelServiceMock)
    .factory('apiServiceMock', apiServiceMock)
    .factory('contactDetailsServiceMock', contactDetailsServiceMock)
    .factory('jobRoleServiceMock', jobRoleServiceMock)
    .factory('keyDetailsServiceMock', 'settingsMock', keyDetailsServiceMock)
    .factory('keyDatesServiceMock', keyDatesServiceMock)
    .factory('contractServiceMock', contractServiceMock)
    .factory('PubSubMock', PubSubMock);
});
