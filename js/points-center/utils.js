'use strict';

var _ = {
    find: require('lodash/find'),
    template: require('lodash/template')
};
var Bloodhound = require('typeahead.js/dist/bloodhound');

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

// searches slivkans collection for either nu_email or full_name
module.exports.findSlivkan = function(slivkans, name) {
    var find = {};

    if (name.length === 0) {
        return null;
    } else {
        find[name.indexOf(' ') !== -1 ? 'full_name' : 'nu_email'] = name;

        return _.find(slivkans, find);
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
            suggestion: _.template(['<div<% if (typeof(dupe) != "undefined") { %> class="dupe"<% } %>><%= full_name %>',
                '<img src="/points/img/slivkans/<%= photo %>" /><div class="clearfix"></div></div>'].join(''))
        }
    };
};

module.exports.destroyTypeahead = function(event) {
    var target = $(this);

    if (target.hasClass('tt-input')) {
        event.data.callback(target.typeahead('destroy').closest('.form-group'));
    }
};
