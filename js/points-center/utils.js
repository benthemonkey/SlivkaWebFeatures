'use strict';

var _ = {
    findIndex: require('lodash/array/findIndex'),
    template: require('lodash/string/template')
};
var Bloodhound = require('typeahead.js/dist/bloodhound');

// Track TAB_PRESSED for destroyTypeahead
var TAB_PRESSED = false;

$(window).on('keydown',	function(event) {
    if (event.keyCode === 9 && !event.shiftKey) {
        TAB_PRESSED = true;
    }
}).on('keyup', function(event) {
    if (event.keyCode === 9) {
        TAB_PRESSED = false;
    }
});

module.exports.ajaxRoot = '/points';

module.exports.appendSlivkanInputs = function(n) {
    // 2-4ms per insertion. Slow but acceptable.
    var i;
    var cloned = $('#slivkan-entry-tab').find('.slivkan-entry-control').last();
    var start = parseInt(cloned.find('.input-group-addon').text(), 10);

    for (i = 0; i < n; i++) {
        cloned.clone().appendTo('#slivkan-entry-tab')
        .removeClass('has-warning')
        .find('.input-group-addon').text(start + i + 1);
    }
};

module.exports.slivkanNameExists = function(slivkans, name) {
    var find = {};

    if (name.length === 0) {
        return null;
    } else {
        find[name.indexOf(' ') !== -1 ? 'full_name' : 'nu_email'] = name;

        return _.findIndex(slivkans, find) !== -1;
    }
};

module.exports.updateValidity = function($element, valid) {
    if (valid === null) {
        $element.removeClass('has-success has-warning has-error');
    } else if (valid) {
        $element.addClass('has-success').removeClass('has-error has-warning');
    } else {
        $element.removeClass('has-success has-warning').addClass('has-error');
    }

    return valid;
};

module.exports.typeaheadOpts = function(name, slivkans) {
    var tmp = new Bloodhound({
        datumTokenizer: function(d) {
            return d.tokens;
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: slivkans
    });

    tmp.initialize();

    return {
        name: name,
        displayKey: 'full_name',
        source: tmp.ttAdapter(),
        templates: {
            suggestion: _.template(['<div class="slivkan-suggestion',
                '<% if (typeof(dupe) !== "undefined") { print(" slivkan-dupe") } %>">',
                '<%= full_name %><img src="/points/img/slivkans/<%= photo %>" /></div>'].join(''))
        }
    };
};

module.exports.destroyTypeahead = function(event) {
    var target = $(this);

    if (target.hasClass('tt-input')) {
        // needs a delay because typeahead.js seems to not like destroying on focusout
        setTimeout(function(_target) {
            event.data.callback(target.typeahead('destroy').closest('.form-group'));

            if (TAB_PRESSED) {
                _target.closest('.form-group').next().find('input').focus();
            }
        }, 1, target);
    }
};
