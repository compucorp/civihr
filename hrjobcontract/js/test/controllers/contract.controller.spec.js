/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/data/contract.data',
  'common/angularMocks',
  'job-contract/modules/job-contract.module'
], function (_, moment, contractMock) {
  'use strict';

  describe('ContractController', function () {
    var $controller, $httpBackend, $modal, $q, $rootScope, $scope, $window, controller,
      AbsenceType, contractService, utilsService;
    var calculationUnitsMock = [{ value: 1, name: 'days' }, { value: 2, name: 'hours' }];

    // Populate contract mock leaves with values
    contractMock.contractEntity.leave = contractMock.contractLeaves.values;

    beforeEach(module('job-contract', 'job-contract.templates', function ($provide) {
      $window = { location: jasmine.createSpyObj('location', ['assign']) };

      $provide.value('$window', $window);
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$uibModal_, _$q_, _$httpBackend_,
      _$window_, _AbsenceType_, _contractService_, _utilsService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $q = _$q_;
      $httpBackend = _$httpBackend_;
      $modal = _$uibModal_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      AbsenceType = _AbsenceType_;
      contractService = _contractService_;
      utilsService = _utilsService_;

      mockBackendCalls();
      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(AbsenceType, 'loadCalculationUnits').and.callThrough();
      spyOn(contractService, 'fullDetails').and.callThrough();
      spyOn(utilsService, 'updateEntitlements');
      makeController();
    }));

    describe('when loads', function () {
      beforeEach(function () {
        $httpBackend.flush();
        $rootScope.$digest();
      });

      it('retrieves contract full details', function () {
        expect(contractService.fullDetails).toHaveBeenCalled();
      });

      describe('when it loads the leave data', function () {
        var leaveMocks = contractMock.contractLeaves.values;
        var testCases = {
          'sorted by id': _.cloneDeep(leaveMocks),
          'reordered': _.shuffle(_.cloneDeep(leaveMocks)),
          'reordered and have extra entry': _.shuffle(_.cloneDeep(leaveMocks).push({ leave_type: '808' })),
          'reordered and lacks entries': _.shuffle(_.cloneDeep(leaveMocks).splice(0, 1))
        };

        _.each(testCases, function (testData, testCase) {
          describe('when Absence Types are ' + testCase, function () {
            beforeEach(function () {
              $scope.leave = testData;
              $scope.revisionCurrent.jobcontract_id = 1;

              $scope.$emit('updateContractView');
              $scope.$digest();
              $httpBackend.flush();
            });

            it('maps Absence Types and Job Contract data correctly', function () {
              _.each($scope.leave, function (entry) {
                expect(entry.leave_amount).toEqual(_.find(leaveMocks, {
                  leave_type: entry.leave_type
                }).leave_amount);
              });
            });
          });
        });
      });

      describe('when fetches revision details', function () {
        var result;
        var leave = _.sample(contractMock.contractLeaves.values);

        beforeEach(function () {
          $scope.model = {
            leave: [{ leave_type: leave.leave_type }]
          };
          controller.fetchRevisionDetails(contractMock.contractRevisionData.values[0])
            .then(function (_result_) {
              result = _result_;
            });
          $scope.$digest();
          $httpBackend.flush();
        });

        it('maps Absence Types and Job Contract data correctly', function () {
          expect(result.leave[0].leave_amount).toEqual(leave.leave_amount);
        });
      });
    });

    describe('Update contract based on new end date', function () {
      describe('When end date is past', function () {
        beforeEach(function () {
          var date = moment().day(-7); // Seven days ago
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as past', function () {
          expect($scope.$parent.contract.is_current).toBe('0');
        });
      });

      describe('When end date is today', function () {
        beforeEach(function () {
          var date = moment();
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as current', function () {
          expect($scope.$parent.contract.is_current).toBe('1');
        });
      });

      describe('When date is future', function () {
        beforeEach(function () {
          var date = moment().day(7); // Seven days from now
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as current', function () {
          expect($scope.$parent.contract.is_current).toBe('1');
        });
      });

      describe('When end date is empty', function () {
        beforeEach(function () {
          var date = ''; //  end date empty
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as current', function () {
          expect($scope.$parent.contract.is_current).toBe('1');
        });
      });

      describe('after updating the contract', function () {
        beforeEach(function () {
          createModalSpy();
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('calls utility service to display entitlement update page', function () {
          expect(utilsService.updateEntitlements).toHaveBeenCalledWith($scope.contract.contact_id);
        });
      });
    });

    function makeController () {
      $scope = $rootScope.$new();

      $scope.contract = {
        id: '1',
        contact_id: '04',
        deleted: '0',
        is_current: '1',
        is_primary: '1'
      };
      $scope.details = {};
      $scope.pay = {};
      $scope.hour = {};
      $scope.health = {};
      $scope.leave = [];
      $scope.$parent.contract = {
        id: '1',
        contact_id: '84',
        deleted: '0',
        is_current: '0',
        is_primary: '1'
      };
      $scope.pension = {};
      $scope.$parent.contractCurrent = [];
      $scope.$parent.contractPast = [];

      controller = $controller('ContractController', {
        $scope: $scope,
        $modal: $modal
      });
    }

    function createModalSpy (newEndDate) {
      spyOn($modal, 'open').and.callFake(function () {
        return {
          result: $q.resolve({
            'files': false,
            'health': {},
            'contract': {
              'id': '48',
              'contact_id': '84',
              'is_primary': '1',
              'deleted': '0'
            },
            'pay': {},
            'hour': {},
            'leave': [],
            'details': {
              'period_end_date': newEndDate
            },
            'pension': {}
          })
        };
      });
    }

    /**
     * Mocks back-end API calls
     */
    function mockBackendCalls () {
      $httpBackend.whenGET(/action=getfulldetails&entity=HRJobContract/).respond(contractMock.contractEntity);
      $httpBackend.whenGET(/action=getcurrentcontract&entity=HRJobContract/).respond({ 'values': [] });
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(contractMock.contract);
      $httpBackend.whenGET(/action=getsingle&entity=HRJobContractRevision/).respond({ 'values': [] });
      $httpBackend.whenGET(/action=get&entity=HRJobDetails/).respond({ 'values': contractMock.contractEntity.details });
      $httpBackend.whenGET(/action=get&entity=HRJobHour/).respond(contractMock.contractHour);
      $httpBackend.whenGET(/action=get&entity=HRJobHealth/).respond({ 'values': [] });
      $httpBackend.whenGET(/action=get&entity=HRJobPay/).respond({ 'values': [] });
      $httpBackend.whenGET(/action=get&entity=HRJobPension/).respond({ 'values': [] });
      $httpBackend.whenGET(/action=get&entity=HRJobLeave/).respond(contractMock.contractLeaves);
      $httpBackend.whenGET(/hrjobcontract\/file\/list/).respond({ 'values': [] });
      // @NOTE This is a temporary solution until we can import mocks
      // from other extensions such as Leave and Absence extension
      $httpBackend.whenGET(/action=get&entity=AbsenceType/).respond({ 'values':
        _.map(contractMock.contractEntity.leave, function (leave, index) {
          return { id: leave.leave_type, calculation_unit: _.sample(calculationUnitsMock).value };
        })
      });
      $httpBackend.whenGET(/action=get&entity=OptionValue/).respond({ 'values': calculationUnitsMock });
    }
  });
});
