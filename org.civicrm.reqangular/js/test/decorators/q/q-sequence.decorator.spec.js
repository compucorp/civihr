/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/decorators/q/q-sequence.decorator'
], function (angular, $qSequence) {
  'use strict';

  describe('$q.sequence', function () {
    var $rootScope, $q, $provide;

    beforeEach(function () {
      module(function (_$provide_) {
        $provide = _$provide_;
      });
      inject(function () {
        $provide.decorator('$q', $qSequence);
      });
    });

    beforeEach(inject(function (_$rootScope_, _$q_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
    }));

    describe('when wrong params are passed', function () {
      describe('when wrong param type', function () {
        it('throws an exception', function () {
          expect(function () { $q.sequence(); }).toThrow();
          expect(function () { $q.sequence('string'); }).toThrow();
          expect(function () { $q.sequence({}); }).toThrow();
          expect(function () { $q.sequence(20); }).toThrow();
          expect(function () { $q.sequence(false); }).toThrow();
          expect(function () { $q.sequence(null); }).toThrow();
          expect(function () { $q.sequence(undefined); }).toThrow();
        });
      });

      describe('when array items are not functions', function () {
        it('throws an exception', function () {
          expect(function () { $q.sequence(['not a function']); }).toThrow();
          expect(function () { $q.sequence([$q.resolve('value')]); }).toThrow();
        });
      });
    });

    describe('when correct params are passed', function () {
      describe('when an array of wrapped promises is passed', function () {
        describe('when all promises are resolved', function () {
          var promises = [
            function () { return $q.resolve(2); },
            function (res) { return $q.resolve(res + 3); },
            function (res) { return $q.resolve(res + 5); }
          ];
          var expectedResult = 10;
          var result;

          beforeEach(function () {
            $q.sequence(promises).then(function (value) { result = value; });
            $rootScope.$apply();
          });

          it('returns an expected result', function () {
            expect(result).toBe(expectedResult);
          });
        });

        describe('when some promise is rejected', function () {
          var valueOnReject = 'error';
          var promises = [
            function () { return $q.resolve(2); },
            function (res) { return $q.reject(valueOnReject); },
            function (res) { return $q.resolve(10); }
          ];
          var expectedResult = valueOnReject;
          var result;

          beforeEach(function () {
            $q.sequence(promises).then(function (value) {
              result = value;
            }).catch(function () {
              result = valueOnReject;
            });
            $rootScope.$apply();
          });

          it('returns an expected result', function () {
            expect(result).toBe(expectedResult);
          });
        });
      });

      describe('when empty array is passed', function () {
        var promises = [];
        var expectedResult;
        var result;

        beforeEach(function () {
          $q.sequence(promises).then(function (value) { result = value; });
          $rootScope.$apply();
        });

        it('returns an expected result', function () {
          expect(result).toBe(expectedResult);
        });
      });
    });
  });
});
