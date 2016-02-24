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

        describe('init()', function () {
            var instance;

            describe('when initializing with data from the API', function () {
                var attributes = {
                    appraisal_cycle_id: '1',
                    is_current: '1',
                    meeting_completed: '0',
                    approved_by_employee: '1',
                    self_appraisal_due: '2016-01-01',
                    manager_appraisal_due: '2016-02-02',
                    grade_due: '2016-03-03',
                    created_date: '2015-01-01',
                };

                beforeEach(function () {
                    instance = AppraisalInstance.init(attributes, true);
                });

                it('normalizes the data', function () {
                    expect(instance.appraisal_cycle_id).toBe(attributes.appraisal_cycle_id);
                    expect(instance.is_current).toBe(true);
                    expect(instance.meeting_completed).toBe(false);
                    expect(instance.approved_by_employee).toBe(true);
                    expect(instance.created_date).toBe('01/01/2015');
                    expect(instance.self_appraisal_due).toBe('01/01/2016');
                    expect(instance.manager_appraisal_due).toBe('02/02/2016');
                    expect(instance.grade_due).toBe('03/03/2016');
                });
            });
        });

        describe('toAPI()', function () {
            var toAPIData;

            beforeEach(function () {
                toAPIData = AppraisalInstance.init({
                    appraisal_cycle_id: '1',
                    is_current: true,
                    meeting_completed: false,
                    approved_by_employee: true,
                    self_appraisal_due: '21/01/2016',
                    manager_appraisal_due: '13/02/2016',
                    grade_due: '31/03/2016',
                    created_date: '20/05/2015',
                }).toAPI();
            });

            it('formats the dates in the YYYY-MM-DD format', function () {
                expect(toAPIData.created_date).toBe('2015-05-20');
                expect(toAPIData.self_appraisal_due).toBe('2016-01-21');
                expect(toAPIData.manager_appraisal_due).toBe('2016-02-13');
                expect(toAPIData.grade_due).toBe('2016-03-31');
            });

            it('converts booleans to string int', function () {
                expect(toAPIData.is_current).toBe('1');
                expect(toAPIData.meeting_completed).toBe('0');
                expect(toAPIData.approved_by_employee).toBe('1');
            });
        });
    });
});
