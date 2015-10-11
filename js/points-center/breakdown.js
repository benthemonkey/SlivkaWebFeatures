'use strict';

var _ = {
    forEach: require('lodash/collection/forEach'),
    template: require('lodash/string/template')
};
var highcharts = require('highcharts-browserify');
var ajaxRoot = require('./utils').ajaxRoot;
var slivkans, qtrs;
var eventsTemplate = _.template($('#eventsTemplate').html(), { imports: { forEach: _.forEach } });
var otherPointsTableTemplate = _.template($('#otherPointsTableTemplate').html(), { imports: { forEach: _.forEach } });

var $breakdown = $('.breakdown');
var $attendedEvents = $('#attendedEvents');
var $unattendedEvents = $('#unattendedEvents');
var $otherPointsTable = $('#otherPointsTable');

var $eventsChart = $('#eventsChart');
var $imsChart = $('#imsChart');

var drawChart = function($el, tableData, titleIn, width) {
    $el.highcharts({
        chart: {
            width: width
        },
        credits: {
            enabled: false
        },
        plotOptions: $el[0].id !== 'eventsChart' ? {} : {
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
};

var getSlivkanPoints = function() {
    var nuEmail = $('#slivkan').val();
    var qtr = localStorage.spc_brk_qtr || qtrs[0].qtr;
    var width = $eventsChart.width();

    // fix height of breakdown so there is no flash of background
    $breakdown.parent().css('min-height', $breakdown.parent().height());

    if (nuEmail.length > 0) {
        localStorage.spc_brk_slivkan = nuEmail;

        $.when(
            $.getJSON(ajaxRoot + '/ajax/getPointsBreakdown.php', { nu_email: nuEmail, qtr: qtr }),
            $breakdown.fadeOut()
        ).then(function(data) {
            var eventData = [];
            var imData = [];
            var eventTotal = 0;
            var imTotal = 0;
            var imExtra = 0;

            data = data[0]; // only care about first entry

            $eventsChart.empty();
            $imsChart.empty();

            $attendedEvents.html(eventsTemplate({ events: data.events.attended.reverse() }));
            $unattendedEvents.html(eventsTemplate({ events: data.events.unattended.reverse() }));
            $otherPointsTable.html(otherPointsTableTemplate(data));

            _.forEach(data.events.counts, function(count) {
                var int = parseInt(count.count, 10);

                eventData.push([count.committee, int]);
                eventTotal += int;
            });

            $('.eventPoints').text(eventTotal);
            drawChart($eventsChart, eventData, 'Event Points (' + eventTotal + ' Total)', width);

            if (data.ims.length > 0) {
                $imsChart.show();
                _.forEach(data.ims, function(im) {
                    im.count = parseInt(im.count, 10);

                    imData.push([im.sport, im.count]);

                    if (im.count >= 3) {
                        imTotal += im.count;
                    } else {
                        imExtra += im.count;
                    }
                });

                if (imTotal > 15) {
                    imExtra += imTotal - 15;
                    imTotal = 15;
                }

                drawChart(
                    $imsChart,
                    imData,
                    ['IMs (', imTotal, ' Points, ', imExtra,
                    (imExtra === 1 ? ' Doesn\'t' : ' Don\'t'), ' Count)'].join(''),
                    width
                );
            } else {
                $imsChart.hide();
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

            $breakdown.fadeIn(function() {
                $breakdown.parent().css('min-height', '');
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
