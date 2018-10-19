/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'leave-absences/leave-type-wizard/leave-type-wizard.module'
], function (angular, _) {
  'use strict';

  describe('LeaveTypeWizard', function () {
    var $componentController, $log, $q, $rootScope, AbsenceType, Contact,
      controller, notificationService;
    var sampleAvailableColours = ['#FFFFFF', '#000000'];
    var sampleContacts = { list: [{ id: '29', display_name: 'Liza' }] };

    beforeEach(angular.mock.module('leave-type-wizard'));

    beforeEach(inject(function (_$componentController_, _$log_, _$q_, _$rootScope_,
      _AbsenceType_, _Contact_, _notificationService_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      Contact = _Contact_;
      notificationService = _notificationService_;
    }));

    beforeEach(function () {
      spyOn(AbsenceType, 'getAvailableColours').and.returnValue($q.resolve(sampleAvailableColours));
      spyOn(Contact, 'all').and.returnValue($q.resolve(sampleContacts));
      spyOn($log, 'debug').and.callThrough();
      spyOn(notificationService, 'error');
    });

    beforeEach(function () {
      initComponent();
    });

    it('loads the wizard controller', function () {
      expect($log.debug).toHaveBeenCalledWith('Controller: LeaveTypeWizardController');
    });

    describe('on init', function () {
      var secondSection, secondSectionFirstTab;

      beforeEach(function () {
        secondSection = controller.sections[1];
        secondSectionFirstTab = _.first(secondSection.tabs);

        controller.$onInit();
        $rootScope.$digest();
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

      it('has the Leave leave type category selected', function () {
        expect(controller.leaveTypeCategory).toBe('leave');
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

      describe('when user clicks the "next section" button', function () {
        beforeEach(function () {
          controller.nextTabHandler();
        });

        it('collapses the first section', function () {
          expect(controller.sections[0].active).toBe(false);
        });

        it('expands the second section ', function () {
          expect(secondSection.active).toBe(true);
        });

        describe('when user clicks the "previous section" button', function () {
          beforeEach(function () {
            controller.previousTabHandler();
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

        it('tells that the user is on the first tab section', function () {
          expect(controller.isOnSectionFirstTab).toEqual(true);
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

          it('tells that the user is neither on the first tab nor on the last one', function () {
            expect(controller.isOnSectionLastTab).toEqual(false);
            expect(controller.isOnSectionFirstTab).toEqual(false);
          });
        });

        describe('when opens the next section tab', function () {
          beforeEach(function () {
            controller.nextTabHandler();
          });

          it('opens the second tab', function () {
            expect(secondSection.tabs[1].active).toEqual(true);
          });

          describe('when opens the previous section tab', function () {
            beforeEach(function () {
              controller.previousTabHandler();
            });

            it('opens the first tab', function () {
              expect(secondSection.tabs[0].active).toEqual(true);
            });
          });
        });

        describe('when user selects the last tab', function () {
          beforeEach(function () {
            controller.openActiveSectionTab(secondSection.tabs.length - 1);
          });

          it('tells that the user is on the last tab', function () {
            expect(controller.isOnSectionLastTab).toEqual(true);
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
              rule: /^\d+$/,
              required: true,
              message: sampleErrorMessage
            }
          ];
        });

        describe('when user enters a value in a wrong format', function () {
          beforeEach(function () {
            sampleField.value = 'Not a number';

            $rootScope.$digest();
          });

          it('sets the error to the field', function () {
            expect(sampleField.error).toBe(sampleErrorMessage);
          });

          describe('when user changes the value to a valid format', function () {
            beforeEach(function () {
              sampleField.value = '1';

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
            controller.nextTabHandler();

            $rootScope.$digest();
          });

          it('sets the error to the missed field', function () {
            expect(sampleField.error).toBe(requiredFieldErrorMessage);
          });

          it('sets the error to the whole tab', function () {
            expect(controller.sections[0].tabs[0].valid).toBe(false);
          });
        });

        describe('when user attempts to save and there are errors', function () {
          beforeEach(function () {
            controller.openSection(1);
            controller.openActiveSectionTab(controller.sections[1].tabs.length - 1);
            controller.nextTabHandler();
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
    });

    /**
     * Initiates the component and stores it for tests
     */
    function initComponent () {
      controller = $componentController('leaveTypeWizard');
    }
  });
});
