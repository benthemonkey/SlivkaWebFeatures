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
    var stream = gulp.src('css/points-center.scss')
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.sass({
            precision: 9
        }).on('error', plugins.sass.logError))
        .pipe(plugins.sourcemaps.write('./', {
            includeContent: false,
            sourceRoot: '../'
        }))
        .pipe(gulp.dest('./build'));

    if (minify) {
        stream = stream.pipe(plugins.ignore('*.map'))
            .pipe(plugins.cleanCss({ keepSpecialComments: 0 }))
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

gulp.task('javascript-clean', function() {
    var del = require('del');

    return del(['build/*.js', 'build/*.js.map']);
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

gulp.task('stylesheets-clean', function() {
    var del = require('del');

    return del(['build/*.css', 'build/*.css.map']);
});

gulp.task('stylesheets-lint', function() {
    return gulp.src(['css/points-center.scss', 'css/points-table.scss'])
        .pipe(plugins.sassLint())
        .pipe(plugins.sassLint.format());
});

gulp.task('stylesheets', function() {
    return stylesheetsShare({ watch: false, minify: false });
});

gulp.task('stylesheets-watch', ['stylesheets-lint'], function() {
    return stylesheetsShare({ watch: true, minify: false });
});

gulp.task('stylesheets-build', ['stylesheets-clean'], function() {
    return stylesheetsShare({ watch: false, minify: true });
});

gulp.task('clean', ['javascript-clean', 'stylesheets-clean']);

gulp.task('watch', ['clean', 'javascript-watch', 'stylesheets-watch'], function() {
    plugins.livereload.listen();

    gulp.watch('js/**/*.js', ['javascript-lint']);
    gulp.watch('css/*.scss', ['stylesheets-watch']);
});

gulp.task('build', ['clean', 'javascript-build', 'stylesheets-build']);

gulp.task('lint', ['javascript-lint']);

gulp.task('default', ['build']);
