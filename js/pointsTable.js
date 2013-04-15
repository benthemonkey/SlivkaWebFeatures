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
      { aTargets: event_targets, bSortable: false},
      { aTargets: event_targets.concat(totals_targets), sTitle: '', sWidth: "14px"},
      { aTargets: totals_targets, sClass: 'totals', asSorting: ['desc']}
      ],
      "bPaginate": false,
      "bAutoWidth": false,
      "sDom": '<"row-fluid"<"span3 table-info"><"span3"fi>><"row-fluid"<"header-row">><"row-fluid"<"span12"rt>>'
    });

    $('#table_filter label').html('Filter: <input type="text" aria-controls="table">');

    var cols_width = 120+14*(event_targets.length + totals_targets.length)+100;
    console.log(cols_width);

    $('.header-row').attr("id","columns");
    $('body').css("min-width",cols_width+"px");

    for(i=0; i<event_names.length; i++){
      $('<li />').html(event_names[i]).popover({
        trigger: 'hover',
        html: true,
        title: event_names[i],
        content: "Date: "+event_dates[i]+"<br/>Attendees: "+events.attendees[i]+(events.description[i].length > 0 ? "<br/>Description: "+events.description[i] : ""),
        placement: 'top',
        container: '.container-fluid'
      }).appendTo('#columns');
    }

    /*$('#columns li').each(function(index){
      $(this).popover({
        trigger: 'hover',
        html: true,
        title: event_names[index],
        content: "Date: "+event_dates[index]+"<br/>Attendees: "+events.attendees[index]+(events.description[index].length > 0 ? "<br/>Description: "+events.description[index] : ""),
        placement: 'top',
        container: '.container-fluid'
      });
    });*/

    //Append "totals" column labels
    $('<li />').addClass('totals-label').html("Events Total").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Helper Points").appendTo('#columns');
    $('<li />').addClass('totals-label').html("IM Sports").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Standing Committees").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Position-Related").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Total").appendTo('#columns');

    $('.totals-label').each(function(index){
      $(this).click(function(){$('th.totals').eq(index).click();});
    });

    $('td').each(function(){
      if($(this).html() == "1" && !$(this).hasClass('totals')){$(this).addClass("green");}
      else if($(this).html() == "0" && !$(this).hasClass('totals')){$(this).addClass("red");}
      else if($(this).html() == "1h" && !$(this).hasClass('totals')){$(this).addClass("gold");}
      else if($(this).html() == "1c" && !$(this).hasClass('totals')){$(this).addClass("blue");}
    });

    $('<div />').text('Hover over column labels to view event information, click to sort by totals.').css({fontSize: '14px'}).addClass('alert alert-info').prependTo('.table-info');
  });
});