var slivkans, nicknames, fellows,
type = "other",
valid_event_name = false;

Array.prototype.getUnique = function(){
   var u = {}, a = [];
   for(var i = 0, l = this.length; i < l; ++i){
      if(u.hasOwnProperty(this[i])) {
         continue;
      }
      a.push(this[i]);
      u[this[i]] = 1;
   }
   return a;
}

jQuery(document).ready(function(){ 
	//navigate away warning
	$(window).bind('beforeunload', function() {return 'Your points form was not submitted.'});

    $.getJSON("ajax/getSlivkans.php",function(data){
        slivkans = data.slivkans
        nicknames = data.nicknames
        fellows = data.fellows;
        init();
    })

    $("#date").datepicker({
    	minDate: -5,
    	maxDate: 0,
    	dateFormat: "yy-mm-dd",
    	onSelect: function(date){
    		$("#date-val").text(date);
    		validateEventName();
    	}
    })

    //$('#tabs').tabs();
    $('#tabs a:first').tab('show');
    $('#dialog').dialog({
    	autoOpen: false,
    	buttons : [{text: "Add Names", 'class':'btn', click: function(){addBulkNames(); $('#bulk-names').val(""); $(this).dialog( "close" )}}],
    	width: 338
    });
})

function init(){
	appendNameInputs(10);
	appendFellowInputs(5);
	$("#date-val").text($("#date").val());
	$('#filled-by').typeahead({source: slivkans.full_name.concat(nicknames.nickname)})
	$('#bulk-corrections').typeahead({source: slivkans.full_name.concat(nicknames.nickname)});
}

function appendNameInputs(n){
	function makestring(m){
		return '<div class="control-group input-append single-entry-control">\
		<input type="text" class="single-entry" name="single-entry" placeholder="Slivkan" onfocus="$(\'.single-entry-control\').eq('+m+').addClass(\'warning\')" onfocusout="validateSingleName('+m+')">\
		<div class="btn helper-point" title="Give a Helper Point" onclick="toggleHelperPoint('+m+')"><i class="icon-heart"></i></div>\
		<div class="btn committee-member disabled" title="Committee Member" onclick="toggleCommitteeMember('+m+')"><i class="icon-user"></i></div>\
		</div>';
	}
	
	num_inputs = $('.single-entry').length;

	for (var i=0; i<n; i++){
		$(makestring(i+num_inputs)).appendTo('#single-entry-tab');
	}

	$('.single-entry').typeahead({source: slivkans.full_name.concat(nicknames.nickname)});

	$('.single-entry').last().bind('focus',function(){
		var num_inputs = $('.single-entry').length;
		$('.single-entry').unbind('focus');
		if(num_inputs < 100){appendNameInputs(1)};
	});
}

function appendFellowInputs(n){
	function makestring(m){
		return '<div class="control-group fellow-entry-control">\
		<input type="text" class="fellow-entry" name="fellow-entry" placeholder="Fellow" onfocus="$(\'.fellow-entry-control\').eq('+m+').addClass(\'warning\')" onfocusout="validateFellowName('+m+')">\
		</div>';
	}
	
	num_inputs = $('.fellow-entry').length;

	for (var i=0; i<n; i++){
		$(makestring(i+num_inputs)).appendTo('#fellow-entry-tab');
	}

	$('.fellow-entry').typeahead({source: fellows});

	$('.fellow-entry').last().bind('focus',function(){
		var num_inputs = $('.fellow-entry').length;
		$('.fellow-entry').unbind('focus');
		if(num_inputs < 20){appendFellowInputs(1)};
	});
}

function toggleType(ind){
	type = $(".type-btn").eq(ind).val();

	if(type == "p2p"){
		$("#committee").val("Faculty");
		$("#event").val("P2P");
		validateEventName();
	}else if(type == "im"){
		$("#committee").val("Social");
	}else if(type == "house meeting"){
		type="house_meeting";
		$("#committee").val("Exec");
		$("#event").val("House Meeting");
		validateEventName();
	}

	if(type == "other"){
		$("#description-error").slideDown();
	}else{
		validateCommittee();
		$("#description-error").slideUp();
	}
}

