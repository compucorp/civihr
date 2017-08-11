/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/models/job-role',
  'common/mocks/services/hr-settings-mock',
  'common/mocks/services/api/job-role-mock',
  'common/mocks/models/instances/job-role-instance-mock'
], function () {
  'use strict';

  describe('JobRole', function () {
    var $provide, $rootScope, JobRole, JobRoleInstanceMock, jobRoleAPI, jobRoles;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject([
        'api.job-role.mock', 'HR_settingsMock',
        function (_jobRoleAPIMock_, HRSettingsMock) {
          $provide.value('api.job-role', _jobRoleAPIMock_);
          $provide.value('HR_settings', HRSettingsMock);
        }
      ]);
    });

    beforeEach(inject([
      '$rootScope', 'JobRole', 'JobRoleInstanceMock', 'api.job-role',
      function (_$rootScope_, _JobRole_, _JobRoleInstanceMock_, _jobRoleAPI_) {
        $rootScope = _$rootScope_;

        JobRole = _JobRole_;
        JobRoleInstanceMock = _JobRoleInstanceMock_;
        jobRoleAPI = _jobRoleAPI_;

        jobRoleAPI.spyOnMethods();
        jobRoles = jobRoleAPI.mockedJobRoles;
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(JobRole)).toEqual(['all', 'find']);
    });

    describe('all()', function () {
      describe('instances', function () {
        it('returns a list of model instances', function (done) {
          JobRole.all().then(function (response) {
            expect(response.list.every(function (jobRole) {
              return JobRoleInstanceMock.isInstance(jobRole);
            })).toBe(true);
          })
                    .finally(done) && $rootScope.$digest();
        });
      });

      describe('when called without arguments', function () {
        it('returns all job roles', function (done) {
          JobRole.all().then(function (response) {
            expect(jobRoleAPI.all).toHaveBeenCalled();
            expect(response.list.length).toEqual(jobRoles.list.length);
          })
                    .finally(done) && $rootScope.$digest();
        });
      });

      xdescribe('when called with filters', function () {
      });

      describe('when called with pagination', function () {
        var pagination = { page: 2, size: 2 };

        it('can paginate the job roles list', function (done) {
          JobRole.all(null, pagination).then(function (response) {
            expect(jobRoleAPI.all).toHaveBeenCalledWith(null, pagination);
            expect(response.list.length).toEqual(2);
          })
                    .finally(done) && $rootScope.$digest();
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
  });
});
