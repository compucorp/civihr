define([
  'common/angularMocks',
  'job-contract/app'
], function(Mock) {
  'use strict';

  describe('ContractLeaveService', function() {
    var $httpBackend, responseData, $rootScope, ContractLeaveService;

    beforeEach(module('hrjc'));

    beforeEach(inject(function (_ContractLeaveService_, _$httpBackend_, _$rootScope_) {
      ContractLeaveService = _ContractLeaveService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      responseData = {
        "is_error": 0,
        "undefined_fields": [
          "jobcontract_revision_id"
        ],
        "version": 3,
        "count": 8,
        "values": [
          {
            "id": "375",
            "leave_type": "1",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          },
          {
            "id": "376",
            "leave_type": "2",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          },
          {
            "id": "377",
            "leave_type": "3",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          },
          {
            "id": "378",
            "leave_type": "4",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          },
          {
            "id": "379",
            "leave_type": "5",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          },
          {
            "id": "380",
            "leave_type": "6",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          },
          {
            "id": "381",
            "leave_type": "7",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          },
          {
            "id": "382",
            "leave_type": "8",
            "leave_amount": "0",
            "add_public_holidays": "0",
            "jobcontract_revision_id": "99"
          }
        ],
        "xdebug": {
          "peakMemory": 57659904,
          "memory": 57490280,
          "timeIndex": 1.50788617134
        }
      };

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(responseData);
      $httpBackend.whenGET(/action=get&entity=HRJobLeave/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function() {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('when calling getOne()', function () {
      it('makes http call', function () {
        $httpBackend.expectGET(/action=get&entity=HRJobContract/);
      });

      it("defines getOne() function", function () {
        expect(ContractLeaveService.getOne).toBeDefined();
      });

      it('calls getOne fuction and return expected values', function () {
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
