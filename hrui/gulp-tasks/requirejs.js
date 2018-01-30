var concat = require('gulp-concat');
var find = require('find');
var gulp = require('gulp');
var path = require('path');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');

module.exports = function () {
  return {
    /**
     * Standard JS pipeline without requirejs: uglifies, concats and outputs
     * a single minified file, while writing a sourcemap in the process
     */
    main: function () {
      gulp.src(path.join(__dirname, '..', 'js/src/**/*.js'))
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(concat('hrui.min.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(path.join(__dirname, '..', 'js/dist')));
    },
    /**
     * Detects if there are any js files in the js/src folder
     *
     * @return {Boolean}
     */
    canRunCriteria: function () {
      return find.fileSync(/js\/src\/[^/]+\.js$/, path.join(__dirname, '..')).length;
    }
  };
};
