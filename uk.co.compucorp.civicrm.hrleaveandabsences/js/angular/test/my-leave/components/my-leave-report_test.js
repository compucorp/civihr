(function (CRM) {
  define([
    'common/angular',
    'common/lodash',
    'mocks/helpers/helper',
    'mocks/data/entitlement-data',
    'mocks/data/leave-request-data',
    'mocks/data/option-group-mock-data',
    'common/angularMocks',
    'common/mocks/services/hr-settings-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/entitlement-api-mock',
    'mocks/apis/leave-request-api-mock',
    'leave-absences/my-leave/app'
  ], function (angular, _, helper, entitlementMock, leaveRequestMock, optionGroupMock) {
    'use strict';

    describe('myLeaveReport', function () {
      var contactId = CRM.vars.leaveAndAbsences.contactId;
      var $compile, $q, $log, $provide, $rootScope, component, controller;
      var AbsencePeriod, AbsenceType, Entitlement, LeaveRequest, LeaveRequestInstance, OptionGroup, HR_settings, dialog;

      beforeEach(module('leave-absences.templates', 'my-leave', 'leave-absences.mocks', function (_$provide_) {
        $provide = _$provide_;
      }));
      beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock, EntitlementAPIMock, LeaveRequestAPIMock, HR_settingsMock) {
        $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('EntitlementAPI', EntitlementAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
        $provide.value('HR_settings', HR_settingsMock);
      }));

      beforeEach(inject(function (_$compile_, _$q_, _$log_, _$rootScope_, _$httpBackend_) {
        $compile = _$compile_;
        $q = _$q_;
        $log = _$log_;
        $rootScope = _$rootScope_;

        spyOn($log, 'debug');
      }));
      beforeEach(inject(function (_AbsencePeriod_, _AbsenceType_, _Entitlement_, _LeaveRequest_, _LeaveRequestInstance_, _OptionGroup_, _HR_settings_, _dialog_) {
        AbsencePeriod = _AbsencePeriod_;
        AbsenceType = _AbsenceType_;
        Entitlement = _Entitlement_;
        LeaveRequest = _LeaveRequest_;
        LeaveRequestInstance = _LeaveRequestInstance_;
        OptionGroup = _OptionGroup_;
        HR_settings = _HR_settings_;
        dialog = _dialog_;

        spyOn(AbsencePeriod, 'all').and.callThrough();
        spyOn(AbsenceType, 'all').and.callThrough();
        spyOn(Entitlement, 'all').and.callThrough();
        spyOn(Entitlement, 'breakdown').and.callThrough();
        spyOn(LeaveRequest, 'all').and.callThrough();
        spyOn(LeaveRequest, 'balanceChangeByAbsenceType').and.callThrough();
        spyOn(OptionGroup, 'valuesOf').and.callFake(function () {
          return $q.resolve(optionGroupMock.getCollection('hrleaveandabsences_leave_request_status'));
        });
      }));

      beforeEach(function () {
        compileComponent();
      });

      describe('initialization', function () {
        it('is initialized', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('holds the date format', function () {
          expect(controller.dateFormat).toBeDefined();
          expect(controller.dateFormat).toBe(HR_settings.DATE_FORMAT);
        });

        it('has all the sections collapsed', function () {
          expect(Object.values(controller.sections).every(function (section) {
            return section.open === false;
          })).toBe(true);
        });

        it('contains the expected markup', function () {
          expect(component.find('.chr_leave-report').length).toBe(1);
          expect(component.find('.chr_leave-report__table').length).toBe(3);
        });

        describe('data loading', function () {
          xdescribe('before data is loaded', function () {
            // TODO: check why it doesn't work
            it('is in loading mode', function () {
              expect(controller.loading.page).toBe(true);
              expect(controller.loading.content).toBe(false);
            });
          });

          describe('after data is loaded', function () {
            it('is out of loading mode', function () {
              expect(controller.loading.page).toBe(false);
              expect(controller.loading.content).toBe(false);
            });

            it('has fetched the leave request statuses', function () {
              expect(OptionGroup.valuesOf).toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
              expect(controller.leaveRequestStatuses.length).not.toBe(0);
            });

            it('has fetched the absence types', function () {
              expect(AbsenceType.all).toHaveBeenCalled();
              expect(controller.absenceTypes.length).not.toBe(0);
            });

            it('has fetched the absence periods', function () {
              expect(AbsencePeriod.all).toHaveBeenCalled();
              expect(controller.absencePeriods.length).not.toBe(0);
            });

            it('has automatically selected the period, choosing the current one', function () {
              expect(controller.selectedPeriod).not.toBe(null);
              expect(controller.selectedPeriod).toBe(_.find(controller.absencePeriods, function (period) {
                return period.current === true;
              }));
            });

            describe('entitlements', function () {
              it('has fetched all the entitlements', function () {
                expect(Entitlement.all).toHaveBeenCalled();
                expect(controller.entitlements.length).not.toBe(0);
              });

              it('has fetched the entitlements for the current contact and selected period', function () {
                expect(Entitlement.all.calls.argsFor(0)[0]).toEqual({
                  contact_id: contactId,
                  period_id: controller.selectedPeriod.id
                });
              });

              it('has fetched current and future remainder of the entitlements', function () {
                expect(Entitlement.all.calls.argsFor(0)[1]).toEqual(true);
              });

              it('has stored the remainder in each absence type', function () {
                _.forEach(controller.absenceTypes, function (absenceType) {
                  var remainder = absenceType.remainder;

                  expect(remainder).toBeDefined();
                  expect(remainder).toEqual(_.find(controller.entitlements, function (entitlement) {
                    return entitlement.type_id === absenceType.id
                  })['remainder']);
                });
              });

              it('has stored the entitlement value in each absence type', function () {
                _.forEach(controller.absenceTypes, function (absenceType) {
                  var value = absenceType.entitlement;

                  expect(value).toBeDefined();
                  expect(value).toEqual(_.find(controller.entitlements, function (entitlement) {
                    return entitlement.type_id === absenceType.id
                  })['value']);
                });
              });
            });

            describe('balance changes', function () {
              var mockData;

              beforeEach(function () {
                mockData = leaveRequestMock.balanceChangeByAbsenceType().values;
              });

              it('has fetched the balance changes for the current contact and selected period', function () {
                var args = LeaveRequest.balanceChangeByAbsenceType.calls.argsFor(0);

                expect(args[0]).toEqual(contactId);
                expect(args[1]).toEqual(controller.selectedPeriod.id);
              });

              describe('public holidays', function () {
                it('has fetched the balance changes for the public holidays', function () {
                  var args = LeaveRequest.balanceChangeByAbsenceType.calls.argsFor(0);
                  expect(args[3]).toEqual(true);
                });

                it('has stored them in each absence type', function () {
                  _.forEach(controller.absenceTypes, function (absenceType) {
                    var balanceChanges = absenceType.balanceChanges.publicHolidays;

                    expect(balanceChanges).toBeDefined();
                    expect(balanceChanges).toBe(mockData[absenceType.id]);
                  });
                });
              });

              describe('approved requests', function () {
                it('has fetched the balance changes for the approved requests', function () {
                  var args = LeaveRequest.balanceChangeByAbsenceType.calls.argsFor(1);
                  expect(args[2]).toEqual([ valueOfRequestStatus('approved') ]);
                });

                it('has stored them in each absence type', function () {
                  _.forEach(controller.absenceTypes, function (absenceType) {
                    var balanceChanges = absenceType.balanceChanges.approved;

                    expect(balanceChanges).toBeDefined();
                    expect(balanceChanges).toBe(mockData[absenceType.id]);
                  });
                });
              });

              describe('open requests', function () {
                it('has fetched the balance changes for the open requests', function () {
                  var args = LeaveRequest.balanceChangeByAbsenceType.calls.argsFor(2);

                  expect(args[2]).toEqual([
                    valueOfRequestStatus('waiting_approval'),
                    valueOfRequestStatus('more_information_requested')
                  ]);
                });

                it('has stored them in each absence type', function () {
                  _.forEach(controller.absenceTypes, function (absenceType) {
                    var balanceChanges = absenceType.balanceChanges.pending;

                    expect(balanceChanges).toBeDefined();
                    expect(balanceChanges).toBe(mockData[absenceType.id]);
                  });
                });
              });
            });
          });
        });
      });

      describe('period label', function () {
        var label, period;

        describe('when the period is current', function () {
          beforeEach(function () {
            period = _(controller.absencePeriods).find(function (period) {
              return period.current;
            });
            label = controller.labelPeriod(period);
          })

          it('labels it as such', function () {
            expect(label).toBe('Current Period (' + period.title + ')');
          });
        });

        describe('when the period is not current', function () {
          beforeEach(function () {
            period = _(controller.absencePeriods).filter(function (period) {
              return !period.current;
            }).sample();
            label = controller.labelPeriod(period);
          });

          it('returns the title as it is', function () {
            expect(label).toBe(period.title);
          });
        });
      });

      describe('when refreshing the data with a new absence period', function () {
        var newPeriod;

        beforeEach(function () {
          newPeriod = _(controller.absencePeriods).filter(function (period) {
            return period !== controller.selectedPeriod;
          }).sample();

          controller.selectedPeriod = newPeriod;
        });

        describe('basic tests', function () {
          beforeEach(function () {
            Entitlement.all.calls.reset();
            LeaveRequest.balanceChangeByAbsenceType.calls.reset();

            controller.refresh();
          });

          it('goes into loading mode', function () {
            expect(controller.loading.content).toBe(true);
          });

          it('reloads the entitlements', function () {
            expect(Entitlement.all).toHaveBeenCalled();
            expect(Entitlement.all.calls.argsFor(0)[0]).toEqual(jasmine.objectContaining({
              period_id: newPeriod.id
            }));
          });

          it('reloads all the balance changes', function () {
            var args = LeaveRequest.balanceChangeByAbsenceType.calls.argsFor(_.random(0, 2));

            expect(LeaveRequest.balanceChangeByAbsenceType).toHaveBeenCalledTimes(3);
            expect(args[1]).toEqual(newPeriod.id);
          });
        });

        describe('open sections', function () {
          beforeEach(function () {
            controller.sections.approved.open = true;
            controller.sections.entitlements.open = true;

            controller.refresh();
            $rootScope.$digest();
          });

          it('reloads all data for sections already opened', function () {
            expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
              from_date: { from: newPeriod.start_date },
              to_date: {to: newPeriod.end_date },
              status_id: valueOfRequestStatus('approved')
            }));
            expect(Entitlement.breakdown).toHaveBeenCalledWith(jasmine.objectContaining({
              period_id: newPeriod.id
            }), jasmine.any(Array));
          });
        });

        describe('closed sections', function () {
          beforeEach(function () {
            controller.sections.holidays.data = [jasmine.any(Object), jasmine.any(Object)];
            controller.sections.pending.data = [jasmine.any(Object), jasmine.any(Object)];

            controller.refresh();
            $rootScope.$digest();
          });

          it('removes all cached data for sections that are closed', function () {
            expect(controller.sections.holidays.data.length).toBe(0);
            expect(controller.sections.pending.data.length).toBe(0);
          });
        });

        describe('after loading', function () {
          beforeEach(function () {
            $rootScope.$digest();
          });

          it('goes out of loading mode', function () {
            expect(controller.loading.content).toBe(false);
          });
        });
      });

      describe('when opening a section', function () {
        beforeEach(function () {
          _.forEach(controller.sections, function (section) {
            section.open = false;
          });
        });

        describe('basic tests', function () {
          beforeEach(function () {
            openSection('approved', false);
          });

          it('marks the section as open', function () {
            expect(controller.sections.approved.open).toBe(true);
          });

          it('puts the section in loading mode', function () {
            expect(controller.sections.approved.loading).toBe(true);
          });

          describe('after the data has been loaded', function () {
            beforeEach(function () {
              $rootScope.$digest();
            });

            it('puts the section out of loading mode', function () {
              expect(controller.sections.approved.loading).toBe(false);
            });
          });
        });

        describe('data caching', function () {
          describe('when the section had not been opened yet', function () {
            beforeEach(function () {
              openSection('approved');
            });

            it('makes a request to fetch the data', function () {
              expect(LeaveRequest.all).toHaveBeenCalled();
            });
          });

          describe('when the section had already been opened', function () {
            beforeEach(function () {
              controller.sections.approved.data = [
                LeaveRequestInstance.init(helper.createRandomLeaveRequest(), true),
                LeaveRequestInstance.init(helper.createRandomLeaveRequest(), true)
              ];

              openSection('approved');
            });

            it('does not make another request to fetch the data', function () {
              expect(LeaveRequest.all).not.toHaveBeenCalled();
            });
          });
        });

        describe('section: Public Holidays', function () {
          beforeEach(function () {
            openSection('holidays');
          });

          it('fetches all leave requests linked to a public holiday', function () {
            expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
              public_holiday: true
            }));
          });

          it('caches the data', function () {
            expect(controller.sections.holidays.data.length).not.toBe(0);
          });
        });

        describe('section: Approved Requests', function () {
          beforeEach(function () {
            openSection('approved');
          });

          it('fetches all approved leave requests', function () {
            expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
              status_id: valueOfRequestStatus('approved')
            }));
          });

          it('caches the data', function () {
            expect(controller.sections.approved.data.length).not.toBe(0);
          });
        });

        describe('section: Open Requests', function () {
          beforeEach(function () {
            openSection('pending');
          });

          it('fetches all pending leave requests', function () {
            expect(LeaveRequest.all.calls.argsFor(0)[0]).toEqual(jasmine.objectContaining({
              status_id: { in: [
                valueOfRequestStatus('waiting_approval'),
                valueOfRequestStatus('more_information_requested')
              ] }
            }));
          });

          it('caches the data', function () {
            expect(controller.sections.pending.data.length).not.toBe(0);
          });
        });

        describe('section: Cancelled and Other', function () {
          beforeEach(function () {
            openSection('other');
          });

          it('fetches all cancelled/rejected leave requests', function () {
            expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
              status_id: { in: [
                valueOfRequestStatus('rejected'),
                valueOfRequestStatus('cancelled')
              ] }
            }));
          });

          it('caches the data', function () {
            expect(controller.sections.other.data.length).not.toBe(0);
          });
        });

        describe('breakdown-based sections', function () {
          describe('section: Period Entitlement', function () {
            beforeEach(function () {
              openSection('entitlements');
            });

            it('fetches the entitlements breakdown', function () {
              expect(Entitlement.breakdown).toHaveBeenCalled();
            });

            it('passes to the Model the entitlements already stored', function () {
              expect(Entitlement.breakdown).toHaveBeenCalledWith(jasmine.any(Object), controller.entitlements);
            });

            it('caches the data', function () {
              expect(controller.sections.entitlements.data.length).not.toBe(0);
            });

            describe('cached data format', function () {
              var expectedFormat;

              beforeEach(function () {
                var entitlements = controller.entitlements;

                expectedFormat = Array.prototype.concat.apply([], entitlements.map(function (entitlement) {
                  return entitlement.breakdown;
                }));
              });

              it('groups and flattens all breakdown entries before caching them', function () {
                expect(controller.sections.entitlements.data.length).toBe(expectedFormat.length);
              });
            });

            describe('absence type reference in breakdown', function () {
              it('stores the absence type_id in every breakdown entry', function () {
                controller.entitlements.forEach(function (entitlement) {
                  var entries = entitlementBreakdownEntries(entitlement);

                  expect(entries.every(function (breakdownEntry) {
                    return breakdownEntry.type_id === entitlement.type_id;
                  })).toBe(true);
                });
              });

              function entitlementBreakdownEntries(entitlement) {
                return controller.sections.entitlements.data.filter(function (entry) {
                  return _.contains(entitlement.breakdown, entry);
                });
              }
            });
          });

          describe('section: Expired', function () {
            var dataReturnedFromAPI;

            beforeEach(function () {
              dataReturnedFromAPI = entitlementMock.breakdown().values;

              openSection('expired');
            });

            it('fetches all expired balance changes', function () {
              expect(Entitlement.breakdown).toHaveBeenCalledWith(jasmine.objectContaining({
                expired: true
              }));
            });

            it('does not pass to the Model the entitlements already stored', function () {
              expect(Entitlement.breakdown).not.toHaveBeenCalledWith(jasmine.any(Object), controller.entitlements);
            });

            it('caches the data', function () {
              expect(controller.sections.expired.data.length).not.toBe(0);
            });

            describe('cached data format', function () {
              var expectedFormat;

              beforeEach(function () {
                expectedFormat = Array.prototype.concat.apply([], dataReturnedFromAPI.map(function (entitlement) {
                  return entitlement.breakdown;
                }));
              });

              it('groups and flattens all breakdown entries before caching them', function () {
                expect(controller.sections.expired.data.length).toBe(expectedFormat.length);
              });
            });
          });
        });

        /**
         * Open the given section and runs the digest cycle
         *
         * @param {string} section
         */
        function openSection(section, digest) {
          digest = typeof digest === 'undefined' ? true : !!digest;

          controller.toggleSection(section);
          digest && $rootScope.$digest();
        }
      });

      describe('when closing a section', function () {
        beforeEach(function () {
          controller.sections.approved.open = true;
        });

        describe('basic tests', function () {
          beforeEach(function () {
            controller.toggleSection('approved');
            $rootScope.$digest();
          });

          it('marks the section as closed', function () {
            expect(controller.sections.approved.open).toBe(false);
          });
        });
      });

      describe('action matrix for a leave request', function () {
        var actionMatrix;

        describe('status: awaiting approval', function () {
          beforeEach(function () {
            actionMatrix = getActionMatrixForStatus('waiting_approval');
          });

          it('shows the "edit" and "cancel" actions', function () {
            expect(actionMatrix.length).toBe(2);
            expect(actionMatrix).toContain('edit');
            expect(actionMatrix).toContain('cancel');
          });
        });

        describe('status: more information required', function () {
          beforeEach(function () {
            actionMatrix = getActionMatrixForStatus('more_information_requested');
          });

          it('shows the "respond" and "cancel" actions', function () {
            expect(actionMatrix.length).toBe(2);
            expect(actionMatrix).toContain('respond');
            expect(actionMatrix).toContain('cancel');
          });
        });

        describe('status: approved', function () {
          beforeEach(function () {
            actionMatrix = getActionMatrixForStatus('approved');
          });

          it('shows the "cancel" action', function () {
            expect(actionMatrix.length).toBe(2);
            expect(actionMatrix).toContain('cancel');
          });
        });

        describe('status: cancelled', function () {
          beforeEach(function () {
            actionMatrix = getActionMatrixForStatus('cancelled');
          });

          it('shows the "view" action', function () {
            expect(actionMatrix.length).toBe(1);
            expect(actionMatrix).toContain('view');
          });
        });

        describe('status: rejected', function () {
          beforeEach(function () {
            actionMatrix = getActionMatrixForStatus('rejected');
          });

          it('shows the "view" action', function () {
            expect(actionMatrix.length).toBe(1);
            expect(actionMatrix).toContain('view');
          });
        });

        /**
         * Calls the controller method that returns the action matrix for
         * a given Leave Request in a particular status
         *
         * @param  {string} statusName
         * @return {Array}
         */
        function getActionMatrixForStatus(statusName) {
          return controller.actionsFor(LeaveRequestInstance.init({
            status_id: valueOfRequestStatus(statusName)
          }));
        }
      });

      describe('when cancelling a leave request', function () {
        var leaveRequest1, leaveRequest2, leaveRequest3;

        beforeEach(function () {
          Entitlement.all.calls.reset();
          LeaveRequest.balanceChangeByAbsenceType.calls.reset();
        });

        beforeEach(function () {
          leaveRequest1 = LeaveRequestInstance.init(leaveRequestMock.all().values[0], true);
          leaveRequest2 = LeaveRequestInstance.init(leaveRequestMock.all().values[1], true);
          leaveRequest3 = LeaveRequestInstance.init(leaveRequestMock.all().values[2], true);

          spyOn(leaveRequest1, 'cancel').and.callThrough();
          spyOn(leaveRequest1, 'update').and.callFake(function () {
            return $q.resolve(leaveRequestMock.singleDataSuccess());
          });

          controller.sections.pending.data = [leaveRequest1, leaveRequest2, leaveRequest3];
        });

        describe('the user is prompted for confirmation', function () {
          beforeEach(function () {
            resolveDialogWith(null);

            controller.action(leaveRequest1, 'cancel');
            $rootScope.$digest();
          });

          it('shows a confirmation dialog', function () {
            expect(dialog.open).toHaveBeenCalled();
          });
        });

        describe('when the user confirms', function () {
          var oldBalanceChange, oldList, oldRemainder;

          describe('basic tests', function () {
            beforeEach(function () {
              oldBalanceChange = controller.absenceTypes[leaveRequest1.type_id].balanceChanges.pending;
              oldList = controller.sections.pending.data;
              oldRemainder = controller.absenceTypes[leaveRequest1.type_id].remainder.future;
            });

            beforeEach(function () {
              cancelRequest(leaveRequest1);
            });

            it('sends the cancellation request', function () {
              expect(leaveRequest1.cancel).toHaveBeenCalled();
            });

            it('removes the leave request from the current section', function () {
              expect(controller.sections.pending.data).not.toContain(leaveRequest1);
            });

            it('remove the leave request without creating a new array', function () {
              expect(controller.sections.pending.data).toBe(oldList);
            });

            describe('balance changes', function () {
              var newBalanceChange;

              beforeEach(function () {
                newBalanceChange = controller.absenceTypes[leaveRequest1.type_id].balanceChanges.pending;
              });

              it('updates the balance changes for the section the leave request was in', function () {
                expect(newBalanceChange).not.toBe(oldBalanceChange);
                expect(newBalanceChange).toBe(oldBalanceChange - leaveRequest1.balance_change);
              });

              it('does not send a request to the backend to fetch the updated balance changes', function () {
                expect(LeaveRequest.balanceChangeByAbsenceType).not.toHaveBeenCalled();
              });
            });

            describe('remainders', function () {
              var newRemainder;

              beforeEach(function () {
                newRemainder = controller.absenceTypes[leaveRequest1.type_id].remainder.future;
              });

              it('updates the remainder of the entitlement of the absence type the leave request was for', function () {
                expect(newRemainder).not.toBe(oldRemainder);
                expect(newRemainder).toBe(oldRemainder - leaveRequest1.balance_change);
              });

              it('does not send a request to the backend to fetch the updated remainders', function () {
                expect(Entitlement.all).not.toHaveBeenCalled();
              });
            });
          });

          describe('moving the request to the "Other" section', function () {
            describe('when the section has cached data', function () {
              beforeEach(function () {
                var alreadyRejected = LeaveRequestInstance.init(helper.createRandomLeaveRequest(), true);
                controller.sections.other.data = [alreadyRejected];

                cancelRequest(leaveRequest1);
              });

              it('moves the leave request in it', function () {
                expect(controller.sections.other.data).toContain(leaveRequest1);
              });
            });

            describe('when the section has not any cached data', function () {
              beforeEach(function () {
                controller.sections.other.data = [];

                cancelRequest(leaveRequest1);
              });

              it('does not move the leave request in it', function () {
                expect(controller.sections.other.data).not.toContain(leaveRequest1);
              });
            });
          });

          /**
           * Set up the confirmation dialog to be confirmed and then
           * triggers the leave request cancellation
           *
           * @param  {LeaveRequestInstance} leaveRequest
           */
          function cancelRequest(leaveRequest) {
            resolveDialogWith(true);
            controller.action(leaveRequest1, 'cancel');
            $rootScope.$digest();
          }
        });

        describe('when the user does not confirm', function () {
          beforeEach(function () {
            resolveDialogWith(false);

            controller.action(leaveRequest1, 'cancel');
            $rootScope.$digest();
          });

          it('does not send the cancellation request', function () {
            expect(leaveRequest1.cancel).not.toHaveBeenCalled();
          });

          it('does not remove the leave request from the current section', function () {
            expect(controller.sections.pending.data).toContain(leaveRequest1);
          });
        });

        describe('when new leave request is created', function() {
          beforeEach(function() {
            spyOn(controller,'refresh').and.callThrough();
            $rootScope.$emit('LeaveRequest::new', jasmine.any(Object));
          });

          it('refreshes the report', function() {
            expect(controller.refresh).toHaveBeenCalled();
          });
        });
        /**
         * Spyes on dialog.open() method and resolves it with the given value
         *
         * @param {any} value
         */
        function resolveDialogWith(value) {
          var spy;

          if (typeof dialog.open.calls !== 'undefined') {
            spy = dialog.open;
          } else {
            spy = spyOn(dialog, 'open');
          }

          spy.and.callFake(function (options) {
            return $q.resolve()
              .then(function () {
                return options.onConfirm && value ? options.onConfirm() : null;
              })
              .then(function () {
                return value;
              });
          });;
        }
      });

      /**
       * Returns the value of the given leave request status
       *
       * @param  {string} statusName
       * @return {integer}
       */
      function valueOfRequestStatus(statusName) {
        var statuses = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status');

        return _.find(statuses, function (status) {
          return status.name === statusName;
        })['value'];
      }

      function compileComponent() {
        var $scope = $rootScope.$new();

        component = angular.element('<my-leave-report contact-id="' + contactId + '"></my-leave-report>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('myLeaveReport');
      }
    });
  })
})(CRM);
