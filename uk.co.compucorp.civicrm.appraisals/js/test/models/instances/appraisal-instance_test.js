define([
    'common/lodash',
    'common/angularMocks',
    'appraisals/app'
], function (_) {
    'use strict';

    describe('AppraisalInstance', function () {
        var AppraisalInstance, ModelInstance;

        beforeEach(module('appraisals'));
        beforeEach(inject(function (_AppraisalInstance_, _ModelInstance_) {
            AppraisalInstance = _AppraisalInstance_;
            ModelInstance = _ModelInstance_;
        }));

        it('inherits from ModelInstance', function () {
            expect(_.functions(AppraisalInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
        });
    });
});
