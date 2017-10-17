/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'contact-summary/app',
  'contact-summary/controllers/keyDates',
  'mocks/constants',
  'mocks/services'
], function () {
  'use strict';

  describe('KeyDetailsCtrl', function () {
    var ctrlConstructor,
      PubSubMock, ContractServiceMock, ContactDetailsServiceMock;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('pubSub', function () {
        return PubSubMock;
      });

      $provide.factory('ContractService', function () {
        return ContractServiceMock;
      });

      $provide.factory('ContactDetailsService', function () {
        return ContactDetailsServiceMock;
      });
    }));

    beforeEach(inject(function ($injector, _$controller_) {
      PubSubMock = $injector.get('PubSubMock');
      ContractServiceMock = $injector.get('ContractServiceMock');
      ContactDetailsServiceMock = $injector.get('ContactDetailsServiceMock');
      ctrlConstructor = _$controller_;
    }));

    describe('constructor', function () {
      it('Should subscribe for contract changes', function () {
        spyOn(ContactDetailsServiceMock, 'get').and.callThrough();
        ctrlConstructor('KeyDetailsCtrl');
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('contract-refresh', jasmine.any(Function));
        expect(ContractServiceMock.resetContracts).toHaveBeenCalled();
        expect(ContactDetailsServiceMock.data.item).toEqual({});
        expect(ContactDetailsServiceMock.get).toHaveBeenCalled();
      });
    });
  });
});
