define([
    'common/angularMocks',
    'appraisals/app'
], function () {
    'use strict';

    describe('BasicModalCtrl', function () {
        var ctrl;
        var fakeModalInstance = {
            dismiss: function() {}
        };

        beforeEach(module('appraisals'));
        beforeEach(inject(function ($controller, _$modal_) {
            var $modal = _$modal_;

            spyOn($modal, 'open').and.returnValue(fakeModalInstance);
            spyOn(fakeModalInstance, 'dismiss');

            ctrl = $controller('BasicModalCtrl', {
                $modalInstance: $modal.open()
            });
        }));

        describe('on close', function () {
            beforeEach(function () {
                ctrl.cancel();
            });

            it('calls modal instance dismiss()', function () {
                expect(fakeModalInstance.dismiss).toHaveBeenCalledWith('cancel');
            });
        });
    });
})