function toggleHelperPoint(ind){
	if(!($('.helper-point').eq(ind).hasClass("disabled"))){
		$('.helper-point').eq(ind).toggleClass("active");
		if($('.committee-member').eq(ind).hasClass("active")){
			$('.committee-member').eq(ind).toggleClass("active");
		}
	}
}

function toggleCommitteeMember(ind){
	if(!($('.committee-member').eq(ind).hasClass("disabled"))){
		$('.committee-member').eq(ind).toggleClass("active");
		if($('.helper-point').eq(ind).hasClass("active")){
			$('.helper-point').eq(ind).toggleClass("active");
		};
	}
}

function validatePointsForm(){
	var valid = true,
	errors = new Array();

	if (!valid_event_name){valid = false; updateValidity($("#event-error"),valid); errors.push("Name");}
	if (!validateCommittee()){valid = false; errors.push("Committee");}
	if (!validateDescription()){valid = false; errors.push("Description");}
	if (!validateFilledBy()){valid = false; errors.push("Filled By");}

	var valid_singles = true;

	for (var i=0; i<$('.single-entry').length; i++){
		if(!validateSingleName(i)){valid_singles = false;}
	}

	if(!valid_singles){valid = false; errors.push("Attendees");}

	if(valid){submitPointsForm()}else{
		$("#submit-error").text("Validation errors in: "+errors.join(", ")).fadeIn();
	}

	return valid;
}

function validateEventName(){
	var valid = false, event_name = $('#event').val(), event_names = new Array();

	if(event_name.length > 5){
		event_name += ' '+$("#date").val();

		$.getJSON("ajax/getEvents.php",function(data){
			$("#event-error").removeClass("warning");

			if(data.event_names.length > 0){
				event_names = data.event_names;

				if(event_names.indexOf(event_name) != -1){
					valid_event_name = false;
					$("#event-name-error").fadeIn();
				}else{
					valid_event_name = true;
					$("#event-name-error").fadeOut();
				}

			}else{
				valid_event_name = true;
			}

			updateValidity($("#event-error"),valid_event_name);
		})
	}else{
		updateValidity($("#event-error"),valid_event_name);
	}

	return valid;
}

function validateCommittee(){
	var valid, committee = $('#committee').val();

	valid = committee != "Select One";

	updateValidity($('#committee-error'),valid);

	$('.single-entry').each(function(index){
		validateSingleName(index);
	})

	return valid;
}

function validateDescription(){
	var valid = true, description = $("#description").val();
	if(description.length < 10 && type == "other"){
		valid = false;
		$("#description-length-error").fadeIn();
	}else{
		$("#description-length-error").fadeOut();
	}

	updateValidity($("#description-error"),valid);

	return valid;
}

function validateFilledBy(){
	var valid, name = $('#filled-by').val();

	if (nicknames.nickname.indexOf(name) != -1){
    	name = nicknames.aka[nicknames.nickname.indexOf(name)];
    	$('#filled-by').val(name);
    }

	valid = slivkans.full_name.indexOf(name) != -1;

	updateValidity($('#filled-by-error'),valid);

	return valid;
}

