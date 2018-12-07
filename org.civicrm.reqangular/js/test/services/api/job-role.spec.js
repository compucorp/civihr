/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/services/api/job-role',
  'common/mocks/services/api/job-role-mock'
], function () {
  'use strict';

  describe('api.job-role', function () {
    var JobRoleAPI, $rootScope, JobRoleAPIMock, $q;

    beforeEach(module('common.apis', 'common.mocks'));

    beforeEach(inject(['api.job-role', 'api.job-role.mock', '$rootScope', '$q', function (_JobRoleAPI_, _JobRoleAPIMock_, _$rootScope_, _$q_) {
      JobRoleAPI = _JobRoleAPI_;
      JobRoleAPIMock = _JobRoleAPIMock_;
      $rootScope = _$rootScope_;
      $q = _$q_;
    }]));

    it('has expected interface', function () {
      expect(Object.keys(JobRoleAPI)).toContain('all');
      expect(Object.keys(JobRoleAPI)).toContain('find');
    });

    describe('all()', function () {
      var result;
      var filters = { key: 'filters' };
      var pagination = { key: 'pagination' };
      var sort = 'sort';
      var additionalParams = { key: 'additionalParams' };
      var cache = false;

      beforeEach(function (done) {
        spyOn(JobRoleAPI, 'getAll').and.returnValue($q.resolve(JobRoleAPIMock.mockedJobRoles));

        JobRoleAPI.all(filters, pagination, sort, additionalParams, cache)
          .then(function (_result_) {
            result = _result_;
          })
          .finally(done);

        $rootScope.$digest();
      });

      it('returns all the job roles', function () {
        expect(result).toEqual(JobRoleAPIMock.mockedJobRoles);
      });

      it('calls getAll method', function () {
        expect(JobRoleAPI.getAll).toHaveBeenCalledWith('HrJobRoles',
          filters, pagination, sort, additionalParams, undefined, cache);
      });
    });

    describe('find()', function () {
      var result;
      var jobRoleID = '2';

      beforeEach(function (done) {
        spyOn(JobRoleAPI, 'sendGET').and.returnValue($q.resolve({
          values: JobRoleAPIMock.mockedJobRoles.list
        }));

        JobRoleAPI.find(jobRoleID)
          .then(function (_result_) {
            result = _result_;
          })
          .finally(done);

        $rootScope.$digest();
      });

      it('returns a job role', function () {
        expect(result).toEqual(JobRoleAPIMock.mockedJobRoles.list[0]);
      });

      it('calls sendGET method', function () {
        expect(JobRoleAPI.sendGET).toHaveBeenCalledWith('HRJobRole', 'get', {id: '' + jobRoleID}, false);
      });
    });
  });
});
