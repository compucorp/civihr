describe('Unit: DateControllerTest', function () {

    var Element, scope, compile, Controller, crtlScope;

    beforeEach(function () {
        module('angular-date');

        inject(function ($compile, $rootScope, $controller) {
            compile = $compile;
            scope = $rootScope.$new();
            crtlScope = $rootScope.$new();
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
            "datepicker-popup\n" +
            "is-open=\"CalendarShow.start_date\"\n" +
            "min-date=\"minDate\"\n" +
            "ng-model=\"edit_data[10].start_date\"\n" +
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


    it('Should refresh input', function () {
        scope.edit_data[10].start_date = new Date(2014, 2, 2);
        scope.$digest();
        expect(Element[0].value).toEqual('02/03/2014');
    });
});