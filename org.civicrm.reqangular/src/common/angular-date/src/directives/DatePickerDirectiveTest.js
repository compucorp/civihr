describe('Unit: DateControllerTest', function () {

    var Element, scope, compile, Controller, crtlScope;

    beforeEach(function () {
        module('angular-date');

        inject(function ($compile, $rootScope, $controller) {
            compile = $compile;
            scope = $rootScope.$new();
            crtlScope = $rootScope.$new();

            Controller = $controller('DatePickerController', {
                $scope: crtlScope,
                $attrs: {}
            });
        });

        Element = getCompiledElement();
    });

    function getCompiledElement() {
        scope.CalendarShow = {
            'start_date': false
        };

        scope.minDate = new Date(2000, 1, 1);
        scope.maxDate = new Date(2020, 11, 1);
        scope.edit_data = {
            10: {
                'start_date': new Date()
            }
        };
        scope.select = function (name) {
            return 'selected ' + name;
        };


        var element = angular.element("<input type=\"text\" class=\"form-control\"\n" +
            "id=\"start_date\"\n" +
            "name=\"start_date\"\n" +
            "datepicker-popup=\"dd/MM/yyyy\"\n" +
            "is-open=\"CalendarShow['start_date'] == true\"\n" +
            "min-date=\"minDate\"\n" +
            "ng-model=\"edit_data[10]['start_date']\"\n" +
            "ng-change=\"select('start_date')\"\n" +
            "ng-disabled=\"isDisabled\"\n" +
            "close-text=\"Close\"\n" +
            "custom-date-input\n" +
            "required />");

        var compiledElement = compile(element)(scope);
        scope.$digest();
        return compiledElement;
    }


    it('Should be defined', function () {
        expect(Element).toBeDefined();
    });

    it('Should be defined', function () {
        expect(function () {
            Controller.init(scope);
        }).not.toThrow();
    });

    it('Should be defined', function () {
        var iso = Element.isolateScope();
        expect(iso).toEqual('');
    });
})
;