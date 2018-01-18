/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/before-hash-query-params.service'
  ], function () {
    'use strict';

    describe('$beforeHashQueryParams', function () {
      var $beforeHashQueryParams, testMap;

      beforeEach(module('common.services'));
      beforeEach(inject(function (_$beforeHashQueryParams_) {
        $beforeHashQueryParams = _$beforeHashQueryParams_;
        testMap = [
          // url, expected query params
          ['/civicrm/contact/view', { }],
          ['/civicrm/contact/view/?reset=1', { reset: '1' }],
          ['/civicrm/contact/view/?reset=1&cid=4', { reset: '1', cid: '4' }],
          [
            '/civicrm/contact/view/?reset=1&cid=4&selectedChild=civitasks#/',
            { reset: '1', cid: '4', selectedChild: 'civitasks' }
          ],
          [
            '/civicrm/contact/view/?reset=1&cid=4&selectedChild=civitasks#/tasks',
            { reset: '1', cid: '4', selectedChild: 'civitasks' }
          ],
          [
            '/civicrm/contact/view/?reset=1&cid=4&selectedChild',
            { reset: '1', cid: '4', selectedChild: null }
          ],
          [
            '/civicrm/contact/view/?reset=1&cid=4&',
            { reset: '1', cid: '4' }
          ],
          [
            '/civicrm/contact/view/?reset=1&cid=4&foo=',
            { reset: '1', cid: '4', foo: '' }
          ]
        ];
      }));

      describe('$beforeHashQueryParams.parse()', function () {
        it('parses the given url correctly', function () {
          testMap.forEach(function (testCase) {
            expect($beforeHashQueryParams.parse(testCase[0])).toEqual(testCase[1]);
          });
        });
      });
    });
  });
})(CRM);
