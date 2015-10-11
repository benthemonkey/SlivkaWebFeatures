'use strict';

var _ = {
    find: require('lodash/collection/find'),
    forEach: require('lodash/collection/forEach')
};
var utils = require('./utils');
var slivkans, nicknames;
var submitConfigOrQuarterInfo = function(name, value, confirmMessage) {
    if (window.confirm(confirmMessage)) {
        $.post(utils.ajaxRoot + '/ajax/submitConfigOrQuarterInfo.php', { name: name, value: value }, function(status) {
            if (status === '1') {
                window.location.reload();
            } else {
                window.alert(status);
            }
        });
    }
};

var slivkanTypeahead = function() {
    var numInputs;
    var target = $(this);

    if (target.closest('.slivkan-entry-control').addClass('has-warning').is(':last-child')) {
        numInputs = $('#slivkan-entry-tab').find('.slivkan-entry').length;
        if (numInputs < 20) {
            utils.appendSlivkanInputs(1);
        }
    }
    if (!target.hasClass('tt-input')) {
        target.typeahead(null, utils.typeaheadOpts('slivkans', slivkans)).focus();
    }

    return false;
};

var validateSlivkanName = function(entry) {
    var valid = true;
    var slivkanEntry = entry.find('.slivkan-entry');
    var name = slivkanEntry.val();
    var nameArray = [];

    // clear duplicates
    $('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
        var _name = $(this).val();

        if (_name.length > 0) {
            if (nameArray.indexOf(_name) === -1) {
                nameArray.push(_name);
            } else {
                $(this).val('');
                $('#duplicate-alert').show();
            }
        }
    });

    // no names = invalid
    if (nameArray.length === 0) {
        valid = false;
    }

    if (name.length > 0) {
        if (!utils.findSlivkan(slivkans, name)) {
            valid = false;
        }
        utils.updateValidity(entry, valid);
    } else {
        utils.updateValidity(entry, null);
    }

    return valid;
};

var addSlivkans = function(data) {
    var $entries = $('.slivkan-entry-control', '#slivkan-entry-tab');
    var len = data.length;

    $entries.find('.slivkan-entry').val('').each(function(i, el) {
        utils.updateValidity($(el), null);
    });

    if ($entries.length <= len) {
        utils.appendSlivkanInputs(len - $entries.length + 1);
        $entries = $('.slivkan-entry-control', '#slivkan-entry-tab');
    }

    _.forEach(data, function(slivkan, i) {
        var $entry = $entries.eq(i);
        var name = _.find(slivkans, { nu_email: slivkan.nu_email }).full_name;

        $entry.find('.slivkan-entry').val(name);

        if (slivkan.points) {
            $entry.find('.committee-points').val(slivkan.points);
        }
    });
};

