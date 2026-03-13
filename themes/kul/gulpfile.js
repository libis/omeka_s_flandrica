'use strict'; 

const gulp = require('gulp');

//sass
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const sassGlob = require('gulp-sass-glob');

//js
const uglify = require('gulp-uglify');
var concat = require('gulp-concat');


gulp.task('buildingStyles', function () {
    return gulp.src('./asset/sass/*.scss')
        .pipe(sassGlob())
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS({}))
        .pipe(gulp.dest('./asset/css'));
});

gulp.task('uglifyJs', function () {
    return gulp.src('./asset/js/*.js')
        .pipe(uglify())
        .pipe(concat('main.js'))
        .pipe(gulp.dest('./asset/js'));
});

gulp.task('all:watch', function () {
    gulp.watch('./asset/sass/**/*.scss', gulp.parallel('buildingStyles'));
    gulp.watch('./asset/sass/**/*.js', gulp.parallel('uglifyJs'));
});

gulp.task('all:build', gulp.parallel(['buildingStyles', 'uglifyJs']));
