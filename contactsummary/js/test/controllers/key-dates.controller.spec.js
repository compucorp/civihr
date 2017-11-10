/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'common/services/pub-sub',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('KeyDatesController', function () {
    var $rootScope, contractServiceMock, controller, ctrlConstructor, pubSub;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('contractService', function () {
        return contractServiceMock;
      });
    }));

    beforeEach(inject(function ($injector, _$controller_, _pubSub_, _$rootScope_) {
      contractServiceMock = $injector.get('contractServiceMock');
      ctrlConstructor = _$controller_;
      pubSub = _pubSub_;
      $rootScope = _$rootScope_;
    }));

    beforeEach(function () {
      spyOn(contractServiceMock, 'get').and.callThrough();
      spyOn(pubSub, 'publish').and.callThrough();
      spyOn(pubSub, 'subscribe').and.callThrough();

      controller = ctrlConstructor('KeyDatesController');
    });

    describe('constructor', function () {
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
        var events = [
          'Contract::created',
          'Contract::updated',
          'Contract::deleted',
          'JobRole::created',
          'JobRole::updated',
          'JobRole::deleted'
        ];

        events.map(function (event) {
          expect(pubSub.subscribe).toHaveBeenCalledWith(event, jasmine.any(Function));
        });
      });
    });

    describe('When contract or job role creating/deleting/updating events are published', function () {
      describe('when contract is deleted', function () {
        beforeEach(function () {
          pubSub.publish('Contract::deleted', '1');
          $rootScope.$apply();
        });

        it('calls contract service to remove contract from the list', function () {
          expect(contractServiceMock.removeContract).toHaveBeenCalledWith('1');
        });
      });

      describe('when contract is created', function () {
        beforeEach(function () {
          pubSub.publish('Contract::created', '1');
        });

        it('calls contract service to get new contract data', function () {
          expect(contractServiceMock.get).toHaveBeenCalled();
        });
      });

      describe('when JobRole is deleted', function () {
        beforeEach(function () {
          pubSub.publish('JobRole::deleted');
        });

        it('calls contract service to get new role data', function () {
          expect(contractServiceMock.get).toHaveBeenCalled();
        });
      });

      describe('when JobRole is updated', function () {
        beforeEach(function () {
          pubSub.publish('JobRole::updated');
        });

        it('calls contract service to get new role data', function () {
          expect(contractServiceMock.get).toHaveBeenCalled();
        });
      });
    });
  });
});
