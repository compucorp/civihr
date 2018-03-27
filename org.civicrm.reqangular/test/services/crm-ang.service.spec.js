/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/crm-ang.service'
  ], function () {
    'use strict';

    describe('crmAngService', function () {
      var crmAngService;

      beforeEach(module('common.services'));
      beforeEach(inject(function (_crmAngService_) {
        crmAngService = _crmAngService_;
      }));

      describe('loadForm()', function () {
        var result;
        var url = '/some-url';
        var options = { optionKey: 'optionValue' };
        var originalResult = 'jQuery object';

        beforeEach(function () {
          CRM.loadForm = function () {};

          spyOn(CRM, 'loadForm').and.returnValue(originalResult);

          result = crmAngService.loadForm(url, options);
        });

        it('calls CRM.loadForm with according parameters', function () {
          expect(CRM.loadForm).toHaveBeenCalledWith(url, options);
        });

        it('returns the same as the original method', function () {
          expect(result).toBe(originalResult);
        });
      });
    });
  });
})(CRM);
