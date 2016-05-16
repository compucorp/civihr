define([
    'common/angularMocks',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/api/appraisal-mock',
    'appraisals/app',
    'mocks/models/instances/appraisal-instance'
], function () {
    'use strict';

    describe('Appraisal', function () {
        var $log, $provide, $rootScope, Appraisal, AppraisalMock, AppraisalInstanceMock,
            appraisalAPI, appraisals;

        beforeEach(function () {
            module('appraisals', 'appraisals.mocks', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject([
                'api.appraisal.mock','HR_settingsMock',
                function (_appraisalAPIMock_, HR_settingsMock) {
                    $provide.value('api.appraisal', _appraisalAPIMock_);
                    $provide.value('HR_settings', HR_settingsMock);
                }
            ]);
        });

        beforeEach(inject([
            '$log', '$rootScope', 'Appraisal', 'AppraisalInstanceMock', 'api.appraisal',
            function (_$log_, _$rootScope_, _Appraisal_, _AppraisalInstanceMock_, _appraisalAPI_) {
                $log = _$log_;
                $rootScope = _$rootScope_;

                Appraisal = _Appraisal_;
                AppraisalInstanceMock = _AppraisalInstanceMock_;
                appraisalAPI = _appraisalAPI_;

                appraisalAPI.spyOnMethods();
                appraisals = _appraisalAPI_.mockedAppraisals();
            }
        ]));

        it('has the expected api', function () {
            expect(Object.keys(Appraisal)).toEqual(['all', 'create', 'find', 'overdue']);
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

        describe('create()', function () {
            var newAppraisal = { contact_id: '1', appraisal_cycle_id: '2', status_id: '5' };

            beforeEach(function () {
                spyOn($log, 'error');
            });

            describe('when contact id is missing', function () {
                beforeEach(function () {
                    Appraisal.create(_.omit(newAppraisal, 'contact_id'));
                })

                it('throws an error', function () {
                    expect($log.error).toHaveBeenCalledWith('ERR_APPRAISAL_CREATE: CONTACT ID MISSING');
                });
            });

            describe('when appraisal cycle id is missing', function () {
                beforeEach(function () {
                    Appraisal.create(_.omit(newAppraisal, 'appraisal_cycle_id'));
                })

                it('throws an error', function () {
                    expect($log.error).toHaveBeenCalledWith('ERR_APPRAISAL_CREATE: APPRAISAL CYCLE ID MISSING');
                });
            });

            describe('when mandatory params are present', function () {
                var promise;

                beforeEach(function () {
                    promise = Appraisal.create(newAppraisal);
                });

                it('creates a new appraisal', function (done) {
                    promise.then(function () {
                        expect(appraisalAPI.create).toHaveBeenCalled();
                    })
                    .finally(done) && $rootScope.$digest();
                });

                it('returns an instance of the model', function (done) {
                    promise.then(function (savedAppraisal) {
                        expect(AppraisalInstanceMock.isInstance(savedAppraisal)).toBe(true);
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
