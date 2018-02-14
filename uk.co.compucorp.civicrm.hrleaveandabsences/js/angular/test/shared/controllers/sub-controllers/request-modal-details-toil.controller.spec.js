describe('when request type is TOIL', function () {
    describe('on initialize', function () {
      beforeEach(function () {
        leaveRequest = TOILRequestInstance.init();

        var params = compileComponent({
          leaveType: 'toil',
          request: leaveRequest
        });

        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
        $rootScope.$digest();

        controller.request.type_id = params.selectedAbsenceType.id;
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has leave type as "toil"', function () {
        expect(controller.isLeaveType('toil')).toBeTruthy();
      });

      it('loads toil amounts', function () {
        expect(Object.keys(controller.toilAmounts).length).toBeGreaterThan(0);
      });

      it('defaults to a multiple day selection', function () {
        expect(controller.uiOptions.multipleDays).toBe(true);
      });

      describe('when multiple/single days mode changes', function () {
        describe('when the balance can but fails to be calculated', function () {
          beforeEach(function () {
            // This ensures the balance can be calculated
            controller.request.toil_to_accrue = 1;
            // While this ensures it fails to be calculated for some reason
            spyOn(controller, 'calculateBalanceChange').and.returnValue($q.reject());
            spyOn(controller, 'setDaysSelectionModeExtended').and.callThrough();
            controller.daysSelectionModeChangeHandler();
            $rootScope.$digest();
          });

          it('still performs the actions extended for TOIL', function () {
            expect(controller.setDaysSelectionModeExtended).toHaveBeenCalled();
          });
        });
      });

      describe('onDateChangeExtended()', function () {
        var promiseIsResolved = false;

        beforeEach(function () {
          // Resetting dates will make calculateToilExpiryDate() to reject
          controller.request.from_date = null;
          controller.request.to_date = null;
          controller.onDateChangeExtended().then(function () {
            promiseIsResolved = true;
          });
          $rootScope.$digest();
        });

        it('resolves disregarding of the result of calculateToilExpiryDate()', function () {
          expect(promiseIsResolved).toBeTruthy();
        });
      });

      describe('create', function () {
        describe('with selected duration and dates', function () {
          describe('when multiple days request', function () {
            beforeEach(function () {
              var toilAccrue = optionGroupMock.specificObject('hrleaveandabsences_toil_amounts', 'name', 'quarter_day');

              setTestDates(date2016, date2016To);
              controller.request.toilDurationHours = 1;
              controller.request.updateDuration();
              controller.request.toil_to_accrue = toilAccrue.value;

              $rootScope.$apply();
            });

            it('sets expiry date', function () {
              expect(controller.expiryDate).toEqual(absenceTypeData.calculateToilExpiryDate().values.toil_expiry_date);
            });

            it('calls calculateToilExpiryDate on AbsenceType', function () {
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[0]).toEqual(controller.request.type_id);
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[1]).toEqual(controller.request.to_date);
            });

            describe('when user changes number of days selected', function () {
              beforeEach(function () {
                controller.daysSelectionModeChangeHandler();
              });

              it('does not reset toil attributes', function () {
                expect(controller.request.toilDurationHours).not.toEqual('0');
                expect(controller.request.toilDurationMinutes).toEqual('0');
                expect(controller.request.toil_to_accrue).not.toEqual('');
              });
            });
          });

          describe('when single days request', function () {
            beforeEach(function () {
              controller.uiOptions.multipleDays = false;
              setTestDates(date2016);
            });

            it('calls calculateToilExpiryDate on AbsenceType', function () {
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[1]).toEqual(controller.request.from_date);
            });
          });
        });
      });

      describe('edit', function () {
        var toilRequest, absenceType;

        beforeEach(function () {
          toilRequest = TOILRequestInstance.init(leaveRequestData.findBy('request_type', 'toil'));
          toilRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

          compileComponent({
            leaveType: 'toil',
            mode: 'edit',
            request: toilRequest
          });
          spyOn(controller, 'performBalanceChangeCalculation').and.callThrough();

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();

          absenceType = _.find(controller.absenceTypes, function (absenceType) {
            return absenceType.id === controller.request.type_id;
          });
        });

        it('does not calculate balance yet', function () {
          expect(controller.performBalanceChangeCalculation).not.toHaveBeenCalled();
        });

        it('sets balance', function () {
          expect(controller.balance.opening).not.toBeLessThan(0);
        });

        it('sets absence types', function () {
          expect(absenceType.id).toEqual(toilRequest.type_id);
        });

        it('shows balance', function () {
          expect(controller.uiOptions.showBalance).toBeTruthy();
        });
      });
    });

    describe('respond', function () {
      describe('by manager', function () {
        var expiryDate, originalToilToAccrue, toilRequest;

        beforeEach(function () {
          selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {remainder: 0});
          expiryDate = '2017-12-31';
          toilRequest = TOILRequestInstance.init();
          toilRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          toilRequest.toil_expiry_date = expiryDate;

          var params = compileComponent({
            leaveType: 'toil',
            request: toilRequest,
            role: 'manager'
          });

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();
          controller.request.type_id = params.selectedAbsenceType.id;
          setTestDates(date2016, date2016To);
          $rootScope.$digest();

          expiryDate = new Date(controller.request.toil_expiry_date);
          originalToilToAccrue = optionGroupMock.specificObject('hrleaveandabsences_toil_amounts', 'name', 'quarter_day');
          controller.request.toil_to_accrue = originalToilToAccrue.value;
        });

        it('expiry date is set on ui', function () {
          expect(controller.uiOptions.expiryDate).toEqual(expiryDate);
        });

        describe('and changes expiry date', function () {
          var oldExpiryDate, newExpiryDate;

          beforeEach(function () {
            oldExpiryDate = controller.request.toil_expiry_date;
            controller.uiOptions.expiryDate = new Date();
            newExpiryDate = controller.convertDateToServerFormat(controller.uiOptions.expiryDate);
            controller.updateExpiryDate();
          });

          it('new expiry date is not same as old expiry date', function () {
            expect(oldExpiryDate).not.toEqual(controller.request.toil_expiry_date);
          });

          it('sets new expiry date', function () {
            expect(controller.request.toil_expiry_date).toEqual(newExpiryDate);
          });

          describe('and staff edits open request', function () {
            beforeEach(function () {
              compileComponent({
                leaveType: 'toil',
                mode: 'edit',
                request: controller.request
              });

              $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
              $rootScope.$digest();

              controller.uiOptions.expiryDate = oldExpiryDate;

              controller.updateExpiryDate();
            });

            it('has role as "staff"', function () {
              expect(controller.isRole('staff')).toBeTruthy();
            });

            it('has expired date set by manager', function () {
              expect(controller.request.toil_expiry_date).toEqual(oldExpiryDate);
            });

            it('has toil amount set by manager', function () {
              expect(controller.request.toil_to_accrue).toEqual(originalToilToAccrue.value);
            });
          });

          describe('clears expiry date', function () {
            beforeEach(function () {
              controller.clearExpiryDate();
            });

            it('resets expiry date in both UI and request', function () {
              expect(controller.request.toil_expiry_date).toBeFalsy();
              expect(controller.uiOptions.expiryDate).toBeFalsy();
            });
          });
        });
      });
    });

    describe('when TOIL Request does not expire', function () {
      beforeEach(function () {
        AbsenceType.canExpire.and.returnValue($q.resolve(false));
        compileComponent({
          leaveType: 'toil',
          request: controller.request
        });

        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
        $rootScope.$digest();
      });

      it('should set requestCanExpire to false', function () {
        expect(controller.requestCanExpire).toBe(false);
      });

      describe('when request date changes', function () {
        beforeEach(function () {
          spyOn(AbsenceType, 'calculateToilExpiryDate');
          controller.request.to_date = new Date();
          $rootScope.$digest();
        });

        it('should not calculate the expiry date field', function () {
          expect(AbsenceType.calculateToilExpiryDate).not.toHaveBeenCalled();
        });

        it('should set expiry date to false', function () {
          expect(controller.request.toil_expiry_date).toBe(false);
        });
      });
    });
  });
