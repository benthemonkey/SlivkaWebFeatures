var pointsCenter = (function($){
	var slivkans, nicknames, fellows, type = "Other", valid_event_name = false,

	//Quarter-related variables:
	quarter_start = "4/1", //first day of classes
	quarter_end = "6/7", //last day of reading week

	//jQuery selectors

	//functions used by multiple pages
	common = {
		updateValidity: function(element,valid){
			if (valid){
				element.addClass("success").removeClass("error");
			}else{
				element.removeClass("success").addClass("error");
			}
		}
	},

	breakdown = {
		init: function(){
			//nav
			$('.nav li').eq(0).addClass('active');

			$.getJSON('ajax/getSlivkans.php',function(data){
				slivkans = data.slivkans;

				for(var i=0; i<slivkans.full_name.length; i++){
					$('<option />').attr("value",slivkans.nu_email[i]).text(slivkans.full_name[i]).appendTo('#slivkan');
				}

				if(localStorage.spc_brk_slivkan){
					$('#slivkan').val(localStorage.spc_brk_slivkan);
					breakdown.getSlivkanPoints();
				}

				if(!localStorage.spc_brk_showUnattended){ $('#showUnattended').click(); }
			});

			$( "#start" ).datepicker({
				showOn: "button",
				dateFormat: "m/d",
				altField: "#start-val",
				altFormat: "yy-mm-dd",
				minDate: quarter_start,
				maxDate: localStorage.spc_brk_end || quarter_end,
				onSelect: function( selectedDate ) {
					$( "#end" ).datepicker( "option", "minDate", selectedDate );
					localStorage.spc_brk_start = selectedDate;
					breakdown.fixDateButtons();
					breakdown.getSlivkanPoints();
				}
			});
			$( "#end" ).datepicker({
				showOn: "button",
				dateFormat: "m/d",
				altField: "#end-val",
				altFormat: "yy-mm-dd",
				minDate: localStorage.spc_brk_start || quarter_start,
				maxDate: quarter_end,
				onSelect: function( selectedDate ) {
					$('#start').datepicker( "option", "maxDate", selectedDate );
					localStorage.spc_brk_end = selectedDate;
					breakdown.fixDateButtons();
					breakdown.getSlivkanPoints();
				}
			});

			$("#start").datepicker("setDate", localStorage.spc_brk_start || quarter_start);
			$("#end").datepicker("setDate",localStorage.spc_brk_end || quarter_end);

			breakdown.fixDateButtons();

			$('#slivkan')		.on('change',function(){ breakdown.getSlivkanPoints(); });
			$('#start-label')	.on('click',function(){ $('#start').datepicker('show'); });
			$('#end-label')		.on('click',function(){ $('#end').datepicker('show'); });
			$('#showUnattended').on('click',function(event){ breakdown.toggleShowUnattended(event); });
		},
		fixDateButtons: function(){
			$('button.ui-datepicker-trigger').each(function(){
				if(!$(this).hasClass("btn")){
					$(this).addClass("btn").html('<i class="icon-calendar"></i>');
				}
			});
		},
		toggleShowUnattended: function(event){
			if(event.target.checked){
				$('#unattended-col').show('slideup');//,{direction: 'up'});
				localStorage.spc_brk_showUnattended = 1;
			}else{
				$('#unattended-col').hide('slideup');//,{direction: 'up'});
				localStorage.spc_brk_showUnattended = "";
			}
		},
		getSlivkanPoints: function(){
			var nu_email = $('#slivkan').val(),
			start = $('#start-val').val(),
			end = $('#end-val').val();

			localStorage.spc_brk_slivkan = $('#slivkan').val();

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
						for(var j=events.event_name.length-1; 0<=j; j--){
							$('<tr />').appendTo('#unattended');
							$('<td />').html(events.event_name[j]).appendTo('#unattended tr:last');
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

					breakdown.drawChart(tableData,"Attended Events Committee Distribution","attendedByCommittee");

					tableData = [['Committee','Events Unattended']];
					for(c in data.unattended.committees){
						tableData.push([c,data.unattended.committees[c]]);
					}
					breakdown.drawChart(tableData,"Unattended Events Committee Distribution","unattendedByCommittee");
				});
			});
		},
		drawChart: function(tableData,title_in,id){
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
	},

	table = {
		init: function(){
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

				var table = $("#table").dataTable({
					"aaData": aDataSet,
					"aoColumnDefs": [
					{ aTargets: [0], sTitle: "Name", sWidth: "120px", sClass: "name"},
					{ aTargets: [1], bVisible: false },
					{ aTargets: event_targets, asSorting: ['desc','asc']},
					{ aTargets: event_targets.concat(totals_targets), sTitle: '', sWidth: "14px"},
					{ aTargets: totals_targets, sClass: 'totals', asSorting: ['desc','asc']}
					],
					"bPaginate": false,
					"bAutoWidth": false,
					"oLanguage": {
						"sSearch": "Filter by Name:<br/>"
					},
					"sDom": '<"row-fluid"<"span3 table-info"><"span3"f><"span3 filter"i>><"row-fluid"<"header-row">><"row-fluid"<"span12"rt>>'
				});
				/*jshint multistr: true */
				$('<label>Filter by Gender:</label><select id="gender-filter">\
						<option value="">All</option>\
						<option value="m">Male</option>\
						<option value="f">Female</option>\
					</select>').prependTo('.filter');
				$('#gender-filter').on('change',function(){
					var option = $('#gender-filter').val();
					table.fnFilter(option,1);
				});

				var cols_width = 120+14*(event_targets.length + totals_targets.length)+100;

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
						container: '#table'
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
				var headers = $('#table').find('th');
				$('#columns').find('li').each(function(index){
					$(this).on('click',function(){headers.eq(index+1).click();});
				});

				$('td').each(function(){
					var self = $(this);
					if(!self.hasClass('totals')){
						var html = self.html();
						if(html == "1"){self.addClass("green");}
						else if(html == "0"){self.addClass("red");}
						else if(html == "1.1" || html == "0.1"){self.addClass("gold"); self.html(html.substr(0,1));}
						else if(html == "1.2" || html == "0.2"){self.addClass("blue"); self.html(html.substr(0,1));}
					}
				});

				$('<div />').text('Hover over column labels to view event information, click to sort.')
				.css({fontSize: '14px'}).addClass('alert alert-info').prependTo('.table-info');
			});
		}
	},

	correction = {
		init: function(){
			//nav
			$('.nav li').eq(2).addClass('active');

			$.getJSON("ajax/getSlivkans.php",function(data){
				slivkans = data.slivkans;
				nicknames = data.nicknames;

				$('#filled-by').typeahead({source: slivkans.full_name.concat(nicknames.nickname)});
			});

			$.getJSON("ajax/getEvents.php",function(data){
				event_name = data.event_name;

				for(var i=0; i<event_name.length; i++){
					$('<option></option>').text(event_name[i]).appendTo('#event-name');
				}
			});

			//event handlers
			$('#filled-by').on('focusout',function(){ correction.validateFilledBy(); });
			$('#submit').on('click',function(){ correction.validatePointsCorrectionForm(); });
			$('#reset').on('click',function(){ correction.resetForm(); });
		},
		validatePointsCorrectionForm: function(){
			var valid = true,
			errors = [];

			if(!correction.validateFilledBy()){ valid = false; errors.push("Your Name"); }
			if($('#event-name').val() == 'Select One'){ valid = false; errors.push("Event Name"); }

			if(valid){
				correction.submitPointsCorrection();
			}else{
				$("#submit-error").text("Validation errors in: "+errors.join(", ")).fadeIn();
			}
		},
		validateFilledBy: function(){
			var valid, name = $('#filled-by').val();

			if (nicknames.nickname.indexOf(name) != -1){
				name = nicknames.full_name[nicknames.nickname.indexOf(name)];
				$('#filled-by').val(name);
			}

			$('.filled-by-control').removeClass("warning");

			if(name.length > 0){
				valid = slivkans.full_name.indexOf(name) != -1;
				common.updateValidity($('.filled-by-control'),valid);
			}else{
				$('.filled-by-control').addClass('error');
				valid = false;
			}

			return valid;
		},
		resetForm: function(){
			$("#filled-by").val(""); $('.filled-by-control').removeClass("success").removeClass("error");
			$("#event-name").val("Select One");
			$("#comments").val("");
			$("#submit-error").fadeOut();
		},
		submitPointsCorrection: function(){
			var data = {
				event_name: $('#event-name').val(),
				name: $('#filled-by').val(),
				sender_email: slivkans.nu_email[slivkans.full_name.indexOf($('#filled-by').val())],
				comments: $('#comments').val()
			};
			$('#response').fadeOut();

			$.getJSON('./ajax/sendPointsCorrection.php',data,function(response){
				$('#response').html("<p>Response: "+response.message+"</p>");
				$('<a href="table.php" class="btn btn-primary">View Points</a>').appendTo('#response');
				$('<a class="btn" href="correction.php">Submit Another</a>').appendTo('#response');
				$('#response').fadeIn();
			});
		}
	},

	submission = {
		init: function(){
			//nav
			$('.nav li').eq(3).addClass('active');

			$.getJSON("ajax/getSlivkans.php",function(data){
				slivkans = data.slivkans;
				nicknames = data.nicknames;
				fellows = data.fellows;

				slivkans.autocomplete = slivkans.full_name.concat(nicknames.nickname);

				//initialization
				submission.appendNameInputs(14);
				submission.appendFellowInputs(9);

				//loading saved values
				if(localStorage.spc_sub_attendees){
					var attendees = localStorage.spc_sub_attendees;
					attendees = attendees.split(", ");
					if(attendees.length > 14){ submission.appendNameInputs(attendees.length - 14); }
					submission.addSlivkans(attendees);
				}
				if(localStorage.spc_sub_filledby){
					$('#filled-by').val(localStorage.spc_sub_filledby);
					submission.validateFilledBy();
				}
				if(localStorage.spc_sub_type && localStorage.spc_sub_type != "Other"){
					$('.type-btn[value="'+localStorage.spc_sub_type+'"]').click();
				}
				if(localStorage.spc_sub_date){
					$("#date").datepicker("setDate", localStorage.spc_sub_date);
				}
				if(localStorage.spc_sub_name){
					$('#event').val(localStorage.spc_sub_name);
					submission.validateEventName();
				}
				if(localStorage.spc_sub_committee){
					$('#committee').val(localStorage.spc_sub_committee);
					submission.validateCommittee();
				}
				if(localStorage.spc_sub_comments){
					$('#comments').val(localStorage.spc_sub_comments);
				}

				//autocomplete and other events
				$('#event').on('focus',submission.makeHandler.addClassWarning())
				.on('focusout',function(){ submission.validateEventName(); });

				$('#filled-by').typeahead({source: slivkans.autocomplete})
				.on('focus',submission.makeHandler.addClassWarning())
				.on('focusout',function(){ submission.validateFilledBy(); });

				$('#slivkan-entry-tab').on('focus','.slivkan-entry',submission.makeHandler.addClassWarning())
				.on('focusout','.slivkan-entry',submission.makeHandler.validateSlivkanName())
				.on('click','.helper-point',submission.makeHandler.toggleActive())
				.on('click','.committee-point',submission.makeHandler.toggleActive());

				$('#fellow-entry-tab').find('.fellow-entry').on('focus',submission.makeHandler.addClassWarning())
				.on('focusout',submission.makeHandler.validateFellowName());
			});

			$("#date").datepicker({
				minDate: -5,
				maxDate: 0,
				dateFormat: "m/d",
				showOn: "button",
				buttonText: "",
				altField: "#date-val",
				altFormat: "yy-mm-dd",
				onSelect: function(date){
					submission.validateEventName();
					localStorage.spc_sub_date = date;
				}
			});
			$("#date").datepicker("setDate", new Date());
			$('button.ui-datepicker-trigger').addClass("btn").html('<i class="icon-calendar"></i>');

			//event handlers for inputs
			$('.type-btn')        .on('click',function(event){ submission.toggleType(event); });
			$('#date-label')      .on('click',function(){ $('#date').datepicker('show'); });
			$('#im-team')         .on('change',function(){ submission.validateIMTeam(); });
			$('#committee')       .on('change',function(){ submission.validateCommittee(); });
			$('#description')     .on('focusout',function(){ submission.validateDescription(); });
			$('#comments')        .on('focusout',function(){ localStorage.spc_sub_comments = $('#comments').val(); });
			$('#close-sort-alert').on('click',function(){ $('#sort-alert').slideUp(); });
			$('#close-dupe-alert').on('click',function(){ $('#duplicate-alert').slideUp(); });
			$('#sort-entries')    .on('click',function(){ submission.sortEntries(); });
			$('#submit')          .on('click',function(){ submission.validatePointsForm(); });
			$('#reset')           .on('click',function(){ submission.resetForm(); });
			$('#bulk-names')      .on('keyup',function(){ submission.processBulkNames(); });
			$('#add-bulk-names')  .on('click',function(){ submission.addBulkNames(); });

			$('#tabs a:first').tab('show');
		},
		makeHandler: {
			addClassWarning : function(){
				return function(){ $(this).parent().addClass("warning"); };
			},
			validateSlivkanName : function(){
				return function(){ submission.validateSlivkanName($(this).parent()); };
			},
			validateFellowName : function(){
				return function(){ submission.validateFellowName($(this).parent()); };
			},
			toggleActive : function(){
				return function(){ $(this).toggleClass("active"); submission.saveSlivkans(); };
			}
		},
		appendNameInputs: function(n){
			//2-4ms per insertion. Slow but acceptable.
			var cloned = $('#slivkan-entry-tab').find('.slivkan-entry-control').last(),
			start = parseInt(cloned.find('.add-on').text(),10);
			for (var i=0; i<n; i++){
				next = cloned.clone().appendTo('#slivkan-entry-tab')
				.removeClass("warning")
				.find('.add-on').text(start+i+1);
			}

			$('#slivkan-entry-tab').find('.slivkan-entry').typeahead({source:slivkans.autocomplete});

			$('#slivkan-entry-tab').find('.slivkan-entry').last().on('focus',function(){
				$(this).parent().addClass("warning");
				var num_inputs = $('#slivkan-entry-tab').find('.slivkan-entry').length;
				$(this).off('focus');
				if(num_inputs < 120){ submission.appendNameInputs(1); }
			});
		},
		appendFellowInputs: function(n){
			var cloned = $('#fellow-entry-tab').find('.fellow-entry-control').last(),
			start = parseInt(cloned.find('.add-on').text(),10);
			for (var i=0; i<n; i++){
				next = cloned.clone().appendTo('#fellow-entry-tab')
				.removeClass("warning")
				.find('.add-on').text(start+i+1);
			}

			$('#fellow-entry-tab').find('.fellow-entry').typeahead({source: fellows});

			$('.fellow-entry').last().on('focus',function(){
				$(this).parent().addClass("warning");
				var num_inputs = $('.fellow-entry').length;
				$(this).off('focus');
				if(num_inputs < 20){ submission.appendFellowInputs(1); }
			});
		},
		toggleType: function(event){
			type = event.target.value;

			//store value
			localStorage.spc_sub_type = type;

			//clear description if **previous** type was IM
			if($('.type-btn.active').val() == "IM"){
				$('#description').val("");
				submission.validateDescription();
			}

			if(type == "P2P"){
				$("#committee").val("Faculty");
				$("#event").val("P2P");
			}else if(type == "IM"){
				$("#committee").val("Social");
				$('#event').val($('#im-team').val()+' 1');

				var sport = $('#im-team').val().split(" ");
				$('#description').val(sport[1]);
			}else if(type == "House Meeting"){
				$("#committee").val("Exec");
				$("#event").val("House Meeting");
			}else{
				$("#event").val("");
			}
			submission.validateEventName();
			submission.validateCommittee();

			if(type == "IM"){
				$(".im-team-control").slideDown();
				$("#event").attr("disabled","disabled");
			}else{
				$(".im-team-control").slideUp();
				$("#event").removeAttr("disabled");
			}

			if(type == "Other"){
				$(".description-control").slideDown();
				$("#committee").removeAttr("disabled");
			}else{
				//$(".description-control").slideUp();
				$("#committee").attr("disabled","disabled");
			}
		},
		validatePointsForm: function(){
			var valid = true,
			errors = [];

			if (!submission.validateFilledBy()){ valid = false; errors.push("Filled By"); }
			if (!valid_event_name){ valid = false; common.updateValidity($(".event-control"),valid); errors.push("Name"); }
			if (!submission.validateCommittee()){ valid = false; errors.push("Committee"); }
			if (!submission.validateDescription()){ valid = false; errors.push("Description"); }

			var valid_slivkans = true;

			$('.slivkan-entry-control').each(function(index){
				if(!submission.validateSlivkanName($(this),(index != 1))){ valid_slivkans = false; }
			});

			if(!valid_slivkans){ valid = false; errors.push("Attendees"); }


			var valid_fellows = true;

			$('.fellow-entry-control').each(function(){
				if(!submission.validateFellowName($(this))){ valid_fellows = false; }
			});

			if(!valid_fellows){ valid = false; errors.push("Fellows"); }


			if(valid){
				$('#submit-error').fadeOut();
				submission.submitPointsForm();
			}else{
				$("#submit-error").text("Validation errors in: "+errors.join(", ")).fadeIn();
			}

			return valid;
		},
		validateEventName: function(){
			var valid = false, event_name = $('#event').val(), event_names = [];

			//store value
			localStorage.spc_sub_name = event_name;

			valid_event_name = false;

			if((event_name.length <= 40 && event_name.length >= 8) || event_name == "P2P"){
				event_name += ' '+$("#date-val").val();

				$.getJSON("ajax/getEvents.php",function(data){
					$(".event-control").removeClass("warning");

					if(data.event_name.length > 0){
						event_names = data.event_name;

						if(event_names.indexOf(event_name) != -1){
							if(type == 'IM'){
								var last = parseInt($('#event').val().slice(-1),10);
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
					common.updateValidity($(".event-control"),valid_event_name);
				});
			}else{
				$('#event-name-length-error-count').html("Currently "+event_name.length+" characters");
				$('#event-name-length-error').fadeIn();
				common.updateValidity($(".event-control"),valid_event_name);
			}

			return valid;
		},
		validateIMTeam: function(){
			$('#event').val($('#im-team').val()+' 1');
			var sport = $('#im-team').val().split(" ");
			$('#description').val(sport[1]);
			submission.validateEventName();
		},
		validateCommittee: function(){
			var valid, committee = $('#committee').val();

			valid = committee != "Select One";

			common.updateValidity($('.committee-control'),valid);

			$('.slivkan-entry-control').each(function(){
				submission.validateSlivkanName($(this),true);
			});

			//store values
			submission.saveSlivkans();
			localStorage.spc_sub_committee = committee;

			return valid;
		},
		validateDescription: function(){
			var valid = true, description = $("#description").val();

			//store value
			localStorage.spc_sub_description = description;

			if(description.length < 10 && type == "Other"){
				valid = false;
				$("#description-length-error").fadeIn();
			}else{
				$("#description-length-error").fadeOut();
			}

			common.updateValidity($(".description-control"),valid);

			return valid;
		},
		validateFilledBy: function(){
			var valid = true, name = $('#filled-by').val();

			//store value
			localStorage.spc_sub_filledby = name;

			if (nicknames.nickname.indexOf(name) != -1){
				name = nicknames.full_name[nicknames.nickname.indexOf(name)];
				$('#filled-by').val(name);
			}

			$('.filled-by-control').removeClass("warning");

			if(name.length > 0){
				valid = slivkans.full_name.indexOf(name) != -1;
				common.updateValidity($('.filled-by-control'),valid);
			}else{
				$('.filled-by-control').addClass('error');
				valid = false;
			}

			return valid;
		},
		validateSlivkanName: function(entry,inBulk){
			var valid = true,
			slivkan_entry = entry.find('.slivkan-entry'),
			helper = entry.find('.helper-point'),
			committee = entry.find('.committee-point'),
			name = slivkan_entry.val();

			if (nicknames.nickname.indexOf(name) != -1){
				name = nicknames.full_name[nicknames.nickname.indexOf(name)];
				slivkan_entry.val(name);
			}

			//only process individually
			if(!inBulk){
				var nameArray = [];

				//clear duplicates
				$('#slivkan-entry-tab').find('.slivkan-entry').each(function(index){
					var self = $(this);
					if (self.val().length > 0){
						if (nameArray.indexOf(self.val()) == -1){
							nameArray.push(self.val());
						}else{
							self.val('');
							$('#duplicate-alert').slideDown();
							submission.validateSlivkanName(self.parent(),true);
						}
					}
				});

				//no names = invalid
				if(nameArray.length === 0){ valid=false; }

				//store values
				submission.saveSlivkans();

				//update name in case it changed
				name = slivkan_entry.val();
			}


			entry.removeClass("warning");

			if (name.length > 0){
				name_ind = slivkans.full_name.indexOf(name);
				if(name_ind != -1){
					if(slivkans.committee[name_ind] == $('#committee').val() && type != 'IM'){
						submission.showCommitteeMember(helper,committee,inBulk);
					}else if(type == 'IM' || slivkans.committee[name_ind] == 'Facilities' || slivkans.committee[name_ind] == 'Exec'){
						submission.hideButtons(helper,committee,inBulk);
					}else{
						submission.showHelperPoint(helper,committee,inBulk);
					}
				}else{ valid=false; }
				common.updateValidity(entry,valid);
			}else{
				entry.removeClass("success").removeClass("error");
				submission.hideButtons(helper,committee,inBulk);
			}

			return valid;
		},
		showHelperPoint: function(helper,committee,inBulk){ //quick: 46.15
			committee.removeClass('active');
			if(inBulk){
				committee.hide();
				helper.show();
			}else{
				committee.hide('slide',function(){
					helper.show('slide');
				});
			}
		},
		showCommitteeMember: function(helper,committee,inBulk){
			helper.removeClass('active');
			if(inBulk){
				helper.hide();
				committee.show();
			}else{
				helper.hide('slide',function(){
					committee.show('slide');
				});
			}
			committee.addClass('active');
		},
		hideButtons: function(helper,committee,inBulk){
			helper.removeClass('active');
			committee.removeClass('active');
			if(inBulk){
				helper.hide();
				committee.hide();
			}else{
				helper.hide('slide');
				committee.hide('slide');
			}
		},
		validateFellowName: function(entry){
			var valid = true,
			nameArray = [],
			fellow_entry = entry.find('.fellow-entry'),
			name = fellow_entry.val();

			//clear duplicates
			$('.fellow-entry').each(function(index){
				if (nameArray.indexOf($(this).val()) != -1){ $(this).val(''); name=''; $('#duplicate-alert').slideDown(); }
				if ($(this).val().length > 0){ nameArray.push($(this).val()); }
			});

			entry.removeClass("warning");

			if (name.length > 0){
				valid = fellows.indexOf(name) != -1;
				common.updateValidity(entry,valid);
			}else{
				entry.removeClass("success").removeClass("error");
			}

			return valid;
		},
		processBulkNames: function(){
			names = $('#bulk-names').val();

			//remove "__ mins ago" and blank lines
			names = names.replace(/(\d+ .+ago[\r\n]?$)|(^[\r\n])/gm,"");

			$('#bulk-names').val(names);
		},
		addBulkNames: function(){
			var slots = [],
			free_slots = 0;

			$('#slivkan-entry-tab').find('.slivkan-entry').each(function(){
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
				submission.appendNameInputs(n);
				for(var k=0; k<n; k++){slots.push(0);}
			}

			var slivkan_entries = $('#slivkan-entry-tab').find('.slivkan-entry'),
			len = nameArray.length;
			for(var i=0; i<len; i++){
				var name = nameArray[i];

				//check if wildcard
				wildcardInd = slivkans.wildcard.indexOf(name);
				if(wildcardInd != -1){
					name = slivkans.full_name[wildcardInd];
				}

				ind = slots.indexOf(0);
				slots[ind] = 1;
				slivkan_entries.eq(ind).val(name);
				submission.validateSlivkanName(slivkan_entries.eq(ind).parent(),(i < len-1));
			}
		},
		sortEntries: function(){
			var nameArray = [];

			nameArray = submission.saveSlivkans();

			//clear slivkans
			$('#slivkan-entry-tab').find('.slivkan-entry').val("");

			//reset buttons
			$('.committee-point').removeClass('active');
			$('.helper-point').removeClass('active');

			nameArray = nameArray.sort();

			submission.addSlivkans(nameArray);

			$('#sort-alert').slideDown();
		},
		saveSlivkans: function(){
			var nameArray = [];

			//forming name array, but appending values corresponding to the helper/committee buttons:
			//0 - unpressed, 1 - pressed
			$('#slivkan-entry-tab').find('.slivkan-entry-control').each(function(){
				var self = $(this), name = self.find('.slivkan-entry').val();
				if(name.length > 0){
					h = (self.find('.helper-point').hasClass("active")) ? "1" : "0";
					c = (self.find('.committee-point').hasClass("active")) ? "1" : "0";

					nameArray.push(name+h+c);
				}
			});

			localStorage.spc_sub_attendees = nameArray.join(", ");

			return nameArray;
		},
		addSlivkans: function(nameArray){
			var entries = $('#slivkan-entry-tab').find('.slivkan-entry-control'),
			len = nameArray.length;

			for(var i=0; i<len; i++){
				var name = nameArray[i].slice(0,nameArray[i].length-2);
				h = nameArray[i].slice(nameArray[i].length-2,nameArray[i].length-1);
				c = nameArray[i].slice(nameArray[i].length-1);

				entry = entries.eq(i);
				entry.find('.slivkan-entry').val(name);
				submission.validateSlivkanName(entry,(i < len-1));
				if(h=="1"){ entry.find('.helper-point').addClass("active"); }
				if(c=="0"){ entry.find('.committee-point').removeClass("active"); }
			}

			for(i; i<entries.length; i++){
				submission.validateSlivkanName(entries.eq(i),true);
			}
		},
		resetForm: function(force){
			if(force || confirm("Reset form?")){
				$('.type-btn').last().click();
				$('#event').val(""); $('.event-control').removeClass("success").removeClass("error");
				$('#description').val(""); $('.description-control').removeClass("success").removeClass("error");
				$('#committee').val("Select One"); $('.committee-control').removeClass("success").removeClass("error");
				$('#filled-by').val(""); $('.filled-by-control').removeClass("success").removeClass("error");
				$('#comments').val("");

				$('#slivkan-entry-tab').find('.slivkan-entry-control').slice(15).remove();

				$('#slivkan-entry-tab').find('.slivkan-entry-control').each(function(){
					$(this).find('.slivkan-entry').val("");
					submission.validateSlivkanName($(this),true);
				});
				submission.validateSlivkanName($('.slivkan-entry-control').last());

				$('.fellow-entry').each(function(index){
					$(this).val("");
					submission.validateFellowName($(this).parent());
				});

				$('#event-name-error').fadeOut();
				$('#event-name-length-error').fadeOut();
				$('#description-length-error').fadeOut();
				$('#duplicate-error').fadeOut();
				$('#submit-error').fadeOut();

				localStorage.spc_sub_filledby = "";
				localStorage.spc_sub_type = "";
				localStorage.spc_sub_name = "";
				localStorage.spc_sub_committee = "";
				localStorage.spc_sub_comments = "";
				localStorage.spc_sub_attendees = "";
			}
		},
		submitPointsForm: function(){
			var data = {
				date: $('#date-val').val(),
				type: type.toLowerCase().replace(" ","_"),
				committee: $('#committee').val(),
				event_name: $('#event').val(),
				description: $('#description').val(),
				filled_by: slivkans.nu_email[slivkans.full_name.indexOf($('#filled-by').val())],
				comments: $('#comments').val(),
				attendees: [],
				helper_points: [],
				committee_members: [],
				fellows: []
			};

			$('#slivkan-entry-tab').find('.slivkan-entry').each(function(index){
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
			});


			//console.log(JSON.stringify(data));

			//clear receipt:
			$('.results-row').remove();

			for(var obj in data){
				if(obj == "attendees" || obj == "helper_points" || obj == "committee_members" || obj == "fellows"){
					val = data[obj].join(", ");
				}else{
					val = data[obj];
				}

				$('<tr class="results-row" />').append(
					$('<td class="results-label" />').html(obj)
				).append(
					$('<td class="results" />').html(val)
				).appendTo("#receipt tbody");
			}

			$('#submit-results').modal({
				/*backdrop: "static",
				keyboard: false,*/
				show: true
			}).on('shown', function(){
				$('body').css('overflow', 'hidden');
			}).on('hidden', function(){
				$('body').css('overflow', 'auto');
			});

			$('#real-submit').off('click');
			$('#real-submit').on('click',function(){
				$.getJSON('./ajax/submitPointsForm.php',data,function(data_in){
					$('#results-status').parent().removeClass("warning");
					if(data_in.error){
						$('#results-status').html("Error in Step "+data_in.step).parent().addClass("error");
					}else{
						$('#unconfirmed').fadeOut({complete: function(){$('#confirmed').fadeIn();}});
						$('#results-status').html("Success!").parent().addClass("success");

						submission.resetForm("force");
					}
				});
			});
		}
	};

	return {
		breakdown: breakdown,
		table: table,
		correction: correction,
		submission: submission
	};
}(jQuery));