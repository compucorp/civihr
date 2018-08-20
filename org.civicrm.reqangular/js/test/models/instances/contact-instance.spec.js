/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/contact.data',
  'common/angularMocks',
  'common/mocks/services/api/contact-mock',
  'common/models/instances/contact-instance'
], function (_, contactData) {
  'use strict';

  describe('ContactInstance', function () {
    var $provide, $q, $rootScope, contact, ContactInstance,
      instance, ModelInstance, ContactAPI;

    beforeEach(function () {
      module('common.models.instances', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject(['api.contact.mock', function (contactAPIMock) {
        $provide.value('api.contact', contactAPIMock);
      }]);
    });

    beforeEach(inject(['$q', '$rootScope', 'api.contact', 'ContactInstance', 'ModelInstance',
      function (_$q_, _$rootScope_, _ContactAPI_, _ContactInstance_, _ModelInstance_) {
        $q = _$q_;
        $rootScope = _$rootScope_;
        ContactInstance = _ContactInstance_;
        ModelInstance = _ModelInstance_;
        ContactAPI = _ContactAPI_;
      }
    ]));

    beforeEach(function () {
      contact = contactData.all.values[0];
      instance = ContactInstance.init(contact, true);
    });

    it('inherits from ModelInstance', function () {
      expect(_.functions(ContactInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('checkIfSelfLeaveApprover()', function () {
      var callCheckIfSelfLeaveApprover, result;

      beforeEach(function () {
        callCheckIfSelfLeaveApprover = function () {
          instance.checkIfSelfLeaveApprover()
            .then(function (_result_) {
              result = _result_;
            });
          $rootScope.$digest();
        };
      });

      describe('when leave managees of the contact contain the contact', function () {
        beforeEach(function () {
          callCheckIfSelfLeaveApprover();
        });

        it('identifies the contact as a self-approver', function () {
          expect(result).toBe(true);
        });
      });

      describe('when leave managees of the contact do not contain the contact', function () {
        beforeEach(function () {
          spyOn(ContactAPI, 'leaveManagees').and.returnValue($q.resolve(
            _.filter(contactData.all.values, function (leaveManagee) {
              return leaveManagee.id !== contact.id;
            })));
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
        spyOn(ContactAPI, 'leaveManagees');
        instance.leaveManagees(params);
      });

      it('calls leaveManagees of Contact API', function () {
        expect(ContactAPI.leaveManagees).toHaveBeenCalledWith(contact.id, params);
      });
    });
  });
});
