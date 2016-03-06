'use strict';

var moment = require('moment');
var _ = {
    findIndex: require('lodash/findIndex'),
    forEach: require('lodash/forEach'),
    cloneDeep: require('lodash/cloneDeep'),
    template: require('lodash/template')
};
var utils = require('./utils');
var slivkans, fellows;
var type = 'Other';
var typeaheadUniqueIndex = 0;
var VALID_EVENT_NAME = false;

var appendFellowInputs = function(n) {
    var i;
    var cloned = $('#fellow-entry-tab').find('.fellow-entry-control').last();
    var start = parseInt(cloned.find('.input-group-addon').text(), 10);

    for (i = 0; i < n; i++) {
        cloned.clone().appendTo('#fellow-entry-tab')
        .removeClass('has-warning')
        .find('.input-group-addon').text(start + i + 1);
    }
};

var saveSlivkans = function() {
    var nameArray = [];

    // forming name array, but appending values corresponding to the helper/committee buttons:
    // 0 - unpressed, 1 - pressed
    $('#slivkan-entry-tab').find('.slivkan-entry-control').each(function() {
        var $self = $(this);
        var name = $self.find('.slivkan-entry').val();

        if (name.length > 0) {
            nameArray.push(name);
        }
    });

    localStorage.spc_sub_attendees = nameArray.join(', ');

    return nameArray;
};

var validateSlivkanName = function(entry, inBulk) {
    var foundSlivkan;
    var valid = true;
    var slivkanEntry = entry.find('.slivkan-entry');
    var name = slivkanEntry.val();
    var nameArray = [];

    // only process individually
    if (!inBulk) {
        // clear duplicates
        $('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
            var $self = $(this);
            var _name = $self.val();

            if (_name.length > 0) {
                if (nameArray.indexOf(_name) === -1) {
                    nameArray.push(_name);
                } else {
                    $self.val('');
                    $('#duplicate-alert').slideDown();
                    validateSlivkanName($self.parent(), true);
                }
            }
        });

        // no names = invalid
        if (nameArray.length === 0) {
            valid = false;
        }

        // store values
        saveSlivkans();

        // update name in case it changed
        name = slivkanEntry.val();
    }

    if (name.length > 0) {
        foundSlivkan = utils.findSlivkan(slivkans, name);

        if (!foundSlivkan) {
            valid = false;
        } else if (type === 'Committee Only' && $('#committee').val() !== foundSlivkan.committee) {
            valid = false;
        }

        utils.updateValidity(entry, valid);
    } else {
        utils.updateValidity(entry, null);
    }

    return valid;
};

var validateFellowName = function(entry) {
    var valid = true;
    var nameArray = [];
    var fellowEntry = entry.find('.fellow-entry');
    var name = fellowEntry.val();

    // clear duplicates
    $('.fellow-entry').each(function() {
        if (nameArray.indexOf($(this).val()) !== -1) {
            $(this).val('');
            name = '';
            $('#duplicate-alert').slideDown();
        }

        if ($(this).val().length > 0) {
            nameArray.push($(this).val());
        }
    });

    // save fellows
    localStorage.spc_sub_fellows = nameArray.join(', ');

    entry.removeClass('has-warning');

    if (name.length > 0) {
        valid = _.findIndex(fellows, { full_name: name }) !== -1;
        utils.updateValidity(entry, valid);
    } else {
        entry.removeClass('has-success has-error');
    }

    return valid;
};

var validateEventName = function() {
    var valid = false;
    var eventEl = $('#event');
    var eventName = eventEl.val();
    var eventNameTrimmed = eventName.replace(/^\s+|\s+$/g, '');

    // errors abound in the PHP with trailing whitespace
    if (eventName.length > eventNameTrimmed.length) {
        $('#event').val(eventNameTrimmed);
        eventName = eventNameTrimmed;
    }

    // store value
    localStorage.spc_sub_name = eventName;

    VALID_EVENT_NAME = false;

    if (eventName.length === 0) {
        utils.updateValidity($('.event-control'), null);
    } else if ((eventName.length <= 32 && eventName.length >= 8) || (type === 'P2P' && eventName === 'P2P')) {
        eventName += ' ' + $('#date').val();

        $.getJSON(utils.ajaxRoot + '/ajax/eventNameExists.php', { event_name: eventName }, function(response) {
            var last;

            if (response.eventNameExists) {
                if (type === 'IM') {
                    last = parseInt(eventEl.val().slice(-1), 10);
                    eventEl.val(eventEl.val().slice(0, -1) + (last + 1).toString());
                    validateEventName();
                } else {
                    VALID_EVENT_NAME = false;
                    $('#event-name-error').fadeIn();
                }
            } else {
                VALID_EVENT_NAME = true;
                $('#event-name-error').fadeOut();
            }

            $('#event-name-length-error').fadeOut();
            utils.updateValidity($('.event-control'), VALID_EVENT_NAME);
        });
    } else {
        $('#event-name-length-error-count').html('Currently ' + eventName.length + ' characters');
        $('#event-name-length-error').fadeIn();
        utils.updateValidity($('.event-control'), VALID_EVENT_NAME);
    }

    return valid;
};

