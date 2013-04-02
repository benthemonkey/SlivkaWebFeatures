var slivkans, nicknames, fellows, type = "Other",
valid_event_name = false;

jQuery(document).ready(function(){ 
	//navigate away warning
	$(window).bind('beforeunload', function() {return 'Your points form was not submitted.'});

	//nav
	$('.nav li').eq(3).addClass('active');

    $.getJSON("ajax/getSlivkans.php",function(data){
        slivkans = data.slivkans
        nicknames = data.nicknames
        fellows = data.fellows;
        
        //initialization
        appendNameInputs(14);
		appendFellowInputs(5);
		$('#filled-by').typeahead({source: slivkans.full_name.concat(nicknames.nickname)});
    })

    $("#date").datepicker({
    	minDate: -5,
    	maxDate: 0,
    	dateFormat: "m/d",
    	altField: "#date-val",
    	altFormat: "yy-mm-dd",
    	onSelect: function(date){
    		validateEventName();
    	}
    })
    $("#date").datepicker("setDate", new Date());

    //remove annoying hover class
    $('.ui-state-hover').removeClass('ui-state-hover');

    //event handler for type
    $('.type-btn').click(function(event){toggleType(event)});

    $('#tabs a:first').tab('show');
})

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
		helper.click(function(){$(this).toggleClass("active")});
		committee.click(function(){$(this).toggleClass("active")});


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
	for (var i=0; i<n; i++){
		entry = $('.fellow-entry-control').last();
		fellow_entry = entry.find('.fellow-entry');

		//autocomplete and other events
		fellow_entry.typeahead({source: fellows})
		.on('focus',function(){$(this).parent().addClass("warning")})
		.on('focusout',function(){validateFellowName($(this).parent())});

		$('.fellow-entry-control').last().clone().appendTo('#fellow-entry-tab')
		.removeClass("warning")
	}

	$('.fellow-entry').last().bind('focus',function(){
		$(this).parent().addClass("warning");
		var num_inputs = $('.fellow-entry').length;
		$(this).unbind('focus');
		if(num_inputs < 20){appendFellowInputs(1)};
	});
}

function toggleType(event){
	type = event.target.value;

	if(type == "P2P"){
		$("#committee").val("Faculty");
		$("#event").val("P2P");
	}else if(type == "IM"){
		$("#committee").val("Social");
		$('#event').val($('#im-team').val()+' 1');
		$('#description').val($('#im-team').val());
	}else if(type == "House Meeting"){
		$("#committee").val("Exec");
		$("#event").val("House Meeting");
	}else{
		$("#event").val("");
	}
	validateEventName();
	validateCommittee();

	if(type == "IM"){
		$(".im-team-control").slideDown();
	}else{
		$(".im-team-control").slideUp();
	}

	if(type == "Other"){
		$(".description-control").slideDown();
	}else{
		$(".description-control").slideUp();
	}
}

function validatePointsForm(){
	var valid = true,
	errors = new Array();

	if (!validateFilledBy()){valid = false; errors.push("Filled By");}
	if (!valid_event_name){valid = false; updateValidity($(".event-control"),valid); errors.push("Name");}
	if (!validateCommittee()){valid = false; errors.push("Committee");}
	if (!validateDescription()){valid = false; errors.push("Description");}

	var valid_slivkans = true;

	$('.slivkan-entry-control').each(function(){
		if(!validateSlivkanName($(this))){valid_slivkans = false;}
	});

	if(!valid_slivkans){valid = false; errors.push("Attendees");}


	var valid_fellows = true;

	$('.fellow-entry-control').each(function(){
		if(!validateFellowName($(this))){valid_fellows = false;}
	});

	if(!valid_fellows){valid = false; errors.push("Fellows");}


	if(valid){submitPointsForm()}else{
		$("#submit-error").text("Validation errors in: "+errors.join(", ")).fadeIn();
	}

	return valid;
}

