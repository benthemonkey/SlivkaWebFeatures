'use strict';

var datatables = require('datatables');
var utils = require('./utils');
var i, table, events;

var columnFilter = function() {
    var committees = $('#committeeFilter').find('input:checked').map(function() {
        return this.value;
    }).get();
    var ims = $('#imFilter').val();
    var n = 0;

    if (ims === '2') {
        committees = [];
        $('#committeeFilter').parent().find('.dropdown-toggle').attr('disabled', 'disabled');
    } else {
        $('#committeeFilter').parent().find('.dropdown-toggle').removeAttr('disabled');
    }

    for (i = 0; i < events.length; i++) {
        if (committees.indexOf(events[i].committee) !== -1 &&
            (ims !== '1' || events[i].type !== 'im') || (ims === '2' && events[i].type === 'im')) {
            table.column(i + 2).visible(true, false);
            n++;
        } else {
            table.column(i + 2).visible(false, false);
        }
    }

    table.draw();
};

module.exports = {
    init: function() {
        var eventName, name, date;
        var nameColWidth = $('th.name').width();
        var eventColWidth = $('th.event').width();
        var totalsColWidth = $('th.total').width();
        var tableWrapper = $('.points-table-wrapper');
        var headers = $('th');
        var pointsTable = JSON.parse(window.points_table);
        var byYear = pointsTable.by_year;
        var bySuite = pointsTable.by_suite;
        var lastScroll = 0;
        var delay = (function() {
            var timer = 0;

            return function(callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();
        var adjustWidth = function() {
            var width = (tableWrapper.width() - nameColWidth - 6 * totalsColWidth - 2) + 'px';

            $('th.end').css({
                'width': width,
                'min-width': width,
                'max-width': width
            });
        };

        events = pointsTable.events;

        tableWrapper.scroll(function() {
            delay(function() {
                var scroll, round;

                if ($('body').width() > 768) {
                    scroll = tableWrapper.scrollLeft();
                    round = lastScroll < scroll ? Math.ceil : Math.floor;

                    if (lastScroll !== scroll) {
                        lastScroll = round(scroll / eventColWidth) * eventColWidth;
                        tableWrapper.scrollLeft(lastScroll);
                    }
                }
            }, 100);
        });

        $(window).resize(function() {
            delay(adjustWidth, 500);
        });
        adjustWidth();

        table = $('#table').DataTable({ // eslint-disable-line new-cap
            columnDefs: [
                { targets: ['_all'], orderSequence: ['desc', 'asc'] },
                { targets: [-1], orderable: false }
            ],
            paging: false,
            dom: 't'
        });

        $('#nameFilter').on('keyup', function() {
            delay(function() {
                table.column([0]).search($('#name-filter').val()).draw();
            }, 500);
        });

        $('#genderFilter').on('change', function() {
            var option = $('#gender-filter').val();

            table.column([1]).search(option).draw();
        });

        $('#imFilter').on('change', columnFilter);

        $('#committeeFilter input').on('click', columnFilter);

        $('#noFilter').on('click', function() {
            localStorage.spc_tab_noFilter = 1;
        });

        for (i = 0; i < events.length; i++) {
            eventName = events[i].event_name;
            name = eventName.substr(0, eventName.length - 11);
            date = eventName.substr(eventName.length - 5);

            headers.eq(i + 2).popover({
                trigger: 'hover',
                html: true,
                title: name,
                content: ['Date: ', date, '<br/>Attendees: ', events[i].attendees,
                    (events[i].description.length > 0 ? '<br/>Description: ' + events[i].description : '')].join(''),
                placement: 'bottom',
                container: '#table'
            });
        }

        // filling years and suites tables
        byYear.sort(function(a, b) {
            return b[1] - a[1];
        });

        for (i = 0; i < byYear.length; i++) {
            $('<tr><td>' + byYear[i][0] + '</td><td>' + byYear[i][1] + '</td></tr>').appendTo('#years');
        }

        bySuite.sort(function(a, b) {
            return b[1] - a[1];
        });

        for (i = 0; i < bySuite.length; i++) {
            $('<tr><td>' + bySuite[i][0] + '</td><td>' + bySuite[i][1] + '</td></tr>').appendTo('#suites');
        }
    }
};