var validateIMTeam = function() {
    var imTeam = $('#im-team').val();

    $.getJSON(utils.ajaxRoot + '/ajax/getIMs.php', { team: imTeam }, function(events) {
        $('#event').val(imTeam + ' ' + (events.length + 1));
        $('#description').val(imTeam.split(' ')[1]);
        validateEventName();
    });
};

var validateCommittee = function() {
    var committee = $('#committee').val();
    var valid = committee !== 'Select One';

    utils.updateValidity($('.committee-control'), valid);

    if (valid) {
        localStorage.spc_sub_committee = committee;

        $('.slivkan-entry-control').each(function(index) {
            validateSlivkanName($(this), (index !== 0));
        });
    }

    return valid;
};

var validateDescription = function() {
    var valid = true;
    var description = $('#description').val();

    // store value
    localStorage.spc_sub_description = description;

    if (description.length < 10 && type === 'Other') {
        valid = false;
        $('#description-length-error').fadeIn();
    } else {
        $('#description-length-error').fadeOut();
    }

    utils.updateValidity($('.description-control'), valid);

    return valid;
};

var validateFilledBy = function() {
    var name = $('#filled-by').val();

    $('.filled-by-control').removeClass('has-warning');

    if (name.length === 0) {
        return false;
    }

    // store value
    localStorage.spc_sub_filledby = name;

    return utils.updateValidity($('.filled-by-control'), utils.findSlivkan(slivkans, name));
};

var toggleType = function(event) {
    var previousType;

    type = $(event.target).closest('label').find('input').val();

    // store value
    localStorage.spc_sub_type = type;

    // clear description if **previous** type was IM
    previousType = $('.type-btn.active').find('input').val();
    if (previousType === 'IM') {
        $('#description').val('');
        validateDescription();
    }

    $('#committee').attr('disabled', 'disabled');

    switch (type) {
    case 'P2P':
        $('#committee').val('Faculty');
        $('#event').val('P2P');
        break;
    case 'IM':
        $('#committee').val('Social');
        validateIMTeam();
        break;
    case 'House Meeting':
        $('#committee').val('Exec');
        $('#event').val('House Meeting');
        break;
    case 'Committee Only':
        if ($('#committee :selected').hasClass('not-standing-committee')) {
            $('#committee').val('Academic');
        }
        $('#event').val('');
        $('#committee').removeAttr('disabled');
        break;
    default:
        $('#event').val('');
        $('#committee').removeAttr('disabled');
    }

    validateEventName();
    validateCommittee();

    if (type === 'IM') {
        $('.im-team-control').slideDown();
        $('#event').attr('disabled', 'disabled');
    } else {
        $('.im-team-control').slideUp();
        $('#event').removeAttr('disabled');
    }

    if (type === 'Committee Only') {
        $('.not-standing-committee').attr('disabled', 'disabled');
    } else {
        $('.not-standing-committee').removeAttr('disabled');
    }

    if (type === 'Other') {
        $('.description-control').slideDown();
    } else {
        $('.description-control').slideUp();
    }
};

var processBulkNames = function() {
    var names = $('#bulk-names').val();

    // remove '__ mins ago' and blank lines
    names = names.replace(/(\d+ .+ago[\r\n]?$)|(^[\r\n])/gm, '');

    $('#bulk-names').val(names);
};

