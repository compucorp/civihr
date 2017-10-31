/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('ModelService', function () {
    var ModelService,
      ItemServiceMock;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('ItemService', function () {
        return ItemServiceMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      ItemServiceMock = $injector.get('ItemServiceMock');
    }));

    beforeEach(inject(function (_ModelService_) {
      ModelService = _ModelService_;
    }));

    describe('createInstance', function () {
      it('should create the expected instance', function () {
        var instance = ModelService.createInstance();

        // Because 'instanceof' isn't allowed for instances created from object literals
        expect(ModelService.isPrototypeOf(instance)).toBe(true);
      });
    });

    describe('getData', function () {
      var data, instance;
      var expectedData = {id: 123, dateOfBirth: '1970/01/01'};

      beforeEach(function () {
        ItemServiceMock.get.and.returnValue(expectedData);

        instance = ModelService.createInstance();
        data = instance.getData();
      });

      it('should call "get()" on ItemService', function () {
        expect(ItemServiceMock.get).toHaveBeenCalled();
      });

      it('should return the expected data', function () {
        expect(data).toEqual(expectedData);
      });
    });

    describe('setDataKey', function () {
      var instance;
      var expectedId = 123;

      beforeEach(function () {
        instance = ModelService.createInstance();

        instance.setDataKey('id', expectedId);
      });

      it('should call "setKey()" on ItemService', function () {
        expect(ItemServiceMock.setKey).toHaveBeenCalledWith('id', expectedId);
      });
    });
  });
});
