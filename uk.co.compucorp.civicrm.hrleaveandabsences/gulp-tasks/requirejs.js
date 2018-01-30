var gulp = require('gulp');
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var path = require('path');

module.exports = function () {
  var distFolder = path.join(__dirname, '..', 'js/angular/dist/');

  return {
    post: [
      {
        name: 'requirejs:rename',
        fn: function () {
          return gulp.src(path.join(distFolder, '*.js'))
            .pipe(rename(function (path) {
              path.basename += '.min';
            }))
            .pipe(gulp.dest(distFolder));
        }
      },
      {
        name: 'requirejs:clean',
        fn: function () {
          return gulp.src([
            path.join(distFolder, 'leave-absences/'),
            path.join(distFolder, '', 'build.txt'),
            path.join(distFolder, '', '*.js'),
            path.join(distFolder, 'mocks/'),
            '!' + path.join(distFolder, '*.min.js')
          ], {read: false})
          .pipe(clean({ force: true }));
        }
      }
    ]
  };
};