function validateSingleName(ind){
    var valid = true, nameArray = [], name = $('.single-entry').eq(ind).val();

    if (nicknames.nickname.indexOf(name) != -1){
    	name = nicknames.aka[nicknames.nickname.indexOf(name)];
    	$('.single-entry').eq(ind).val(name);
    }

	//clear duplicates
    $('.single-entry').each(function(index){
    	if (nameArray.indexOf($(this).val()) != -1){ $(this).val(''); name=''; $('#duplicate-alert').show(); } 
    	if ($(this).val().length > 0){ nameArray.push($(this).val()) }
  	});
    
    $('.single-entry-control').eq(ind).removeClass("warning")

    if (name.length > 0){
    	name_ind = slivkans.full_name.indexOf(name);
		if(name_ind != -1){
			valid=true;
			if(slivkans.committee[name_ind] == $('#committee').val()){
				$('.committee-member').eq(ind).removeClass("disabled");
				$('.helper-point').eq(ind).addClass("disabled");
				if(!($('.committee-member').eq(ind).hasClass('active'))){toggleCommitteeMember(ind);}
			}else{
				$('.committee-member').eq(ind).addClass("disabled");
				$('.helper-point').eq(ind).removeClass("disabled");
				if($('.committee-member').eq(ind).hasClass('active')){toggleCommitteeMember(ind);}
			}
		}else{ valid=false }
		updateValidity($('.single-entry-control').eq(ind),valid)
	}else{
		$('.single-entry-control').eq(ind).removeClass("success").removeClass("error");
		$('.committee-member').eq(ind).addClass("disabled");
		$('.helper-point').eq(ind).removeClass("disabled");
		if($('.committee-member').eq(ind).hasClass('active')){toggleCommitteeMember(ind);}
	}

	if(nameArray.length == 0){ valid=false }
    
    return valid;
}

function validateFellowName(ind){
    var valid = true, nameArray = [], name = $('.fellow-entry').eq(ind).val();

    $('.fellow-entry').each(function(index){
    	if($(this).val().length > 0)
  		nameArray.push($(this).val());
  	});
    
    $('.fellow-entry-control').eq(ind).removeClass("warning")

    if (name.length > 0){
    	valid = fellows.indexOf(name) != -1;
		updateValidity($('.fellow-entry-control').eq(ind),valid)
	}else{
		$('.fellow-entry-control').eq(ind).removeClass("success").removeClass("error");
	}

	if(!checkForDuplicates(nameArray)){ valid=false }
    
    return valid;
}

function processBulkNames(){
	names = $('#bulk-names').val();

	//remove "__ mins ago" and blank lines
	names = names.replace(/(\d+ .+ago[\r\n]?$)|(^[\r\n])/gm,"");

	$('#bulk-names').val(names);
}

function addBulkNames(){
	var slots = new Array(),
	num_inputs = $('.single-entry').length,
	free_slots = 0;

	for(var i=0; i<num_inputs; i++){
		if($('.single-entry').eq(i).val().length > 0){
			slots.push(1);
		}else{
			slots.push(0);
			free_slots++;
		}
	}

	names = $('#bulk-names').val();

	//if there's a hanging newline, remove it for adding but leave it in the textarea
	if (names[names.length-1] == "\r" || names[names.length-1] == "\n"){
		names = names.slice(0,names.length-1);
	}

	nameArray = names.split(/[\r\n]/gm);

	if(nameArray.length >= free_slots){
		n = nameArray.length-free_slots+1;
		appendNameInputs(n);
		for(var i=0; i<n; i++){slots.push(0);}
	}

	while(nameArray.length > 0){
		name = nameArray.shift();
		ind = slots.indexOf(0);
		slots[ind] = 1;
		$('.single-entry').eq(ind).val(name);
		validateSingleName(ind);
	}
}

