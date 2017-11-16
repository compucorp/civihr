/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('KeyDetailsController', function () {
    var ctrlConstructor,
      PubSubMock, contractServiceMock, contactDetailsServiceMock;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('pubSub', function () {
        return PubSubMock;
      });

      $provide.factory('contractService', function () {
        return contractServiceMock;
      });

      $provide.factory('contactDetailsService', function () {
        return contactDetailsServiceMock;
      });
    }));

    beforeEach(inject(function ($injector, _$controller_) {
      PubSubMock = $injector.get('PubSubMock');
      contractServiceMock = $injector.get('contractServiceMock');
      contactDetailsServiceMock = $injector.get('contactDetailsServiceMock');
      ctrlConstructor = _$controller_;
    }));

    describe('init()', function () {
      beforeEach(function () {
        spyOn(contactDetailsServiceMock, 'get').and.callThrough();
        ctrlConstructor('KeyDetailsController');
      });

      it('subscribes for contract refresh event', function () {
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('contract-refresh', jasmine.any(Function));
      });

      it('calls function to get contract options', function () {
        expect(contractServiceMock.getOptions).toHaveBeenCalled();
        expect(contactDetailsServiceMock.get).toHaveBeenCalled();
      });

      it('resets data for contracts and its details', function () {
        expect(contractServiceMock.resetContracts).toHaveBeenCalled();
        expect(contactDetailsServiceMock.data.item).toEqual({});
      });
    });
  });
});
