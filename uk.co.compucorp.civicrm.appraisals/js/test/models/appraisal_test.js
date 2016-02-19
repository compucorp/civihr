define([
    'common/angularMocks',
    'common/mocks/services/api/appraisal-mock',
    'appraisals/app',
    'mocks/models/instances/appraisal-instance'
], function () {
    'use strict';

    describe('Appraisal', function () {
        var $provide, $rootScope, Appraisal, AppraisalMock, AppraisalInstanceMock,
            appraisalAPI, appraisals;

        beforeEach(function () {
            module('appraisals', 'appraisals.mocks', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject(['api.appraisal.mock', function (_appraisalAPIMock_) {
                $provide.value('api.appraisal', _appraisalAPIMock_);
            }]);
        });

        beforeEach(inject([
            '$rootScope', 'Appraisal', 'AppraisalInstanceMock', 'api.appraisal',
            function (_$rootScope_, _Appraisal_, _AppraisalInstanceMock_, _appraisalAPI_) {
                $rootScope = _$rootScope_;

                Appraisal = _Appraisal_;
                AppraisalInstanceMock = _AppraisalInstanceMock_;
                appraisalAPI = _appraisalAPI_;

                appraisals = _appraisalAPI_.mockedAppraisals();
            }
        ]));

        describe('all()', function () {
            describe('instances', function () {
                it('returns a list of model instances', function () {

                });
            });

            describe('when called without arguments', function () {
                it('returns all appraisals', function () {

                });
            });

            describe('when called with filters', function () {
                it('returns only the appraisals that match the filters', function () {

                });
            });

            describe('when called with pagination', function () {
                it('can paginate the list', function () {

                });
            });
        });

        describe('find()', function () {
            var promise, appraisal;

            beforeEach(function () {
                appraisal = appraisals.list[1];
                promise = Appraisal.find(appraisal.id);
            });

            it('finds an appraisal by id', function (done) {
                promise.then(function (found) {
                    expect(appraisalAPI.find).toHaveBeenCalledWith(appraisal.id);
                    expect(found.id).toEqual(appraisal.id);
                    expect(found.appraisal_cycle_id).toEqual(found.appraisal_cycle_id);
                })
                .finally(done) && $rootScope.$digest();
            });

            it('returns a model instance', function (done) {
                promise.then(function (found) {
                    expect(AppraisalInstanceMock.isInstance(found)).toBe(true);
                })
                .finally(done) && $rootScope.$digest();
            });
        });
    });
});
