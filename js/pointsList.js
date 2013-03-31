var slivkans,events;

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
	})

    $( "#start" ).datepicker({
    	dateFormat: "yy-mm-dd",
		defaultDate: localStorage.pointsStart || "2013-03-18",
		minDate: '2013-03-18',//first day of winter finals
		maxDate: localStorage.pointsEnd || '2013-06-10',//Last day of spring quarter
		onSelect: function( selectedDate ) {
	        $( "#end" ).datepicker( "option", "minDate", selectedDate );
	        $('.ui-state-hover').removeClass('ui-state-hover'); //remove annoying hover class
	        $('#start-val').text(selectedDate);
	        localStorage.pointsStart = selectedDate;
	        if($('#slivkan').val()){getSlivkanPoints()}
	    }
    });
    $( "#end" ).datepicker({
    	dateFormat: "yy-mm-dd",
        defaultDate: localStorage.pointsEnd || '2013-06-10',
        minDate: localStorage.pointsStart || '2013-03-18',//first day of winter finals
        maxDate: '2013-06-10',//Last day of spring quarter
        onSelect: function( selectedDate ) {
            $('#start').datepicker( "option", "maxDate", selectedDate );
            $('.ui-state-hover').removeClass('ui-state-hover'); //remove annoying hover class
            $('#end-val').text(selectedDate);
            localStorage.pointsEnd = selectedDate;
            if($('#slivkan').val()){getSlivkanPoints()}
        }
    });

    $('.ui-state-hover').removeClass('ui-state-hover'); //remove annoying hover class

    $('#start-val').text($('#start').val());
    $('#end-val').text($('#end').val());
})

function getSlivkanPoints(){
	var nu_email = $('#slivkan').val(),
	start = $('#start').val(),
	end = $('#end').val();

	localStorage.points = $('#slivkan').val();

	$('.slivkan-submit').html($('#slivkan option:selected').html());

	$('#tables').hide('slideup',function(){
		$('#attended').empty();
		$('#missed').empty();
		$.getJSON('ajax/getPoints.php',{nu_email: nu_email,start: start,end: end},function(data){
			if(data.attended.length > 0){
				for(var i=0; i<data.attended.length; i++){
					$('<tr />').appendTo('#attended');
					$('<td />').html(data.attended[i]).appendTo('#attended tr:last');
				}
			}else{
				$('<tr />').appendTo('#attended');
				$('<td />').html("None :(").appendTo('#attended tr:last');
			}
			
			if(data.missed.length > 0){
				for(var i=0; i<data.missed.length; i++){
					$('<tr />').appendTo('#missed');
					$('<td />').html(data.missed[i]).appendTo('#missed tr:last');
				}
			}else{
				$('<tr />').appendTo('#missed');
				$('<td />').html("None :D").appendTo('#missed tr:last');
			}

			$('#tables').show('slidedown');
		});
	});
}