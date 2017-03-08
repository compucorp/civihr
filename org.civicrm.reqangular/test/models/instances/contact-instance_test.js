define([
    'common/lodash',
    'common/angularMocks',
    'common/models/instances/contact-instance',
], function (_) {
    'use strict';

    describe('ContactInstance', function () {
        var ContactInstance, ModelInstance, ContactAPI;

        beforeEach(module('common.models.instances'));
        beforeEach(inject(['api.contact', 'ContactInstance', 'ModelInstance',
        function (_ContactAPI_, _ContactInstance_, _ModelInstance_) {
          ContactInstance = _ContactInstance_;
          ModelInstance = _ModelInstance_;
          ContactAPI = _ContactAPI_;
        }]));

        beforeEach(function () {
          spyOn(ContactAPI, 'leaveManagees');
        });

        it('inherits from ModelInstance', function () {
            expect(_.functions(ContactInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
        });

        describe('leaveManagees()', function() {
          beforeEach(function () {
            ContactInstance.id = '101';
            ContactInstance.leaveManagees();
          });

          it('calls leaveManagees of Contact API', function () {
            expect(ContactAPI.leaveManagees).toHaveBeenCalledWith('101');
          });
        });
    });
});
