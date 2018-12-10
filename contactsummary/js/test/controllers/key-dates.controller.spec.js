/* eslint-env amd, jasmine */

define([
  'common/moment',
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'common/models/job-role',
  'common/services/pub-sub',
  'contact-summary/contact-summary.module'
], function (moment) {
  'use strict';

  describe('KeyDatesController', function () {
    var $httpBackend, $rootScope, $q, contractServiceMock, controller,
      ctrlConstructor, JobRole, jobRoleActiveForContactSpy, pubSub;

    beforeEach(module('contactsummary', 'contactsummary.mocks', 'contactsummary.templates'));

    beforeEach(module(function ($provide) {
      $provide.factory('contractService', function () {
        return contractServiceMock;
      });
    }));

    beforeEach(inject(function (_$controller_, $injector, _$httpBackend_, _$q_,
      _JobRole_, _pubSub_, _$rootScope_) {
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
      $q = _$q_;
      ctrlConstructor = _$controller_;
      contractServiceMock = $injector.get('contractServiceMock');
      JobRole = _JobRole_;
      pubSub = _pubSub_;
    }));

    beforeEach(function () {
      spyOn(pubSub, 'publish').and.callThrough();
      spyOn(pubSub, 'subscribe').and.callThrough();

      jobRoleActiveForContactSpy = spyOn(JobRole, 'activeForContact');
    });

    describe('when controller is initialized', function () {
      describe('when there are no job contracts and job roles', function () {
        beforeEach(function () {
          spyOn(contractServiceMock, 'get').and.callThrough();
          jobRoleActiveForContactSpy.and.returnValue($q.resolve([{}]));

          controller = ctrlConstructor('KeyDatesController');

          controller.$onInit();
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

      describe('when there are job contracts and job roles', function () {
        beforeEach(function () {
          $httpBackend.whenPOST(/civicrm/).respond({});
        });

        describe('when end date of a job roles is greater than today', function () {
          beforeEach(function () {
            jobRoleActiveForContactSpy.and.returnValue($q.resolve([{ id: 50 }]));

            controller = ctrlConstructor('KeyDatesController');

            initControllerAndDigest();
          });

          it('sets the active jobroles counter to one (1)', function () {
            expect(controller.activeRoles).toBe(1);
          });
        });
      });
    });

    describe('When contract or job role creating/deleting/updating events are published', function () {
      beforeEach(function () {
        spyOn(contractServiceMock, 'get').and.returnValue($q.resolve([]));
        jobRoleActiveForContactSpy.and.returnValue($q.resolve([{}]));

        controller = ctrlConstructor('KeyDatesController');

        initControllerAndDigest();
      });

      describe('when contract is deleted', function () {
        beforeEach(function () {
          pubSub.publish('Contract::deleted', '1');
          $rootScope.$digest();
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

    /**
     * Initialises the controller and digests the root scope
     */
    function initControllerAndDigest () {
      controller.$onInit();
      $rootScope.$digest();
    }
  });
});
