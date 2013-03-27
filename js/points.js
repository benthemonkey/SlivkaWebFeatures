var slivkans,events;

$(document).ready(function(){
	$.getJSON('ajax/getSlivkans.php',function(data){
		slivkans = data.slivkans;

		for(var i=0; i<slivkans.full_name.length; i++){
			$('<option />').attr("value",slivkans.nu_email[i]).text(slivkans.full_name[i]).appendTo('#slivkan');
		}
	})

    $( "#start" ).datepicker({
    	dateFormat: "yy-mm-dd",
		//defaultDate: "-2w",
		//numberOfMonths: 3,
		minDate: '2013-03-18',//first day of winter finals
		maxDate: '2013-06-10',//Last day of spring quarter
		onClose: function( selectedDate ) {
	        $( "#end" ).datepicker( "option", "minDate", selectedDate );
	        if($('#slivkan').val()){getSlivkanPoints()}
	    }
    });
    $( "#end" ).datepicker({
    	dateFormat: "yy-mm-dd",
        //defaultDate: "0d",
        //numberOfMonths: 3,
        minDate: '2013-03-18',//first day of winter finals
        maxDate: '2013-06-10',//Last day of spring quarter
        onClose: function( selectedDate ) {
            $( "#start" ).datepicker( "option", "maxDate", selectedDate );
            if($('#slivkan').val()){getSlivkanPoints()}
        }
    });

   $('#start').val('2013-03-18');
   $('#end').val('2013-03-31');
})

function getSlivkanPoints(){
	var nu_email = $('#slivkan').val(),
	start = $('#start').val(),
	end = $('#end').val();

	$('.slivkan-submit').html($('#slivkan option:selected').html());
	$('#table-body').empty();

	$.getJSON('ajax/getPoints.php',{nu_email: nu_email,start: start,end: end},function(data){
		var num = Math.max(data.attended.length,data.missed.length);

		for(var i=0; i<num; i++){
			$('<tr />').appendTo('#table-body');
			$('<td />').html(data.attended[i]||"").appendTo('#table-body tr:last');
			$('<td />').html(data.missed[i]||"").appendTo('#table-body tr:last');	
		}
		
	});
}