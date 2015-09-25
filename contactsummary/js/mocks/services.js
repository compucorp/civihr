define(['mocks/module'], function (module) {
  'use strict';

  function ApiServiceMock() {
    var factory = {};
    factory.get = jasmine.createSpy('get');
    factory.getValue = jasmine.createSpy('getValue');
    factory.create = jasmine.createSpy('create');
    factory.update = jasmine.createSpy('update');
    factory.delete = jasmine.createSpy('delete');

    return factory;
  }

  function KeyDetailsServiceMock() {
    var factory = {};
    factory.get = jasmine.createSpy('get');

    return factory;
  }

  function KeyDatesServiceMock() {
    var factory = {};
    factory.get = jasmine.createSpy('get');

    return factory;
  }

  module
    .factory('ApiServiceMock', [ApiServiceMock])
    .factory('KeyDetailsServiceMock', [KeyDetailsServiceMock])
    .factory('KeyDatesServiceMock', [KeyDatesServiceMock]);
});
