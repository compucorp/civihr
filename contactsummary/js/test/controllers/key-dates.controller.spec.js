/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('KeyDatesController', function () {
    var ctrlConstructor, PubSubMock, contractServiceMock, controller;

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
      beforeEach(function () {
        spyOn(contractServiceMock, 'get').and.callThrough();
        controller = ctrlConstructor('KeyDatesController');
      });

      it('Should subscribe for contract changes', function () {
        expect(controller.dates).toEqual([]);
        expect(contractServiceMock.get).toHaveBeenCalled();
      });

      it('initialiaze contracts and job roles count and key dates', function () {
        expect(controller.activeContracts).toBe(0);
        expect(controller.activeRoles).toBe(0);
        expect(controller.dates.length).toBe(0);
      });

      it('initialiaze pubsub subscriptions', function () {
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('Contract::created', jasmine.any(Function));
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('Contract::updated', jasmine.any(Function));
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('Contract::deleted', jasmine.any(Function));
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('JobRole::created', jasmine.any(Function));
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('JobRole::updated', jasmine.any(Function));
        expect(PubSubMock.subscribe).toHaveBeenCalledWith('JobRole::deleted', jasmine.any(Function));
      })
    });
  });
});
