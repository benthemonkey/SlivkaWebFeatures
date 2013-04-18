$(document).ready(function(){
  //nav
  $('.nav li').eq(1).addClass('active');

  var aDataSet = [],
  event_names = [],
  event_dates = [],
  event_targets = [],
  events = [];

  $.getJSON("ajax/getPointsTable.php",function(data){
    events = data.events;

    for(var row in data.points_table){
      aDataSet.push([row].concat(data.points_table[row]));
    }

    for(var i=0;i<events.event_name.length;i++){
      var en = events.event_name[i],
      name = en.substr(0,en.length-11),
      date = en.substr(en.length-5);

      event_targets.push(i+2);

      event_names.push(name);
      event_dates.push(date);
    }

    var totals_targets = [],
    first_totals_target = data.events.event_name.length+2;
    for(i=0; i<6; i++){
      totals_targets.push(first_totals_target + i);
    }

    $("#table").dataTable({
      "aaData": aDataSet,
      "aoColumnDefs": [
      { aTargets: [0], sTitle: "Name", sWidth: "120px", sClass: "name"},
      { aTargets: [1], bVisible: false },
      { aTargets: event_targets, asSorting: ['desc']},
      { aTargets: event_targets.concat(totals_targets), sTitle: '', sWidth: "14px"},
      { aTargets: totals_targets, sClass: 'totals', asSorting: ['desc']}
      ],
      "bPaginate": false,
      "bAutoWidth": false,
      "sDom": '<"row-fluid"<"span3 table-info"><"span3"fi><"span3 filter">><"row-fluid"<"header-row">><"row-fluid"<"span12"rt>>'
    });

    $('#table_filter label').html('Filter: <input type="text" aria-controls="table">');

    var cols_width = 120+14*(event_targets.length + totals_targets.length)+100;
    console.log(cols_width);

    $('body').css("min-width",cols_width+"px");
    $('.header-row').attr('id','columns');
    var columns = $('.header-row');

    for(i=0; i<event_names.length; i++){
      $('<li />').html(event_names[i]).popover({
        trigger: 'hover',
        html: true,
        title: event_names[i],
        content: "Date: "+event_dates[i]+"<br/>Attendees: "+events.attendees[i]+(events.description[i].length > 0 ? "<br/>Description: "+events.description[i] : ""),
        placement: 'bottom',
        container: '.container-fluid'
      }).appendTo('#columns');
    }

    //Append "totals" column labels
    $('<li />').addClass('totals-label').html("Events Total").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Helper Points").appendTo('#columns');
    $('<li />').addClass('totals-label').html("IM Sports").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Standing Committees").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Position-Related").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Total").appendTo('#columns');

    //event handler for column labels
    $('#columns').find('li').each(function(index){
      $(this).on('click',function(){$('#table').find('th').eq(index+1).click();});
    });

    /*$('.totals-label').each(function(index){
      $(this).click(function(){$('th.totals').eq(index).click();});
    });*/

    $('td').each(function(){
      var self = $(this);
      if(!self.hasClass('totals')){
        if(self.html() == "1"){self.addClass("green");}
        else if(self.html() == "0"){self.addClass("red");}
        else if(self.html() == "1h"){self.addClass("gold"); self.html("1");}
        else if(self.html() == "1c"){self.addClass("blue");}
      }
    });

    $('<div />').text('Hover over column labels to view event information, click to sort by totals.').css({fontSize: '14px'}).addClass('alert alert-info').prependTo('.table-info');
  });
});