'use strict';

var gulp = require('gulp');
var plugins = require('gulp-load-plugins')();

var browserifyShare = function(opts) {
    var bundle, cache;
    var browserify = require('browserify');
    var watchify = require('watchify');
    var source = require('vinyl-source-stream');
    var buffer = require('vinyl-buffer');
    var watch = opts.watch || false;
    var minify = opts.minify || false;
    var bundler = function() {
        var b;

        if (cache) {
            return cache.bundle();
        }

        b = browserify(
            'js/points-center/index.js',
            {
                basedir: __dirname,
                debug: true
            }
        );

        if (watch) {
            b = watchify(b);
            b.on('update', bundle);
            cache = b;
        }

        return b.bundle();
    };

    bundle = function() {
        var stream = bundler()
            .pipe(source('points-center.js'))
            .pipe(buffer())
            .pipe(plugins.sourcemaps.init({ loadMaps: true }))
            .pipe(plugins.sourcemaps.write('./', {
                includeContent: false,
                sourceRoot: '../'
            }))
            .pipe(gulp.dest('./build'));

        if (minify) {
            stream = stream.pipe(plugins.ignore('*.map'))
                .pipe(plugins.uglify())
                .pipe(plugins.rename({
                    extname: '.min.js'
                }))
                .pipe(gulp.dest('./build'));
        }

        if (watch) {
            stream = stream.pipe(plugins.livereload());
        }

        return stream;
    };

    return bundle();
};

var stylesheetsShare = function(opts) {
    var watch = opts.watch || false;
    var minify = opts.minify || false;
    var stream = gulp.src([
        'node_modules/bootstrap/dist/css/bootstrap.css',
        'node_modules/bootstrap/dist/css/bootstrap-theme.css',
        'node_modules/bootstrap-multiselect/dist/css/bootstrap-multiselect.css',
        'node_modules/font-awesome/css/font-awesome.css',
        'node_modules/nprogress/nprogress.css',
        'css/typeahead.js-bootstrap.css',
        'css/points-center.css',
        'css/points-table.css'
    ])
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.concat('points-center.css'))
        .pipe(plugins.sourcemaps.write('./', {
            includeContent: false,
            sourceRoot: '../'
        }))
        .pipe(gulp.dest('./build'));

    if (minify) {
        stream = stream.pipe(plugins.ignore('*.map'))
            .pipe(plugins.minifyCss({ keepSpecialComments: 0 }))
            .pipe(plugins.rename({
                extname: '.min.css'
            }))
            .pipe(gulp.dest('./build'));
    }

    if (watch) {
        stream = stream.pipe(plugins.livereload());
    }

    return stream;
};

gulp.task('clean', function() {
    var del = require('del');

    return del(['build']);
});

gulp.task('javascript-lint', function() {
    return gulp.src(['gulpfile.js', 'js/**/*.js'])
        .pipe(plugins.cached('scripts'))
        .pipe(plugins.eslint())
        .pipe(plugins.eslint.format());
});

gulp.task('javascript', function() {
    return browserifyShare({ watch: false, minify: false });
});

gulp.task('javascript-watch', ['javascript-lint'], function() {
    return browserifyShare({ watch: true, minify: false });
});

gulp.task('javascript-build', ['clean'], function() {
    return browserifyShare({ watch: false, minify: true });
});

gulp.task('stylesheets', function() {
    return stylesheetsShare({ watch: false, minify: false });
});

gulp.task('stylesheets-watch', function() {
    return stylesheetsShare({ watch: true, minify: false });
});

gulp.task('stylesheets-build', ['clean'], function() {
    return stylesheetsShare({ watch: false, minify: true });
});

gulp.task('watch', ['clean', 'javascript-watch', 'stylesheets-watch'], function() {
    plugins.livereload.listen();

    gulp.watch('js/**/*.js', ['javascript-lint']);
    gulp.watch('css/*.css', ['stylesheets-watch']);
});

gulp.task('build', ['clean', 'javascript-build', 'stylesheets-build']);

gulp.task('lint', ['javascript-lint']);

gulp.task('default', ['build']);
