/* eslint-env amd, jasmine */

define([
  'common/moment',
  'common/angularMocks',
  'common/models/job-role',
  'common/mocks/services/hr-settings-mock',
  'common/mocks/services/api/job-role-mock',
  'common/mocks/models/instances/job-role-instance-mock'
], function (moment) {
  'use strict';

  describe('JobRole', function () {
    var $provide, $q, $rootScope, Contract, JobRole, JobRoleInstance,
      JobRoleInstanceMock, jobRoleAPI, jobRoles;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject(['api.job-role.mock', 'HR_settingsMock',
        function (_jobRoleAPIMock_, HRSettingsMock) {
          $provide.value('api.job-role', _jobRoleAPIMock_);
          $provide.value('HR_settings', HRSettingsMock);
        }
      ]);
    });

    beforeEach(inject(['api.job-role',
      function (_jobRoleAPI_) {
        jobRoleAPI = _jobRoleAPI_;
        jobRoles = jobRoleAPI.mockedJobRoles;

        jobRoleAPI.spyOnMethods();
      }
    ]));

    beforeEach(inject(function (_$q_, _$rootScope_, _Contract_, _JobRole_,
      _JobRoleInstance_, _JobRoleInstanceMock_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      Contract = _Contract_;
      JobRole = _JobRole_;
      JobRoleInstance = _JobRoleInstance_;
      JobRoleInstanceMock = _JobRoleInstanceMock_;
    }));

    it('has the expected api', function () {
      expect(Object.keys(JobRole)).toEqual(['all', 'find', 'activeForContact']);
    });

    describe('all()', function () {
      var response;
      var filters = { key: 'filters' };
      var pagination = { key: 'pagination' };
      var sort = 'sort';
      var additionalParams = { key: 'additionalParams' };
      var cache = false;

      describe('basic tests', function () {
        beforeEach(function (done) {
          JobRole
            .all()
            .then(function (_response_) {
              response = _response_;
            })
            .finally(done);

          $rootScope.$digest();
        });

        it('calls API with according params', function () {
          expect(jobRoleAPI.all).toHaveBeenCalled();
        });

        it('returns Job Role instances', function () {
          expect(response.list.every(function (jobRole) {
            return JobRoleInstanceMock.isInstance(jobRole);
          })).toBe(true);
          expect(response.list.length).toEqual(jobRoles.list.length);
        });
      });

      describe('with parameters', function () {
        beforeEach(function (done) {
          JobRole
            .all(filters, pagination, sort, additionalParams, cache)
            .then(function (_response_) {
              response = _response_;
            })
            .finally(done);

          $rootScope.$digest();
        });

        it('calls API with according params', function () {
          expect(jobRoleAPI.all).toHaveBeenCalledWith(
            filters, pagination, sort, additionalParams, cache);
        });
      });
    });

    describe('find()', function () {
      var targetId = '1';

      it('finds a job role by id', function (done) {
        JobRole.find(targetId).then(function (jobRole) {
          expect(jobRoleAPI.find).toHaveBeenCalledWith(targetId);
          expect(jobRole.id).toBe(targetId);
          expect(jobRole.title).toBe('Job Role #1');
        })
          .finally(done) && $rootScope.$digest();
      });

      it('returns an instance of the model', function (done) {
        JobRole.find(targetId).then(function (jobRole) {
          expect(JobRoleInstanceMock.isInstance(jobRole)).toBe(true);
        })
          .finally(done) && $rootScope.$digest();
      });
    });

    describe('activeForContact()', function () {
      var contractSpy, jobRoleSpy, instance1, instance2, result;
      var contactId = '404';
      var contractId = '12';
      var serverDateFormat = 'YYYY-MM-DD';

      beforeEach(function () {
        contractSpy = spyOn(Contract, 'activeForContact');
        jobRoleSpy = spyOn(JobRole, 'all');
      });

      describe('basic tests', function () {
        beforeEach(function (done) {
          instance1 = JobRoleInstance.init({ id: '50' });
          instance2 = JobRoleInstance.init({
            id: '51',
            end_day: moment().format(serverDateFormat)
          });

          contractSpy.and.returnValue($q.resolve([{ id: contractId }]));
          jobRoleSpy.and.returnValue($q.resolve({ list: [instance1, instance2] }));
          loadActiveJobRoles(done);
        });

        it('fetches active contracts for a contact', function () {
          expect(Contract.activeForContact).toHaveBeenCalledWith(contactId);
        });

        it('fetches active job roles based on contracts IDs', function () {
          expect(JobRole.all).toHaveBeenCalledWith({
            job_contract_id: { IN: [contractId] } });
        });

        it('returns job roles instances', function () {
          expect(result.length).toBe(2);
          expect(result[0]).toBe(instance1);
          expect(result[1]).toBe(instance2);
        });
      });

      describe('when there is no current job contract', function () {
        beforeEach(function (done) {
          contractSpy.and.returnValue($q.resolve([]));
          loadActiveJobRoles(done);
        });

        it('does not fetch job roles', function () {
          expect(JobRole.all).not.toHaveBeenCalled();
        });

        it('returns empty array', function () {
          expect(result).toEqual([]);
        });
      });

      describe('when job roles are past', function () {
        beforeEach(function (done) {
          instance1 = JobRoleInstance.init({ end_date: moment().subtract(1, 'day').format(serverDateFormat) });

          contractSpy.and.returnValue($q.resolve([{ id: contractId }]));
          jobRoleSpy.and.returnValue($q.resolve({ list: [instance1] }));
          loadActiveJobRoles(done);
        });

        it('does not include past job roles', function () {
          expect(result).toEqual([]);
        });
      });

      /**
       * Loads active job roles and executes a passed callback when done
       *
       * @param {Function} callback
       */
      function loadActiveJobRoles (callback) {
        JobRole
          .activeForContact(contactId)
          .then(function (_result_) {
            result = _result_;
          })
          .finally(callback);
        $rootScope.$digest();
      }
    });
  });
});
