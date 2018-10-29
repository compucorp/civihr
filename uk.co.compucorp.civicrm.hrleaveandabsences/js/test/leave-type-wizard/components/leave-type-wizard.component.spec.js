/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'leave-absences/leave-type-wizard/leave-type-wizard.module'
], function (angular, _) {
  'use strict';

  describe('LeaveTypeWizard', function () {
    var $componentController, $log, $q, $rootScope, $window,
      absenceTypeSaverSpy, AbsenceType, Contact, controller, formSections,
      leaveTypeCategoriesIcons, notificationService, OptionGroup;
    var leaveTypeListPageURL = '/civicrm/admin/leaveandabsences/types?action=browse&reset=1';
    var sampleAvailableColours = ['#FFFFFF', '#000000'];
    var sampleContacts = { list: [{ id: '29', display_name: 'Liza' }] };
    var sampleAbsenceTypeTitle = 'Holiday';
    var sampleAbsenceTypes = [
      { title: 'hOliday' }
    ];
    var sampleFieldValidationExpression = /^\w+ \w+$/;
    var sampleFieldValue = 'Some value';
    var sampleLeaveTypeCategoriesOptionGroupValues = [
      { name: 'leave', label: 'Leave' },
      { name: 'sickness', label: 'Sickness' }
    ];
    var tabsIndexed = {};

    beforeEach(angular.mock.module('leave-type-wizard'));

    beforeEach(module('common.mocks', function ($provide) {
      $provide.value('$window', { location: { href: '' } });
    }));

    beforeEach(inject(function (_$componentController_, _$log_, _$q_,
      _$rootScope_, _$window_, _AbsenceType_, _Contact_, _notificationService_,
      _OptionGroup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      AbsenceType = _AbsenceType_;
      Contact = _Contact_;
      notificationService = _notificationService_;
      OptionGroup = _OptionGroup_;
    }));

    beforeEach(inject(['form-sections', 'leave-type-categories-icons',
      function (_formSections_, _leaveTypeCategoriesIcons_) {
        formSections = _formSections_;
        leaveTypeCategoriesIcons = _leaveTypeCategoriesIcons_;
      }]));

    beforeEach(function () {
      spyOn($log, 'debug').and.callThrough();
      spyOn(AbsenceType, 'getAvailableColours').and.returnValue($q.resolve(sampleAvailableColours));
      spyOn(AbsenceType, 'all').and.returnValue($q.resolve(sampleAbsenceTypes));
      spyOn(Contact, 'all').and.returnValue($q.resolve(sampleContacts));
      spyOn(notificationService, 'error');
      spyOn(OptionGroup, 'valuesOf').and.returnValue($q.resolve(sampleLeaveTypeCategoriesOptionGroupValues));
      spyOn(CRM, 'url').and.returnValue(leaveTypeListPageURL);

      absenceTypeSaverSpy = spyOn(AbsenceType, 'save');
    });

    beforeEach(function () {
      initComponent();
    });

    it('loads the wizard controller', function () {
      expect($log.debug).toHaveBeenCalledWith('Controller: LeaveTypeWizardController');
    });

    it('has form sections', function () {
      expect(controller.sections).toEqual(formSections);
    });

    describe('before init ends', function () {
      beforeEach(function () {
        controller.$onInit();
      });

      it('is loading', function () {
        expect(controller.loading).toBe(true);
      });
    });

    describe('on init', function () {
      var secondSection, secondSectionFirstTab;

      beforeEach(function () {
        secondSection = controller.sections[1];
        secondSectionFirstTab = _.first(secondSection.tabs);
        tabsIndexed = _.chain(controller.sections)
          .flatMap('tabs')
          .keyBy('name')
          .value();

        controller.$onInit();
        $rootScope.$digest();
      });

      it('finishes loading', function () {
        expect(controller.loading).toBe(false);
      });

      it('exports the absolute path to the components folder', function () {
        expect(/leave-type-wizard\/components$/.test(
          controller.componentsPath)).toBe(true);
      });

      it('has the General section active', function () {
        expect(controller.sections[0].active).toBe(true);
      });

      it('has the second section collapsed', function () {
        expect(secondSection.active).toBe(false);
      });

      it('has second section tabs defined', function () {
        expect(secondSection.tabs.length).toBe(4);
        expect(_.sample(secondSection.tabs)).toEqual(jasmine.objectContaining({
          label: jasmine.any(String)
        }));
      });

      it('has fields defined in second section tabs', function () {
        expect(_.sample(_.sample(secondSection.tabs).fields)).toEqual(jasmine.objectContaining({
          name: jasmine.any(String)
        }));
      });

      it('loads available colours', function () {
        expect(AbsenceType.getAvailableColours).toHaveBeenCalledWith();
        expect(controller.availableColours).toEqual(sampleAvailableColours);
      });

      it('loads contacts', function () {
        expect(Contact.all).toHaveBeenCalledWith();
        expect(controller.contacts).toEqual(sampleContacts.list);
      });

      it('indexes fields', function () {
        var firstIndex = Object.keys(controller.fieldsIndexed)[0];

        expect(controller.fieldsIndexed).toEqual(jasmine.any(Object));
        expect(firstIndex).toBe(controller.sections[0].tabs[0].fields[0].name);
        expect(controller.fieldsIndexed[firstIndex]).toBe(controller.sections[0].tabs[0].fields[0]);
      });

      it('sets default values', function () {
        expect(_.every(controller.fieldsIndexed, function (field) {
          return field.value === field.defaultValue;
        }));
      });

      it('loads absence types titles', function () {
        expect(AbsenceType.all).toHaveBeenCalledWith({}, { return: ['title'] });
      });

      it('has leave type categories', function () {
        expect(controller.leaveTypeCategories.map(function (category) {
          return _.omit(category, ['icon']);
        })).toEqual(sampleLeaveTypeCategoriesOptionGroupValues);
        expect(_.map(controller.leaveTypeCategories, 'icon'))
          .toEqual(_.values(leaveTypeCategoriesIcons));
      });

      it('marks the "General" tab as the first and last one because it is the only tab in the section', function () {
        expect(tabsIndexed.general.first).toBe(true);
        expect(tabsIndexed.general.last).toBe(true);
      });

      it('marks the "Basic" tab as the first one in the section', function () {
        expect(tabsIndexed.basic.first).toBe(true);
      });

      it('marks the "Carry Forwards" tab as the last one in the section', function () {
        expect(tabsIndexed['carry-forwards'].last).toBe(true);
      });

      describe('when user clicks the "next section" button', function () {
        beforeEach(function () {
          controller.openNextTab();
        });

        it('collapses the first section', function () {
          expect(controller.sections[0].active).toBe(false);
        });

        it('expands the second section ', function () {
          expect(secondSection.active).toBe(true);
        });

        describe('when user clicks the "previous section" button', function () {
          beforeEach(function () {
            controller.openPreviousTab();
          });

          it('collapses the second section', function () {
            expect(secondSection.active).toBe(false);
          });

          it('expands the first section ', function () {
            expect(controller.sections[0].active).toBe(true);
          });
        });
      });

      describe('when user clicks the second section header', function () {
        beforeEach(function () {
          controller.openSection(1);
        });

        it('collapses the first section', function () {
          expect(controller.sections[0].active).toBe(false);
        });

        it('expands the second section ', function () {
          expect(secondSection.active).toBe(true);
        });

        it('has the first tab selected', function () {
          expect(secondSectionFirstTab.active).toBe(true);
        });

        describe('when user selects the middle tab', function () {
          beforeEach(function () {
            controller.openActiveSectionTab(1);
          });

          it('collapses the first tab', function () {
            expect(secondSectionFirstTab.active).toBe(false);
          });

          it('expands the middle tab', function () {
            expect(secondSection.tabs[1].active).toBe(true);
          });
        });

        describe('when opens the next section tab', function () {
          beforeEach(function () {
            controller.openNextTab();
          });

          it('opens the second tab', function () {
            expect(secondSection.tabs[1].active).toEqual(true);
          });

          describe('when opens the previous section tab', function () {
            beforeEach(function () {
              controller.openPreviousTab();
            });

            it('opens the first tab', function () {
              expect(secondSection.tabs[0].active).toEqual(true);
            });
          });
        });

        describe('when user cancels the form filling', function () {
          beforeEach(function () {
            controller.openSection(0);
            controller.openPreviousTab();
            $rootScope.$digest();
          });

          it('redirects to the leave types list page', function () {
            expect(CRM.url).toHaveBeenCalledWith('civicrm/admin/leaveandabsences/types', {
              action: 'browse',
              reset: 1
            });
            expect($window.location.href).toBe(leaveTypeListPageURL);
          });

          it('shows that the component is "loading"', function () {
            expect(controller.loading).toBe(true);
          });
        });

        describe('when user submits the whole wizard form', function () {
          describe('basic tests', function () {
            beforeEach(function () {
              absenceTypeSaverSpy.and.returnValue($q.resolve());
              fillWizardIn();
              submitWizard();
            });

            it('is loading', function () {
              expect(controller.loading).toBe(true);
            });

            describe('when saving finishes', function () {
              beforeEach(function () {
                $rootScope.$digest();
              });

              it('saves absence type', function () {
                expect(AbsenceType.save).toHaveBeenCalledWith(jasmine.objectContaining({
                  title: jasmine.any(String)
                }));
              });

              it('removes auxiliary fields', function () {
                var params = AbsenceType.save.calls.mostRecent().args[0];

                expect(params.carry_forward_expiration_duration_switch).toBeUndefined();
              });

              it('redirects to the leave types list page', function () {
                expect(CRM.url).toHaveBeenCalledWith('civicrm/admin/leaveandabsences/types', {
                  action: 'browse',
                  reset: 1
                });
                expect($window.location.href).toBe(leaveTypeListPageURL);
              });

              it('still shows that the component is loading', function () {
                expect(controller.loading).toBe(true);
              });
            });
          });

          describe('when default entitlement field is empty', function () {
            beforeEach(function () {
              absenceTypeSaverSpy.and.returnValue($q.resolve());
              fillWizardIn();

              controller.fieldsIndexed.default_entitlement.value = '';

              submitWizard();
              $rootScope.$digest();
            });

            it('sends the default entitlement to "0"', function () {
              expect(AbsenceType.save).toHaveBeenCalledWith(jasmine.objectContaining({
                default_entitlement: '0'
              }));
            });
          });

          describe('when there are errors', function () {
            var error = 'error';

            beforeEach(function () {
              absenceTypeSaverSpy.and.callFake(function () {
                return $q.reject(error);
              });
              fillWizardIn();
              submitWizard();
              $rootScope.$digest();
            });

            it('throws an error notification', function () {
              expect(notificationService.error)
                .toHaveBeenCalledWith('', error);
            });

            it('navigates to the start of the form', function () {
              var firstSection = _.first(controller.sections);

              expect(firstSection.active).toBe(true);
              expect(_.first(firstSection.tabs).active).toBe(true);
            });

            it('finishes loading', function () {
              expect(controller.loading).toBe(false);
            });
          });
        });
      });

      describe('fields watchers', function () {
        describe('on default values', function () {
          it('hides the "Maximum carry forward" field', function () {
            expect(controller.fieldsIndexed.max_number_of_days_to_carry_forward.hidden).toBe(true);
          });

          it('hides the "Carry forward expiry" field', function () {
            expect(controller.fieldsIndexed.max_number_of_days_to_carry_forward.hidden).toBe(true);
          });
        });

        describe('when user changes "Allow carry forward" to "Yes"', function () {
          beforeEach(function () {
            controller.fieldsIndexed.allow_carry_forward.value = true;

            $rootScope.$digest();
          });

          it('shows the "Maximum carry forward" field', function () {
            expect(controller.fieldsIndexed.max_number_of_days_to_carry_forward.hidden).toBe(false);
          });

          it('shows the "Carry forward expiry" field', function () {
            expect(controller.fieldsIndexed.max_number_of_days_to_carry_forward.hidden).toBe(false);
          });

          describe('when user changes "Carry forward expiry" to "Expire after"', function () {
            beforeEach(function () {
              controller.fieldsIndexed.carry_forward_expiration_duration_switch.value = true;

              $rootScope.$digest();
            });

            it('shows the expiration duration field', function () {
              expect(controller.fieldsIndexed.carry_forward_expiration_duration.hidden).toBe(false);
            });
          });
        });

        describe('when user inputs an already used leave type title', function () {
          beforeEach(function () {
            controller.fieldsIndexed.title.value = sampleAbsenceTypeTitle;

            $rootScope.$digest();
          });

          it('shows the error', function () {
            expect(controller.fieldsIndexed.title.error)
              .toBe('This leave type title is already in use');
          });
        });
      });

      describe('validators', function () {
        var sampleField;
        var sampleErrorMessage = 'Invalid format';
        var requiredFieldErrorMessage = 'This field is required';

        beforeEach(function () {
          sampleField = controller.sections[0].tabs[0].fields[0];
          sampleField.validations = [
            {
              rule: sampleFieldValidationExpression,
              required: true,
              message: sampleErrorMessage
            }
          ];
        });

        describe('when user enters a value in a wrong format', function () {
          beforeEach(function () {
            sampleField.value = '???';

            $rootScope.$digest();
          });

          it('sets the error to the field', function () {
            expect(sampleField.error).toBe(sampleErrorMessage);
          });

          describe('when user changes the value to a valid format', function () {
            beforeEach(function () {
              sampleField.value = sampleFieldValue;

              $rootScope.$digest();
            });

            it('removes the error from the field', function () {
              expect(sampleField.error).toBeUndefined();
            });
          });

          describe('when user erases the value', function () {
            beforeEach(function () {
              sampleField.value = '';

              $rootScope.$digest();
            });

            it('sets the error to the field', function () {
              expect(sampleField.error).toBe(requiredFieldErrorMessage);
            });
          });
        });

        describe('when user does not fill in the field and navigates to the next tab', function () {
          beforeEach(function () {
            controller.openNextTab();

            $rootScope.$digest();
          });

          it('sets the error to the missed field', function () {
            expect(sampleField.error).toBe(requiredFieldErrorMessage);
          });

          it('sets the error to the whole tab', function () {
            expect(controller.sections[0].tabs[0].valid).toBe(false);
          });
        });

        describe('when user attempts to submit and there are errors', function () {
          beforeEach(function () {
            controller.openSection(1);
            controller.openActiveSectionTab(controller.sections[1].tabs.length - 1);
            controller.openNextTab();
          });

          it('navigates to the first section and the tab where errors occured', function () {
            expect(controller.sections[0].active).toBe(true);
            expect(controller.sections[0].tabs[0].active).toBe(true);
          });

          it('throws an error notification', function () {
            expect(notificationService.error)
              .toHaveBeenCalledWith('', jasmine.any(String));
          });
        });
      });

      describe('section locking', function () {
        describe('when title is not filled in', function () {
          beforeEach(function () {
            controller.fieldsIndexed.title.value = '';

            $rootScope.$digest();
          });

          it('locks the Settings section', function () {
            expect(controller.sections[1].disabled).toBe(true);
          });

          it('locks the "next section" button in the General section', function () {
            expect(_.first(controller.sections).disableNextSectionButton).toBe(true);
          });

          describe('when title is filled in', function () {
            beforeEach(function () {
              controller.fieldsIndexed.title.value = sampleFieldValue;

              $rootScope.$digest();
            });

            it('unlocks the Settings section', function () {
              expect(controller.sections[1].disabled).toBe(false);
            });

            it('unlocks the "next section" button in the General section', function () {
              expect(_.first(controller.sections).disableNextSectionButton).toBe(false);
            });
          });
        });
      });

      describe('checkIfAccordionHeaderClicked()', function () {
        var mockedEvent = { originalEvent: {} };
        var panelHeaderHTML = '<div class="panel-heading"></div>';

        describe('when user clicks on accordion header', function () {
          beforeEach(function () {
            mockedEvent.originalEvent.path =
              angular.element(panelHeaderHTML)[0];
          });

          it('returns `true`', function () {
            expect(controller.checkIfAccordionHeaderClicked(mockedEvent)).toBe(true);
          });
        });

        describe('when user clicks on element inside accordion header', function () {
          var $element;
          beforeEach(function () {
            $element = angular.element('<button></button>');
            $element.wrap(panelHeaderHTML);

            mockedEvent.originalEvent.path =
              angular.element($element)[0];
          });

          it('returns `true`', function () {
            expect(controller.checkIfAccordionHeaderClicked(mockedEvent)).toBe(true);
          });
        });

        describe('when user clicks outside accordion header', function () {
          beforeEach(function () {
            mockedEvent.originalEvent.path =
              angular.element('<div class="outside-header"></div>')[0];
          });

          it('returns `false`', function () {
            expect(controller.checkIfAccordionHeaderClicked(mockedEvent)).toBe(false);
          });
        });
      });

      describe('when user selects the "Leave" category', function () {
        beforeEach(function () {
          controller.fieldsIndexed.category.value = 'leave';

          $rootScope.$digest();
        });

        describe('when user submits the whole wizard form', function () {
          var params;

          beforeEach(function () {
            absenceTypeSaverSpy.and.returnValue($q.resolve());
            fillWizardIn();
            submitWizard();
            $rootScope.$digest();

            params = AbsenceType.save.calls.mostRecent().args[0];
          });

          it('sends `allow_accruals_request` as "false"', function () {
            expect(params.allow_accruals_request).toBe(false);
          });

          it('sends `add_public_holiday_to_entitlement` as "false"', function () {
            expect(params.allow_accruals_request).toBe(false);
          });
        });
      });

      describe('when user selects the "Sickness" category', function () {
        beforeEach(function () {
          controller.fieldsIndexed.category.value = 'sickness';

          $rootScope.$digest();
        });

        it('hides the "Public Holidays" tab', function () {
          expect(tabsIndexed['public-holidays'].hidden).toBe(true);
        });

        it('hides the "Carry Forwards" tab', function () {
          expect(tabsIndexed['carry-forwards'].hidden).toBe(true);
        });

        it('marks the "Leave Requests" tab as the last one in the section', function () {
          expect(tabsIndexed['leave-requests'].last).toBe(true);
        });

        describe('when user submits the whole wizard form', function () {
          var params;

          beforeEach(function () {
            absenceTypeSaverSpy.and.returnValue($q.resolve());
            fillWizardIn();
            submitWizard();
            $rootScope.$digest();

            params = AbsenceType.save.calls.mostRecent().args[0];
          });

          it('sends `must_take_public_holiday_as_leave` as "false"', function () {
            expect(params.must_take_public_holiday_as_leave).toBe(false);
          });

          it('sends `allow_carry_forward` as "false"', function () {
            expect(params.allow_carry_forward).toBe(false);
          });

          it('sends `allow_accruals_request` as "false"', function () {
            expect(params.allow_accruals_request).toBe(false);
          });

          it('sends `add_public_holiday_to_entitlement` as "false"', function () {
            expect(params.allow_accruals_request).toBe(false);
          });
        });
      });

      /**
       * Fills in all required and valid fields in the wizard
       */
      function fillWizardIn () {
        controller.fieldsIndexed.title.value = sampleFieldValue;
        controller.fieldsIndexed.color.value = _.sample(sampleAvailableColours);
        controller.fieldsIndexed.default_entitlement.value = '100';

        $rootScope.$digest();
      }

      /**
       * Submits wizard form
       */
      function submitWizard () {
        controller.openSection(1);
        controller.openActiveSectionTab(controller.sections[1].tabs.length - 1);
        controller.openNextTab();
      }
    });

    /**
     * Initiates the component and stores it for tests
     */
    function initComponent () {
      controller = $componentController('leaveTypeWizard');
    }
  });
});
