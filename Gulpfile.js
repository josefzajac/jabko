
var gulp        = require('gulp');
var less        = require('gulp-less');
var streamify   = require('gulp-streamify');
var minifyCss   = require('gulp-minify-css');
var concat      = require('gulp-concat');
var sourcemaps  = require('gulp-sourcemaps');
var merge       = require('merge-stream');
var debug       = require('gulp-debug');
var fs          = require('fs');
var uglify      = require('gulp-uglifyjs');

gulp.task('less-admin', function () {
    gulp.src(['./web/styles/less/admin/bstrap.less','./web/styles/less/admin/app.less'])
        .pipe(less({
            paths: [
                './bower_components/bootstrap/less',
                './bower_components/admin-lte/build/less'
            ]
        }))
        .pipe(gulp.dest('./web/styles/css/admin'))
        .pipe(streamify(minifyCss()))
        .pipe(gulp.dest('./www/css/admin'))
    ;
});


gulp.task('less-frontend', function () {
    gulp.src(['./web/styles/less/frontend/app.less'])
        .pipe(less())
        .pipe(gulp.dest('./web/styles/css/frontend'))
    ;
    gulp.src(['./web/styles/css/frontend/app.css', './web/styles/css/frontend/xcustom.css'])
        // .pipe(streamify(minifyCss()))
        .pipe(concat('app.css'))
        .pipe(gulp.dest('./www/css/frontend'))
    ;
});

gulp.task('js-admin', function() {
    var libs = gulp.src(
        [
            './bower_components/jquery/dist/jquery.js',
            './bower_components/bootstrap/dist/js/bootstrap.js',
            './bower_components/admin-lte/plugins/slimScroll/jquery.slimscroll.js',
            './bower_components/admin-lte/dist/js/app.js'
        ])
        .pipe(sourcemaps.init())
        .pipe(concat('libs.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./www/js/admin'));

    var app = gulp.src(
        [
            './web/js/admin/app.js'
        ])
        .pipe(sourcemaps.init())
        .pipe(concat('app.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./www/js/admin'));

    return merge(libs, app);
});

gulp.task('js-frontend', function() {
    var libs = gulp.src(['./web/js/frontend/*.js'])
        .pipe(sourcemaps.init())
        .pipe(concat('libs.js'))
        .pipe(sourcemaps.write())
        .pipe(uglify())
        .pipe(gulp.dest('./www/js/frontend'));
    return merge(libs);
});

gulp.task('copyfonts', function() {
    gulp.src('./bower_components/bootstrap/fonts/*.{ttf,woff,woff2,eof,eot,svg}')
        .pipe(gulp.dest('./www/css/fonts'));
});

gulp.task('watch', function() {
    gulp.watch('./web/styles/less/**/*.less', ['less-frontend']);
//    gulp.watch('./web/js/**/*.js', ['js']);
});


gulp.task('less', ['less-frontend', 'less-admin']);
gulp.task('js', ['js-frontend', 'js-admin']);
gulp.task('admin', ['less-admin', 'js-admin']);
gulp.task('frontend', ['less-frontend', 'js-frontend']);

gulp.task('default', ['less', 'js', 'copyfonts']);
gulp.task('dev', ['less', 'js']);
gulp.task('dist', ['less', 'js']);

require('fs').writeFileSync('temp/cache/_%0094b9d4f06e5046c7bfaa93e39a5aece7', '<?php //netteCache[01]000048a:1:{s:4:"time";s:21:"0.44135100 1456605938";}?>'+(new Date()).getTime());
