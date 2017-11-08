/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/shared/components/leave-widget/leave-widget.component',
  'mocks/apis/absence-period-api-mock',
  'mocks/apis/absence-type-api-mock',
  'common/mocks/services/api/contract-mock',
  'common/services/pub-sub'
], function (_) {
  describe('LeaveWidget', function () {
    var $componentController, $provide, $q, $rootScope, $scope, AbsencePeriod,
      AbsenceType, Contract, ctrl, OptionGroup, pubSub;
    var contactId = 208;

    beforeEach(module('common.mocks', 'leave-absences.components.leave-widget',
    'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(['AbsencePeriodAPIMock', 'AbsenceTypeAPIMock',
      'api.contract.mock', 'OptionGroupAPIMock', function (AbsencePeriodAPIMock,
      AbsenceTypeAPIMock, ContractMock, OptionGroupAPIMock) {
        $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('Contract', ContractMock);
        $provide.value('OptionGroup', OptionGroupAPIMock);
      }]));

    beforeEach(inject(function (_$componentController_, _$q_, _$rootScope_,
    _AbsencePeriod_, _AbsenceType_, _Contract_, _OptionGroup_, _pubSub_) {
      $componentController = _$componentController_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      AbsencePeriod = _AbsencePeriod_;
      AbsenceType = _AbsenceType_;
      Contract = _Contract_;
      OptionGroup = _OptionGroup_;
      pubSub = _pubSub_;

      spyOn($scope, '$on').and.callThrough();
      spyOn(AbsencePeriod, 'all').and.callThrough();
      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(Contract, 'all').and.callThrough();
      spyOn(OptionGroup, 'valuesOf').and.callThrough();
    }));

    beforeEach(function () {
      ctrl = $componentController('leaveWidget',
        { $scope: $scope },
        { contactId: contactId }
      );
    });

    it('should be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('on init', function () {
      it('sets loading child components to false', function () {
        expect(ctrl.loading.childComponents).toBe(false);
      });

      it('sets loading component to true', function () {
        expect(ctrl.loading.component).toBe(true);
      });

      it('sets absence types equal to an empty array', function () {
        expect(ctrl.absenceTypes).toEqual([]);
      });

      it('sets absence period to null', function () {
        expect(ctrl.absencePeriod).toBe(null);
      });

      it('sets job contract to null', function () {
        expect(ctrl.jobContract).toBe(null);
      });

      it('sets sickness absence types equal to an empty array', function () {
        expect(ctrl.sicknessAbsenceTypes).toEqual([]);
      });

      it('sets leave request statuses equal to an empty array', function () {
        expect(ctrl.leaveRequestStatuses).toEqual([]);
      });

      it('watches for child components loading and ready events', function () {
        expect($scope.$on).toHaveBeenCalledWith(
          'LeaveWidget::childIsLoading', jasmine.any(Function));
        expect($scope.$on).toHaveBeenCalledWith(
          'LeaveWidget::childIsReady', jasmine.any(Function));
      });

      describe('child components', function () {
        describe('when child components are loading', function () {
          beforeEach(function () {
            $rootScope.$broadcast('LeaveWidget::childIsLoading');
            $rootScope.$broadcast('LeaveWidget::childIsLoading');
            $rootScope.$broadcast('LeaveWidget::childIsLoading');
          });

          it('sets loading child components to true', function () {
            expect(ctrl.loading.childComponents).toBe(true);
          });

          describe('when a few child components are ready', function () {
            beforeEach(function () {
              $rootScope.$broadcast('LeaveWidget::childIsReady');
              $rootScope.$broadcast('LeaveWidget::childIsReady');
            });

            it('keeps loading child components set to true', function () {
              expect(ctrl.loading.childComponents).toBe(true);
            });

            describe('when all child components are ready', function () {
              beforeEach(function () {
                $rootScope.$broadcast('LeaveWidget::childIsReady');
              });

              it('sets loading child components to false', function () {
                expect(ctrl.loading.childComponents).toBe(false);
              });
            });
          });
        });
      });

      describe('Leave requests updated', function () {
        beforeEach(function () {
          Contract.all.calls.reset();
        });

        describe('when a leave request is created', function () {
          beforeEach(function () {
            pubSub.publish('LeaveRequest::new');
            $rootScope.$digest();
          });

          it('reloads the dependencies', function () {
            expect(Contract.all).toHaveBeenCalled();
          });
        });

        describe('when a leave request is edited', function () {
          beforeEach(function () {
            pubSub.publish('LeaveRequest::edit');
            $rootScope.$digest();
          });

          it('reloads the dependencies', function () {
            expect(Contract.all).toHaveBeenCalled();
          });
        });

        describe('when a leave request is deleted', function () {
          beforeEach(function () {
            pubSub.publish('LeaveRequest::deleted');
            $rootScope.$digest();
          });

          it('reloads the dependencies', function () {
            expect(Contract.all).toHaveBeenCalled();
          });
        });

        describe('when a leave request is updated by a manager', function () {
          beforeEach(function () {
            pubSub.publish('LeaveRequest::updatedByManager');
            $rootScope.$digest();
          });

          it('reloads the dependencies', function () {
            expect(Contract.all).toHaveBeenCalled();
          });
        });

        describe('when a contract is created', function () {
          beforeEach(function () {
            pubSub.publish('contract:created');
            $rootScope.$digest();
          });

          it('reloads the dependencies', function () {
            expect(Contract.all).toHaveBeenCalled();
          });
        });

        describe('when a contract is deleted', function () {
          beforeEach(function () {
            pubSub.publish('contract:deleted');
            $rootScope.$digest();
          });

          it('reloads the dependencies', function () {
            expect(Contract.all).toHaveBeenCalled();
          });
        });

        describe('when a contract is updated', function () {
          beforeEach(function () {
            pubSub.publish('contract-refresh');
            $rootScope.$digest();
          });

          it('reloads the dependencies', function () {
            expect(Contract.all).toHaveBeenCalled();
          });
        });
      });

      describe('job contract', function () {
        it('loads the contact\'s current job contract', function () {
          expect(Contract.all).toHaveBeenCalledWith({
            contact_id: contactId,
            deleted: false
          });
        });

        describe('after loading the job contract', function () {
          var expectedJobContract;

          beforeEach(function () {
            Contract.all({
              contact_id: contactId,
              deleted: false
            })
            .then(function (contracts) {
              expectedJobContract = _.find(contracts, function (contract) {
                return +contract.is_current === 1;
              });
            });
            $rootScope.$digest();
          });

          it('stores the job contract', function () {
            expect(ctrl.jobContract).toEqual(expectedJobContract);
          });
        });
      });

      describe('absence types', function () {
        beforeEach(function () {
          $rootScope.$digest();
        });

        it('loads all absence types', function () {
          expect(AbsenceType.all).toHaveBeenCalledWith({ is_active: true });
        });

        describe('after loading all absence types', function () {
          var expectedTypes;
          var expectedSicknessTypes;

          beforeEach(function () {
            AbsenceType.all().then(function (types) {
              expectedTypes = types;
              expectedSicknessTypes = types.filter(function (type) {
                return +type.is_sick;
              });
            });
            $rootScope.$digest();
          });

          it('stores all absence types', function () {
            expect(ctrl.absenceTypes).toEqual(expectedTypes);
          });

          it('stores all sickness absence types', function () {
            expect(ctrl.sicknessAbsenceTypes).toEqual(expectedSicknessTypes);
          });
        });
      });

      describe('loading the absence period', function () {
        var absencePeriods;

        beforeEach(function () {
          absencePeriods = [
            {
              'id': '1',
              'title': '2016',
              'start_date': '2016-01-01',
              'end_date': '2016-12-31',
              'weight': '1'
            },
            {
              'id': '2',
              'title': '2017',
              'start_date': '2017-01-01',
              'end_date': '2017-12-31',
              'weight': '2',
              'current': true
            }
          ];

          AbsencePeriod.all.and.returnValue($q.resolve(absencePeriods));
        });

        describe('when the component initializes', function () {
          beforeEach(function () {
            $rootScope.$digest();
          });

          it('loads the absence periods', function () {
            expect(AbsencePeriod.all).toHaveBeenCalled();
          });
        });

        describe('when there is a current absence period', function () {
          beforeEach(function () {
            $rootScope.$digest();
          });

          it('stores the current one', function () {
            expect(ctrl.absencePeriod.title).toEqual('2017');
          });
        });

        describe('when there are no current absence periods', function () {
          beforeEach(function () {
            absencePeriods.forEach(function (period) {
              period.current = false;
            });
            $rootScope.$digest();
          });

          it('stores the last one', function () {
            expect(ctrl.absencePeriod.title).toEqual('2017');
          });
        });
      });

      describe('leave request statuses', function () {
        beforeEach(function () {
          $rootScope.$digest();
        });

        it('loads the leave requests statuses', function () {
          expect(OptionGroup.valuesOf)
            .toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
        });

        describe('after loadinv leave request statuses', function () {
          var expectedStatuses;
          var allowedLeaveStatuses = ['approved', 'admin_approved',
            'awaiting_approval', 'more_information_required'];

          beforeEach(function () {
            OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
              .then(function (statuses) {
                expectedStatuses = statuses.filter(function (status) {
                  return _.includes(allowedLeaveStatuses, status.name);
                });
              });
            $rootScope.$digest();
          });

          it('sotres the leave request statuses', function () {
            expect(ctrl.leaveRequestStatuses).toEqual(expectedStatuses);
          });
        });
      });

      describe('after init', function () {
        beforeEach(function () { $rootScope.$digest(); });

        it('sets loading component to false', function () {
          expect(ctrl.loading.component).toBe(false);
        });
      });
    });
  });
});
