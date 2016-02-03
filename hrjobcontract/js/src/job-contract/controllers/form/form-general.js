define([
    'common/moment',
    'job-contract/controllers/controllers'
], function (moment, controllers){
    'use strict';

    controllers.controller('FormGeneralCtrl',['$scope','$log','$timeout',
        function ($scope, $log) {
            $log.debug('Controller: FormGeneralCtrl');

            var entityDetails = $scope.entity.details;

            $scope.dpOpen = function($event, opened){
                $event.preventDefault();
                $event.stopPropagation();

                $scope[opened] = true;
            }

            function duration(dateStart, dateEnd){

                if (!dateStart || !dateEnd) {
                    return 'Unspecified'
                }

                var days, months, m, years;

                m = moment(dateEnd);
                years = m.diff(dateStart, 'years');

                m.add(-years, 'years');
                months = m.diff(dateStart, 'months');

                m.add(-months, 'months');
                days = m.diff(dateStart, 'days');

                years = years > 0  ? (years > 1 ? years + ' years ' : years + ' year ') :  '';
                months = months > 0 ? (months > 1 ? months + ' months ' : months + ' month ') :  '';
                days = days > 0 ? (days > 1 ? days + ' days' : days + ' day') : '';

                return (years + months + days) || '0 days';

            }

            $scope.$watch('entity.details.period_start_date', function(){
                moment(entityDetails.period_start_date);
                $scope.dpDateEndMin = moment(entityDetails.period_start_date).add(1, 'day').format();
                $scope.duration = duration(entityDetails.period_start_date, entityDetails.period_end_date);
            });

            $scope.$watch('entity.details.period_end_date', function(){
                $scope.dpDateStartMax = entityDetails.period_end_date ? moment(entityDetails.period_end_date).subtract(1, 'day').format() : '';
                $scope.duration = duration(entityDetails.period_start_date, entityDetails.period_end_date);
            });

            $scope.$watch('entity.details.position', function(newVal, oldVal){
                if (newVal != oldVal && entityDetails.title == oldVal) {
                    $scope.contractForm.detailsTitle.$setViewValue(newVal);
                    $scope.contractForm.detailsTitle.$render();
                }
            });

            $scope.$watch('entity.details.notice_amount', function(newVal, oldVal){
                if (+newVal && !entityDetails.notice_unit) {
                    $scope.contractForm.detailsNoticeUnit.$setValidity('required', false);
                    $scope.contractForm.detailsNoticeUnit.$dirty = true;
                }

                if (newVal != oldVal && entityDetails.notice_amount_employee == oldVal) {
                    entityDetails.notice_amount_employee = newVal;
                }
            });

            $scope.$watch('entity.details.notice_amount_employee', function(newVal){
                if (+newVal && !entityDetails.notice_unit_employee) {
                    $scope.contractForm.detailsNoticeUnitEmployee.$setValidity('required', false);
                    $scope.contractForm.detailsNoticeUnitEmployee.$dirty = true;
                }
            });

            $scope.$watch('entity.details.notice_unit', function(newVal, oldVal){
                if (newVal != oldVal && entityDetails.notice_unit_employee == oldVal) {
                    entityDetails.notice_unit_employee = newVal;
                }
            });

        }]);
});
