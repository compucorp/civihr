/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/services/before-hash-query-params.service'
], function () {
  'use strict';

  describe('beforeHashQueryParams', function () {
    var beforeHashQueryParams;

    beforeEach(module('common.services'));
    beforeEach(inject(function (_beforeHashQueryParams_) {
      beforeHashQueryParams = _beforeHashQueryParams_;
    }));

    describe('when there is no query string in the url', function () {
      it('returns an empty object', function () {
        expect(beforeHashQueryParams.parse('/civicrm/contact/view')).toEqual({});
      });
    });

    describe('when there is a query string, but after the hash', function () {
      it('ignores the query string and returns an empty object', function () {
        expect(beforeHashQueryParams.parse('/civicrm/contact/view#/?foo=bar')).toEqual({});
      });
    });

    describe('when there is a query string that trails with an ampersand', function () {
      it('parses the query string ignoring the ampersand', function () {
        expect(beforeHashQueryParams.parse('/civicrm/contact/view?foo=bar&')).toEqual({ 'foo': 'bar' });
      });
    });

    describe('when there is a query string that trails with a key without value', function () {
      it('parses the query string and assigns a null value to the key', function () {
        expect(beforeHashQueryParams.parse('/civicrm/contact/view?foo=bar&baz')).toEqual({ 'foo': 'bar', 'baz': null });
        expect(beforeHashQueryParams.parse('/civicrm/contact/view?foo=bar&baz=')).toEqual({ 'foo': 'bar', 'baz': null });
      });
    });

    describe('when there are query strings without value', function () {
      it('parses the query string and assigns a null value to respective key', function () {
        expect(beforeHashQueryParams.parse('/civicrm/contact/view?foo&baz')).toEqual({ 'foo': null, 'baz': null });
      });
    });
  });
});