var addBulkNames = function() {
    var i, k, n, nameArray, slivkanEntries, len, name, wildcardInd, ind;
    var slots = [];
    var freeSlots = 0;
    var names = $('#bulk-names').val();

    $('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
        if ($(this).val().length > 0) {
            slots.push(1);
        } else {
            slots.push(0);
            freeSlots++;
        }
    });

    // if there's a hanging newline, remove it for adding but leave it in the textarea
    if (names[names.length - 1] === '\r' || names[names.length - 1] === '\n') {
        names = names.slice(0, names.length - 1);
    }

    nameArray = names.split(/[\r\n]/gm);

    if (nameArray.length >= freeSlots) {
        n = nameArray.length - freeSlots + 1;
        utils.appendSlivkanInputs(n);
        for (k = 0; k < n; k++) {
            slots.push(0);
        }
    }

    slivkanEntries = $('#slivkan-entry-tab').find('.slivkan-entry');
    len = nameArray.length;
    for (i = 0; i < len; i++) {
        name = nameArray[i];

        // check if wildcard
        wildcardInd = _.findIndex(slivkans, { wildcard: name });
        if (wildcardInd !== -1) {
            name = slivkans[wildcardInd].full_name;
        }

        ind = slots.indexOf(0);
        slots[ind] = 1;
        slivkanEntries.eq(ind).val(name);
        validateSlivkanName(slivkanEntries.eq(ind).closest('.slivkan-entry-control'), (i < len - 1));
    }
};

var addSlivkans = function(nameArray) {
    var i, name, entry;
    var entries = $('#slivkan-entry-tab').find('.slivkan-entry-control');
    var len = nameArray.length;

    for (i = 0; i < len; i++) {
        name = nameArray[i];
        entry = entries.eq(i);
        entry.find('.slivkan-entry').val(name);
        validateSlivkanName(entry, (i < len - 1));
    }

    for (i; i < entries.length; i++) {
        validateSlivkanName(entries.eq(i), true);
    }
};

var sortEntries = function() {
    var nameArray = saveSlivkans();

    // clear slivkans
    $('#slivkan-entry-tab').find('.slivkan-entry').val('');

    nameArray = nameArray.sort();

    addSlivkans(nameArray);

    $('#sort-alert').slideDown();
};

var resetForm = function(force) {
    if (force === 'force' || window.confirm('Reset form?')) {
        $('.type-btn:last').click();
        $('#event').val('');
        $('.event-control').removeClass('has-success has-warning has-error');

        $('#date').val(moment().format('ddd, M/D'));
        $('#description').val('');
        $('.description-control').removeClass('has-success has-error');

        $('#committee').val('Select One');
        $('.committee-control').removeClass('has-success has-error');

        $('#filled-by').val('');
        $('.filled-by-control').removeClass('has-success has-error');

        $('#comments').val('');

        $('#slivkan-entry-tab').find('.slivkan-entry-control').slice(15).remove();

        $('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
            $(this).val('');
            validateSlivkanName($(this).closest('.form-group'), true);
        });
        validateSlivkanName($('.slivkan-entry-control').last());

        $('.fellow-entry').each(function() {
            $(this).val('');
            validateFellowName($(this).closest('.form-group'));
        });

        $('#event-name-error').fadeOut();
        $('#event-name-length-error').fadeOut();
        $('#description-length-error').fadeOut();
        $('#duplicate-error').fadeOut();
        $('#submit-error').fadeOut();

        localStorage.spc_sub_filledby = '';
        localStorage.spc_sub_type = '';
        localStorage.spc_sub_name = '';
        localStorage.spc_sub_date = '';
        localStorage.spc_sub_committee = '';
        localStorage.spc_sub_description = '';
        localStorage.spc_sub_comments = '';
        localStorage.spc_sub_attendees = '';
    }
};

var submitPointsForm = function() {
    var $realSubmit = $('#real-submit');
    var resultsTemplate = _.template($('#resultsTemplate').html(), { imports: { forEach: _.forEach } });
    var data = {
        date: $('#date').val(),
        type: type.toLowerCase().replace(' ', '_'),
        committee: $('#committee').val(),
        event_name: $('#event').val(),
        description: $('#description').val(),
        filled_by: utils.findSlivkan(slivkans, $('#filled-by').val()).nu_email,
        comments: $('#comments').val(),
        attendees: [],
        committee_members: [],
        fellows: []
    };

    $('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
        var slivkan = utils.findSlivkan(slivkans, $(this).val());

        if (slivkan) {
            data.attendees.push(slivkan.nu_email);

            if (slivkan.committee === data.committee && data.committee !== 'Exec' && type !== 'p2p' && type !== 'im') {
                data.committee_members.push(slivkan.nu_email);
            }
        }
    });

    $('.fellow-entry').each(function() {
        var name = $(this).val();

        if (name.length > 0) {
            data.fellows.push(name);
        }
    });

    // clear receipt:
    $('#receipt').html(resultsTemplate({ data: data }));

    $('#submit-results').modal('show');

    $realSubmit.off('click');
    $realSubmit.on('click', function() {
        $realSubmit.button('loading');

        $.post(utils.ajaxRoot + '/ajax/submitPointsForm.php', data, function(dataIn) {
            $realSubmit.button('reset');
            $('#results-status').parent().removeClass('has-warning');
            if (dataIn.error) {
                $('#results-status').text('Error in Step ' + dataIn.step)
                    .parent().removeClass('warning').addClass('error');
            } else {
                $('#unconfirmed').fadeOut({
                    complete: function() {
                        $('#confirmed').fadeIn();
                    }
                });

                // reset buttons once modal closes
                $('#submit-results').on('hidden.bs.modal', function() {
                    $('#confirmed').hide();
                    $('#unconfirmed').show();
                });

                $('#results-status').text('Success!').parent().removeClass('warning').addClass('success');

                resetForm('force');
            }
        }, 'json');
    });
};

