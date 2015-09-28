'use strict';

window.$ = window.jQuery = require('jquery');
require('bootstrap');

$(function() {
    var nprogress = require('nprogress');
    var pages = {
        breakdown: require('./breakdown'),
        submission: require('./submission'),
        table: require('./table'),
        correction: require('./correction'),
        rankings: require('./rankings'),
        admin: require('./admin'),
        'committee-headquarters': require('./committee-headquarters')
    };
    var page = (/([\-a-z]+)(\.php|\/)$/).exec(window.location.pathname);

    if (page) {
        page = page[1];
    } else {
        page = 'breakdown';
    }

    $('.' + page + '-link').addClass('active');

    // bind ajax start and stop to nprogress
    nprogress.configure({
        trickleRate: 0.1
    });
    $(document).on('ajaxStart', nprogress.start);
    $(document).on('ajaxStop', nprogress.done);

    pages[page].init();
});
