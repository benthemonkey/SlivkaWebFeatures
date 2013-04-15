var slivkans,events;

google.load("visualization", "1", {packages:["corechart"]});

$(document).ready(function(){
    //nav
    $('.nav li').eq(0).addClass('active');

    $.getJSON('ajax/getSlivkans.php',function(data){
        slivkans = data.slivkans;

        for(var i=0; i<slivkans.full_name.length; i++){
            $('<option />').attr("value",slivkans.nu_email[i]).text(slivkans.full_name[i]).appendTo('#slivkan');
        }

        if(localStorage.points){
            $('#slivkan').val(localStorage.points);
            getSlivkanPoints();
        }

        if(!localStorage.showUnattended){ $('#showUnattended').click(); }
    });

    $( "#start" ).datepicker({
        showOn: "button",
        dateFormat: "m/d",
        altField: "#start-val",
        altFormat: "yy-mm-dd",
        minDate: '4/1',//first day of winter finals
        maxDate: localStorage.pointsEnd || '6/10',//Last day of spring quarter
        onSelect: function( selectedDate ) {
            $( "#end" ).datepicker( "option", "minDate", selectedDate );
            localStorage.pointsStart = selectedDate;
            fixDateButtons();
            getSlivkanPoints();
        }
    });
    $( "#end" ).datepicker({
        showOn: "button",
        dateFormat: "m/d",
        altField: "#end-val",
        altFormat: "yy-mm-dd",
        minDate: localStorage.pointsStart || '4/1',//first day of winter finals
        maxDate: '6/10',//Last day of spring quarter
        onSelect: function( selectedDate ) {
            $('#start').datepicker( "option", "maxDate", selectedDate );
            localStorage.pointsEnd = selectedDate;
            fixDateButtons();
            getSlivkanPoints();
        }
    });

    $("#start").datepicker("setDate", localStorage.pointsStart || "4/1");
    $("#end").datepicker("setDate",localStorage.pointsEnd || '6/10');

    fixDateButtons();

    $('#showUnattended').on('click',function(event){ toggleShowUnattended(event); });
    //if(localStorage.showUnattended){ $('#showUnattended').click(); }
});

function fixDateButtons(){
    $('button.ui-datepicker-trigger').each(function(){
        if(!$(this).hasClass("btn")){
            $(this).addClass("btn").html('<i class="icon-calendar"></i>');
        }
    });
}

function toggleShowUnattended(event){
    if(event.target.checked){
        $('#unattended-col').show('slideup');//,{direction: 'up'});
        localStorage.showUnattended = 1;
    }else{
        $('#unattended-col').hide('slideup');//,{direction: 'up'});
        localStorage.showUnattended = "";
    }
}

function getSlivkanPoints(){
    var nu_email = $('#slivkan').val(),
    start = $('#start-val').val(),
    end = $('#end-val').val();

    localStorage.points = $('#slivkan').val();

    $('.slivkan-submit').html($('#slivkan option:selected').html());

    $('#breakdown').hide('slideup',function(){
        $('#attended').empty();
        $('#unattended').empty();
        $.getJSON('ajax/getPointsBreakdown.php',{nu_email: nu_email,start: start,end: end},function(data){
            var events = data.attended.events;
            if(events.event_name.length > 0){
                for(var i=events.event_name.length-1; 0<=i; i--){
                    $('<tr />').appendTo('#attended');
                    $('<td />').html(events.event_name[i]).appendTo('#attended tr:last');
                }
            }else{
                $('<tr />').appendTo('#attended');
                $('<td />').html("None :(").appendTo('#attended tr:last');
            }

            events = data.unattended.events;
            if(events.event_name.length > 0){
                for(var i=events.event_name.length-1; 0<=i; i--){
                    $('<tr />').appendTo('#unattended');
                    $('<td />').html(events.event_name[i]).appendTo('#unattended tr:last');
                }
            }else{
                $('<tr />').appendTo('#unattended');
                $('<td />').html("None :D").appendTo('#unattended tr:last');
            }

            $('#breakdown').show('slidedown');
            //Google Chart:
            var tableData = [['Committee','Events Attended']];
            for(var c in data.attended.committees){
                tableData.push([c,data.attended.committees[c]]);
            }

            drawChart(tableData,"Attended Events Committee Distribution","attendedByCommittee");

            tableData = [['Committee','Events Unattended']];
            for(c in data.unattended.committees){
                tableData.push([c,data.unattended.committees[c]]);
            }
            drawChart(tableData,"Unattended Events Committee Distribution","unattendedByCommittee");
        });
    });
}

function drawChart(tableData,title_in,id){
    tableData = google.visualization.arrayToDataTable(tableData);

    var options = {
        title: title_in,
        chartArea: {
            left: 10,
            top: 40
        },
        height: 250,
        width: 400
    };

    var chart = new google.visualization.PieChart(document.getElementById(id));
    chart.draw(tableData, options);
}