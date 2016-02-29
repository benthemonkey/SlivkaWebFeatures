'use strict';

var _ = {
    forEach: require('lodash/forEach'),
    template: require('lodash/template')
};
var moment = require('moment');
var utils = require('./utils');

var openPopover = null;

var updateTotal = function(row) {
    var total = row.find('td.pts').map(function(i, el) {
        return parseFloat(el.innerText);
    }).toArray().reduce(function(a, b) {
        return a + b;
    });

    row.find('.totals').text(total.toFixed(1));
};

var validatePoints = function(target) {
    var control = target.closest('.form-group');
    var button = control.find('button');
    var valid = true;

    if (/^-?(\.[1-9]|[0-2](\.\d)?|3(\.0)?)$/.test(target.val())) {
        button.removeAttr('disabled');
    } else {
        valid = false;
        button.attr('disabled', 'disabled');
    }

    utils.updateValidity(control, valid);
};

var isModified = function() {
    var isBonus = openPopover.data('event') === 'bonus';
    var ptsInput = $('#pts-input');

    return parseFloat(ptsInput.val()) !== parseFloat(ptsInput.data('original-value')) ||
        $('#comments').val() !== openPopover.data('comments') ||
        (!isBonus && ($('#contributions').val() || []).sort().join() !== openPopover.data('contributions'));
};

var submitCommitteePoint = function() {
    var data = {
        points: $('#pts-input').val(),
        contributions: ($('#contributions input[type=checkbox]:checked').map(function() {
            return $(this).val();
        }).get() || []).sort().join(),
        comments: $('#comments').val()
    };

    $.ajax({
        type: 'POST',
        url: utils.ajaxRoot + '/ajax/submitCommitteePoint.php',
        context: data,
        data: {
            nu_email: openPopover.parent().data('slivkan'),
            event_name: openPopover.data('event'),
            points: data.points,
            contributions: data.contributions,
            comments: data.comments
        },
        success: function(status) {
            var pointValue;

            if (status === '1') {
                pointValue = parseFloat(this.points).toFixed(1);
                openPopover.text(pointValue);
                openPopover.data({
                    contributions: this.contributions,
                    comments: this.comments
                });

                if (!openPopover.hasClass('green') && !openPopover.hasClass('blue')) {
                    if (pointValue > 0) {
                        openPopover.addClass('yellow');
                    } else {
                        openPopover.removeClass('yellow');
                    }
                }

                updateTotal(openPopover.closest('tr'));
                openPopover.popover('hide');
                openPopover = null;
            }
        }
    });
};

var submitModalForm = function(form, id, extra) {
    var data = {
        full_name:	$('#' + id + '-slivkan option:selected').text(),
        nu_email:	$('#' + id + '-slivkan').val(),
        comments:	$('#' + id + '-comments').val()
    };

    data[extra] = $('#' + id + '-' + extra).val();

    $('#' + id + '-form').find('button[type=submit]').button('loading');
    $.post(utils.ajaxRoot + '/ajax/' + form + '.php', data, function(status) {
        $('#' + id + '-form').find('button[type=submit]').button('reset');
        if (status === '1') {
            window.alert('Success!');
        } else {
            window.alert('Something went wrong. Ask the VP.');
        }

        $('#' + id + '-modal').modal('hide');
        $('#' + id + '-slivkan').val('');
        $('#' + id + '-comments').val('');
        $('#' + id + '-' + extra).val('');
    });
};

module.exports = {
    init: function() {
        var i, date;
        var ptsInputTemplate = _.template($('#pts-input-template').html(), { imports: { forEach: _.forEach } });

        $('.committee-points-table td.pts').popover({
            placement: 'bottom auto',
            html: true,
            container: 'body',
            trigger: 'manual',
            content: function() {
                var contributions = [];
                var el = $(this);
                var isBonus = el.data('event') === 'bonus';
                var check = function(value) {
                    return contributions.indexOf(value) !== -1;
                };

                if (isBonus) {
                    return ptsInputTemplate({
                        value: el.text(),
                        contributions: false,
                        comments: el.data('comments')
                    });
                }

                contributions = el.data('contributions').split(',');

                return ptsInputTemplate({
                    value: el.text(),
                    contributions: true,
                    contributions_list: [
                        { pts: 1.5, title: 'Planned event', value: 'plan', selected: check('plan') },
                        { pts: 2.0, title: 'Ran event',     value: 'ran', selected: check('ran') },
                        { pts: 1.0, title: 'Poster',        value: 'poster', selected: check('poster') },
                        { pts: 0.5, title: 'Set up',        value: 'setup', selected: check('setup') },
                        { pts: 0.5, title: 'Clean up',      value: 'clean', selected: check('clean') },
                        { pts: 0,   title: 'Other',         value: 'other', selected: check('other') }
                    ],
                    comments: el.data('comments')
                });
            }
        });

        $('body').on('click', '.popover input[type=checkbox]', function(e) {
            var ptsInput, newVal;
            var target = $(e.target);

            if (!target.data('pts')) {
                return;
            }

            ptsInput = $('.pts-input').last();
            newVal = parseFloat(ptsInput.val()) + (target.prop('checked') ? 1 : -1) * parseFloat(target.data('pts'));

            if (newVal < -3) {
                ptsInput.val(-3);
            }else if (newVal > 3) {
                ptsInput.val(3);
            } else {
                ptsInput.val(newVal);
            }

            validatePoints(ptsInput);
        }).on('click', function(e) {
            var modified;
            var target = $(e.target);
            var clickedOutsidePopover = target.closest('.popover').length === 0;

            if (!openPopover && target.hasClass('pts')) {
                openPopover = target.popover('show');
            } else if (openPopover) {
                modified = isModified();

                if (target.hasClass('pts-input-submit')) {
                    if (modified) {
                        submitCommitteePoint();
                    } else {
                        openPopover.popover('hide');
                        openPopover = null;
                    }
                } else if ((!modified && clickedOutsidePopover) || target.hasClass('pts-input-cancel')) {
                    openPopover.popover('hide');

                    if (target.hasClass('pts') && !openPopover.is(target)) {
                        openPopover = target.popover('show');
                    } else {
                        openPopover = null;
                    }
                }
            }
        }).on('input', '.pts-input', function() {
            validatePoints($(this));
        });

        // dates for no-show form
        for (i = 0; i < 5; i++) {
            date = moment().subtract(i, 'days');
            $('<option />').text(date.format('ddd, M/D'))
                .attr('value', date.format('YYYY-MM-DD'))
                .appendTo('#no-show-date');
        }

        $('#helper-form').on('submit', function() {
            submitModalForm('submitHelperPoint', 'helper', 'event');
        });
        $('#no-show-form').on('submit', function() {
            submitModalForm('submitNoShow', 'no-show', 'date');
        });
    }
};
