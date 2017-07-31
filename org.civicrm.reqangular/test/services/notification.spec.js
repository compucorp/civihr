/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/notification.service'
  ], function () {
    'use strict';

    describe('notificationService', function () {
      var title, body, options, notificationService;

      beforeEach(module('common.services'));
      beforeEach(inject(function (_notificationService_) {
        notificationService = _notificationService_;
        title = 'Sample title';
        body = 'Sample Body text';
        options = {
          unique: true,
          expires: 0
        };

        spyOn(CRM, 'alert').and.returnValue({});
      }));

      describe('alert()', function () {
        beforeEach(function () {
          notificationService.alert(title, body, options);
        });

        it('calls CRM.alert to display alert notification', function () {
          expect(CRM.alert).toHaveBeenCalledWith(body, title, 'alert', options);
        });
      });

      describe('success()', function () {
        beforeEach(function () {
          notificationService.success(title, body, options);
        });

        it('calls CRM.alert to display success notification', function () {
          expect(CRM.alert).toHaveBeenCalledWith(body, title, 'success', options);
        });
      });

      describe('info()', function () {
        beforeEach(function () {
          notificationService.info(title, body, options);
        });

        it('calls CRM.alert to display info notification', function () {
          expect(CRM.alert).toHaveBeenCalledWith(body, title, 'info', options);
        });
      });

      describe('error()', function () {
        beforeEach(function () {
          notificationService.error(title, body, options);
        });

        it('calls CRM.alert to display error notification', function () {
          expect(CRM.alert).toHaveBeenCalledWith(body, title, 'error', options);
        });
      });
    });
  });
})(CRM);
