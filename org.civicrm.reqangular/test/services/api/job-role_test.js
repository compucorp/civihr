define([
  'common/angularMocks',
  'common/services/api/job-role',
  'common/mocks/services/api/job-role-mock'
], function () {
  'use strict';

  describe("api.job-role", function () {
    var JobRoleAPI,
      $rootScope,
      JobRoleAPIMock,
      $q;

    beforeEach(module('common.apis', 'common.mocks'));

    beforeEach(inject(['api.job-role', 'api.job-role.mock', '$rootScope', '$q', function (_JobRoleAPI_, _JobRoleAPIMock_, _$rootScope_, _$q_) {
      JobRoleAPI = _JobRoleAPI_;
      JobRoleAPIMock = _JobRoleAPIMock_;
      $rootScope = _$rootScope_;
      $q = _$q_;
    }]));

    it("has expected interface", function () {
      expect(Object.keys(JobRoleAPI)).toContain("all");
      expect(Object.keys(JobRoleAPI)).toContain("find");
    });

    describe("all()", function () {
      var jobRoleApiPromise,
        defer;

      beforeEach(function () {
        defer = $q.defer();
        spyOn(JobRoleAPI, 'getAll').and.returnValue(defer.promise);
      });

      beforeEach(function () {
        jobRoleApiPromise = JobRoleAPI.all(jasmine.any(Object), jasmine.any(Object), jasmine.any(String), jasmine.any(Object));
        defer.resolve(JobRoleAPIMock.mockedJobRoles);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it("returns all the job roles", function () {
        jobRoleApiPromise.then(function (result) {
          expect(result).toEqual(JobRoleAPIMock.mockedJobRoles);
        });
      });

      it("calls getAll method", function () {
        expect(JobRoleAPI.getAll).toHaveBeenCalledWith('HRJobRole', jasmine.any(Object), jasmine.any(Object), jasmine.any(String), jasmine.any(Object));
      });
    });

    describe("find()", function () {
      var jobRoleApiPromise,
        defer,
        mockID = '2';

      beforeEach(function () {
        defer = $q.defer();
        spyOn(JobRoleAPI, 'sendGET').and.returnValue(defer.promise);
      });

      beforeEach(function () {
        jobRoleApiPromise = JobRoleAPI.find(mockID);
        defer.resolve({
          values: JobRoleAPIMock.mockedJobRoles.list
        });
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it("returns a job role", function () {
        jobRoleApiPromise.then(function (result) {
          expect(result).toEqual(JobRoleAPIMock.mockedJobRoles.list[0]);
        });
      });

      it("calls sendGET method", function () {
        expect(JobRoleAPI.sendGET).toHaveBeenCalledWith('HRJobRole', 'get', {id: '' + mockID}, false);
      });
    });
  });
});
