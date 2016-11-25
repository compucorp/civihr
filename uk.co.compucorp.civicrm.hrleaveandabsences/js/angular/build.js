({
  baseUrl : 'src',
  out: 'dist/leave-absences.min.js',
  name: 'leave-absences',
  skipModuleInsertion: true,
  paths: {
    'common': 'empty:',
    'leave-absences/vendor/ui-router': 'leave-absences/vendor/angular-ui-router.min'
  }
})