function sortEntries(){
	var nameArray = new Array();


	//forming name array, but appending values corresponding to the helper/committee buttons:
	//0 - enabled unpressed, 1 - enabled pressed, 2 - disabled
	$('.single-entry').each(function(index){
    	if($(this).val().length > 0){
    		name = $(this).val();
    		h = "0"; 
    		if($('.helper-point').eq(index).hasClass("active")){
    			h = "1";
    		}else if($('.helper-point').eq(index).hasClass("disabled")){
    			h = "2";
    		}

			c = "0";
    		if($('.committee-member').eq(index).hasClass("active")){
    			c = "1";
    		}else if($('.committee-member').eq(index).hasClass("disabled")){
    			c = "2";
    		}

    		nameArray.push($(this).val()+h+c);
    	}
  		$(this).val("");
  		validateSingleName(index);
  	});

  	//reset buttons
  	$('.committee-member').removeClass('active').addClass('disabled');
	$('.helper-point').removeClass('active').removeClass('disabled');

  	nameArray = nameArray.sort().getUnique();

  	for(var i=0; i<nameArray.length; i++){
  		name = nameArray[i].slice(0,nameArray[i].length-2);
  		h = nameArray[i].slice(nameArray[i].length-2,nameArray[i].length-1);
  		c = nameArray[i].slice(nameArray[i].length-1);

  		$('.single-entry').eq(i).val(name);
  		if(h=="1") $('.helper-point').eq(i).addClass("active");
  		if(h=="2") $('.helper-point').eq(i).addClass("disabled");
  		if(c=="1") $('.committee-member').eq(i).addClass("active");
  		if(c=="2") $('.committee-member').eq(i).addClass("disabled");
  		validateSingleName(i);
  	}

  	$('#sort-alert').show();
}

function checkForDuplicates(arr){
	var valid = true, dupes = new Array();

	$("#duplicate-error-names").empty();

	arr = arr.sort();

	for (var i = 0; i < arr.length - 1; i++) {
	    if (arr[i + 1] == arr[i]) {
	        dupes.push(arr[i]);
	        valid = false;
	    }
	}

	dupes = dupes.getUnique();

	for (var i=0; i<dupes.length; i++){
		$("<li></li>").text(dupes[i]).appendTo("#duplicate-error-names");
	}

	if (valid){
		$('#duplicate-error').fadeOut();
	}else{
		$('#duplicate-error').fadeIn();
	}

	return valid;
}

function updateValidity(element,valid){
	if (valid){
    	element.addClass("success").removeClass("error");
    }else{
    	element.removeClass("success").addClass("error");
    }
}

function resetForm(){
	$('#event').val(""); $('#event-error').removeClass("success").removeClass("error");
	$('#description').val(""); $('#description-error').removeClass("success").removeClass("error");
	$('#committee').val("Select One"); $('#committee-error').removeClass("success").removeClass("error");
	toggleType(3);
	$('#filled-by').val(""); $('#filled-by-error').removeClass("success").removeClass("error").removeClass("warning");
	$('#comments').val("");

	$('.single-entry').each(function(index){
		$(this).val("");
		validateSingleName(index);
	});

	$('.fellow-entry').each(function(index){
		$(this).val("");
		validateFellowName(index);
	})

	$('.committee-member').removeClass('active').addClass('disabled');
	$('.helper-point').removeClass('active').removeClass('disabled');

	$('#event-name-error').fadeOut();
	$('#description-length-error').fadeOut();
	$('#duplicate-error').fadeOut();
	$('#submit-error').fadeOut();
}

function submitPointsForm(){
	$(window).unbind('beforeunload');

	var data = {
		date: $('#date').val(),
		type: type,
		committee: $('#committee').val(),
		event_name: $('#event').val(),
		description: $('#description').val(),
		filled_by: slivkans.nu_email[slivkans.full_name.indexOf($('#filled-by').val())],
		comments: $('#comments').val(),
		attendees: new Array(),
		helper_points: new Array(),
		committee_members: new Array(),
		fellows: new Array()
	}

	$('.single-entry').each(function(index){
		var name = $(this).val();
		if(name.length > 0){
			name_ind = slivkans.full_name.indexOf(name);
			data.attendees.push(slivkans.nu_email[name_ind]);
			if($('.helper-point').eq(index).hasClass("btn-inverse")){
				data.helper_points.push(slivkans.nu_email[name_ind]);
			}else if($('.committee-member').eq(index).hasClass("btn-inverse")){
				data.committee_members.push(slivkans.nu_email[name_ind]);
			}
		}
	});

	$('.fellow-entry').each(function(index){
		if($(this).val().length > 0){
			data.fellows.push($(this).val());
		}
	})

	window.location.href = "/features/ajax/submitPointsForm.php?"+$.param(data);
}