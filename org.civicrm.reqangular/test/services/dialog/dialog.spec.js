/* eslint-env amd, jasmine */

define([
  'common/angularBootstrap',
  'common/angularMocks',
  'common/services/dialog/dialog'
], function () {
  'use strict';

  describe('dialog', function () {
    var dialog, modal, $templateCache;

    beforeEach(module('common.services', 'ui.bootstrap'));

    beforeEach(inject(['dialog', '$uibModal', '$q', '$templateCache',
      function (_dialog_, _modal_, _$q_, _$templateCache_) {
        dialog = _dialog_;
        modal = _modal_;
        $templateCache = _$templateCache_;
      }
    ]));

    describe('open()', function () {
      var options;

      beforeEach(function () {
        spyOn(modal, 'open').and.callThrough();
        spyOn($templateCache, 'get').and.returnValue('/some-template');
      });

      describe('when called with incorrect options', function () {
        beforeEach(function () {
          options = 'For example, a String instead of Object';

          dialog.open(options);
        });

        it('does not open a modal', function () {
          expect(modal.open).not.toHaveBeenCalled();
        });
      });

      describe('when called with correct options', function () {
        beforeEach(function () {
          options = {
            title: 'Are you sure?',
            onConfirm: function () {}
          };

          dialog.open(options);
        });

        it('opens a modal with correct parameters', function () {
          expect(modal.open).toHaveBeenCalledWith(jasmine.objectContaining({
            size: 'sm',
            appendTo: jasmine.objectContaining({ length: 1 }),
            controller: 'DialogController'
          }));
        });

        it('passes correct parameters to the controller', function () {
          expect(modal.open.calls.mostRecent().args[0].resolve.props())
            .toEqual(options);
        });
      });
    });
  });
});
