/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'mocks/data/contract.data',
  'job-contract/job-contract.module'
], function (angular, _, MockContract) {
  'use strict';

  describe('contractLeaveService', function () {
    var $httpBackend, $rootScope, AbsenceType, contractLeaveService;
    var calculationUnitsMock = [{ value: 1, name: 'days' }, { value: 2, name: 'hours' }];

    beforeEach(module('job-contract'));

    beforeEach(inject(function (_$httpBackend_, _$rootScope_, _AbsenceType_, _contractLeaveService_) {
      AbsenceType = _AbsenceType_;
      contractLeaveService = _contractLeaveService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      mockBackendCalls();
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      var contractLeaves;

      beforeEach(function () {
        contractLeaves = angular.copy(MockContract.contractLeaves.values)
          .map(function (contract) {
            contract.add_public_holidays = contract.add_public_holidays === '1';

            return contract;
          });
      });

      it('calls getOne() and returns expected contract leaves', function () {
        contractLeaveService.getOne({ jobcontract_revision_id: 68 }).then(function (result) {
          expect(result).toEqual(contractLeaves);
        });
      });
    });

    describe('model()', function () {
      var result;
      var leaveMocks = _.cloneDeep(MockContract.contractLeaves.values);
      var randomLeaveIndex = _.random(0, leaveMocks.length - 1);
      var leaveMock = leaveMocks[randomLeaveIndex];

      beforeEach(function () {
        spyOn(AbsenceType, 'all').and.callThrough();
        contractLeaveService.model([{ name: 'leave_type' }]).then(function (_result_) {
          result = _result_;
        });
        $rootScope.$digest();
      });

      it('fetches Absence Types', function () {
        expect(AbsenceType.all).toHaveBeenCalledWith();
      });

      it('sets entitlement to 0 for Absence Types created after the Job Contract was saved last time', function () {
        expect(result[randomLeaveIndex].leave_amount).toBe(0);
      });

      it('sets default "Add Public Holidays" flag', function () {
        expect(result[randomLeaveIndex].add_public_holidays).toBe(!!+leaveMock.add_public_holiday_to_entitlement);
      });
    });

    /**
     * Mocks back-end API calls
     */
    function mockBackendCalls () {
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(200);
      $httpBackend.whenGET(/action=get&entity=HRJobLeave/).respond(MockContract.contractLeaves);
      $httpBackend.whenGET(/action=getfields&entity=HRJobLeave/).respond({ values: [] });
      $httpBackend.whenGET(/views.*/).respond({});
      // @NOTE This is a temporary solution until we can import mocks
      // from other extensions such as Leave and Absence extension
      $httpBackend.whenGET(/action=get&entity=AbsenceType/).respond({ 'values':
        _.map(MockContract.contractEntity.leave, function (leave) {
          return _.assign(leave, { calculation_unit: _.sample(calculationUnitsMock).value });
        })
      });
      $httpBackend.whenGET(/action=get&entity=OptionValue/).respond({ 'values': calculationUnitsMock });
    }
  });
});
