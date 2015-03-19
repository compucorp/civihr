define(['controllers/controllers', 'moment'], function(controllers, moment){
    controllers.controller('ModalChangeReasonCtrl',['$scope','$modalInstance', 'content', 'date', 'reasonId', '$log',
        function($scope, $modalInstance, content, date, reasonId, $log){
            $log.debug('Controller: ModalChangeReasonCtrl');

            var content = content || {},
                copy = content.copy || {};

            copy.title = copy.title || 'Revision data';

            $scope.change_reason = reasonId || '';
            $scope.copy = copy;
            $scope.effective_date = date || '';
            $scope.isPast = false;

            $scope.dpOpen = function($event, opened){
                $event.preventDefault();
                $event.stopPropagation();

                $scope[opened] = true;
            }

            $scope.save = function () {
                $modalInstance.close({
                    reasonId: $scope.change_reason,
                    date: $scope.effective_date ? moment($scope.effective_date).format('YYYY-MM-DD') : ''
                });
            };

            $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
            };

            $scope.$watch('effective_date', function(dateSelected){
                $scope.isPast = (new Date(dateSelected) < new Date());
            });
        }]);
});