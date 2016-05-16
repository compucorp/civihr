define([
    'common/angular',
    'common/lodash',
    'common/moment',
    'common/angularMocks',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/api/appraisal-cycle-mock',
    'appraisals/app',
    'mocks/models/instances/appraisal-cycle-instance'
], function (angular, _, moment) {
    'use strict';

    describe('AppraisalCycle', function () {
        var $q, $provide, $rootScope, AppraisalCycle, AppraisalCycleInstance,
            appraisalCycleAPI, cycles;

        beforeEach(function () {
            module('appraisals', 'appraisals.mocks', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject([
                'api.appraisal-cycle.mock', 'HR_settingsMock',
                function (_appraisalCycleAPIMock_, HR_settingsMock) {
                    $provide.value('api.appraisal-cycle', _appraisalCycleAPIMock_);
                    $provide.value('HR_settings', HR_settingsMock);
                }
            ]);
        });

        beforeEach(inject(['$q', '$rootScope', 'AppraisalCycle',
            'AppraisalCycleInstanceMock', 'api.appraisal-cycle',
            function (_$q_, _$rootScope_, _AppraisalCycle_, _AppraisalCycleInstanceMock_, _appraisalCycleAPI_) {
                $q = _$q_;
                $rootScope = _$rootScope_;
                AppraisalCycle = _AppraisalCycle_;
                AppraisalCycleInstance = _AppraisalCycleInstanceMock_;
                appraisalCycleAPI = _appraisalCycleAPI_;

                appraisalCycleAPI.spyOnMethods();

                cycles = appraisalCycleAPI.mockedCycles();
            }
        ]));

        it('has the expected api', function () {
            expect(Object.keys(AppraisalCycle)).toEqual([
                'active', 'all', 'create', 'find', 'grades', 'statuses',
                'statusOverview', 'total', 'types'
            ]);
        });

        describe('active()', function () {
            var activeCount;

            beforeEach(function () {
                activeCount = cycles.list.filter(function (cycle) {
                    return cycle.cycle_is_active;
                }).length;
            });

            it('returns the active cycles', function (done) {
                AppraisalCycle.active().then(function (count) {
                    expect(appraisalCycleAPI.total).toHaveBeenCalledWith({ cycle_is_active: true });
                    expect(count).toEqual(activeCount);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('statusOverview()', function () {
            describe('API call', function () {
                it('calls the correct API method', function (done) {
                    AppraisalCycle.statusOverview().then(function (overview) {
                        expect(appraisalCycleAPI.statusOverview).toHaveBeenCalled();
                    })
                    .finally(done) && $rootScope.$digest();
                });

                it('passes a date to the API method', function (done) {
                    AppraisalCycle.statusOverview().then(function (overview) {
                        var date = appraisalCycleAPI.statusOverview.calls.argsFor(0)[0].current_date;

                        expect(moment(date, 'YYYY-MM-DD').isValid()).toBe(true);
                    })
                    .finally(done) && $rootScope.$digest();
                });

                describe('current_date argument', function () {
                    var p;

                    describe('when it is not passed', function () {
                        beforeEach(function () {
                            jasmine.clock().mockDate(new Date(2016, 11, 2));

                            p = AppraisalCycle.statusOverview();
                        })

                        it('calls the API method with the actual current date', function (done) {
                            p.then(function (overview) {
                                expect(appraisalCycleAPI.statusOverview).toHaveBeenCalledWith({ current_date: '2016-12-02' });
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when it is passed', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.statusOverview({ current_date: '2015-10-11' });
                        })

                        it('calls the API method with it', function (done) {
                            p.then(function (overview) {
                                expect(appraisalCycleAPI.statusOverview).toHaveBeenCalledWith({ current_date: '2015-10-11' });
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });
                });

                describe('start_date and end_date arguments', function () {
                    var p;

                    describe('when they are not passed', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.statusOverview();
                        })

                        it('calls the API method without them', function (done) {
                            p.then(function (overview) {
                                expect(appraisalCycleAPI.statusOverview).not.toHaveBeenCalledWith(jasmine.objectContaining({
                                    start_date: jasmine.any(String),
                                    end_date: jasmine.any(String)
                                }));
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when they are passed', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.statusOverview({
                                start_date: '2016-03-25',
                                end_date: '2016-05-25',
                            });
                        })

                        it('calls the API method with them', function (done) {
                            p.then(function (overview) {
                                expect(appraisalCycleAPI.statusOverview).toHaveBeenCalledWith(jasmine.objectContaining({
                                    start_date: '2016-03-25',
                                    end_date: '2016-05-25'
                                }));
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });
                });

                describe('cycles_ids argument', function () {
                    var p;

                    describe('when it is not passed', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.statusOverview();
                        })

                        it('calls the API method without it', function (done) {
                            p.then(function (overview) {
                                expect(appraisalCycleAPI.statusOverview).not.toHaveBeenCalledWith(jasmine.objectContaining({
                                    cycles_ids: jasmine.any(String)
                                }));
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when it is passed', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.statusOverview({ cycles_ids: '3543,7654,6363,4534' });
                        })

                        it('calls the API method with it', function (done) {
                            p.then(function (overview) {
                                expect(appraisalCycleAPI.statusOverview).toHaveBeenCalledWith(jasmine.objectContaining({
                                    cycles_ids: '3543,7654,6363,4534'
                                }));
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });
                });
            });

            it('contains the list of steps and the total number of appraisals', function (done) {
                AppraisalCycle.statusOverview().then(function (overview) {
                    expect(Object.keys(overview)).toEqual(['steps', 'totalAppraisalsNumber']);
                    expect(Object.keys(overview.steps).length).toEqual(5);
                    expect(overview.totalAppraisalsNumber).toEqual(85);
                })
                .finally(done) && $rootScope.$digest();
            });

            it('normalizes the steps list', function (done) {
                AppraisalCycle.statusOverview().then(function (overview) {
                    expect(Object.keys(overview.steps)).toEqual(['1', '2', '3', '4', '5'])
                    expect(Object.keys(overview.steps['1'])).toEqual(['name', 'due', 'overdue']);
                })
                .finally(done) && $rootScope.$digest();
            });

            it('retains the data for each step', function (done) {
                AppraisalCycle.statusOverview().then(function (overview) {
                    expect(overview.steps['2'].due).toEqual(10);
                    expect(overview.steps['2'].overdue).toEqual(6);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('grades()', function () {
            it('returns the grades data', function (done) {
                AppraisalCycle.grades().then(function (grades) {
                    expect(appraisalCycleAPI.grades).toHaveBeenCalled();

                    expect(grades.length).toEqual(5);
                    expect(Object.keys(grades[0])).toEqual(['label', 'value']);
                    expect(grades[0].label).toEqual('1');
                    expect(grades[0].value).toEqual(30);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('types()', function () {
            it('returns the appraisal cycle types', function (done) {
                AppraisalCycle.types().then(function (types) {
                    expect(appraisalCycleAPI.types).toHaveBeenCalled();

                    expect(types.length).toEqual(3);
                    expect(types).toEqual([
                        { label: 'type 1', value: '1' },
                        { label: 'type 2', value: '2' },
                        { label: 'type 3', value: '3' }
                    ]);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('statuses()', function () {
            it('returns the appraisal cycle statuses', function (done) {
                AppraisalCycle.statuses().then(function (statuses) {
                    expect(appraisalCycleAPI.statuses).toHaveBeenCalled();

                    expect(statuses.length).toEqual(2);
                    expect(statuses).toEqual([
                        { label: 'status 1', value: '1' },
                        { label: 'status 2', value: '2' }
                    ]);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('create()', function () {
            var newCycle = {
                name: 'new cycle',
                type: 'type 4',
                cycle_start_date: '01/01/2014',
                cycle_end_date: '01/01/2015'
            };

            it('creates a new appraisal cycle', function (done) {
                AppraisalCycle.create(newCycle).then(function () {
                    expect(appraisalCycleAPI.create).toHaveBeenCalled();
                })
                .finally(done) && $rootScope.$digest();
            });

            it('sanitizes the data via instance before calling the api', function (done) {
                var sanitizedData = AppraisalCycleInstance.init(newCycle).toAPI();

                AppraisalCycle.create(newCycle).then(function () {
                    expect(appraisalCycleAPI.create).toHaveBeenCalledWith(sanitizedData);
                })
                .finally(done) && $rootScope.$digest();
            });

            it('returns an instance of the model', function (done) {
                AppraisalCycle.create(newCycle).then(function (savedCycle) {
                    expect(AppraisalCycleInstance.isInstance(savedCycle)).toBe(true);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('all()', function () {
            describe('instances', function () {
                it('returns a list of model instances', function (done) {
                    AppraisalCycle.all().then(function (cycles) {
                        expect(cycles.list.every(function (cycle) {
                            return AppraisalCycleInstance.isInstance(cycle);
                        })).toBe(true);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when called without arguments', function () {
                it('returns all appraisal cycles', function (done) {
                    AppraisalCycle.all().then(function (cycles) {
                        expect(appraisalCycleAPI.all).toHaveBeenCalled();
                        expect(cycles.list.length).toEqual(10);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when called with filters', function () {
                var p;

                describe('falsey values', function () {
                    beforeEach(function () {
                        p = AppraisalCycle.all({
                            filter_1: 'a non-empty string',
                            filter_2: '',
                            filter_3: 456,
                            filter_4: 0,
                            filter_5: undefined,
                            filter_6: { foo: 'foo' },
                            filter_7: null,
                            filter_8: {},
                            filter_9: false
                        });
                    });

                    it('skips falsey (null, undefined, empty string), except for 0', function (done) {
                        p.then(function () {
                            expect(appraisalCycleAPI.all).toHaveBeenCalledWith({
                                filter_1: 'a non-empty string',
                                filter_3: 456,
                                filter_4: 0,
                                filter_6: { foo: 'foo' },
                                filter_8: {},
                                filter_9: false
                            }, undefined);
                        })
                        .finally(done) && $rootScope.$digest();
                    });
                });

                describe('simple filter', function () {
                    var filtered = null;
                    var typeFilter = '1';

                    beforeEach(function () {
                        filtered = cycles.list.filter(function (cycle) {
                            return cycle.cycle_type_id === typeFilter;
                        });
                    });

                    it('can filter the appraisal cycles list', function (done) {
                        AppraisalCycle.all({
                            cycle_type_id: typeFilter
                        }).then(function (cycles) {
                            expect(appraisalCycleAPI.all).toHaveBeenCalledWith({ cycle_type_id: typeFilter }, undefined);
                            expect(cycles.list.length).toEqual(filtered.length);
                        })
                        .finally(done) && $rootScope.$digest();
                    });
                });

                describe('date filters', function () {
                    describe('when searching only by "from" date', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.all({
                                cycle_start_date: { from: '01/09/2016' }
                            });
                        });

                        it('provides the API with the correct filter values', function (done) {
                            p.then(function () {
                                expect(appraisalCycleAPI.all).toHaveBeenCalledWith({
                                    cycle_start_date: { '>=': '2016-09-01' }
                                }, undefined);
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when searching only by "to" date', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.all({
                                cycle_grade_due: { to: '22/10/2016' }
                            });
                        })

                        it('provides the API with the correct filter values', function (done) {
                            p.then(function () {
                                expect(appraisalCycleAPI.all).toHaveBeenCalledWith({
                                    cycle_grade_due: { '<=': '2016-10-22' }
                                }, undefined);
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when searching both by "from" and "to" date', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.all({
                                cycle_manager_appraisal_due: {
                                    from: '01/09/2016',
                                    to: '22/10/2016'
                                }
                            });
                        })

                        it('provides the API with the correct filter values', function (done) {
                            p.then(function () {
                                expect(appraisalCycleAPI.all).toHaveBeenCalledWith({
                                    cycle_manager_appraisal_due: {
                                        'BETWEEN': ['2016-09-01', '2016-10-22']
                                    }
                                }, undefined);
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });
                });
            });

            describe('when called with pagination', function () {
                var pagination = { page: 3, size: 2 };

                it('can paginate the appraisla cycles list', function (done) {
                    AppraisalCycle.all(null, pagination).then(function (cycles) {
                        expect(appraisalCycleAPI.all).toHaveBeenCalledWith(null, pagination);
                        expect(cycles.list.length).toEqual(2);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });
        });

        describe('find()', function () {
            var targetId = '4217';

            it('finds a cycle by id', function (done) {
                AppraisalCycle.find(targetId).then(function (cycle) {
                    expect(appraisalCycleAPI.find).toHaveBeenCalledWith(targetId);
                    expect(cycle.id).toBe('4217');
                    expect(cycle.cycle_type_id).toBe('2');
                })
                .finally(done) && $rootScope.$digest();
            });

            it('returns an instance of the model', function (done) {
                AppraisalCycle.find(targetId).then(function (cycle) {
                    expect(AppraisalCycleInstance.isInstance(cycle)).toBe(true);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('total()', function () {
            it('gets the total number of cycles', function (done) {
                AppraisalCycle.total().then(function (total) {
                    expect(appraisalCycleAPI.total).toHaveBeenCalled();
                    expect(total).toBe(cycles.list.length);
                })
                .finally(done) && $rootScope.$digest();
            });
        });
    });
});
