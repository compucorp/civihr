/* eslint-env amd, jasmine */

define([
  'common/moment',
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'common/services/pub-sub',
  'contact-summary/modules/contact-summary.module'
], function (moment) {
  'use strict';

  describe('KeyDatesController', function () {
    var $httpBackend, $rootScope, $q, contractServiceMock, controller, ctrlConstructor,
      jobRoleServiceMock, pubSub;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('contractService', function () {
        return contractServiceMock;
      });

      $provide.factory('jobRoleService', function () {
        return contractServiceMock;
      });
    }));

    beforeEach(inject(function (_$controller_, $injector, _$httpBackend_, _$q_,
      _pubSub_, _$rootScope_) {
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
      $q = _$q_;
      ctrlConstructor = _$controller_;
      contractServiceMock = $injector.get('contractServiceMock');
      jobRoleServiceMock = $injector.get('jobRoleServiceMock');
      pubSub = _pubSub_;
    }));

    beforeEach(function () {
      spyOn(pubSub, 'publish').and.callThrough();
      spyOn(pubSub, 'subscribe').and.callThrough();
    });

    describe('when controller is initialized', function () {
      describe('when there are no job contracts and job roles', function () {
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
        var today = moment();
        var tomorrow = today.add(2, 'days').format('YYYY-MM-DD');
        var yesterday = today.subtract(3, 'days').format('YYYY-MM-DD');

        beforeEach(function () {
          $httpBackend.whenPOST(/civicrm/).respond({});

          delete jobRoleServiceMock.jobRoles[0]['end_date'];
        });

        describe('when end date of a job roles is greater than today', function () {
          beforeEach(function () {
            jobRoleServiceMock.jobRoles[0]['end_date'] = tomorrow;

            spyOn(contractServiceMock, 'get').and.returnValue($q.resolve(jobRoleServiceMock.jobRoles));

            controller = ctrlConstructor('KeyDatesController');

            $rootScope.$apply();
          });

          it('sets the active jobroles counter to one (1)', function () {
            expect(controller.activeRoles).toBe(1);
          });
        });

        describe('when end date of a job roles is less than today', function () {
          beforeEach(function () {
            jobRoleServiceMock.jobRoles[0]['end_date'] = yesterday;

            spyOn(contractServiceMock, 'get').and.returnValue($q.resolve(jobRoleServiceMock.jobRoles));

            controller = ctrlConstructor('KeyDatesController');

            $rootScope.$apply();
          });

          it('sets the active jobroles counter to zero (0)', function () {
            expect(controller.activeRoles).toBe(0);
          });
        });
      });
    });

    describe('When contract or job role creating/deleting/updating events are published', function () {
      beforeEach(function () {
        spyOn(contractServiceMock, 'get').and.callThrough();
        controller = ctrlConstructor('KeyDatesController');
      });

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
