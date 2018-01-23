var concat = require('gulp-concat');
var find = require('find');
var gulp = require('gulp');
var path = require('path');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');

module.exports = function () {
  return {
    main: function () {
      gulp.src(path.join(__dirname, '..', 'js/src/**/*.js'))
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(concat('hrui.min.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(path.join(__dirname, '..', 'js/dist')));
    },
    canRunCriteria: function () {
      return find.fileSync(/js\/src\/[^/]+.js$/, path.join(__dirname, '..')).length;
    }
  };
};
