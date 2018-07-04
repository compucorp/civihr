(function (require) {
  require([
    'common/angular',
    'leave-absences/calendar-feeds/list/calendar-feeds.list.module'
  ],
  function (angular) {
    angular.bootstrap(
      document.querySelector('[data-leave-absences-calendar-feeds-list]'),
      ['calendar-feeds.list']
    );
  });
})(require);
