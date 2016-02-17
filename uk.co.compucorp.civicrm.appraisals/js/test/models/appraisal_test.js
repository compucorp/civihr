define([
    'common/angularMocks',
    'appraisals/app',
    'mocks/models/appraisal',
], function () {
    'use strict';

    describe('Appraisal', function () {
        var Appraisal, AppraisalMock;

        beforeEach(module('appraisals', 'appraisals.mocks'));
        beforeEach(inject(function (_Appraisal_, _AppraisalMock_) {
            Appraisal = _Appraisal_;
            AppraisalMock = _AppraisalMock_;
        }));

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
            it('finds an appraisal by id', function () {

            });

            it('returns a model instance', function () {

            });
        });
    });
});
