/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/data/contract.data',
  'mocks/data/insurance-plan-types.data',
  'job-contract/modules/job-contract.module'
], function (_, moment, MockContract, InsurancePlanTypesMock) {
  'use strict';

  describe('ModalContractController', function () {
    var $rootScope, $controller, $scope, $q, $httpBackend, $uibModalInstanceMock,
      $uibModalMock, contractDetailsService, contractHealthService,
      contractHourService, contractLeaveService, contractPayService,
      contractPensionService, contractService;

    beforeEach(module('job-contract'));

    beforeEach(module(function ($provide) {
      $provide.factory('contractHealthService', function () {
        return {
          getOptions: function () {},
          getFields: jasmine.createSpy(),
          save: jasmine.createSpy()
        };
      });
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$httpBackend_, _$q_,
      _contractDetailsService_, _contractHealthService_, _contractHourService_,
      _contractLeaveService_, _contractPayService_, _contractPensionService_,
      _contractService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      contractDetailsService = _contractDetailsService_;
      contractHealthService = _contractHealthService_;
      contractHourService = _contractHourService_;
      contractLeaveService = _contractLeaveService_;
      contractPayService = _contractPayService_;
      contractPensionService = _contractPensionService_;
      contractService = _contractService_;
      $q = _$q_;
    }));

    beforeEach(function () {
      $httpBackend.whenGET(/action=validatedates&entity=HRJobDetails/).respond({
        success: true
      });
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenPOST(/action=create&entity/).respond({});
      $httpBackend.whenPOST(/action=replace&entity/).respond({});
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
      mockUIBModal('edit');
      contractHealthServiceSpy();
      makeController();
      createContractDetailsServiceSpy(true);
      addSpiesToServicesSaveMethod();
    });

    describe('init()', function () {
      beforeEach(function () {
        $rootScope.$digest();
      });

      var result = {
        Family: 'Family',
        Individual: 'Individual'
      };

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

    describe('save()', function () {
      describe('makes call to appropriate service and function', function () {
        beforeEach(function () {
          $scope.save();
          $rootScope.$digest();
        });

        it('calls to validate the dates form contractDetailsService', function () {
          expect(contractDetailsService.validateDates).toHaveBeenCalled();
        });

        it('gets confirmation fron user to save contract data', function () {
          expect($uibModalMock.open).toHaveBeenCalled();

          $uibModalMock.open().result.then(function (data) {
            expect(data).toBe('edit');
          });
        });
      });

      describe('When period_end_date is null', function () {
        beforeEach(function () {
          $scope.entity.details.period_end_date = null;
          $scope.save();
          $rootScope.$digest();
        });

        it("sets contract period_end_date to '' ", function () {
          expect($scope.entity.details.period_end_date).toBe('');
        });
      });

      describe('When period_end_date is not falsy', function () {
        var mockDate = '02-03-2017';

        beforeEach(function () {
          $scope.entity.details.period_end_date = mockDate;
          $scope.save();
          $rootScope.$digest();
        });

        it("sets contract period_end_date to '' ", function () {
          expect($scope.entity.details.period_end_date).toBe(mockDate);
        });
      });

      describe('when the contract dates are not valid', function () {
        beforeEach(function () {
          spyOn(CRM, 'alert');

          contractDetailsService.validateDates.and.returnValue($q.resolve({
            success: false,
            message: 'Invalid Date'
          }));

          $scope.save();
          $scope.$digest();
        });

        it('displays an error message', function () {
          expect(CRM.alert).toHaveBeenCalledWith('Invalid Date', 'Error', 'error');
        });
      });

      describe('when the contract dates are valid', function () {
        beforeEach(function () {
          $scope.save();
          $scope.$digest();
        });

        it('saves the contract', function () {
          expect(contractService.save).toHaveBeenCalledWith($scope.entity.contract);
        });

        it('saves the contract details', function () {
          expect(contractDetailsService.save).toHaveBeenCalledWith($scope.entity.details);
        });

        it('saves the contract hours', function () {
          expect(contractHourService.save).toHaveBeenCalledWith($scope.entity.hour);
        });

        it('saves the payment information', function () {
          expect(contractPayService.save).toHaveBeenCalledWith($scope.entity.pay);
        });

        it('saves the leave entitlements', function () {
          expect(contractLeaveService.save).toHaveBeenCalledWith($scope.entity.leave);
        });

        it('saves the health insurance information', function () {
          expect(contractHealthService.save).toHaveBeenCalledWith($scope.entity.health);
        });

        it('saves the pension information', function () {
          expect(contractPensionService.save).toHaveBeenCalledWith($scope.entity.pension);
        });

        it('closes the modal and returns a result', function () {
          expect($uibModalInstanceMock.close).toHaveBeenCalledWith(jasmine.objectContaining({
            contract: $scope.entity.contract
          }));
        });
      });
    });

    describe('when closing the modal after confirming the entitlements change', function () {
      beforeEach(function () {
        $scope.entity.details.period_end_date = new Date();

        $scope.save();
        $scope.$digest();
      });

      it('returns a "have entitlement fields changed" variable set to true', function () {
        expect($uibModalInstanceMock.close).toHaveBeenCalledWith(jasmine.objectContaining({
          haveEntitlementFieldsChanged: true
        }));
      });
    });

    describe('when closing the modal and entitlement fields did not change', function () {
      beforeEach(function () {
        $scope.save();
        $scope.$digest();
      });

      it('returns a "have entitlement fields changed" variable set to false', function () {
        expect($uibModalInstanceMock.close).toHaveBeenCalledWith(jasmine.objectContaining({
          haveEntitlementFieldsChanged: false
        }));
      });
    });

    /**
     * Add spies and return values to the save methods of the different services
     * used by the job contract.
     */
    function addSpiesToServicesSaveMethod () {
      $scope.entity.pay.annual_benefits = [{}];
      $scope.entity.pay.annual_deductions = [{}];

      spyOn(contractDetailsService, 'save').and.returnValue($q.resolve($scope.entity.details));
      spyOn(contractHourService, 'save').and.returnValue($q.resolve($scope.entity.hour));
      spyOn(contractLeaveService, 'save').and.returnValue($q.resolve($scope.entity.leave));
      spyOn(contractPayService, 'save').and.returnValue($q.resolve($scope.entity.pay));
      spyOn(contractPensionService, 'save').and.returnValue($q.resolve($scope.entity.pension));
      spyOn(contractService, 'save').and.returnValue($q.resolve($scope.entity.contract));
    }

    /**
     * Initializes the modal contract controller.
     */
    function makeController () {
      $scope = $rootScope.$new();
      $controller('ModalContractController', {
        $scope: $scope,
        $rootScope: $rootScope,
        $uibModal: $uibModalMock,
        $uibModalInstance: $uibModalInstanceMock,
        contractDetailsService: contractDetailsService,
        action: 'edit',
        entity: MockContract.contractEntity,
        content: {
          allowSave: true
        },
        files: {},
        utils: {
          contractListLen: 1
        }
      });
    }

    /**
     * Mocks the modal instance returned by $modal.open.
     */
    function mockUIBModalInstance () {
      $uibModalInstanceMock = {
        opened: {
          then: jasmine.createSpy()
        },
        close: jasmine.createSpy('close')
      };
    }

    /**
     * Mocks the $modal.open method.
     */
    function mockUIBModal (mode) {
      $uibModalMock = {
        open: jasmine.createSpy('open').and.callFake(function () {
          return {
            result: {
              then: function (callback) {
                callback(mode);
              }
            }
          };
        })
      };
    }

    /**
     * Spies on the validateDates method of the contractDetailsService and resolves
     * the validation to the provided status.
     *
     * @param {Boolean} status determines if the validation was successful or not.
     */
    function createContractDetailsServiceSpy (status) {
      spyOn(contractDetailsService, 'validateDates').and.callFake(function () {
        var deferred = $q.defer();
        deferred.resolve({
          success: status
        });

        return deferred.promise;
      });
    }

    /**
     * Mocks the response of the getOptions method from contractHealthService.
     */
    function contractHealthServiceSpy () {
      spyOn(contractHealthService, 'getOptions').and.callFake(function () {
        return $q.resolve(InsurancePlanTypesMock.values);
      });
    }
  });
});
