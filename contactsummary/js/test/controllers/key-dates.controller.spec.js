/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('KeyDatesController', function () {
    var ctrlConstructor,
      PubSubMock, contractServiceMock, controllerObj;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('pubSub', function () {
        return PubSubMock;
      });

      $provide.factory('contractService', function () {
        return contractServiceMock;
      });
    }));

    beforeEach(inject(function ($injector, _$controller_) {
      PubSubMock = $injector.get('PubSubMock');
      contractServiceMock = $injector.get('contractServiceMock');
      ctrlConstructor = _$controller_;
    }));

    describe('constructor', function () {
      it('Should subscribe for contract changes', function () {
        spyOn(contractServiceMock, 'get').and.callThrough();
        controllerObj = ctrlConstructor('KeyDatesController');
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('contract-refresh', jasmine.any(Function));
        expect(controllerObj.dates).toEqual([]);
        expect(contractServiceMock.get).toHaveBeenCalled();
      });
    });
  });
});
