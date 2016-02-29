'use strict';

var _ = {
    findIndex: require('lodash/findIndex')
};
var typeahead = require('typeahead.js');
var utils = require('./utils');
var slivkans;
var resetForm = function() {
    $('#filled-by').val('');
    $('.filled-by-control').removeClass('has-success has-error');
    $('#event-name').val('Select One');
    $('#comments').val('');
    $('#submit-error').fadeOut();
};

var validateFilledBy = function() {
    return utils.updateValidity($('.filled-by-control'), utils.findSlivkan(slivkans, $('#filled-by').val()));
};

var submitPointsCorrection = function() {
    var data = {
        event_name: $('#event-name').val(),
        name: $('#filled-by').val(),
        sender_email: utils.findSlivkan(slivkans, $('#filled-by').val()).nu_email,
        comments: $('#comments').val()
    };

    $('#response').fadeOut();

    $.post(utils.ajaxRoot + '/ajax/sendPointsCorrection.php', data, function(response) {
        $('#response').text('Response: ' + response.message);
        $('#form-actions').html('<a class="btn btn-primary" href="../table/">View Points</a>' +
            '<a class="btn btn-default" href="../correction/">Submit Another</a>');
        $('#response').fadeIn();
    }, 'json');
};

var validatePointsCorrectionForm = function() {
    var valid = true;
    var errors = [];

    if (!validateFilledBy()) {
        valid = false;
        errors.push('Your Name');
    }
    if ($('#event-name').val() === 'Select One') {
        valid = false;
        errors.push('Event Name');
    }
    if ($('#comments').val() === '') {
        valid = false;
        errors.push('Comments');
    }

    if (valid) {
        $('#submit-error').fadeOut();
        submitPointsCorrection();
    } else {
        $('#submit-error').text('Validation errors in: ' + errors.join(', ')).fadeIn();
    }
};

module.exports = {
    init: function() {
        $.getJSON(utils.ajaxRoot + '/ajax/getSlivkans.php', function(data) {
            slivkans = data.slivkans;

            $('#filled-by').typeahead(null, utils.typeaheadOpts('slivkans', slivkans));
        });

        $.getJSON(utils.ajaxRoot + '/ajax/getRecentEvents.php', function(events) {
            var i;

            for (i = events.length - 1; i >= 0; i--) {
                if (events[i].type === 'p2p') {
                    $('<option disabled="disabled"></option>').text(events[i].event_name).appendTo('#event-name');
                } else {
                    $('<option></option>').text(events[i].event_name).appendTo('#event-name');
                }
            }
        });

        // event handlers
        $('#filled-by').on('focusout', validateFilledBy);
        $('#submit').on('click', validatePointsCorrectionForm);
    }
};
