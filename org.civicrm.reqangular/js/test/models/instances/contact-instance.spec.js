/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/mocks/services/api/contact-mock',
  'common/models/instances/contact-instance'
], function (_) {
  'use strict';

  describe('ContactInstance', function () {
    var $provide, $rootScope, ContactInstance, ModelInstance, ContactAPI;

    beforeEach(function () {
      module('common.models.instances', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject(['api.contact.mock', function (contactAPIMock) {
        $provide.value('api.contact', contactAPIMock);
      }]);
    });

    beforeEach(inject(['$rootScope', 'api.contact', 'ContactInstance', 'ModelInstance',
      function (_$rootScope_, _ContactAPI_, _ContactInstance_, _ModelInstance_) {
        $rootScope = _$rootScope_;
        ContactInstance = _ContactInstance_;
        ModelInstance = _ModelInstance_;
        ContactAPI = _ContactAPI_;
      }
    ]));

    it('inherits from ModelInstance', function () {
      expect(_.functions(ContactInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('checkIfSelfLeaveApprover()', function () {
      var callCheckIfSelfLeaveApprover, result;

      beforeEach(function () {
        callCheckIfSelfLeaveApprover = function () {
          ContactInstance.checkIfSelfLeaveApprover()
            .then(function (_result_) {
              result = _result_;
            });
          $rootScope.$digest();
        };
      });

      describe('when leave managees of the contact contain the contact', function () {
        beforeEach(function () {
          ContactInstance.id = '1';

          callCheckIfSelfLeaveApprover();
        });

        it('identifies the contact as a self-approver', function () {
          expect(result).toBe(true);
        });
      });

      describe('when leave managees of the contact do not contain the contact', function () {
        beforeEach(function () {
          ContactInstance.id = '1010011010';

          callCheckIfSelfLeaveApprover();
        });

        it('does not identify the contact as a self-approver', function () {
          expect(result).toBe(false);
        });
      });
    });

    describe('leaveManagees()', function () {
      var params = { key: 'someval' };

      beforeEach(function () {
        ContactInstance.id = '101';

        spyOn(ContactAPI, 'leaveManagees');
        ContactInstance.leaveManagees(params);
      });

      it('calls leaveManagees of Contact API', function () {
        expect(ContactAPI.leaveManagees).toHaveBeenCalledWith('101', params);
      });
    });
  });
});
