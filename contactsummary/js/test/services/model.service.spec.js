/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('modelService', function () {
    var modelService,
      itemServiceMock;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('itemService', function () {
        return itemServiceMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      itemServiceMock = $injector.get('itemServiceMock');
    }));

    beforeEach(inject(function (_modelService_) {
      modelService = _modelService_;
    }));

    describe('createInstance', function () {
      it('should create the expected instance', function () {
        var instance = modelService.createInstance();

        // Because 'instanceof' isn't allowed for instances created from object literals
        expect(modelService.isPrototypeOf(instance)).toBe(true);
      });
    });

    describe('getData', function () {
      var data, instance;
      var expectedData = {id: 123, dateOfBirth: '1970/01/01'};

      beforeEach(function () {
        itemServiceMock.get.and.returnValue(expectedData);

        instance = modelService.createInstance();
        data = instance.getData();
      });

      it('should call "get()" on itemService', function () {
        expect(itemServiceMock.get).toHaveBeenCalled();
      });

      it('should return the expected data', function () {
        expect(data).toEqual(expectedData);
      });
    });

    describe('setDataKey', function () {
      var instance;
      var expectedId = 123;

      beforeEach(function () {
        instance = modelService.createInstance();

        instance.setDataKey('id', expectedId);
      });

      it('should call "setKey()" on itemService', function () {
        expect(itemServiceMock.setKey).toHaveBeenCalledWith('id', expectedId);
      });
    });
  });
});
