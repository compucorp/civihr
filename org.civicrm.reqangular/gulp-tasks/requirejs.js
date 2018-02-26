var exec = require('child_process').exec;
var gulp = require('gulp');
var path = require('path');
var replace = require('gulp-replace');
var templateCache = require('gulp-angular-templatecache');

module.exports = function () {
  var commonFolder = path.join(__dirname, '..', 'src/common');

  return {
    pre: [
      {
        name: 'cache-templates',
        fn: function (cb) {
          gulp.src(path.join(commonFolder, 'templates', '/**/*.html'))
            .pipe(templateCache({ moduleSystem: 'RequireJS' }))
            .pipe(replace("['angular']", "['common/angular']"))
            .pipe(replace('module(\'templates\')', 'module(\'common.templates\', [])'))
            .pipe(gulp.dest(path.join(commonFolder, 'modules')));

          cb();
        }
      },
      {
        name: 'requirejs:mock',
        fn: function (cb) {
          var buildMocksPath = path.join(__dirname, '..', 'build.mocks.js');

          exec('r.js -o ' + buildMocksPath, function (err, stdout, stderr) {
            err && err.code && console.log(stdout);
            cb();
          });
        }
      }
    ],
    watchPatterns: [
      path.join(commonFolder, '**/*.js'),
      path.join(commonFolder, 'templates/**/*.html'),
      path.join(__dirname, '..', 'test/mocks/**/*.js'),
      '!' + path.join(commonFolder, 'modules/templates.js')
    ]
  };
};
