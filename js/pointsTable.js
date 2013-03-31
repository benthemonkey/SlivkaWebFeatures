$(document).ready(function(){
  var event_names = new Array(),
  event_dates = new Array(),
  event_attendees = new Array(),
  event_descriptions = new Array();

  //nav
  $('.nav li').eq(1).addClass('active');

  var aDataSet = new Array(),
  events_targets = new Array();

  $.getJSON("ajax/getPointsTable.php",function(data){
    for(row in data.points_table){
      aDataSet.push([row].concat(data.points_table[row]));
    }

    for(var i=0;i<data.events.length;i++){
      en = data.events[i].event_name;
      name = en.substr(0,en.length-11);
      date = en.substr(en.length-5);

      events_targets.push(i+1);

      $('<li />').html(name).appendTo('#columns');

      event_names.push(name);
      event_dates.push(date);
      event_attendees.push(data.events[i].attendees);
      event_descriptions.push(data.events[i].description);
    }

    var totals_targets = new Array(), first_totals_target = data.events.length+1;
    for(var i=0; i<7; i++){
      totals_targets.push(first_totals_target + i);
    }

    $("#table").dataTable({
      "aaData": aDataSet,
      "aoColumnDefs": [
      { aTargets: [0], sTitle: "Name", sWidth: "120px", sClass: "name"},
      { aTargets: events_targets, bSortable: false},
      { aTargets: events_targets.concat(totals_targets), sTitle: '', sWidth: "14px"},
      { aTargets: totals_targets, sClass: 'totals', asSorting: ['desc']}
      ],
      "bPaginate": false,
      "bAutoWidth": false
    });

    $('#columns li').each(function(index){
      $(this).popover({
        trigger: 'hover',
        html: true,
        title: event_names[index],
        content: "Date: "+event_dates[index]+"<br/>Attendees: "+event_attendees[index]+(event_descriptions[index].length > 0 ? "<br/>Description: "+event_descriptions[index] : ""),
        placement: 'left',
        container: '.container-fluid'
      });
    });

    //Append "totals" column labels
    $('<li />').addClass('totals-label').html("P2P Total").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Events Total").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Helper Points").appendTo('#columns');
    $('<li />').addClass('totals-label').html("IM Sports").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Standing Committees").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Position-Related").appendTo('#columns');
    $('<li />').addClass('totals-label').html("Total").appendTo('#columns');

    $('.totals-label').each(function(index){
      $(this).click(function(){$('th.totals').eq(index).click();});
    })

    $('td').each(function(){
      if($(this).html() == "1" && !$(this).hasClass('totals')){$(this).addClass("green");}
      else if($(this).html() == "0" && !$(this).hasClass('totals')){$(this).addClass("red");}
      else if($(this).html() == "1h" && !$(this).hasClass('totals')){$(this).addClass("gold");}
      else if($(this).html() == "1c" && !$(this).hasClass('totals')){$(this).addClass("blue");}
    });

    $('<div />').text('Hover over column labels to view event information, click to sort by totals.').css({fontSize: '14px',float: 'right'}).addClass('alert alert-info').prependTo('#table_wrapper')
  });
})