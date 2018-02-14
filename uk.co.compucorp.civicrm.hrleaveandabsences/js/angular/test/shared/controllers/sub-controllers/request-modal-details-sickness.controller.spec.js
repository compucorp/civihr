describe('when request type is Sick', function () {
    describe('on initialize', function () {
      beforeEach(function () {
        selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {remainder: 0});
        leaveRequest = SicknessRequestInstance.init();

        compileComponent({
          leaveType: 'sick',
          request: leaveRequest,
          selectedAbsenceType: selectedAbsenceType
        });

        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
        $rootScope.$digest();
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has leave type as "sickness"', function () {
        expect(controller.isLeaveType('sickness')).toBeTruthy();
      });

      describe('initChildController()', function () {
        it('loads reasons option types', function () {
          expect(Object.keys(controller.sicknessReasons).length).toBeGreaterThan(0);
        });

        it('loads documents option types', function () {
          expect(controller.sicknessDocumentTypes.length).toBeGreaterThan(0);
        });
      });

      describe('isDocumentInRequest()', function () {
        var documents = optionGroupMock.getCollection('hrleaveandabsences_leave_request_required_document');

        it('checks if the document is in the request', function () {
          expect(controller.isDocumentInRequest(documents[0].value)).toBeTruthy();
          expect(controller.isDocumentInRequest('non-existing-document')).toBeFalsy();
        });
      });

      describe('with selected reason', function () {
        beforeEach(function () {
          setTestDates(date2016, date2016To);
          setReason();
        });

        describe('when user changes number of days selected', function () {
          beforeEach(function () {
            controller.daysSelectionModeChangeHandler();
          });

          it('does not reset sickness reason', function () {
            expect(controller.request.sickness_reason).not.toBeNull();
          });
        });
      });

      describe('open sickness request in edit mode', function () {
        var sicknessRequest;

        beforeEach(function () {
          sicknessRequest = SicknessRequestInstance.init(leaveRequestData.findBy('request_type', 'sickness'));
          sicknessRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          sicknessRequest.sickness_required_documents = '1,2';
          sicknessRequest.status_id = optionGroupMock.specificValue(
            'hrleaveandabsences_leave_request_status', 'value', '3');

          compileComponent({
            leaveType: 'sick',
            mode: 'edit',
            request: sicknessRequest,
            selectedAbsenceType: selectedAbsenceType
          });

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();
        });

        it('sets edit mode', function () {
          expect(controller.isMode('edit')).toBeTruthy();
        });

        it('does show balance', function () {
          expect(controller.uiOptions.showBalance).toBeTruthy();
        });

        describe('when request states multiple days', function () {
          beforeEach(function () {
            compileComponent({
              mode: 'edit',
              leaveType: 'sick',
              request: sicknessRequest,
              selectedAbsenceType: selectedAbsenceType
            });
            setTestDates(date2016, date2017);
            $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
            $rootScope.$digest();
          });

          it('shows multiple days', function () {
            expect(controller.uiOptions.multipleDays).toBeTruthy();
          });
        });

        describe('when request states a single day', function () {
          beforeEach(function () {
            compileComponent({
              request: sicknessRequest,
              leaveType: 'sick'
            });
            setTestDates(date2016, date2016To);
            $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
            $rootScope.$digest();
          });

          it('shows single day', function () {
            expect(controller.uiOptions.multipleDays).not.toBeTruthy();
          });
        });

        describe('initializes required documents', function () {
          var testDocumentId = '1';
          var failDocumentId = '3';

          it('checks checkbox', function () {
            expect(controller.isChecked(testDocumentId)).toBeTruthy();
          });

          it('does not check checkbox', function () {
            expect(controller.isChecked(failDocumentId)).toBeFalsy();
          });
        });

        describe('when checking if can submit', function () {
          describe('when sickness reason is not chosen', function () {
            beforeEach(function () {
              controller.request.sickness_reason = null;
            });

            it('does not allow to submit', function () {
              expect(controller.checkSubmitConditions()).toBeFalsy();
            });
          });

          describe('when sickness reason is chosen', function () {
            beforeEach(function () {
              controller.request.sickness_reason = '2';
            });

            it('does not allow to submit', function () {
              expect(controller.checkSubmitConditions()).toBeTruthy();
            });
          });
        });
      });
    });
  });
