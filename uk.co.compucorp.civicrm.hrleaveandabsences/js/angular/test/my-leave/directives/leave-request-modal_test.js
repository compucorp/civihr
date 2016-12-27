(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular) {
    'use strict';

    describe('leaveRequestModal', function () {
      var $compile, $log, $rootScope, component, controller, directive, $uibModal, $controllerScope;

      beforeEach(module('leave-absences.templates', 'my-leave'));

      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$uibModal_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $uibModal = _$uibModal_;
        spyOn($log, 'debug');

        compileDirective();
      }));

      it('is called', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('dialog is open', function () {

        beforeEach(function () {
          spyOn($uibModal, 'open');
          directive.triggerHandler('click');
        });

        it('opens dependent popup', function () {
          expect($uibModal.open).toHaveBeenCalled();
        });

        it('has expected markup', function () {

        });

        describe('initialize leave request', function () {

          it('with absence periods', function () {

          });

          it('with absence types', function () {

          });

          it('with work pattern', function () {

          });

          it('with balance changes', function () {

          });
        });

        describe('when cancels dialog (clicks on X)', function () {

          beforeEach(function () {
            directive.triggerHandler('click');
            var close = directive.querySelectorAll('.close');
            console.log('close', close);
            close.triggerHandler('click');
            //console.log($controllerScope.vm.modalInstance);
            spyOn($controllerScope.modalInstance, 'close');
            //console.log('$controllerScope.closeModal',$controllerScope.closeModal);
            //$controllerScope.closeModal();
          });

          it('closes model', function () {
            expect($controllerScope.closeModal).toHaveBeenCalled();
          });
        });
      });

      describe('leave absence types', function () {

        describe('view all', function () {

          beforeEach(function () {
            directive.triggerHandler('click');
          });

          it('shows all items', function () {

          });

          it('selects the current period', function () {

          });

        });

        describe('change selection', function () {

          it('selects another type', function () {

          });
        });
      });

      describe('number of days', function () {

        describe('single', function () {

          it('selects single day', function () {

          });

          it('has expected markup', function () {

          });

        });

        describe('multiple', function () {

          it('selects multiple days', function () {

          });

          it('has expected markup', function () {

          });
        });
      });

      describe('calendar', function () {

        describe('from date', function () {

          it('has default as current date', function () {

          });

          it('can change date', function () {

          });

        });

        describe('to date', function () {

          it('has default as current date', function () {

          });

          it('can change date', function () {

          });

        });
      });

      describe('day types', function () {

        describe('from', function () {

          it('has default', function () {

          });

          it('allows changing selection', function () {

          });

        });

        describe('to', function () {

          it('has default', function () {

          });

          it('allows changing selection', function () {

          });

        });
      });

      describe('calculate balance', function () {

        describe('opening', function () {

          it('has default', function () {

          });

        });

        describe('change', function () {

          it('has default', function () {

          });

          describe('from date changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });

          describe('from day type changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });

          describe('to date changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });

          describe('to day type changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });
        });

        describe('details', function () {

          describe('hide', function () {

            it('collapses details part', function () {

            });
          });

          describe('show', function () {

            it('expands details part', function () {

            });

            it('has expected markup', function () {

            });

            describe('pagination', function () {

              it('has default page selected', function () {

              });

              it('change selected page', function () {

              });
            });
          });
        });
      });

      describe('user saves leave request', function () {

        it('validates leave request', function () {

        });

        it('updates leave request', function () {

        });
      });

      describe('user cancels selection', function () {

        it('model closes', function () {

        });

        it('back button is clicked', function () {

        });
      });

      /**
       * Creates and compiles the directive
       */
      function compileDirective() {
        $controllerScope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        directive = angular.element('<lr-model-directive contact-id="' + contactId + '"></lr-model-directive>');
        $compile(directive)($controllerScope);
        $controllerScope.$digest();
        controller = directive.controller;
      }
    });
  });
})(CRM);
