/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'mocks/data/contract.data',
  'mocks/data/insurance-plan-types.data',
  'mocks/data/option-value.data',
  'job-contract/job-contract.module'
], function (angular, _, moment, MockContract, InsurancePlanTypesMock, OptionValueMock) {
  'use strict';

  describe('ModalContractController', function () {
    var $rootScope, $controller, $scope, $q, $httpBackend, $uibModalInstanceMock,
      $uibModalMock, crmAngService, contractDetailsService, contractHealthService,
      contractHourService, contractLeaveService, contractPayService,
      contractPensionService, contractRevisionService, contractService, settings,
      utilsService, locationUrl, popupLists, payScaleGradeUrl,
      annualBenefitUrl, annualDeductionUrl;
    var today = moment().format('YYYY-MM-DD');

    beforeEach(module('job-contract', 'job-contract.templates'));

    beforeEach(module(function ($provide) {
      contractRevisionService = {
        validateEffectiveDate: jasmine.createSpy('validateEffectiveDate'),
        save: jasmine.createSpy('save')
      };

      $provide.factory('contractHealthService', function () {
        return {
          getOptions: function () {},
          getFields: jasmine.createSpy(),
          save: jasmine.createSpy()
        };
      });

      $provide.value('contractRevisionService', contractRevisionService);
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$httpBackend_, _$q_,
      _contractDetailsService_, _contractHealthService_, _contractHourService_,
      _contractLeaveService_, _contractPayService_, _contractPensionService_,
      _contractService_, _crmAngService_, _settings_, _utilsService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      $q = _$q_;
      contractDetailsService = _contractDetailsService_;
      contractHealthService = _contractHealthService_;
      contractHourService = _contractHourService_;
      contractLeaveService = _contractLeaveService_;
      contractPayService = _contractPayService_;
      contractPensionService = _contractPensionService_;
      contractService = _contractService_;
      crmAngService = _crmAngService_;
      settings = _settings_;
      utilsService = _utilsService_;
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
      contractRevisionService.validateEffectiveDate
        .and.returnValue($q.resolve({ success: true }));

      mockUIBModalInstance();
      mockUIBModal({
        ModalDialogController: 'edit'
      });
      contractHealthServiceSpy();
      utilsServiceSpy();
      makeController();
      createContractDetailsServiceSpy(true);
      addSpiesToServicesSaveMethod();
      spyOn(contractService, 'saveRevision').and.callThrough();
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

      it('calls getOptionValues() from utilsService', function () {
        expect(utilsService.getOptionValues).toHaveBeenCalled();
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
          expect($uibModalMock.open).toHaveBeenCalledWith(jasmine.objectContaining({
            controller: 'ModalDialogController'
          }));
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

      it('closes the modal and tells it that the entitlement fields have changed', function () {
        expect($uibModalInstanceMock.close).toHaveBeenCalledWith(jasmine.objectContaining({
          haveEntitlementFieldsChanged: true
        }));
      });
    });

    describe('when closing the modal after removing the period end date', function () {
      beforeEach(function () {
        MockContract.contractEntity.details.period_end_date = moment().format('YYYY-MM-DD');
        makeController();

        $scope.entity.details.period_end_date = '';

        $scope.save();
        $scope.$digest();
      });

      it('closes the modal and tells it that the entitlement fields have changed', function () {
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

      it('closes the modal and tells it that the entitlement fields have not changed', function () {
        expect($uibModalInstanceMock.close).toHaveBeenCalledWith(jasmine.objectContaining({
          haveEntitlementFieldsChanged: false
        }));
      });
    });

    describe('when closing the modal and period end date was empty but did not change', function () {
      beforeEach(function () {
        MockContract.contractEntity.details.period_end_date = '';
        makeController();
        $scope.save();
        $scope.$digest();
      });

      it('closes the modal and tells it that the entitlement fields have not changed', function () {
        expect($uibModalInstanceMock.close).toHaveBeenCalledWith(jasmine.objectContaining({
          haveEntitlementFieldsChanged: false
        }));
      });
    });

    describe('cancel', function () {
      describe('when the modal is on view mode', function () {
        beforeEach(function () {
          makeController({ action: 'view' });
          $scope.cancel();
        });

        it('closes the modal', function () {
          expect($uibModalInstanceMock.dismiss).toHaveBeenCalledWith('cancel');
        });
      });

      describe('when the user has not changed the contract', function () {
        beforeEach(function () {
          makeController();
          $scope.cancel();
        });

        it('closes the modal', function () {
          expect($uibModalInstanceMock.dismiss).toHaveBeenCalledWith('cancel');
        });
      });

      describe('when the user changes the contract', function () {
        var modalOptions;

        beforeEach(function () {
          makeController();

          $scope.entity.details.period_end_date = new Date();

          $scope.cancel();

          modalOptions = $uibModalMock.open.calls.mostRecent().args[0];
        });

        it('warns the user before confirming the modal close', function () {
          expect($uibModalMock.open).toHaveBeenCalledWith(jasmine.objectContaining({
            controller: 'ModalDialogController'
          }));
          expect(modalOptions.resolve.content()).toEqual({
            copyCancel: 'No',
            title: 'Alert',
            msg: 'Are you sure you want to cancel? Changes will be lost!'
          });
        });
      });
    });

    describe('Saving the change revision', function () {
      var changeReasonId = 123;

      beforeEach(function () {
        mockUIBModal({
          ModalDialogController: 'change',
          ModalChangeReasonController: {
            reasonId: changeReasonId,
            date: today
          }
        });
        makeController({ action: 'change' });
      });

      describe('basic tests', function () {
        beforeEach(function () {
          $scope.save();
          $scope.$digest();
        });

        it('opens the revision modal', function () {
          expect($uibModalMock.open).toHaveBeenCalledWith(jasmine.objectContaining({
            controller: 'ModalChangeReasonController'
          }));
        });

        it('validates the contract revision effective date', function () {
          expect(contractRevisionService.validateEffectiveDate).toHaveBeenCalledWith({
            contact_id: settings.contactId,
            effective_date: today
          });
        });
      });

      describe('when the contract dates are not valid', function () {
        beforeEach(function () {
          spyOn(CRM, 'alert');
          contractRevisionService.validateEffectiveDate
            .and.returnValue($q.resolve({ success: false, message: 'Message' }));
          $scope.save();
          $scope.$digest();
        });

        it('displays an error message', function () {
          expect(CRM.alert).toHaveBeenCalledWith('Message', 'Error', 'error');
        });
      });

      describe('when the contract dates are valid', function () {
        describe('when changes were made to the contract', function () {
          var expectedEntity, revisionId;

          beforeEach(function () {
            revisionId = $scope.entity.details.jobcontract_revision_id;

            $scope.entity.details.title = 'new job title';
            $scope.entity.hour.hours_amount += 10;
            $scope.entity.pay.is_paid = !$scope.entity.pay.is_paid;
            $scope.entity.leave = [{ id: 1, leave_amount: 12 }];
            $scope.entity.health.description = 'new health description';
            $scope.entity.pension.er_contrib_pct += 10;
            $scope.entity.details.funding_notes = 'new funding note';

            expectedEntity = angular.copy($scope.entity);

            utilsService.prepareEntityIds(expectedEntity.details, expectedEntity.contract.id);
            utilsService.prepareEntityIds(expectedEntity.hour, expectedEntity.contract.id, revisionId);
            utilsService.prepareEntityIds(expectedEntity.pay, expectedEntity.contract.id, revisionId);
            utilsService.prepareEntityIds(expectedEntity.leave, expectedEntity.contract.id, revisionId);
            utilsService.prepareEntityIds(expectedEntity.health, expectedEntity.contract.id, revisionId);
            utilsService.prepareEntityIds(expectedEntity.pension, expectedEntity.contract.id, revisionId);

            $scope.save();
            $scope.$digest();
          });

          it('calls all the corresponding services', function () {
            expect(contractDetailsService.save).toHaveBeenCalledWith(expectedEntity.details);
            expect(contractHourService.save).toHaveBeenCalledWith(expectedEntity.hour);
            expect(contractPayService.save).toHaveBeenCalledWith(expectedEntity.pay);
            expect(contractLeaveService.save).toHaveBeenCalledWith(expectedEntity.leave);
            expect(contractHealthService.save).toHaveBeenCalledWith(expectedEntity.health);
            expect(contractPensionService.save).toHaveBeenCalledWith(expectedEntity.pension);
          });

          it('saves the contract revision', function () {
            expect(contractService.saveRevision).toHaveBeenCalledWith({
              id: revisionId,
              change_reason: changeReasonId,
              effective_date: today
            });
          });
        });

        describe('when no changes were made to the contract', function () {
          beforeEach(function () {
            $scope.save();
            $scope.$digest();
          });

          it('saves the contract details', function () {
            expect(contractDetailsService.save).toHaveBeenCalled();
          });

          it('does not save any other contract data', function () {
            expect(contractHourService.save).not.toHaveBeenCalled();
            expect(contractPayService.save).not.toHaveBeenCalled();
            expect(contractLeaveService.save).not.toHaveBeenCalled();
            expect(contractHealthService.save).not.toHaveBeenCalled();
            expect(contractPensionService.save).not.toHaveBeenCalled();
          });

          it('saves the contract revision', function () {
            expect(contractService.saveRevision).toHaveBeenCalled();
          });
        });
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
            }
          };
        });
        $scope.openAnnualDeductionOptionsEditor();
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(annualDeductionUrl);
      });
    });

    describe('when user clicks on the "Provider options" wrench icon', function () {
      var providersPopupLists;

      beforeEach(function () {
        providersPopupLists = [
          {
            'popupFormUrl': '/civicrm/admin/options/hrjc_health_insurance_provider?reset=1',
            'popupFormField': 'hrjc_health_insurance_provider'
          }
        ];
        spyOn(crmAngService, 'loadForm').and.callFake(function () {
          return {
            on: function (event, callback) {
            }
          };
        });
        _.each(providersPopupLists, function (popupList) {
          $scope.openProvidersEditor(popupList.popupFormField);
        });
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(providersPopupLists[0].popupFormUrl);
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
    function makeController (options) {
      $scope = $rootScope.$new();
      $controller('ModalContractController', _.defaults(options || {}, {
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
      }));
    }

    /**
     * Mocks the modal instance returned by $modal.open.
     */
    function mockUIBModalInstance () {
      $uibModalInstanceMock = {
        opened: {
          then: jasmine.createSpy()
        },
        close: jasmine.createSpy('close'),
        dismiss: jasmine.createSpy('dismiss')
      };
    }

    /**
     * Mocks the $modal.open method and their results
     *
     * @param {Object} controllersAndResults - a map of the controller's name
     * and the result will it return when the modal is closed.
     */
    function mockUIBModal (controllersAndResults) {
      $uibModalMock = {
        open: jasmine.createSpy('open').and.callFake(function (options) {
          return {
            result: {
              then: function (callback) {
                var result = controllersAndResults[options.controller];

                callback(result);
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

    /**
     * Mocks the response of the getOptionValues from utilsService.
     */
    function utilsServiceSpy () {
      spyOn(utilsService, 'getOptionValues').and.callFake(function () {
        return $q.resolve(OptionValueMock);
      });
    }
  });
});
