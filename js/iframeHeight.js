jQuery(window).load(function() {
	var setHeight = function(e){
		e.height = e.contentWindow.document.body.scrollHeight + 35;
	};

	jQuery('iframe.autoHeight').each(function(){ setHeight(this); });
});
