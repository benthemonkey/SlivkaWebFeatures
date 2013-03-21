(function($) {
  $.widget('mobile.tabbar', $.mobile.navbar, {
    _create: function() {
      // Set the theme before we call the prototype, which will 
      // ensure buttonMarkup() correctly grabs the inheritied theme.
      // We default to the "a" swatch if none is found
      var theme = this.element.jqmData('theme') || "a";
      this.element.addClass('ui-footer ui-footer-fixed ui-bar-' + theme);
 
      // Call the NavBar _create prototype
      $.mobile.navbar.prototype._create.call(this);
    },
 
    // Set the active URL for the Tab Bar, and highlight that button on the bar
    setActive: function(url) {
      this.element.find('a[href="#' + url + '"]').addClass('ui-btn-active ui-state-persist');
    }
  });
 
  $(document).bind('pagecreate create', function(e) {
    return $(e.target).find(":jqmData(role='tabbar')").tabbar();
  });
  
  $(":jqmData(role='page')").live('pageshow', function(e) {
    // Grab the id of the page that's showing, and select it on the Tab Bar on the page
    var tabBar, id = $(e.target).attr('id');
 
    tabBar = $.mobile.activePage.find(':jqmData(role="tabbar")');
    if(tabBar.length) {
      tabBar.tabbar('setActive', id);
    }
  });
})(jQuery);

var slivkans = new Array();

$(document).ready(function(){
    $.getJSON("getSlivkans.php",function(data){ //http://slivka.northwestern.edu
        for (i in data){
            slivkans.push(data[i]);
            var id_name = data[i].replace(" ","-")+"-attendance";
            $("<input>").attr("id",id_name).attr("type","checkbox").appendTo("#slivkans-list");
            $("<label></label>").text(data[i]).attr("for",id_name).appendTo("#slivkans-list");
        }
    })

    $("#date").datepicker({
      minDate: -5,
      maxDate: 0,
    })


})

function validateFilledBy(){
  var valid, name = $('#filled-by').val();

  valid = slivkans.indexOf(name) != -1;

  if (valid){
    $('#filled-by-error').addClass("success");
    $('#filled-by-error').removeClass("error");
  }else{
    $('#filled-by-error').removeClass("success");
    $('#filled-by-error').addClass("error");
  }
}