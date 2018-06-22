/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/insurance-plan-types.data',
  'job-contract/modules/job-contract.module'
], function (_, InsurancePlanTypesMock) {
  'use strict';

  describe('ModalContractNewController', function () {
    var $rootScope, $controller, $scope, $q, $httpBackend, $uibModalInstanceMock, crmAngService,
      contractHealthService, locationUrl, popupLists, payScaleGradeUrl,
      annualBenefitUrl, annualDeductionUrl;

    beforeEach(module('job-contract'));

    beforeEach(module(function ($provide) {
      $provide.factory('contractHealthService', function () {
        return {
          getOptions: function () {}
        };
      });
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$httpBackend_, _$q_, _crmAngService_,
      _contractDetailsService_, _contractHealthService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      crmAngService = _crmAngService_;
      contractHealthService = _contractHealthService_;
      $q = _$q_;
    }));

    beforeEach(function () {
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRHoursLocation/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRPayScale/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobDetails/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobHour/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobPay/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobLeave/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobHealth/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobPension/).respond({});
      $httpBackend.whenGET(/action=getoptions&entity=HRJobHealth/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    });

    beforeEach(function () {
      var health = {};

      $rootScope.$digest();
      health.plan_type = {};
      health.plan_type_life_insurance = {};
      $rootScope.options = {
        health: health
      };
    });

    beforeEach(function () {
      mockUIBModalInstance();
      contractHealthServiceSpy();
      makeController();
    });

    describe('init()', function () {
      beforeEach(function () {
        $rootScope.$digest();
      });

      var result = {
        Family: 'Family',
        Individual: 'Individual'
      };

      it('sets the contract property as not primary', function () {
        expect($scope.entity.contract).toEqual({ is_primary: 0 });
      });

      it('calls getOptions() form contractHealthService', function () {
        expect(contractHealthService.getOptions).toHaveBeenCalled();
      });

      it('fetches health insurance plan types', function () {
        expect($rootScope.options.health.plan_type).toEqual(result);
      });

      it('fetches life insurance plan types', function () {
        expect($rootScope.options.health.plan_type_life_insurance).toEqual(result);
      });
    });

    describe('when user clicks on the "HRJobContract options" wrench icon', function () {
      popupLists = [
        {
          'popupFormUrl': '/civicrm/admin/options/hrjc_contract_type?reset=1',
          'popupFormField': 'hrjobcontract_details_contract_type'
        },
        {
          'popupFormUrl': '/civicrm/admin/options/hrjc_location?reset=1',
          'popupFormField': 'hrjobcontract_details_location'
        },
        {
          'popupFormUrl': '/civicrm/admin/options/hrjc_contract_end_reason?reset=1',
          'popupFormField': 'hrjobcontract_details_end_reason'
        },
        {
          'popupFormUrl': '/civicrm/admin/options/hrjc_insurance_plantype?reset=1',
          'popupFormField': 'hrjobcontract_health_health_plan_type'
        }
      ];

      beforeEach(function () {
        spyOn(crmAngService, 'loadForm').and.callFake(function () {
          return {
            on: function (event, callback) {
              if (event === 'crmUnload') {
                callback();
              }
            }
          };
        });
        _.each(popupLists, function (popupList) {
          $scope.openOptionsEditor(popupList.popupFormUrl, popupList.popupFormField);
        });
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(popupLists[0].popupFormUrl);
        expect(crmAngService.loadForm).toHaveBeenCalledWith(popupLists[1].popupFormUrl);
        expect(crmAngService.loadForm).toHaveBeenCalledWith(popupLists[2].popupFormUrl);
        expect(crmAngService.loadForm).toHaveBeenCalledWith(popupLists[3].popupFormUrl);
      });
    });

    describe('when user clicks on the "hours location" wrench icon', function () {
      locationUrl = '/civicrm/standard_full_time_hours?reset=1';

      beforeEach(function () {
        spyOn(crmAngService, 'loadForm').and.callFake(function () {
          return {
            on: function (event, callback) {
              if (event === 'crmUnload') {
                callback();
              }
            }
          };
        });
        $scope.openHoursLocationOptionsEditor();
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(locationUrl);
      });
    });

    describe('when user clicks on the "pay scale/ grade" wrench icon', function () {
      payScaleGradeUrl = '/civicrm/pay_scale?reset=1';

      beforeEach(function () {
        spyOn(crmAngService, 'loadForm').and.callFake(function () {
          return {
            on: function (event, callback) {
              if (event === 'crmUnload') {
                callback();
              }
            }
          };
        });
        $scope.openPayScaleGradeOptionsEditor();
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(payScaleGradeUrl);
      });
    });

    describe('when user clicks on the "annual benefit" wrench icon', function () {
      annualBenefitUrl = '/civicrm/admin/options/hrjc_benefit_name?reset=1';

      beforeEach(function () {
        spyOn(crmAngService, 'loadForm').and.callFake(function () {
          return {
            on: function (event, callback) {
              if (event === 'crmUnload') {
                callback();
              }
            }
          };
        });
        $scope.openAnnualBenefitOptionsEditor();
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(annualBenefitUrl);
      });
    });

    describe('when user clicks on the "annual deduction" wrench icon', function () {
      annualDeductionUrl = '/civicrm/admin/options/hrjc_deduction_name?reset=1';

      beforeEach(function () {
        spyOn(crmAngService, 'loadForm').and.callFake(function () {
          return {
            on: function (event, callback) {
              if (event === 'crmUnload') {
                callback();
              }
            }
          };
        });
        $scope.openAnnualDeductionOptionsEditor();
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(annualDeductionUrl);
      });
    });

    function makeController () {
      $scope = $rootScope.$new();
      $controller('ModalContractNewController', {
        $scope: $scope,
        $rootScope: $rootScope,
        model: {},
        $uibModalInstance: $uibModalInstanceMock,
        utils: {
          contractListLen: 1
        }
      });
    }

    function mockUIBModalInstance () {
      $uibModalInstanceMock = {
        opened: {
          then: jasmine.createSpy()
        }
      };
    }

    function contractHealthServiceSpy () {
      spyOn(contractHealthService, 'getOptions').and.callFake(function () {
        return $q.resolve(InsurancePlanTypesMock.values);
      });
    }
  });
});
