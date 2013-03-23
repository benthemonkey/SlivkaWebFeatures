var slivkans, nicknames, fellows,
valid_event_name = false;

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

    //remove annoying hover class
    $('.ui-state-hover').removeClass('ui-state-hover');

    //event handler for type
    $('.type-btn').click(function(event){toggleType(event)});

    //event handler for slivkan-entry
    $('.slivkan-entry').on('focus',function(event){$(this).parent().addClass("warning")});

    //$('#tabs').tabs();
    $('#tabs a:first').tab('show');
    $('#dialog').dialog({
    	autoOpen: false,
    	buttons : [{text: "Add Names", 'class':'btn', click: function(){addBulkNames(); $('#bulk-names').val(""); $(this).dialog( "close" )}}],
    	width: 338
    });
})

function init(){
	appendNameInputs(14);
	appendFellowInputs(5);
	$("#date-val").text($("#date").val());
	$('#filled-by').typeahead({source: slivkans.full_name.concat(nicknames.nickname)});
}

function appendNameInputs(n){
	for (var i=0; i<n; i++){
		entry = $('.slivkan-entry-control').last();
		add_on = entry.find('.add-on');
		slivkan_entry = entry.find('.slivkan-entry');
		helper = entry.find('.helper-point');
		committee = entry.find('.committee-point');

		//autocomplete and other events
		slivkan_entry.typeahead({source: slivkans.full_name.concat(nicknames.nickname)})
		.on('focus',function(){$(this).parent().addClass("warning")})
		.on('focusout',function(){validateSlivkanName($(this).parent())});

		//buttons
		helper.click(function(){toggleHelperPoint($(this).parent())});
		committee.click(function(){toggleCommitteeMember($(this).parent())});
		$('.slivkan-entry-control').last().clone().appendTo('#slivkan-entry-tab')
		.removeClass("warning")
		.find('.add-on').html(parseInt(add_on.html())+1);
	}

	$('.slivkan-entry').last().bind('focus',function(){
		$(this).parent().addClass("warning");
		var num_inputs = $('.slivkan-entry').length;
		$(this).unbind('focus');
		if(num_inputs < 99){appendNameInputs(1)};
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

function toggleType(event){
	var type = event.target.value;

	if(type == "P2P"){
		$("#committee").val("Faculty");
		$("#event").val("P2P");
	}else if(type == "IM"){
		$("#committee").val("Social");
		$("#event").val("");
	}else if(type == "House Meeting"){
		$("#committee").val("Exec");
		$("#event").val("House Meeting");
	}else{
		$("#event").val("");
	}
	validateEventName();

	if(type == "Other"){
		$(".description-control").slideDown();
	}else{
		validateCommittee();
		$(".description-control").slideUp();
	}
}

function toggleHelperPoint(entry){
	if(!(entry.find('.helper-point').hasClass("disabled"))){
		entry.find('.helper-point').toggleClass("active");
		if(entry.find('.committee-point').hasClass("active")){
			entry.find('.committee-point').toggleClass("active");
		}
	}
}

function toggleCommitteeMember(entry){
	if(!(entry.find('.committee-point').hasClass("disabled"))){
		entry.find('.committee-point').toggleClass("active");
		if(entry.find('.helper-point').hasClass("active")){
			entry.find('.helper-point').toggleClass("active");
		};
	}
}

function validatePointsForm(){
	var valid = true,
	errors = new Array();

	if (!valid_event_name){valid = false; updateValidity($(".event-control"),valid); errors.push("Name");}
	if (!validateCommittee()){valid = false; errors.push("Committee");}
	if (!validateDescription()){valid = false; errors.push("Description");}
	if (!validateFilledBy()){valid = false; errors.push("Filled By");}

	var valid_singles = true;

	$('.slivkan-entry-control').each(function(){
		if(!validateSlivkanName($(this))){valid_singles = false;}
	});

	if(!valid_singles){valid = false; errors.push("Attendees");}

	if(valid){submitPointsForm()}else{
		$("#submit-error").text("Validation errors in: "+errors.join(", ")).fadeIn();
	}

	return valid;
}

function validateEventName(){
	var valid = false, event_name = $('#event').val(), event_names = new Array();

	valid_event_name = false;

	if(event_name.length > 8 || event_name == "P2P"){
		event_name += ' '+$("#date").val();

		$.getJSON("ajax/getEvents.php",function(data){
			$(".event-control").removeClass("warning");

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
			$('#event-name-length-error').fadeOut();
			updateValidity($(".event-control"),valid_event_name);
		})
	}else{
		$('#event-name-length-error').fadeIn();
		updateValidity($(".event-control"),valid_event_name);
	}

	return valid;
}

function validateCommittee(){
	var valid, committee = $('#committee').val();

	valid = committee != "Select One";

	updateValidity($('.committee-control'),valid);

	$('.slivkan-entry-control').each(function(){
		validateSlivkanName($(this));
	})

	return valid;
}

function validateDescription(){
	var valid = true, description = $("#description").val();

	if(description.length < 10 && $('.type-btn.active').val() == "Other"){
		valid = false;
		$("#description-length-error").fadeIn();
	}else{
		$("#description-length-error").fadeOut();
	}

	updateValidity($(".description-control"),valid);

	return valid;
}

function validateFilledBy(){
	var valid, name = $('#filled-by').val();

	if (nicknames.nickname.indexOf(name) != -1){
    	name = nicknames.aka[nicknames.nickname.indexOf(name)];
    	$('#filled-by').val(name);
    }

    $('.filled-by-control').removeClass("warning");

    if(name.length > 0){
    	valid = slivkans.full_name.indexOf(name) != -1;
		updateValidity($('.filled-by-control'),valid);
    }else{
    	$('.filled-by-control').removeClass('error');
    }

	

	return valid;
}

function validateSlivkanName(entry){
    var valid = true, 
    nameArray = new Array(), 
    slivkan_entry = entry.find('.slivkan-entry'),
    helper = entry.find('.helper-point'),
    committee = entry.find('.committee-point'),
    name = slivkan_entry.val();

    if (nicknames.nickname.indexOf(name) != -1){
    	name = nicknames.aka[nicknames.nickname.indexOf(name)];
    	slivkan_entry.val(name);
    }

	//clear duplicates
    $('.slivkan-entry').each(function(index){
    	if (nameArray.indexOf($(this).val()) != -1){ $(this).val(''); name=''; $('#duplicate-alert').slideDown(); }
    	if ($(this).val().length > 0){ nameArray.push($(this).val()) }
  	});
    
    entry.removeClass("warning")

    if (name.length > 0){
    	name_ind = slivkans.full_name.indexOf(name);
		if(name_ind != -1){
			valid=true;
			if(slivkans.committee[name_ind] == $('#committee').val()){
				committee.removeClass("disabled");
				helper.addClass("disabled");
				if(!(committee.hasClass('active'))){toggleCommitteeMember(entry);}
			}else{
				committee.addClass("disabled");
				helper.removeClass("disabled");
				if(committee.hasClass('active')){toggleCommitteeMember(entry);}
			}
		}else{ valid=false }
		updateValidity(entry,valid)
	}else{
		entry.removeClass("success").removeClass("error");
		committee.addClass("disabled");
		helper.removeClass("disabled");
		if(committee.hasClass('active')){toggleCommitteeMember(entry);}
	}

	//no names = invalid
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
	free_slots = 0;

	$('.slivkan-entry').each(function(){
		if($(this).val().length > 0){
			slots.push(1);
		}else{
			slots.push(0);
			free_slots++;
		}
	});

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
		$('.slivkan-entry').eq(ind).val(name);
		validateSlivkanName($('.slivkan-entry').eq(ind).parent());
	}
}

function sortEntries(){
	var nameArray = new Array();


	//forming name array, but appending values corresponding to the helper/committee buttons:
	//0 - enabled unpressed, 1 - enabled pressed, 2 - disabled
	$('.slivkan-entry').each(function(index){
    	if($(this).val().length > 0){
    		name = $(this).val();
    		h = "0"; 
    		if($('.helper-point').eq(index).hasClass("active")){
    			h = "1";
    		}else if($('.helper-point').eq(index).hasClass("disabled")){
    			h = "2";
    		}

			c = "0";
    		if($('.committee-point').eq(index).hasClass("active")){
    			c = "1";
    		}else if($('.committee-point').eq(index).hasClass("disabled")){
    			c = "2";
    		}

    		nameArray.push($(this).val()+h+c);
    	}
  		$(this).val("");
  		validateSlivkanName($(this).parent());
  	});

  	//reset buttons
  	$('.committee-point').removeClass('active').addClass('disabled');
	$('.helper-point').removeClass('active').removeClass('disabled');

  	nameArray = nameArray.sort().getUnique();

  	for(var i=0; i<nameArray.length; i++){
  		name = nameArray[i].slice(0,nameArray[i].length-2);
  		h = nameArray[i].slice(nameArray[i].length-2,nameArray[i].length-1);
  		c = nameArray[i].slice(nameArray[i].length-1);

  		entry = $('.slivkan-entry-control').eq(i);
  		entry.find('.slivkan-entry').val(name);
  		if(h=="1") entry.find('.helper-point').addClass("active");
  		if(h=="2") entry.find('.helper-point').addClass("disabled");
  		if(c=="1") entry.find('.committee-point').addClass("active");
  		if(c=="2") entry.find('.committee-point').addClass("disabled");
  		validateSlivkanName(entry);
  	}

  	$('#sort-alert').slideDown();
}

function updateValidity(element,valid){
	if (valid){
    	element.addClass("success").removeClass("error");
    }else{
    	element.removeClass("success").addClass("error");
    }
}

function resetForm(){
	$('#event').val(""); $('.event-control').removeClass("success").removeClass("error");
	$('#description').val(""); $('.description-control').removeClass("success").removeClass("error");
	$('#committee').val("Select One"); $('.committee-control').removeClass("success").removeClass("error");
	$('.type-btn').last().click();
	$('#filled-by').val(""); $('.filled-by-control').removeClass("success").removeClass("error");
	$('#comments').val("");

	$('.slivkan-entry').each(function(index){
		$(this).val("");
		validateSlivkanName($(this).parent());
	});

	$('.fellow-entry').each(function(index){
		$(this).val("");
		validateFellowName(index);
	})

	$('.committee-point').removeClass('active').addClass('disabled');
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
		type: $('.type-btn.active').val().toLowerCase().replace(" ","_"),
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

	$('.slivkan-entry').each(function(index){
		var name = $(this).val();
		if(name.length > 0){
			name_ind = slivkans.full_name.indexOf(name);
			data.attendees.push(slivkans.nu_email[name_ind]);
			if($(this).parent().find('.helper-point').hasClass("active")){
				data.helper_points.push(slivkans.nu_email[name_ind]);
			}else if($(this).parent().find('.committee-point').hasClass("active")){
				data.committee_members.push(slivkans.nu_email[name_ind]);
			}
		}
	});

	$('.fellow-entry').each(function(index){
		if($(this).val().length > 0){
			data.fellows.push($(this).val());
		}
	})

	console.log(data);

	window.location.href = "/features/ajax/submitPointsForm.php?"+$.param(data);
}