module.exports = {
    init: function() {
        var quarter = $('[data-current-quarter]').text();

        // $('.multiselect').multiselect({
        //     buttonClass: 'btn btn-default'
        // });

        $('[data-toggle="popover"]').popover().on('click', function() {
            return false;
        });

        $('#fellow-photo').parent().on('click', function() {
            $('select[name="fellow"]').show().siblings().hide();
        });

        $('#slivkan-photo').parent().on('click', function() {
            $('select[name="nu_email"]').show().siblings().hide();
        });

        $('[data-edit-qtr]').on('click', function() {
            var el = $(this).closest('tr');
            var val = el.find('.view:eq(0)').text().split(' ');

            el.find('.view').hide().siblings().show();

            switch (val[0]) {
            case 'Winter':
                $('#qtr-season').val('01');
                break;
            case 'Spring':
                $('#qtr-season').val('02');
                break;
            case 'Fall':
                $('#qtr-season').val('03');
            }
            $('#qtr-year').val(val[1]);

            el.find('[data-save]').on('click', function() {
                var qtr = $('#qtr-year').val().substr(2) + $('#qtr-season').val();

                submitConfigOrQuarterInfo('qtr', qtr, 'Update current quarter?');

                return false;
            });

            el.find('[data-cancel]').on('click', function() {
                el.find('.view').show().siblings().hide();

                return false;
            });

            return false;
        });

        $('[data-edit-ims]').on('click', function() {
            var el = $(this).closest('tr');

            el.find('.view').hide().siblings().show();

            el.find('[data-save]').on('click', function() {
                submitConfigOrQuarterInfo(
                    'im_teams',
                    JSON.stringify($('#im-select').val()),
                    'Update IM Teams for ' + quarter + '?'
                );

                return false;
            });

            el.find('[data-cancel]').on('click', function() {
                el.find('.view').show().siblings().hide();

                return false;
            });

            return false;
        });

        $('body').on('click', '[data-edit-toggle]', function() {
            var name = $(this).data('edit-toggle');
            var value = !$(this).data('value'); // flip value

            submitConfigOrQuarterInfo(name, value, 'Toggle value?');

            return false;
        });

        $('body').on('click', '[data-edit]', function() {
            var inputEl;
            var thisEl = $(this);
            var el = thisEl.closest('tr');
            var original = el.find('td:eq(1)').text();
            var type = thisEl.data('type') || 'text';
            var field = thisEl.data('edit');

            thisEl.hide();
            $(['<span class="edit">',
                    '<a href="#" data-save>Save</a><br>',
                    '<a href="#" data-cancel>Cancel</a>',
                '</span>'].join('')).appendTo(el.find('td:eq(2)'));

            el.find('td:eq(1)').html('<input type="' + type + '" class="form-control">');
            inputEl = el.find('input');
            inputEl.val(thisEl.data('value') || original);

            el.find('[data-save]').on('click', function() {
                var val = inputEl.val();
                var isConfig = el.closest('table').data('config');

                if (val === original) {
                    el.find('[data-cancel]').click();
                } else {
                    submitConfigOrQuarterInfo(
                        field,
                        val,
                        'Set ' + field + ' = "' + val + '"' + (isConfig ? '' : ' for ' + quarter) + '?'
                    );
                }

                return false;
            });

            el.find('[data-cancel]').on('click', function() {
                el.find('.edit').remove();
                el.find('td:eq(1)').html(original);
                thisEl.show();

                return false;
            });

            return false;
        });

        $.getJSON(utils.ajaxRoot + '/ajax/getSlivkans.php', function(data) {
            var i;

            slivkans = data.slivkans;

            utils.appendSlivkanInputs(9);

            $('#slivkan-entry-tab')
                .on('focus', '.slivkan-entry', slivkanTypeahead);
                // .on('typeahead:close', '.slivkan-entry',
                //     { callback: validateSlivkanName },
                //     utils.destroyTypeahead);

            $('[data-edit-committee]').on('click', function() {
                var committee = $('#edit-committee').val();

                $('.committee-points').val(0).show()
                    .attr('max', (committee === 'Exec' ? 40 : 20));

                $('#editCommitteeOrSuite').data('is-committee', true);
                $.getJSON(utils.ajaxRoot + '/ajax/getCommitteeOrSuite.php', { committee: committee }, addSlivkans);
            });

            $('[data-edit-suite]').on('click', function() {
                $('.committee-points').hide();
                $('#editCommitteeOrSuite').data('is-committee', false);
                $.getJSON(
                    utils.ajaxRoot + '/ajax/getCommitteeOrSuite.php',
                    { suite: $('#edit-suite').val() },
                    addSlivkans
                );
            });

            $('[data-copy-suites]').on('click', function() {
                if (window.confirm('Are you sure? This can only be done once per quarter.')) {
                    $.getJSON(utils.ajaxRoot + '/ajax/copySuites.php', function(response) {
                        if (response === '1') {
                            window.alert('Success!');
                        } else {
                            window.alert(response);
                        }
                    });
                }

                return false;
            });

            $('#editCommitteeOrSuite form').on('submit', function() {
                var name, pts, formData;
                var entries = $('.slivkan-entry', '#slivkan-entry-tab');
                var committeePoints = $('.committee-points');
                var committee = $('#edit-committee').val();
                var suite = $('#edit-suite').val();
                var isCommittee = $('#editCommitteeOrSuite').data('is-committee');
                var nuEmailArray = [];
                var committeePointsArray = [];

                for (i = 0; i < entries.length; i++) {
                    name = entries.eq(i).val();
                    if (name.length > 0) {
                        nuEmailArray.push(utils.findSlivkan(slivkans, name).nu_email);
                    }

                    if (isCommittee) {
                        pts = committeePoints.eq(i).val();
                        committeePointsArray.push(pts);
                    }
                }

                if (isCommittee) {
                    formData = {
                        committee: committee,
                        slivkans: nuEmailArray,
                        points: committeePointsArray
                    };
                } else {
                    formData = {
                        suite: suite,
                        slivkans: nuEmailArray
                    };
                }

                $.post(
                    utils.ajaxRoot + '/ajax/submitCommitteeOrSuite.php',
                    formData,
                    function(response) {
                        if (response === '1') {
                            $('#editCommitteeOrSuite').modal('hide');
                            window.alert('Success!');
                        } else {
                            window.alert(response);
                        }
                    }
                );

                return false;
            });
        });
    }
};