function validateEventName(){
	var valid = false, event_name = $('#event').val(), event_names = new Array();

	valid_event_name = false;

	if((event_name.length <= 40 && event_name.length >= 8) || event_name == "P2P"){
		event_name += ' '+$("#date-val").val();

		$.getJSON("ajax/getEvents.php",function(data){
			$(".event-control").removeClass("warning");

			if(data.event_names.length > 0){
				event_names = data.event_names;

				if(event_names.indexOf(event_name) != -1){
					if(type == 'IM'){
						var last = parseInt($('#event').val().slice(-1));
						$('#event').val($('#event').val().slice(0,-1) + (last+1).toString());
						validateEventName();
					}else{
						valid_event_name = false;
						$("#event-name-error").fadeIn();
					}
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
		$('#event-name-length-error-count').html("Currently "+event_name.length+" characters");
		$('#event-name-length-error').fadeIn();
		updateValidity($(".event-control"),valid_event_name);
	}

	return valid;
}

function validateIMTeam(){
	$('#event').val($('#im-team').val()+' 1');
	$('#description').val($('#im-team').val());
	validateEventName();
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

	if(description.length < 10 && type == "Other"){
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
			if(slivkans.committee[name_ind] == $('#committee').val() && type != 'IM'){
				showCommitteeMember(helper,committee);
			}else if(type == 'IM' || slivkans.committee[name_ind] == 'Facilities' || slivkans.committee[name_ind] == 'Exec'){
				hideButtons(helper,committee);
			}else{
				showHelperPoint(helper,committee);
			}
		}else{ valid=false }
		updateValidity(entry,valid);
	}else{
		entry.removeClass("success").removeClass("error");
		hideButtons(helper,committee);
	}

	//no names = invalid
	if(nameArray.length == 0){ valid=false }
    
    return valid;
}

function showHelperPoint(helper,committee){
	committee.removeClass('active');
	if(helper.css('display') == 'none'){
		committee.hide('slide',function(){
			helper.show('slide');
		});
	}
}

function showCommitteeMember(helper,committee){
	helper.removeClass('active');
	if(committee.css('display') == 'none'){
		helper.hide('slide',function(){
			committee.show('slide');
		});
	}
	committee.addClass('active');
}

function hideButtons(helper,committee){
	helper.removeClass('active');
	helper.hide('slide');
	committee.removeClass('active');
	committee.hide('slide');
}

function validateFellowName(entry){
    var valid = true, 
    nameArray = new Array(), 
    fellow_entry = entry.find('.fellow-entry'),
    name = fellow_entry.val();

	//clear duplicates
    $('.fellow-entry').each(function(index){
    	if (nameArray.indexOf($(this).val()) != -1){ $(this).val(''); name=''; $('#duplicate-alert').slideDown(); }
    	if ($(this).val().length > 0){ nameArray.push($(this).val()) }
  	});
    
    entry.removeClass("warning")

    if (name.length > 0){
    	valid = fellows.indexOf(name) != -1;
		updateValidity(entry,valid)
	}else{
		entry.removeClass("success").removeClass("error");
	}

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
	//0 - unpressed, 1 - pressed
	$('.slivkan-entry').each(function(index){
    	if($(this).val().length > 0){
    		name = $(this).val();
    		h = "0"; 
    		if($('.helper-point').eq(index).hasClass("active")){
    			h = "1";
    		}

			c = "0";
    		if($('.committee-point').eq(index).hasClass("active")){
    			c = "1";
    		}

    		nameArray.push($(this).val()+h+c);
    	}
  		$(this).val("");
  	});

  	//reset buttons
  	$('.committee-point').removeClass('active');
	$('.helper-point').removeClass('active');

  	nameArray = nameArray.sort();

  	for(var i=0; i<nameArray.length; i++){
  		name = nameArray[i].slice(0,nameArray[i].length-2);
  		h = nameArray[i].slice(nameArray[i].length-2,nameArray[i].length-1);
  		c = nameArray[i].slice(nameArray[i].length-1);

  		entry = $('.slivkan-entry-control').eq(i);
  		entry.find('.slivkan-entry').val(name);
  		validateSlivkanName(entry);
  		if(h=="1") entry.find('.helper-point').addClass("active");
  		if(c=="0") entry.find('.committee-point').removeClass("active");
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

function isSlivkan(name){
	
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

	$('#event-name-error').fadeOut();
	$('#description-length-error').fadeOut();
	$('#duplicate-error').fadeOut();
	$('#submit-error').fadeOut();
}

function submitPointsForm(){
	$(window).unbind('beforeunload');

	var data = {
		date: $('#date-val').val(),
		type: type.toLowerCase().replace(" ","_"),
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

	for(obj in data){
		if(obj == "attendees" || obj == "helper_points" || obj == "committee_members" || obj == "fellows"){
			val = data[obj].join(", ");
		}else{
			val = data[obj];
		}

		if($('.results-label').html().length == 0){
			$('.results-label').html(obj);
			$('.results').html(val);
		}else{
			row = $('.results-row').last().clone().appendTo('#receipt');
			row.find('.results-label').html(obj);
			row.find('.results').html(val);
		}
	}

	$('#submit-results').modal({
		/*backdrop: "static",
		keyboard: false,*/
		show: true
	}).on('shown', function(){
	    $('body').css('overflow', 'hidden');
	}).on('hidden', function(){
	    $('body').css('overflow', 'auto');
	})

	//$('#submit-results').modal('show');
	
	$('#real-submit').click(function(){
		$.getJSON('./ajax/submitPointsForm.php',data,function(data_in){
			$('#results-status').parent().removeClass("warning");
			if(data_in.error){
				$('#results-status').html("Error in Step "+data_in.step).parent().addClass("error");
			}else{
				$('#unconfirmed').fadeOut({complete: function(){$('#confirmed').fadeIn();}});
				
				$('#results-status').html("Success!").parent().addClass("success");
			}
		})
	})
}