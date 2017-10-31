/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('KeyDatesCtrl', function () {
    var ctrlConstructor,
      PubSubMock, ContractServiceMock, controllerObj;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('pubSub', function () {
        return PubSubMock;
      });

      $provide.factory('ContractService', function () {
        return ContractServiceMock;
      });
    }));

    beforeEach(inject(function ($injector, _$controller_) {
      PubSubMock = $injector.get('PubSubMock');
      ContractServiceMock = $injector.get('ContractServiceMock');
      ctrlConstructor = _$controller_;
    }));

    describe('constructor', function () {
      it('Should subscribe for contract changes', function () {
        spyOn(ContractServiceMock, 'get').and.callThrough();
        controllerObj = ctrlConstructor('KeyDatesCtrl');
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('contract-refresh', jasmine.any(Function));
        expect(controllerObj.dates).toEqual([]);
        expect(ContractServiceMock.get).toHaveBeenCalled();
      });
    });
  });
});
