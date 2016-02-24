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

                appraisalAPI.spyOnMethods();
                appraisals = _appraisalAPI_.mockedAppraisals();
            }
        ]));

        it('has the expected api', function () {
            expect(Object.keys(Appraisal)).toEqual(['all', 'find', 'overdue']);
        });

        describe('all()', function () {
            describe('instances', function () {
                it('returns a list of model instances', function (done) {
                    Appraisal.all().then(function (response) {
                        expect(response.list.every(function (appraisal) {
                            return AppraisalInstanceMock.isInstance(appraisal);
                        })).toBe(true);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when called without arguments', function () {
                it('returns all appraisals', function (done) {
                    Appraisal.all().then(function (response) {
                        expect(appraisalAPI.all).toHaveBeenCalled();
                        expect(response.list.length).toEqual(appraisals.list.length);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            xdescribe('when called with filters', function () {
                // TO DO
            });

            describe('when called with pagination', function () {
                var pagination = { page: 3, size: 2 };

                it('can paginate the appraisals list', function (done) {
                    Appraisal.all(null, pagination).then(function (response) {
                        expect(appraisalAPI.all).toHaveBeenCalledWith(null, pagination);
                        expect(response.list.length).toEqual(2);
                    })
                    .finally(done) && $rootScope.$digest();
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

        describe('overdue()', function () {
            var promise;

            beforeEach(function () {
                promise = Appraisal.overdue();
            });

            it('calls the equivalent api method', function (done) {
                promise.then(function (response) {
                    expect(appraisalAPI.overdue).toHaveBeenCalled();
                })
                .finally(done) && $rootScope.$digest();
            });

            it('returns a list of model instances', function (done) {
                promise.then(function (response) {
                    expect(response.list.every(function (appraisal) {
                        return AppraisalInstanceMock.isInstance(appraisal);
                    })).toBe(true);
                })
                .finally(done) && $rootScope.$digest();
            });
        });
    });
});
