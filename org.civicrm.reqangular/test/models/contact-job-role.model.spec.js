/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/models/contact-job-role.model',
  'common/mocks/services/hr-settings-mock',
  'common/mocks/services/api/contact-job-role-api.api.mock',
  'common/mocks/instances/contact-job-role-instance.instance.mock'
], function () {
  'use strict';

  describe('ContactJobRole', function () {
    var $provide, $rootScope, ContactJobRole, ContactJobRoleInstanceMock,
      ContactJobRoleAPI;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject([
        'ContactJobRoleAPIMock', 'HR_settingsMock',
        function (_ContactJobRoleAPIMock_, HRSettingsMock) {
          $provide.value('ContactJobRoleAPI', _ContactJobRoleAPIMock_);
          $provide.value('HR_settings', HRSettingsMock);
        }
      ]);
    });

    beforeEach(inject([
      '$rootScope', 'ContactJobRole', 'ContactJobRoleInstanceMock',
      'ContactJobRoleAPI',
      function (_$rootScope_, _ContactJobRole_, _ContactJobRoleInstanceMock_,
        _ContactJobRoleAPI_) {
        $rootScope = _$rootScope_;
        ContactJobRole = _ContactJobRole_;
        ContactJobRoleInstanceMock = _ContactJobRoleInstanceMock_;
        ContactJobRoleAPI = _ContactJobRoleAPI_;

        ContactJobRoleAPI.spyOnMethods();
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(ContactJobRole)).toEqual(['all']);
    });

    describe('all()', function () {
      var resultsAreInstances;

      beforeEach(function () {
        ContactJobRole.all().then(function (response) {
          resultsAreInstances = response.every(function (contactJobRole) {
            return ContactJobRoleInstanceMock.isInstance(contactJobRole);
          });
        });
        $rootScope.$digest();
      });

      it('returns a list of model instances', function () {
        expect(resultsAreInstances).toBe(true);
      });
    });
  });
});
