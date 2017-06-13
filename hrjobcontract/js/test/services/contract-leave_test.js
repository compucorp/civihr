define([
  'mocks/data/contract',
  'job-contract/app'
], function (MockContract) {
  'use strict';

  describe('ContractLeaveService', function() {
    var $httpBackend, $rootScope, ContractLeaveService;

    beforeEach(module('hrjc'));

    beforeEach(inject(function (_ContractLeaveService_, _$httpBackend_, _$rootScope_) {
      ContractLeaveService = _ContractLeaveService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(MockContract.contractLeaves);
      $httpBackend.whenGET(/action=get&entity=HRJobLeave/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it("defines getOne() function", function () {
        expect(ContractLeaveService.getOne).toBeDefined();
      });

      it('calls getOne() and returns expected leave types ids', function () {
        ContractLeaveService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result[0].leave_type).toBe('1');
          expect(result[1].leave_type).toBe('2');
          expect(result[2].leave_type).toBe('3');
          expect(result[3].leave_type).toBe('4');
          expect(result[4].leave_type).toBe('5');
          expect(result[5].leave_type).toBe('6');
          expect(result[6].leave_type).toBe('7');
          expect(result[7].leave_type).toBe('8');
        });
      });
    });
  });
});
