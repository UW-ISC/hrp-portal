// Admin CSS: prefer Dart Sass from this folder — `npm install` then `npm run build:admin-css` (or `watch:admin-css`).
// This gulp task is legacy and may not match current Node versions.
var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('styles', function() {
    gulp.src('css/admin/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./css/admin/'));
});

gulp.task('default',function() {
    gulp.watch('css/admin/*.scss',['styles']);
});