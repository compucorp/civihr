var gulp = require('gulp');
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var path = require('path');

module.exports = function () {
  // @NOTE one thing to keep in mind is that the custom task always needs to use
  // __dirname for paths, otherwise the paths will be relative to hrcore
  var distFolder = path.join(__dirname, '..', 'js/angular/dist/');

  return {
    // @NOTE here L&A needs to perform to tasks after r.js had run, both for
    // cleaning the dist/ folder. Notice that the order of the tasks is the
    // order in which they will be called
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