var validatePointsForm = function() {
    var valid = true;
    var validSlivkans = true;
    var validFellows = true;
    var errors = [];

    if (!validateFilledBy()) {
        valid = false;
        errors.push('Filled By');
    }

    if (!VALID_EVENT_NAME) {
        valid = false;
        utils.updateValidity($('.event-control'), valid);
        errors.push('Name');
    }

    if (!validateCommittee()) {
        valid = false;
        errors.push('Committee');
    }

    if (!validateDescription()) {
        valid = false;
        errors.push('Description');
    }

    $('.slivkan-entry-control').each(function(index) {
        if (!validateSlivkanName($(this), (index !== 0))) {
            validSlivkans = false;
        }
    });

    if (!validSlivkans) {
        valid = false;
        errors.push('Attendees');
    }

    $('.fellow-entry-control').each(function() {
        if (!validateFellowName($(this))) {
            validFellows = false;
        }
    });

    if (!validFellows) {
        valid = false;
        errors.push('Fellows');
    }

    if (valid) {
        $('#submit-error').fadeOut();
        submitPointsForm();
    } else {
        $('#submit-error').text('Validation errors in: ' + errors.join(', ')).fadeIn();
    }

    return valid;
};

var slivkanTypeahead = function() {
    var ind, committee, numInputs;
    var target = $(this);
    var slivkansTmp = _.cloneDeep(slivkans);

    if (localStorage.spc_sub_attendees) {
        localStorage.spc_sub_attendees.split(', ').forEach(function(el) {
            ind = _.findIndex(slivkansTmp, { full_name: el });
            if (ind !== -1) {
                slivkansTmp[ind].dupe = true;
            }
        });
    }

    if (type === 'Committee Only') {
        committee = $('#committee').val();

        slivkansTmp = slivkansTmp.filter(function(item) {
            return item.committee === committee;
        });
    }

    if (target.closest('.slivkan-entry-control').addClass('has-warning').is(':last-child')) {
        numInputs = $('#slivkan-entry-tab').find('.slivkan-entry').length;
        if (numInputs < 120) {
            utils.appendSlivkanInputs(1);
        }
    }

    if (!target.hasClass('tt-input')) {
        typeaheadUniqueIndex++;
        target.typeahead(null, utils.typeaheadOpts('slivkans' + typeaheadUniqueIndex, slivkansTmp)).focus();
    }
};

var fellowTypeahead = function() {
    var numInputs;
    var target = $(this);

    if (target.closest('.fellow-entry-control').addClass('has-warning').is(':last-child')) {
        numInputs = $('#fellow-entry-tab').find('.fellow-entry').length;
        if (numInputs < 20) {
            appendFellowInputs(1);
        }
    }
    if (!target.hasClass('tt-input')) {
        target.typeahead(null, utils.typeaheadOpts('fellows', fellows)).focus();
    }
};

