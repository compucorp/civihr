define([
  'common/angularMocks',
  'job-contract/app'
], function(Mock) {
  'use strict';

  describe('ContractHourService', function () {
    var $httpBackend, responseData, $rootScope, ContractHourService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractHourService_, _$httpBackend_, _$rootScope_) {
      ContractHourService = _ContractHourService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      responseData = {
        "is_error": 0,
        "undefined_fields": [
          "jobcontract_revision_id"
        ],
        "version": 3,
        "count": 1,
        "id": 59,
        "values": [
          {
            "id": "59",
            "location_standard_hours": "1",
            "hours_fte": "0",
            "fte_num": "0",
            "fte_denom": "0",
            "jobcontract_revision_id": "68"
          }
        ],
        "xdebug": {
          "peakMemory": 57675072,
          "memory": 57494600,
          "timeIndex": 1.55046510696
        }
      };

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(responseData);
      $httpBackend.whenGET(/action=get&entity=HRJobHour/).respond({});
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
        expect(ContractHourService.getOne).toBeDefined();
      });

      it('calls getOne fuction and return expected values', function () {
        ContractHourService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result.location_standard_hours).toEqual(responseData.values[0].location_standard_hours);
        });
      });
    });
  });
});
