define([
  'common/angularMocks',
  'job-contract/app'
], function(Mock) {
  'use strict';

  describe('ContractHealthService', function() {
    var $httpBackend, responseData, $rootScope, ContractHealthService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractHealthService_, _$httpBackend_, _$rootScope_) {
      ContractHealthService = _ContractHealthService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      responseData = {
        "is_error": 0,
        "undefined_fields": [
          "jobcontract_revision_id"
        ],
        "version": 3,
        "count": 1,
        "id": 47,
        "values": [
          {
            "id": "47",
            "jobcontract_revision_id": "68"
          }
        ],
        "xdebug": {
          "peakMemory": 57678792,
          "memory": 57497896,
          "timeIndex": 1.66617894173
        }
      };

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(responseData);
      $httpBackend.whenGET(/action=get&entity=HRJobHealth/).respond({});
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

      it('calls getOne fuction and return expected values', function () {
        ContractHealthService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result.id).toEqual("47");
          expect(result.jobcontract_revision_id).toEqual(responseData.values[0].jobcontract_revision_id);
        });
      });
    });
  });
});