module.exports = {
    init: function() {
        var i, date;

        $(window).on('keydown', function(event) {
            if (event.keyCode === 13) { // prevent [Enter] from causing form submit
                event.preventDefault();
                event.stopPropogation();
            }
        });

        $.getJSON(utils.ajaxRoot + '/ajax/getSlivkans.php', function(data) {
            var attendees, fellowEntry, fellowAttendees;
            var imTeams = data.im_teams;

            slivkans = data.slivkans;
            fellows = data.fellows;

            // initialization
            utils.appendSlivkanInputs(14);
            appendFellowInputs(9);

            // im teams
            for (i = 0; i < imTeams.length; i++) {
                $('<option />').text(imTeams[i]).appendTo('#im-team');
            }

            // loading saved values
            if (localStorage.spc_sub_committee) {
                $('#committee').val(localStorage.spc_sub_committee);
                // validateCommittee();
            }
            if (localStorage.spc_sub_attendees) {
                attendees = localStorage.spc_sub_attendees.split(', ');
                if (attendees.length > 14) {
                    utils.appendSlivkanInputs(attendees.length - 14);
                }
                addSlivkans(attendees);
            }
            if (localStorage.spc_sub_filledby) {
                $('#filled-by').val(localStorage.spc_sub_filledby);
                validateFilledBy();
            }
            if (localStorage.spc_sub_type && localStorage.spc_sub_type !== 'Other') {
                $('input[value="' + localStorage.spc_sub_type + '"]:radio').parent().click();
            }
            if (localStorage.spc_sub_date) {
                $('#date').val(localStorage.spc_sub_date);
            }
            if (localStorage.spc_sub_name) {
                $('#event').val(localStorage.spc_sub_name);
                validateEventName();
            }
            if (localStorage.spc_sub_description) {
                $('#description').val(localStorage.spc_sub_description);
                validateDescription();
            }
            if (localStorage.spc_sub_comments) {
                $('#comments').val(localStorage.spc_sub_comments);
            }
            if (localStorage.spc_sub_fellows) {
                fellowAttendees = localStorage.spc_sub_fellows.split(', ');

                if (fellowAttendees.length > 9) {
                    appendFellowInputs(fellowAttendees.length - 9);
                }

                for (i = 0; i < fellowAttendees.length; i++) {
                    fellowEntry = $('.fellow-entry').eq(i);
                    fellowEntry.val(fellowAttendees[i]);
                    validateFellowName(fellowEntry.closest('.form-group'));
                }
            }

            // autocomplete and events for slivkan/fellow inputs
            $('#filled-by').typeahead(null, utils.typeaheadOpts('slivkans', slivkans));

            $('#slivkan-entry-tab')
                .on('focus', '.slivkan-entry', slivkanTypeahead)
                .on('typeahead:close', '.slivkan-entry',
                    { callback: validateSlivkanName },
                    utils.destroyTypeahead)
                .on('typeahead:autocomplete typeahead:select', '.slivkan-entry', function() {
                    $(this).closest('.form-group').next().find('input').focus();
                });

            $('#fellow-entry-tab')
                .on('focus', '.fellow-entry', fellowTypeahead)
                .on('typeahead:close', '.fellow-entry',
                    { callback: validateFellowName },
                    utils.destroyTypeahead)
                .on('typeahead:autocomplete typeahead:select', '.fellow-entry', function() {
                    $(this).closest('.form-group').next().find('input').focus();
                });
        });

        // dates
        for (i = 0; i < 8; i++) {
            date = moment().subtract(i, 'days');
            $('<option />').text(date.format('ddd, M/D')).attr('value', date.format('YYYY-MM-DD')).appendTo('#date');
        }

        // event handlers for inputs
        $('#filled-by')         .on('focus',    function() { $(this).closest('.form-group').addClass('has-warning'); })
                                .on('focusout', validateFilledBy);
        $('#type')              .on('click',    toggleType);
        $('#event')             .on('focus',    function() { $(this).closest('.form-group').addClass('has-warning'); })
                                .on('focusout', validateEventName);
        $('#date')              .on('change',   function() {
            localStorage.spc_sub_date = $(this).val();
            validateEventName();
        });
        $('#im-team')           .on('change',   validateIMTeam);
        $('#committee')         .on('change',   validateCommittee);
        $('#description')       .on('focusout', validateDescription);
        $('#comments')          .on('focusout', function() { localStorage.spc_sub_comments = $(this).val(); });
        $('#close-sort-alert')  .on('click',    function() { $('#sort-alert').slideUp(); });
        $('#close-dupe-alert')  .on('click',    function() { $('#duplicate-alert').slideUp(); });
        $('#sort-entries')      .on('click',    sortEntries);
        $('#submit')            .on('click',    validatePointsForm);
        $('#reset')             .on('click',    resetForm);
        $('#bulk-names')        .on('keyup',    processBulkNames);
        $('#add-bulk-names')    .on('click',    addBulkNames);

        $('#tabs').find('a:first').tab('show');
    }
};
