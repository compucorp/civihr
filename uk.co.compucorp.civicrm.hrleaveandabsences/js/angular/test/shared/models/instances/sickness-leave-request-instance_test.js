define([
  'mocks/helpers/helper',
  'mocks/apis/leave-request-api-mock',
  'mocks/apis/option-group-api-mock',
  'leave-absences/shared/models/instances/sickness-leave-request-instance',
], function (helper) {
  'use strict';

  describe('SicknessRequestInstance', function () {
    var expectedError, instance, LeaveRequestAPI, $provide, promise, $q, requestData, $rootScope;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('OptionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject([
      'SicknessRequestInstance', '$rootScope', 'LeaveRequestAPI', '$q',
      function (_SicknessRequestInstance_, _$rootScope_, _LeaveRequestAPI_, _$q_) {
        instance = _SicknessRequestInstance_.init({}, false);
        $rootScope = _$rootScope_;
        LeaveRequestAPI = _LeaveRequestAPI_;
        $q = _$q_;

        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
      }
    ]));

    describe('init', function () {
      it('sickness request', function () {
        expect(instance).toBeDefined();
      });
    });

    describe('create()', function () {
      beforeEach(function () {
        requestData = helper.createRandomSicknessRequest();
        instance = instance.init(requestData, false);
        promise = instance.create();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.create).toHaveBeenCalled();
        });
      });

      it('id is appended to instance', function () {
        expect(instance.id).not.toBeDefined();
        promise.then(function () {
          expect(instance.id).toBeDefined();
          expect(instance.id).toEqual(jasmine.any(String));
        });
      });

      describe('when one mandatory field is missing', function () {
        beforeEach(function () {
          expectedError = 'contact_id, from_date and from_date_type in params are mandatory';
          delete instance.contact_id;
          promise = instance.create();
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('fails to create instance', function () {
          promise.catch(function (error) {
            expect(error).toBe(expectedError);
          });
        });
      });
    });

    describe('isValid()', function () {
      beforeEach(function () {
        requestData = {
          contact_id: '123'
        };
        instance = instance.init(requestData);
        promise = instance.isValid();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
        });
      });

      describe('when leave request is valid', function () {
        it('returns no error', function () {
          promise.then(function (result) {
            expect(result).toEqual([]);
          });
        });

        describe('when valid data not present', function () {
          beforeEach(function () {
            delete instance.contact_id;
            promise = instance.isValid();
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('returns array of errors', function () {
            promise.catch(function (result) {
              expect(Object.keys(result).length).toBeGreaterThan(0);
            });
          });
        });
      });
    });

    describe('update()', function () {
      var toAPIReturnValue = {
          key: jasmine.any(String)
        };

      beforeEach(function () {
        var defer = $q.defer();
        LeaveRequestAPI.update.and.returnValue(defer.promise);
        defer.resolve(jasmine.any(Object));
        spyOn(instance, 'toAPI').and.returnValue(toAPIReturnValue);

        promise = instance.update();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls update api method with the return value of toAPI method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.update).toHaveBeenCalledWith(toAPIReturnValue, 'sick');
        });
      });

      it('calls toAPI method', function () {
        promise.then(function () {
          expect(instance.toAPI).toHaveBeenCalled();
        });
      })
    });
  });
});
