/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/modules/shared-settings'
], function () {
  'use strict';

  describe('shared-settings', function () {
    var sharedSettings;

    beforeEach(module('leave-absences.settings'));
    beforeEach(inject(['shared-settings', function (_sharedSettings_) {
      sharedSettings = _sharedSettings_;
    }]));

    describe('drupal permissions', function () {
      it('contains the list of the SSP permissions', function () {
        expect(sharedSettings.permissions.admin.access).toBeDefined();
        expect(sharedSettings.permissions.admin.administer).toBeDefined();
      });

      it('contains the list of the admin permissions', function () {
        expect(sharedSettings.permissions.ssp.access).toBeDefined();
        expect(sharedSettings.permissions.ssp.manage).toBeDefined();
      });
    });

    describe('file uploader', function () {
      it('contains the attachment token', function () {
        expect(sharedSettings.attachmentToken).toBeDefined();
      });

      it('contains the queue limit setting', function () {
        expect(sharedSettings.fileUploader.queueLimit).toBeDefined();
      });

      it('contains the list of allowed mime types', function () {
        expect(sharedSettings.fileUploader.mimeTypesMap).toEqual(jasmine.any(Object));
      });
    });

    describe('leave request statuses', function () {
      it('contains the list of possible statuses a leave request can be in', function () {
        expect(sharedSettings.statusNames).toBeDefined();
      });
    });
  });
});
