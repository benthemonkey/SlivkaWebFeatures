/* eslint strict:0 */

$(function() {
    var setHeight = function(e) {
        e.height = e.contentWindow.document.body.scrollHeight + 35;
    };

    $('iframe.autoHeight').each(function() {
        setHeight(this);
    });
});
