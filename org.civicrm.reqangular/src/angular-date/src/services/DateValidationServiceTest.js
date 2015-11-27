describe('Unit: DateValidationService', function() {

    var Service;

    beforeEach(module('angular-date'));

    beforeEach(inject(function(DateValidationService) {
        Service = DateValidationService;
    }));

    it('Should be defined.', function() {
        expect(Service).toBeDefined();
    });

    it('SetOptions method extends options object.', function() {
        expect(Object.keys(Service.options).length).toEqual(0);

        Service.setOptions({
            test: 1,
            test2: 2
        });

        expect(Object.keys(Service.options).length).toEqual(2);

        Service.setOptions({
            test3: 1,
            test9: 2
        });

        expect(Object.keys(Service.options).length).toEqual(2+2);

        Service.setOptions({
            test8: 1,
            test: 2
        });

        expect(Object.keys(Service.options).length).toEqual(2+3);
    });

    it('_error() should throw error if custom function is not appiled', function() {
        var shouldThrow = function(){
            Service._error('Test', ['1', '2']);
        };

        expect(shouldThrow).toThrow(new Error('Test'));
    });


    it('Custom function can be assigned to _error()', function() {
        function customError(error_msg, fields){}

        Service.setErrorCallback(customError);

        expect(Service._error).toEqual(customError);

        expect(function(){
            Service.setErrorCallback();
        }).toThrow();

        expect(function(){
            Service.setErrorCallback(3);
        }).toThrow();

        expect(function(){
            Service.setErrorCallback('dfsdf');
        }).toThrow();

        expect(function(){
            Service.setErrorCallback({});
        }).toThrow();

        expect(function(){
            Service.setErrorCallback(true);
        }).toThrow();
    });


    it('SetDates', function() {
        Service.setDates('12/11/2013', '10/01/2009');

        expect(Service.start).toEqual('12/11/2013');
        expect(Service.end).toEqual('10/01/2009');

        expect(Service.start_parts).toEqual(['12','11','2013']);
        expect(Service.end_parts).toEqual(['10','01','2009']);
    });

    it('Check if Format is valid', function() {
        /* Should pass */
        expect(function() {
            Service.validate('12/11/2009', '10/01/2013');
        }).not.toThrow();

        expect(function() {
            Service.validate('1/1/112013', '10/01/2009');
        }).toThrow();

        expect(function() {
            Service.validate('testString', '10/01/2009');
        }).toThrow();

        expect(function() {
            Service.validate('12/11/2013', '123/01/2009');
        }).toThrow();

        expect(function() {
            Service.validate('aa/bb/ccss', 'dd/MM/yyyy');
        }).toThrow();

        expect(function(){
            Service.validate('12/11/2009', '10/-1/2012');
        }).toThrow(new Error('Neither Days nor Months can be negative or equal to 0.'));

        expect(function(){
            Service.validate('12/11/2009', '10/91/2012');
        }).toThrow(new Error('This month doesn\'t exist.'));

        expect(function(){
            Service.validate('12/11/2009', '90/11/2012');
        }).toThrow(new Error('Day of the month is invalid.'));

        expect(function(){
            Service.validate('12/111/013', '10/01/2009');
        }).toThrow();

        expect(function(){
            Service.validate('12/11/2013', '1001/20/09');
        }).toThrow();

        expect(function(){
            Service.validate('1/11/2009', '10/16/2012');
        }).toThrow();
    });

    it('Start date cannot be higher', function() {
        /* Should pass */
        expect(function(){
            Service.validate('12/11/2003', '10/01/2009');
        }).not.toThrow();

        /*  Should NOT pass. Start date cannot be higher */
        expect(function(){
            Service.validate('12/11/2009', '10/01/2003');
        }).toThrow();

        expect(function(){
            Service.checkIfStartDateIsLower(['10', '02', '2013'], [], 2);
        }).not.toThrow();
    });

    it('Start date cannot be higher - recursive check', function() {
        /* Should pass */
        expect(function(){
            Service.validate('12/11/2003', '13/11/2003');
        }).not.toThrow();

        expect(function(){
            Service.validate('16/11/2003', '13/11/2003');
        }).toThrow();

        expect(function(){
            Service.validate('13/11/2003', '13/11/2003');
        }).toThrow(new Error('Start Date cannot be he same as End Date!'));
    });

    it('Function will break even if wont throw an exception', function(){
        Service.setErrorCallback(function(msg, fields){
            //msg
        });

        expect(Service.checkIfStartDateIsLower(['13', '11', '2003'], ['13', '11', '2003'], 2)).toBe(true);
    });

    it('Start date is required while End date isnt ', function() {
        /* Should pass */
        expect(function(){
            Service.setDates('12/11/2003');
        }).not.toThrow();
        Service._reset();

        expect(function () {
            Service.setDates();
        }).toThrow();
        Service._reset();

    });

    it('Can run validate while only start date is entered', function() {
        Service._reset();
        expect(function(){
            Service.validate('02/11/2013', undefined);
        }).not.toThrow();

        Service._reset();
        /* Cannot run validate while no dates are entered */
        expect(function(){
            Service.validate('', '02/11/2017');
        }).toThrow();

        Service._reset();
        expect(function(){
            Service.validate(null, '02/11/2017');
        }).toThrow();

        Service._reset();
        expect(function () {
            Service.validate(true);
        }).toThrow();

        Service._reset();
        expect(function () {
            Service.validate({});
        }).toThrow();

        Service._reset();
        expect(function () {
            Service.validate(Service);
        }).toThrow();
    });


    it('Max & Min Date', function() {
        Service.setOptions({
            minDate: new Date(2000, 1, 1),
            maxDate: new Date(2020, 12, 30)
        });

        /* valid date */
        expect(function(){
            Service.validate('20/03/2013', '02/11/2017');
        }).not.toThrow();

        /* invalid dates */
        expect(function(){
            Service.validate('20/03/1992', '02/11/2017');
        }).toThrow();

        expect(function(){
            Service.validate('20/03/2006', '02/11/2037');
        }).toThrow();
    });

    it('Invalid values', function() {
        /* valid date */
        expect(function(){
            Service.validate('20/03/2013', 'ttestowystrring');
        }).toThrow();

        /* invalid dates */
        expect(function(){
            Service.validate('testteest', '02/11/2017');
        }).toThrow();

        expect(function(){
            Service.validate('sgdlskgs', 'sdgskjdgsdkg');
        }).toThrow();
    });
});