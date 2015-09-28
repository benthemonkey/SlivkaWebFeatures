'use strict';

var highcharts = require('highcharts-browserify');
var ajaxRoot = require('./utils').ajaxRoot;
var slivkans, qtrs;
var drawChart = function(tableData, titleIn, id) {
    setTimeout(function() {
        $('#' + id).highcharts({
            credits: {
                enabled: false
            },
            plotOptions: id !== 'eventsChart' ? {} : {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true
                    },
                    point: {
                        events: {
                            select: function() {
                                $('.' + this.name).css({
                                    'background-color': this.color,
                                    'color': 'white'
                                });
                            },
                            unselect: function() {
                                $('.' + this.name).removeAttr('style');
                            }
                        }
                    }
                }
            },
            title: {
                text: titleIn,
                style: {
                    'font-size': '8pt'
                }
            },
            tooltip: {
                pointFormat: '{series.name}: {point.y}, <b>{point.percentage:.1f}%</b>'
            },
            series: [{
                type: 'pie',
                name: 'Events',
                data: tableData
            }]
        });
    }, 500);
};

var getSlivkanPoints = function() {
    var nuEmail = $('#slivkan').val();
    var qtr = localStorage.spc_brk_qtr || qtrs[0].qtr;
    var attendedEventsEl = $('#attendedEvents');
    var unattendedEventsEl = $('#unattendedEvents');

    if (nuEmail.length > 0) {
        localStorage.spc_brk_slivkan = nuEmail;

        $('.breakdown').fadeOut(function() {
            attendedEventsEl.empty();
            unattendedEventsEl.empty();
            $('#otherPointsTableBody').empty();

            $.getJSON(ajaxRoot + '/ajax/getPointsBreakdown.php', { nu_email: nuEmail, qtr: qtr }, function(data) {
                var i;
                var eventData = [];
                var imData = [];
                var eventTotal = 0;
                var imTotal = 0;
                var imExtra = 0;
                var hasOther = false;

                if (data.events.attended.length > 0) {
                    for (i = data.events.attended.length - 1; i >= 0; i--) {
                        attendedEventsEl
                            .append($('<tr/>')
                                .append($('<td/>')
                                    .addClass(data.events.attended[i].committee)
                                    .text(data.events.attended[i].event_name)));
                    }
                } else {
                    attendedEventsEl
                        .append($('<tr/>')
                            .append($('<td/>').text('None :(')));
                }

                if (data.events.unattended.length > 0) {
                    for (i = data.events.unattended.length - 1; i >= 0; i--) {
                        unattendedEventsEl
                            .append($('<tr/>')
                                .append($('<td/>')
                                    .addClass(data.events.unattended[i].committee)
                                    .text(data.events.unattended[i].event_name)));
                    }
                } else {
                    unattendedEventsEl
                        .append($('<tr/>')
                            .append($('<td/>').text('None :)')));
                }

                for (i = 0; i < data.other_breakdown.length; i++) {
                    if (data.other_breakdown[i][0]) {
                        $('#otherPointsTableBody')
                            .append($('<tr/>')
                                .append($('<td/>').text(data.other_breakdown[i][0]))
                                .append($('<td/>').text(data.other_breakdown[i][1])));

                        hasOther = true;
                    }
                }

                if (hasOther) {
                    $('#otherPointsTable').show();
                } else {
                    $('#otherPointsTable').hide();
                }

                for (i = 0; i < data.events.counts.length; i++) {
                    eventData.push([data.events.counts[i].committee, parseInt(data.events.counts[i].count, 10)]);

                    eventTotal += parseInt(data.events.counts[i].count, 10);
                }

                $('.eventPoints').text(eventTotal);
                drawChart(eventData, 'Event Points (' + eventTotal + ' Total)', 'eventsChart');

                if (data.ims.length > 0) {
                    $('#imsChart').show();
                    for (i = 0; i < data.ims.length; i++) {
                        data.ims[i].count = parseInt(data.ims[i].count, 10);

                        imData.push([data.ims[i].sport, data.ims[i].count]);

                        if (data.ims[i].count >= 3) {
                            imTotal += data.ims[i].count;
                        } else {
                            imExtra += data.ims[i].count;
                        }
                    }

                    if (imTotal > 15) {
                        imExtra += imTotal - 15;
                        imTotal = 15;
                    }

                    drawChart(
                        imData,
                        ['IMs (', imTotal, ' Points, ', imExtra,
                        (imExtra === 1 ? ' Doesn\'t' : ' Don\'t'), ' Count)'].join(''),
                        'imsChart'
                    );
                } else {
                    $('#imsChart').hide();
                }

                $('.imPoints').text(imTotal);
                $('.helperPoints').text(data.helper);
                $('.committeePoints').text(data.committee);
                $('.otherPoints').text(data.other);

                $('.totalPoints').text(
                    [eventTotal, imTotal, data.helper, data.committee, data.other].map(function(n) {
                        return parseInt(n, 10);
                    }).reduce(function(a, b) {
                        return a + b;
                    }));

                $('.breakdown').fadeIn();
            });
        });
    }
};

module.exports = {
    init: function() {
        $.getJSON(ajaxRoot + '/ajax/getSlivkans.php', { qtr: localStorage.spc_brk_qtr }, function(data) {
            var i;

            slivkans = data.slivkans;
            qtrs = data.qtrs;
            // quarter_start = data.quarter_info.start_date;
            // quarter_end = data.quarter_info.end_date;

            for (i = 0; i < slivkans.length; i++) {
                $('<option />').attr('value', slivkans[i].nu_email).text(slivkans[i].full_name).appendTo('#slivkan');
            }

            for (i = 0; i < qtrs.length; i++) {
                $('<option />').attr('value', qtrs[i].qtr).text(qtrs[i].quarter).appendTo('#qtr');
            }

            if (localStorage.spc_brk_slivkan) {
                $('#slivkan').val(localStorage.spc_brk_slivkan);
                getSlivkanPoints();
            }

            if (localStorage.spc_brk_qtr) {
                $('#qtr').val(localStorage.spc_brk_qtr);
            }

            $('#slivkan').on('change', getSlivkanPoints);
            $('#qtr').on('change', function() {
                localStorage.spc_brk_qtr = $(this).val();
                window.location.reload();
            });
        });
    }
};
