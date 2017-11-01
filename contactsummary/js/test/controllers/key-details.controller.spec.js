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

    describe('constructor', function () {
      it('Should subscribe for contract changes', function () {
        spyOn(contactDetailsServiceMock, 'get').and.callThrough();
        ctrlConstructor('KeyDetailsController');
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('contract-refresh', jasmine.any(Function));
        expect(contractServiceMock.resetContracts).toHaveBeenCalled();
        expect(contactDetailsServiceMock.data.item).toEqual({});
        expect(contactDetailsServiceMock.get).toHaveBeenCalled();
      });
    });
  });
});
