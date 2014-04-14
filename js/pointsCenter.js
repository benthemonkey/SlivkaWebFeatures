define(['jquery', 'moment', 'hogan'], function($, moment, Hogan) {
	'use strict';
	var slivkans, nicknames, fellows, events, type = 'Other', valid_event_name = false, quarter_start, quarter_end;

	//add indexOfKey (useful: http://jsperf.com/js-for-loop-vs-array-indexof)
	Array.prototype.indexOfKey = function(key, value) {
		for(var i=0; i < this.length; i++){
			if(this[i][key] === value){
				return i;
			}
		}

		return -1;
	};

	//functions used by multiple pages
	var common = {
		updateValidity: function(element, valid) {
			if(valid){
				element.addClass('has-success').removeClass('has-error');
			}else{
				element.removeClass('has-success').addClass('has-error');
			}
		},
		typeaheadOpts: function(name, slivkans) {
			return {
				name: name,
				valueKey: 'full_name',
				local: slivkans,
				template: ['<div class="slivkan-suggestion{{#dupe}} slivkan-dupe{{/dupe}}">{{full_name}}',
							'{{#photo}}<img src="img/slivkans/{{photo}}.jpg" />{{/photo}}</div>'].join(''),
				engine: Hogan
			};
		},
		destroyTypeahead: function(event) {
			var target = $(this);
			if(target.hasClass('tt-query')){
				//needs a delay because typeahead.js seems to not like destroying on focusout
				setTimeout(function(target) {
					event.data.callback(target.typeahead('destroy').closest('.form-group'));
				}, 1, target);
			}
		}
	},

	breakdown = {
		init: function() {
			$.getJSON('ajax/getSlivkans.php', function(data) {
				slivkans = data.slivkans;
				quarter_start = data.quarter_info.start_date;
				quarter_end = data.quarter_info.end_date;

				for(var i=0; i<slivkans.length; i++){
					$('<option />').attr('value', slivkans[i].nu_email).text(slivkans[i].full_name).appendTo('#slivkan');
				}

				if(localStorage.spc_brk_slivkan){
					$('#slivkan').val(localStorage.spc_brk_slivkan);
					breakdown.getSlivkanPoints();
				}

				$('#slivkan').on('change', breakdown.getSlivkanPoints);
			});
		},
		getSlivkanPoints: function() {
			var nu_email = $('#slivkan').val(),
				attendedEventsEl = $('#attendedEvents'),
				unattendedEventsEl = $('#unattendedEvents');

			if(nu_email.length > 0){
				localStorage.spc_brk_slivkan = nu_email;

				$('.breakdown').fadeOut(function() {
					attendedEventsEl.empty();
					unattendedEventsEl.empty();
					$('#otherPointsTableBody').empty();

					$.getJSON('ajax/getPointsBreakdown.php', {nu_email: nu_email, start: quarter_start, end: quarter_end}, function(data) {
						var i, eventData = [],
							imData = [],
							event_total = 0,
							im_total = 0,
							im_extra = 0,
							has_other = false;

						if(data.events.attended.length > 0){
							for(i=data.events.attended.length-1; i>=0; i--){
								attendedEventsEl
									.append($('<tr/>')
										.append($('<td/>').text(data.events.attended[i].event_name)));
							}
						}else{
							attendedEventsEl
								.append($('<tr/>')
									.append($('<td/>').text('None :(')));
						}

						if(data.events.unattended.length > 0){
							for(i=data.events.unattended.length-1; i>=0; i--){
								unattendedEventsEl
									.append($('<tr/>')
										.append($('<td/>').text(data.events.unattended[i].event_name)));
							}
						}else{
							unattendedEventsEl
								.append($('<tr/>')
									.append($('<td/>').text('None :)')));
						}

						for(i=0; i<data.other_breakdown.length; i++){
							if(data.other_breakdown[i][0]){
								$('#otherPointsTableBody')
									.append($('<tr/>')
										.append($('<td/>').text(data.other_breakdown[i][0]))
										.append($('<td/>').text(data.other_breakdown[i][1])));

								has_other = true;
							}
						}

						if(has_other){
							$('#otherPointsTable').show();
						}else{
							$('#otherPointsTable').hide();
						}

						for(i=0; i<data.events.counts.length; i++){
							eventData.push([data.events.counts[i].committee, parseInt(data.events.counts[i].count, 10)]);

							event_total += parseInt(data.events.counts[i].count, 10);
						}

						$('.eventPoints').text(event_total);
						breakdown.drawChart(eventData, 'Event Points (' + event_total + ' Total)', 'eventsChart');

						if(data.ims.length > 0){
							$('#imsChart').show();
							for(i=0; i<data.ims.length; i++){
								data.ims[i].count = parseInt(data.ims[i].count, 10);

								imData.push([data.ims[i].sport, data.ims[i].count]);

								if(data.ims[i].count >= 3){
									im_total += data.ims[i].count;
								} else {
									im_extra += data.ims[i].count;
								}
							}

							if(im_total > 15){
								im_extra += im_total - 15;
								im_total = 15;
							}

							breakdown.drawChart(imData,
								['IMs (', im_total, ' Points, ', im_extra, (im_extra==1 ? ' Doesn\'t':' Don\'t'), ' Count)'].join(''),
								'imsChart');
						}else{
							$('#imsChart').hide();
						}

						$('.imPoints').text(im_total);
						$('.helperPoints').text(data.helper);
						$('.committeePoints').text(data.committee);
						$('.otherPoints').text(data.other);

						$('.totalPoints').text(
							[event_total, im_total, data.helper, data.committee, data.other].map(function(n) {
								return parseInt(n, 10);
							}).reduce(function(a, b) {
								return a+b;
							}));

						$('.breakdown').fadeIn();
					});
				});
			}
		},
		drawChart: function(tableData, title_in, id) {
			setTimeout(function() {
				$('#'+id).highcharts({
					credits: {
						enabled: false
					},
					title: {
						text: title_in,
						style: {
							'font-size': '8pt'
						}
					},
					tooltip: {
						pointFormat: '{series.name}: {point.y}, <b>{point.percentage:.1f}%</b>'
					},
					series: [{
						type: 'pie',
						name: 'Events',
						data: tableData
					}]
				});
			}, 500);
		}
	},

	table = {
		init: function() {
			var nameColWidth = $('.nameHeader').width(),//170,
				eventColWidth = $('.eventHeader').width(),//20,
				totalsColWidth = $('.totalsHeader').width(),//24,
				tableWrapper = $('.table-wrapper'),
				points_table = JSON.parse(window.points_table),
				by_year = points_table.by_year,
				by_suite = points_table.by_suite,
				lastScroll = 0,
				delay = (function() {
					var timer = 0;
					return function(callback, ms) {
						clearTimeout (timer);
						timer = setTimeout(callback, ms);
					};
				})(),
				adjustWidth = function() {
					var width = (tableWrapper.width() - nameColWidth - 6*totalsColWidth - 2) + 'px';
					$('.endHeader').css({
						'width': width,
						'min-width': width,
						'max-width': width
					});
				};

			events = points_table.events;

			tableWrapper.scroll(function() {
				delay(function() {
					if($('body').width() > 768){
						var scroll = tableWrapper.scrollLeft(),
							round = lastScroll < scroll ? Math.ceil : Math.floor;

						if(lastScroll != scroll){
							lastScroll = round(scroll/eventColWidth) * eventColWidth;
							tableWrapper.scrollLeft(lastScroll);
						}
					}
				}, 100);
			});

			$(window).resize(function() {
				delay(adjustWidth, 500);
			});
			adjustWidth();

			$('.filter-row').show();
			if(localStorage.spc_tab_noFilter != '1'){
				table.oTable = $('#table').dataTable({
					aoColumnDefs: [
						{ aTargets: ['_all'], asSorting: ['desc', 'asc'] },
						{ aTargets: [-1], bSortable: false }
					],
					bPaginate: false,
					sDom: 't'
				});

				$('#name-filter').on('keyup', function() {
					delay(function() {
						table.oTable.fnFilter($('#name-filter').val());
					}, 500);
				});

				$('#gender-filter').on('change', function() {
					var option = $('#gender-filter').val();
					table.oTable.fnFilter(option, 1);
				});

				$('#im-filter').on('change', table.columnFilter);

				$('.multiselect').multiselect({
					buttonClass: 'btn btn-default',
					onChange: table.columnFilter
				});

				$('#noFilter').on('click', function() {
					localStorage.spc_tab_noFilter = 1;
				});
			}else{
				$('.filter').not('.dropdown').hide();
				$('#noFilter').hide();
				$('#enableFilter').on('click', function() {
					localStorage.spc_tab_noFilter = 0;
				}).show();
				$('td').css('font-size', '12px');
			}

			var headers = $('th');

			for(var i=0; i<events.length; i++){
				var en = events[i].event_name,
					name = en.substr(0, en.length-11),
					date = en.substr(en.length-5);

				headers.eq(i+2).popover({
					trigger: 'hover',
					html: true,
					title: name,
					content: ['Date: ', date, '<br/>Attendees: ', events[i].attendees,
						(events[i].description.length > 0 ? '<br/>Description: ' + events[i].description : '')].join(''),
					placement: 'bottom',
					container: '#table'
				});
			}

			// filling years and suites tables
			by_year.sort(function(a, b) {
				return b[1]-a[1];
			});

			for(i=0; i<by_year.length; i++){
				$('<tr><td>'+by_year[i][0]+'</td><td>'+by_year[i][1]+'</td></tr>').appendTo('#years');
			}

			by_suite.sort(function(a, b) {
				return b[1]-a[1];
			});

			for(i=0; i<by_suite.length; i++){
				$('<tr><td>'+by_suite[i][0]+'</td><td>'+by_suite[i][1]+'</td></tr>').appendTo('#suites');
			}
		},
		oTable: null,
		columnFilter: function() {
			var committees = $('#committee-filter').find('option:selected').map(function() { return this.innerHTML; }).get(),
				ims = $('#im-filter').val(),
				n = 0;

			if(ims === '2'){
				committees = [];
				$('#committee-filter').parent().find('.dropdown-toggle').attr('disabled', 'disabled');
			}else{
				$('#committee-filter').parent().find('.dropdown-toggle').removeAttr('disabled');
			}

			for(var i=0; i<events.length; i++){
				if(committees.indexOf(events[i].committee) !== -1 && (ims !== '1' || events[i].type !== 'im') || (ims === '2' && events[i].type === 'im')){
					table.oTable.fnSetColumnVis(i + 2, true, false);
					n++;
				}else{
					table.oTable.fnSetColumnVis(i + 2, false, false);
				}
			}

			table.oTable.fnDraw();
		}
	},

	correction = {
		init: function() {
			$.getJSON('ajax/getSlivkans.php', function(data) {
				slivkans = data.slivkans;
				nicknames = data.nicknames;

				//tack on nicknames to slivkans
				for(var i=0; i<nicknames.length; i++){
					var ind = slivkans.indexOfKey('nu_email', nicknames[i].nu_email);
					if(ind !== -1){
						slivkans[ind].tokens.push(nicknames[i].nickname);
					}
				}

				$('#filled-by').typeahead(common.typeaheadOpts('slivkans', slivkans));
			});

			$.getJSON('ajax/getEvents.php', function(events) {
				for(var i=events.length-1; i>=0; i--){
					if(events[i].type == 'p2p'){
						$('<option disabled="disabled"></option>').text(events[i].event_name).appendTo('#event-name');
					}else{
						$('<option></option>').text(events[i].event_name).appendTo('#event-name');
					}
				}
			});

			//event handlers
			$('#filled-by').on('focusout', correction.validateFilledBy);
			$('#submit').on('click', correction.validatePointsCorrectionForm);
		},
		validatePointsCorrectionForm: function() {
			var valid = true,
			errors = [];

			if(!correction.validateFilledBy()){ valid = false; errors.push('Your Name'); }
			if($('#event-name').val() == 'Select One'){ valid = false; errors.push('Event Name'); }
			if($('#comments').val() === ''){ valid = false; errors.push('Comments'); }

			if(valid){
				$('#submit-error').fadeOut();
				correction.submitPointsCorrection();
			}else{
				$('#submit-error').text('Validation errors in: '+errors.join(', ')).fadeIn();
			}
		},
		validateFilledBy: function() {
			var valid, name = $('#filled-by').val();

			$('.filled-by-control').removeClass('has-warning');

			if(name.length > 0){
				valid = slivkans.indexOfKey('full_name', name) != -1;
				common.updateValidity($('.filled-by-control'), valid);
			}else{
				$('.filled-by-control').addClass('error');
				valid = false;
			}

			return valid;
		},
		resetForm: function() {
			$('#filled-by').val('');
			$('.filled-by-control').removeClass('has-success has-error');
			$('#event-name').val('Select One');
			$('#comments').val('');
			$('#submit-error').fadeOut();
		},
		submitPointsCorrection: function() {
			var data = {
				event_name: $('#event-name').val(),
				name: $('#filled-by').val(),
				sender_email: slivkans[slivkans.indexOfKey('full_name', $('#filled-by').val())].nu_email,
				comments: $('#comments').val()
			};
			$('#response').fadeOut();

			$.getJSON('./ajax/sendPointsCorrection.php', data, function(response) {
				$('#response').text('Response: '+response.message);
				$('#form-actions').html('<a class="btn btn-primary" href="table.php">View Points</a>' +
					'<a class="btn btn-default" href="correction.php">Submit Another</a>');
				$('#response').fadeIn();
			});
		}
	},

	submission = {
		init: function() {
			//prevent [Enter] from causing form submit
			$(window).on('keydown',	function(event) {
				if(event.keyCode == 13) {
					event.preventDefault();
					return false;
				}
			});

			$.getJSON('ajax/getSlivkans.php', function(data) {
				slivkans = data.slivkans;
				nicknames = data.nicknames;
				fellows = data.fellows;
				var im_teams = data.quarter_info.im_teams;

				//tack on nicknames to slivkans
				for(var i=0; i<nicknames.length; i++){
					var ind = slivkans.indexOfKey('nu_email', nicknames[i].nu_email);
					if(ind !== -1){
						slivkans[ind].tokens.push(nicknames[i].nickname);
					}
				}

				//initialization
				submission.appendSlivkanInputs(14);
				submission.appendFellowInputs(9);

				//im teams
				for(i=0; i<im_teams.length; i++){
					$('<option />').text(im_teams[i]).appendTo('#im-team');
				}

				//loading saved values
				if(localStorage.spc_sub_committee){
					$('#committee').val(localStorage.spc_sub_committee);
					//submission.validateCommittee();
				}
				if(localStorage.spc_sub_attendees){
					var attendees = localStorage.spc_sub_attendees.split(', ');
					if(attendees.length > 14){ submission.appendSlivkanInputs(attendees.length - 14); }
					submission.addSlivkans(attendees);
				}
				if(localStorage.spc_sub_filledby){
					$('#filled-by').val(localStorage.spc_sub_filledby);
					submission.validateFilledBy();
				}
				if(localStorage.spc_sub_type && localStorage.spc_sub_type != 'Other'){
					$('input[value="'+localStorage.spc_sub_type+'"]:radio').parent().click();
				}
				if(localStorage.spc_sub_date){
					$('#date').val(localStorage.spc_sub_date);
				}
				if(localStorage.spc_sub_name){
					$('#event').val(localStorage.spc_sub_name);
					submission.validateEventName();
				}
				if(localStorage.spc_sub_description){
					$('#description').val(localStorage.spc_sub_description);
					submission.validateDescription();
				}
				if(localStorage.spc_sub_comments){
					$('#comments').val(localStorage.spc_sub_comments);
				}
				if(localStorage.spc_sub_fellows){
					var fellow_attendees = localStorage.spc_sub_fellows.split(', '), fellow_entry;
					if(fellow_attendees.length > 9){ submission.appendFellowInputs(fellow_attendees.length - 9); }

					for(i=0; i<fellow_attendees.length; i++){
						fellow_entry = $('.fellow-entry').eq(i);
						fellow_entry.val(fellow_attendees[i]);
						submission.validateFellowName(fellow_entry.closest('.form-group'));
					}
				}

				//autocomplete and events for slivkan/fellow inputs
				$('#filled-by').typeahead(common.typeaheadOpts('slivkans', slivkans));

				$('#slivkan-entry-tab')	.on('focus', '.slivkan-entry', submission.handlers.slivkanTypeahead)
										.on('typeahead:closed', '.slivkan-entry.tt-query',
											{callback: submission.validateSlivkanName},
											common.destroyTypeahead);

				$('#fellow-entry-tab')	.on('focus', '.fellow-entry', submission.handlers.fellowTypeahead)
										.on('typeahead:closed', '.fellow-entry.tt-query',
											{callback: submission.validateFellowName},
											common.destroyTypeahead);
			});

			/*$('#date').datepicker({
				minDate: -5,
				maxDate: 0,
				dateFormat: 'm/d',
				showOn: 'button',
				buttonText: '',
				altField: '#date-val',
				altFormat: 'yy-mm-dd',
				onSelect: function(date) {
					submission.validateEventName();
					localStorage.spc_sub_date = date;
				}
			});
			$('#date').datepicker('setDate', new Date());
			$('button.ui-datepicker-trigger').addClass('btn btn-default').html('<i class="glyphicon glyphicon-calendar"></i>')
				.wrap('<div class="input-group-btn"></div>');
			*/
			//dates
			for(var i=0; i<5; i++){
				var date = moment().subtract('days', i).format('YYYY-MM-DD');
				$('<option />').text(moment(date).format('ddd, M/D')).attr('value', date).appendTo('#date');
			}

			//event handlers for inputs
			$('#filled-by')			.on('focus',	submission.handlers.addClassWarning)
									.on('focusout',	submission.validateFilledBy);
			$('#type')				.on('click',	submission.toggleType);
			$('#event')				.on('focus',	submission.handlers.addClassWarning)
									.on('focusout',	submission.validateEventName);
			$('#date')				.on('change',	function() { localStorage.spc_sub_date = $(this).val(); submission.validateEventName(); });
			$('#im-team')			.on('change',	submission.validateIMTeam);
			$('#committee')			.on('change',	submission.validateCommittee);
			$('#description')		.on('focusout',	submission.validateDescription);
			$('#comments')			.on('focusout',	function() { localStorage.spc_sub_comments = $('#comments').val(); });
			$('#close-sort-alert')	.on('click',	function() { $('#sort-alert').slideUp(); });
			$('#close-dupe-alert')	.on('click',	function() { $('#duplicate-alert').slideUp(); });
			$('#sort-entries')		.on('click',	submission.sortEntries);
			$('#submit')			.on('click',	submission.validatePointsForm);
			$('#reset')				.on('click',	submission.resetForm);
			$('#bulk-names')		.on('keyup',	submission.processBulkNames);
			$('#add-bulk-names')	.on('click',	submission.addBulkNames);

			$('#tabs a:first').tab('show');
		},
		handlers: {
			addClassWarning: function() {
				$(this).closest('.form-group').addClass('has-warning');
			},
			slivkanTypeahead: function() {
				var target = $(this), slivkans_tmp = JSON.parse(JSON.stringify(slivkans));

				if(localStorage.spc_sub_attendees){
					var ind;
					localStorage.spc_sub_attendees.split(', ').forEach(function(el) {
						ind = slivkans_tmp.indexOfKey('full_name', el);
						if(ind !== -1){
							slivkans_tmp[ind].dupe = true;
						}
					});
				}

				if(type == 'Committee Only'){
					var committee = $('#committee').val();

					slivkans_tmp = slivkans_tmp.filter(function(item) {
						return item.committee == committee;
					});
				}

				if(target.closest('.slivkan-entry-control').addClass('has-warning').is(':last-child')){
					var num_inputs = $('#slivkan-entry-tab').find('.slivkan-entry').length;
					if(num_inputs < 120){
						submission.appendSlivkanInputs(1);
					}
				}
				if(!target.hasClass('tt-query')){
					target.typeahead(common.typeaheadOpts('slivkans'+Math.random(), slivkans_tmp)).focus();
				}
			},
			fellowTypeahead: function() {
				var target = $(this);
				if(target.closest('.fellow-entry-control').addClass('has-warning').is(':last-child')){
					var num_inputs = $('#fellow-entry-tab').find('.fellow-entry').length;
					if(num_inputs < 20){ submission.appendFellowInputs(1); }
				}
				if(!target.hasClass('tt-query')){
					target.typeahead(common.typeaheadOpts('fellows', fellows)).focus();
				}
			}
		},
		appendSlivkanInputs: function(n) {
			//2-4ms per insertion. Slow but acceptable.
			var cloned = $('#slivkan-entry-tab').find('.slivkan-entry-control').last(),
			start = parseInt(cloned.find('.input-group-addon').text(), 10);
			for (var i=0; i<n; i++){
				cloned.clone().appendTo('#slivkan-entry-tab')
				.removeClass('has-warning')
				.find('.input-group-addon').text(start+i+1);
			}
		},
		appendFellowInputs: function(n) {
			var cloned = $('#fellow-entry-tab').find('.fellow-entry-control').last(),
			start = parseInt(cloned.find('.input-group-addon').text(), 10);
			for (var i=0; i<n; i++){
				cloned.clone().appendTo('#fellow-entry-tab')
				.removeClass('has-warning')
				.find('.input-group-addon').text(start+i+1);
			}
		},
		toggleType: function(event) {
			type = $(event.target).find('input').val();

			//store value
			localStorage.spc_sub_type = type;

			//clear description if **previous** type was IM
			var previous_type = $('.type-btn.active').find('input').val();
			if(previous_type == 'IM'){
				$('#description').val('');
				submission.validateDescription();
			}

			$('#committee').attr('disabled', 'disabled');

			switch(type){
			case 'P2P':
				$('#committee').val('Faculty');
				$('#event').val('P2P');
				break;
			case 'IM':
				$('#committee').val('Social');
				submission.validateIMTeam();
				break;
			case 'House Meeting':
				$('#committee').val('Exec');
				$('#event').val('House Meeting');
				break;
			case 'Committee Only':
				if($('#committee :selected').hasClass('not-standing-committee')){
					$('#committee').val('Academic');
				}
			default:
				$('#event').val('');
				$('#committee').removeAttr('disabled');
			}

			submission.validateEventName();
			submission.validateCommittee();

			if(type == 'IM'){
				$('.im-team-control').slideDown();
				$('#event').attr('disabled', 'disabled');
			}else{
				$('.im-team-control').slideUp();
				$('#event').removeAttr('disabled');
			}

			if(type == 'Committee Only'){
				$('.not-standing-committee').attr('disabled', 'disabled');
			}else{
				$('.not-standing-committee').removeAttr('disabled');
			}

			if(type == 'Other'){
				$('.description-control').slideDown();
			}else{
				$('.description-control').slideUp();
			}
		},
		validatePointsForm: function() {
			var valid = true, valid_slivkans = true, valid_fellows = true,
			errors = [];

			if(!submission.validateFilledBy()){ valid = false; errors.push('Filled By'); }
			if(!valid_event_name){ valid = false; common.updateValidity($('.event-control'), valid); errors.push('Name'); }
			if(!submission.validateCommittee()){ valid = false; errors.push('Committee'); }
			if(!submission.validateDescription()){ valid = false; errors.push('Description'); }

			$('.slivkan-entry-control').each(function(index) {
				if(!submission.validateSlivkanName($(this), (index !== 0))){ valid_slivkans = false; }
			});

			if(!valid_slivkans){ valid = false; errors.push('Attendees'); }


			$('.fellow-entry-control').each(function() {
				if(!submission.validateFellowName($(this))){ valid_fellows = false; }
			});

			if(!valid_fellows){ valid = false; errors.push('Fellows'); }


			if(valid){
				$('#submit-error').fadeOut();
				submission.submitPointsForm();
			}else{
				$('#submit-error').text('Validation errors in: '+errors.join(', ')).fadeIn();
			}

			return valid;
		},
		validateEventName: function() {
			var valid = false,
				event_name = $('#event').val(),
				event_name_trimmed = event_name.replace(/^\s+|\s+$/g, '');

			//errors abound in the PHP with trailing whitespace
			if(event_name.length > event_name_trimmed.length){
				$('#event').val(event_name_trimmed);
				event_name = event_name_trimmed;
			}

			//store value
			localStorage.spc_sub_name = event_name;

			valid_event_name = false;

			if((event_name.length <= 32 && event_name.length >= 8) || event_name == 'P2P'){
				event_name += ' ' + $('#date').val();

				$.getJSON('ajax/getEvents.php', function(events) {
					$('.event-control').removeClass('has-warning');

					if(events.length > 0 && events.indexOfKey('event_name', event_name) != -1){
						if(type == 'IM'){
							var last = parseInt($('#event').val().slice(-1), 10);
							$('#event').val($('#event').val().slice(0, -1) + (last+1).toString());
							submission.validateEventName();
						}else{
							valid_event_name = false;
							$('#event-name-error').fadeIn();
						}
					}else{
						valid_event_name = true;
						$('#event-name-error').fadeOut();
					}

					$('#event-name-length-error').fadeOut();
					common.updateValidity($('.event-control'), valid_event_name);
				});
			}else{
				$('#event-name-length-error-count').html('Currently '+event_name.length+' characters');
				$('#event-name-length-error').fadeIn();
				common.updateValidity($('.event-control'), valid_event_name);
			}

			return valid;
		},
		validateIMTeam: function() {
			var im_team = $('#im-team').val();
			$.getJSON('ajax/getIMs.php', {team: im_team}, function(events) {
				$('#event').val(im_team + ' ' + (events.length + 1));
				$('#description').val(im_team.split(' ')[1]);
				submission.validateEventName();
			});
		},
		validateCommittee: function() {
			var committee = $('#committee').val(),
				valid = committee != 'Select One';

			common.updateValidity($('.committee-control'), valid);

			if(valid){
				localStorage.spc_sub_committee = committee;

				$('.slivkan-entry-control').each(function(index) {
					submission.validateSlivkanName($(this), (index !== 0));
				});
			}

			return valid;
		},
		validateDescription: function() {
			var valid = true, description = $('#description').val();

			//store value
			localStorage.spc_sub_description = description;

			if(description.length < 10 && type == 'Other'){
				valid = false;
				$('#description-length-error').fadeIn();
			}else{
				$('#description-length-error').fadeOut();
			}

			common.updateValidity($('.description-control'), valid);

			return valid;
		},
		validateFilledBy: function() {
			var valid = true, name = $('#filled-by').val(),
			nickname_ind = nicknames.indexOfKey('nickname', name);

			if(nickname_ind != -1){
				name = slivkans[slivkans.indexOfKey('nu_email', nicknames[nickname_ind].nu_email)].full_name;
				$('#filled-by').typeahead('setQuery', name);
			}

			//store value
			localStorage.spc_sub_filledby = name;

			$('.filled-by-control').removeClass('has-warning');

			if(name.length > 0){
				valid = slivkans.indexOfKey('full_name', name) != -1;
			}else{
				valid = false;
			}

			common.updateValidity($('.filled-by-control'), valid);

			return valid;
		},
		validateSlivkanName: function(entry, inBulk) {
			var valid = true,
			slivkan_entry = entry.find('.slivkan-entry'),
			name = slivkan_entry.val(),
			nickname_ind = nicknames.indexOfKey('nickname', name);

			if(nickname_ind != -1){
				name = slivkans[slivkans.indexOfKey('nu_email', nicknames[nickname_ind].nu_email)].full_name;
				slivkan_entry.val(name);
			}

			//only process individually
			if(!inBulk){
				var nameArray = [];

				//clear duplicates
				$('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
					var self = $(this);
					if(self.val().length > 0){
						if(nameArray.indexOf(self.val()) == -1){
							nameArray.push(self.val());
						}else{
							self.val('');
							$('#duplicate-alert').slideDown();
							submission.validateSlivkanName(self.parent(), true);
						}
					}
				});

				//no names = invalid
				if(nameArray.length === 0){ valid = false; }

				//store values
				submission.saveSlivkans();

				//update name in case it changed
				name = slivkan_entry.val();
			}

			entry.removeClass('has-warning');

			if(name.length > 0){
				var ind = slivkans.indexOfKey('full_name', name);

				valid &= ind != -1;

				if(type == 'Committee Only'){
					var committee = $('#committee').val();

					valid &= committee == slivkans[ind].committee;
				}

				common.updateValidity(entry, valid);
			}else{
				entry.removeClass('has-success has-error');
			}

			return valid;
		},
		validateFellowName: function(entry) {
			var valid = true,
			nameArray = [],
			fellow_entry = entry.find('.fellow-entry'),
			name = fellow_entry.val();

			//clear duplicates
			$('.fellow-entry').each(function() {
				if(nameArray.indexOf($(this).val()) != -1){
					$(this).val('');
					name='';
					$('#duplicate-alert').slideDown();
				}

				if($(this).val().length > 0){
					nameArray.push($(this).val());
				}
			});

			//save fellows
			localStorage.spc_sub_fellows = nameArray.join(', ');

			entry.removeClass('has-warning');

			if(name.length > 0){
				valid = fellows.indexOfKey('full_name', name) != -1;
				common.updateValidity(entry, valid);
			}else{
				entry.removeClass('has-success has-error');
			}

			return valid;
		},
		processBulkNames: function() {
			var names = $('#bulk-names').val();

			//remove '__ mins ago' and blank lines
			names = names.replace(/(\d+ .+ago[\r\n]?$)|(^[\r\n])/gm, '');

			$('#bulk-names').val(names);
		},
		addBulkNames: function() {
			var slots = [],
			free_slots = 0,
			names = $('#bulk-names').val(),
			nameArray;

			$('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
				if($(this).val().length > 0){
					slots.push(1);
				}else{
					slots.push(0);
					free_slots++;
				}
			});

			//if there's a hanging newline, remove it for adding but leave it in the textarea
			if(names[names.length-1] == '\r' || names[names.length-1] == '\n'){
				names = names.slice(0, names.length-1);
			}

			nameArray = names.split(/[\r\n]/gm);

			if(nameArray.length >= free_slots){
				var n = nameArray.length - free_slots + 1;
				submission.appendSlivkanInputs(n);
				for(var k=0; k<n; k++){
					slots.push(0);
				}
			}

			var slivkan_entries = $('#slivkan-entry-tab').find('.slivkan-entry'),
			len = nameArray.length;
			for(var i=0; i<len; i++){
				var name = nameArray[i];

				//check if wildcard
				var wildcardInd = slivkans.indexOfKey('wildcard', name);
				if(wildcardInd != -1){
					name = slivkans[wildcardInd].full_name;
				}

				var ind = slots.indexOf(0);
				slots[ind] = 1;
				slivkan_entries.eq(ind).val(name);
				submission.validateSlivkanName(slivkan_entries.eq(ind).closest('.slivkan-entry-control'), (i < len-1));
			}
		},
		sortEntries: function() {
			var nameArray = submission.saveSlivkans();

			//clear slivkans
			$('#slivkan-entry-tab').find('.slivkan-entry').val('');

			nameArray = nameArray.sort();

			submission.addSlivkans(nameArray);

			$('#sort-alert').slideDown();
		},
		saveSlivkans: function() {
			var nameArray = [];

			//forming name array, but appending values corresponding to the helper/committee buttons:
			//0 - unpressed, 1 - pressed
			$('#slivkan-entry-tab').find('.slivkan-entry-control').each(function() {
				var self = $(this), name = self.find('.slivkan-entry').val();
				if(name.length > 0){
					nameArray.push(name);
				}
			});

			localStorage.spc_sub_attendees = nameArray.join(', ');

			return nameArray;
		},
		addSlivkans: function(nameArray) {
			var entries = $('#slivkan-entry-tab').find('.slivkan-entry-control'),
			len = nameArray.length;

			for(var i=0; i<len; i++){
				var name = nameArray[i],
					entry = entries.eq(i);
				entry.find('.slivkan-entry').val(name);
				submission.validateSlivkanName(entry, (i < len-1));
			}

			for(i; i<entries.length; i++){
				submission.validateSlivkanName(entries.eq(i), true);
			}
		},
		resetForm: function(force) {
			if(force === 'force' || window.confirm('Reset form?')){
				$('.type-btn:last').click();
				$('#event').val('');
				$('.event-control').removeClass('has-success has-warning has-error');

				$('#date').val(moment().format('ddd, M/D'));
				$('#description').val('');
				$('.description-control').removeClass('has-success has-error');

				$('#committee').val('Select One');
				$('.committee-control').removeClass('has-success has-error');

				$('#filled-by').val('');
				$('.filled-by-control').removeClass('has-success has-error');

				$('#comments').val('');

				$('#slivkan-entry-tab').find('.slivkan-entry-control').slice(15).remove();

				$('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
					$(this).val('');
					submission.validateSlivkanName($(this).closest('.form-group'), true);
				});
				submission.validateSlivkanName($('.slivkan-entry-control').last());

				$('.fellow-entry').each(function() {
					$(this).val('');
					submission.validateFellowName($(this).closest('.form-group'));
				});

				$('#event-name-error').fadeOut();
				$('#event-name-length-error').fadeOut();
				$('#description-length-error').fadeOut();
				$('#duplicate-error').fadeOut();
				$('#submit-error').fadeOut();

				localStorage.spc_sub_filledby = '';
				localStorage.spc_sub_type = '';
				localStorage.spc_sub_name = '';
				localStorage.spc_sub_date = '';
				localStorage.spc_sub_committee = '';
				localStorage.spc_sub_description = '';
				localStorage.spc_sub_comments = '';
				localStorage.spc_sub_attendees = '';
			}
		},
		submitPointsForm: function() {
			var name, nu_email, val,
				data = {
					date: $('#date').val(),
					type: type.toLowerCase().replace(' ', '_'),
					committee: $('#committee').val(),
					event_name: $('#event').val(),
					description: $('#description').val(),
					filled_by: slivkans[slivkans.indexOfKey('full_name', $('#filled-by').val())].nu_email,
					comments: $('#comments').val(),
					attendees: [],
					fellows: []
				};

			$('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
				name = $(this).val();
				if(name.length > 0){
					nu_email = slivkans[slivkans.indexOfKey('full_name', name)].nu_email;

					data.attendees.push(nu_email);
				}
			});

			$('.fellow-entry').each(function() {
				name = $(this).val();

				if(name.length > 0){
					data.fellows.push(name);
				}
			});

			//clear receipt:
			$('#receipt').empty();

			for(var obj in data){
				if(data.hasOwnProperty(obj)){
					if(obj == 'attendees' || obj == 'fellows'){
						val = data[obj].join(', ');
					}else{
						val = data[obj];
					}

					$('<tr class="results-row" />').append(
						$('<td class="results-label" />').text(obj)
					).append(
						$('<td class="results" />').text(val)
					).appendTo('#receipt');
				}
			}

			$('<tr class="warning" />').append($('<td>Status</td>'))
				.append($('<td id="results-status">Unsubmitted</td>'))
				.appendTo('#receipt');

			$('#submit-results').modal('show');

			var real_submit = $('#real-submit');

			real_submit.off('click');
			real_submit.on('click', function() {
				real_submit.button('loading');

				$.getJSON('./ajax/submitPointsForm.php', data, function(data_in) {
					real_submit.button('reset');
					$('#results-status').parent().removeClass('warning');
					if(data_in.error){
						$('#results-status').text('Error in Step '+data_in.step).parent().addClass('error');
					}else{
						$('#unconfirmed').fadeOut({complete: function() {$('#confirmed').fadeIn();}});

						//reset buttons once modal closes
						$('#submit-results').on('hidden.bs.modal', function() {
							$('#confirmed').hide();
							$('#unconfirmed').show();
						});

						$('#results-status').text('Success!').parent().addClass('success');

						submission.resetForm('force');
					}
				});
			});
		}
	},

	faq = {
		init: function() {
			//nothing to do
		}
	},

	inboundPoints = {
		init: function() {
			$.ajax({
				url: './ajax/getCalendar.php',
				type: 'xml',
				async: true,
				success: function(xml) {
					xml = $.parseXML(xml);

					var events = [];

					$(xml).find('entry').each(function(i, el) {
						var title = el.childNodes[4].textContent,
						date = el.childNodes[5].textContent;
						date = date.slice(6, date.indexOf('to') - 1);

						var dt = parseInt(moment(date, ['ddd MMM DD, YYYY h:mma', 'ddd MMM DD, YYYY ha']).format('X'), 10);

						events.push([title, date, dt]);
					});

					events = events.sort(function(a, b) { return a[2] - b[2]; });

					for(var i=0; i<events.length; i++){
						$('<li />').html(events[i][0] + ' ' + events[i][1]).appendTo('#events'); // + ' ' + moment(events[i][2]+'', 'X').format('ddd MMM DD, YYYY h:mma')
					}
				}
			});
		}
	},

	rankings = {
		init: function() {
			$.getJSON('./ajax/getRankings.php', function(data) {
				var males = [], females = [], tmp, row, i, j, mtable, ftable,
					numQtrs = data.qtrs.length,
					cutoffNum = 39,
					colDefs = [
						{ sTitle: '#', sClass: 'num', sWidth: '5px' },
						{ sTitle: 'Name', sClass: 'name', sWidth: '140px' }
					];

				for(i=0; i<numQtrs; i++){
					colDefs.push({
						sTitle: rankings.qtrToQuarter(data.qtrs[i]),
						sWidth: '20px'
					});
				}

				colDefs.push(
					{ sTitle: 'Total',
						sWidth: '20px' },
					{ sTitle: 'Mult',
						sWidth: '20px' },
					{ sTitle: 'Total w/ Mult',
						sWidth: '30px' },
					{ bVisible: false });

				for(i=0; i<data.rankings.length; i++){
					row = data.rankings[i];
					tmp = ['', row.full_name];

					for(j=0; j<numQtrs; j++){
						tmp.push(parseInt(row[data.qtrs[j]], 10));
					}

					tmp.push(row.total, row.mult, row.total_w_mult, row.abstains);

					if(row.gender == 'm'){
						males.push(tmp);
					}else{
						females.push(tmp);
					}
				}

				//apply styles for cutoffs
				males.sort(function(a, b) { return b[numQtrs + 4] - a[numQtrs + 4]; });
				females.sort(function(a, b) { return b[numQtrs + 4] - a[numQtrs + 4]; });

				mtable = $('#males_table').dataTable({
					aaData: males,
					aoColumns: colDefs,
					aaSorting: [[numQtrs + 4, 'desc']],
					bPaginate: false,
					bAutoWidth: false,
					sDom: 't'
				});

				ftable = $('#females_table').dataTable({
					aaData: females,
					aoColumns: colDefs,
					aaSorting: [[numQtrs + 4, 'desc']],
					bPaginate: false,
					bAutoWidth: false,
					sDom: 't'
				});

				var mj = 0, fj = 0, underCutoff = [];
				row = mtable.find('tr');

				for(i=0; i<males.length; i++){
					if(males[i][numQtrs+5]){
						row.eq(i+1).addClass('red');
					}else if(mj<cutoffNum){
						row.eq(i+1).addClass('green').find('.num').text(mj+1);
						mj++;
					}else if(mj==cutoffNum){
						underCutoff.push(['m', row.eq(i+1), males[i][numQtrs+4]]);
					}
				}

				row = ftable.find('tr');

				for(i=0; i<females.length; i++){
					if(females[i][numQtrs+5]){
						row.eq(i+1).addClass('red');
					}else if(fj<cutoffNum){
						row.eq(i+1).addClass('green').find('.num').text(fj+1);
						fj++;
					}else if(fj==cutoffNum){
						underCutoff.push(['f', row.eq(i+1), females[i][numQtrs+4]]);
					}
				}

				underCutoff.sort(function(a, b) { return b[2] - a[2]; });

				for(i=0; i<4; i++){
					if(underCutoff[i][0]=='m'){
						mj++;
						underCutoff[i][1].addClass('green').find('.num').text(mj);
					}else{
						fj++;
						underCutoff[i][1].addClass('green').find('.num').text(fj);
					}
				}
			});

			// $('#update').on('click', function() {
			// 	$('#update').button('loading');
			// 	$.getJSON('ajax/updateTotals.php', function() {
			// 		window.location.reload();
			// 	});
			// });
		},

		qtrToQuarter: function(qtr) {
			var yr = Math.floor(qtr / 100),
				q = qtr - yr * 100;

			switch (q) {
				case 1:
					return 'Winter 20' + yr;
				case 2:
					return 'Spring 20' + yr;
				case 3:
					return 'Fall 20' + yr;
			}
		}
	},

	updateSlivkans = {
		init: function() {
			$.getJSON('ajax/getSlivkans.php', function(data) {
				slivkans = data.slivkans;
				nicknames = data.nicknames;

				//tack on nicknames to slivkans
				for(var i=0; i<nicknames.length; i++){
					var ind = slivkans.indexOfKey('nu_email', nicknames[i].nu_email);
					if(ind !== -1){
						slivkans[ind].tokens.push(nicknames[i].nickname);
					}
				}

				submission.appendSlivkanInputs(9);

				$('#slivkan-entry-tab')	.on('focus', '.slivkan-entry', updateSlivkans.slivkanTypeahead)
										.on('typeahead:closed', '.slivkan-entry.tt-query',
											{callback: updateSlivkans.validateSlivkanName},
											common.destroyTypeahead);
			});

			$('#committee').on('change', function(event){
				$('#suite').val('');
				$('.committee-points').val(0).show();

				if(event.target.value.length > 0){
					$.getJSON('ajax/getCommitteeOrSuite.php', {committee: event.target.value}, updateSlivkans.addSlivkans);
				}
			});

			$('#suite').on('change', function(event){
				$('#committee').val('');
				$('.committee-points').hide();

				if(event.target.value.length > 0){
					$.getJSON('ajax/getCommitteeOrSuite.php', {suite: event.target.value}, updateSlivkans.addSlivkans);
				}
			});

			$('#submit').on('click', function(){
				var name, pts,
					entries = $('#slivkan-entry-tab').find('.slivkan-entry'),
					committeePoints = $('.committee-points'),
					committee = $('#committee').val(),
					suite = $('#suite').val(),
					nuEmailArray = [],
					committeePointsArray = [];

				for(var i=0; i<entries.length; i++){
					name = entries.eq(i).val();
					if(name.length > 0){
						nuEmailArray.push(slivkans[slivkans.indexOfKey('full_name', name)].nu_email);
					}

					if(committee.length > 0){
						pts = committeePoints.eq(i).val();
						committeePointsArray.push(pts);
					}
				}

				if(committee.length > 0){
					$.getJSON('ajax/submitCommitteeOrSuite.php', {committee: committee, slivkans: nuEmailArray, points: committeePointsArray}, function(data){
						window.alert(data);
					});
				}else if(suite.length > 0){
					$.getJSON('ajax/submitCommitteeOrSuite.php', {suite: suite, slivkans: nuEmailArray}, function(data){
						window.alert(data);
					});
				}
			});
		},
		slivkanTypeahead: function() {
			var target = $(this);

			if(target.closest('.slivkan-entry-control').addClass('has-warning').is(':last-child')){
				var num_inputs = $('#slivkan-entry-tab').find('.slivkan-entry').length;
				if(num_inputs < 20){
					submission.appendSlivkanInputs(1);
				}
			}
			if(!target.hasClass('tt-query')){
				target.typeahead(common.typeaheadOpts('slivkans', slivkans)).focus();
			}
		},
		validateSlivkanName: function(entry) {
			var valid = true,
			slivkan_entry = entry.find('.slivkan-entry'),
			name = slivkan_entry.val(),
			nickname_ind = nicknames.indexOfKey('nickname', name);

			if(nickname_ind != -1){
				name = slivkans[slivkans.indexOfKey('nu_email', nicknames[nickname_ind].nu_email)].full_name;
				slivkan_entry.val(name);
			}

			var nameArray = [];

			//clear duplicates
			$('#slivkan-entry-tab').find('.slivkan-entry').each(function() {
				var self = $(this);
				if(self.val().length > 0){
					if(nameArray.indexOf(self.val()) == -1){
						nameArray.push(self.val());
					}else{
						self.val('');
						$('#duplicate-alert').show();
						submission.validateSlivkanName(self.parent(), true);
					}
				}
			});

			//no names = invalid
			if(nameArray.length === 0){ valid = false; }

			//update name in case it changed
			name = slivkan_entry.val();

			entry.removeClass('has-warning');

			if(name.length > 0){
				if(slivkans.indexOfKey('full_name', name) == -1){ valid=false; }
				common.updateValidity(entry, valid);
			}else{
				entry.removeClass('has-success has-error');
			}

			return valid;
		},
		addSlivkans: function(data) {
			var entries = $('#slivkan-entry-tab').find('.slivkan-entry-control'),
				len = data.length;

			entries.find('.slivkan-entry').val('');

			if(entries.length <= len){
				submission.appendSlivkanInputs(len - entries.length + 1);
				entries = $('#slivkan-entry-tab').find('.slivkan-entry-control');
			}

			for(var i=0; i<len; i++){
				var entry = entries.eq(i),
					name = slivkans[slivkans.indexOfKey('nu_email', data[i].nu_email)].full_name;
				entry.find('.slivkan-entry').val(name);
				if(data[i].committee){
					entry.find('.committee-points').val(data[i].committee);
				}
				updateSlivkans.validateSlivkanName(entry);
			}

			for(i; i<entries.length; i++){
				updateSlivkans.validateSlivkanName(entries.eq(i));
			}
		}
	},

	committeeHeadquarters = {
		init: function() {
			var openPopover;

			$.getJSON('ajax/getSlivkans.php', function(data) {
				slivkans = data.slivkans;

				for(var i=0; i<slivkans.length; i++){
					$('<option />').attr('value', slivkans[i].nu_email).text(slivkans[i].full_name).appendTo('#helper-slivkan');
				}
			});

			$('.committee-points-table td.pts').popover({
				placement: 'bottom auto',
				html: true,
				container: 'body',
				trigger: 'manual',
				content: function(){
					return ['',
						'<div class="form-group has-success" style="width:100px;">',
							'<label class="control-label" for="pts-input">Edit Points:</label>',
							'<div class="input-group">',
								'<input type="number" id="pts-input" min="0.0" max="3.0" step="0.1" ',
									'data-original-value="', this.innerText, '" ',
									'value="', this.innerText, '" class="form-control pts-input" >',
								'<span class="input-group-btn">',
									'<button class="btn btn-primary submit-committee-point">',
										'<span class="glyphicon glyphicon-ok"></span>',
									'</button>',
								'</span>',
							'</div>',
						'</div>'
					].join('');
				}
			}).on('click', function(e){
				var target = $(e.target);

				if(target.is(openPopover)){
					target.popover('hide');
					openPopover = null;
					return;
				}

				if(openPopover){
					openPopover.popover('hide');
				}

				openPopover = target.popover('show');
			});

			$('body').on('click', function(e){
				var target = $(e.target);

				if(openPopover && target.closest('.pts').length === 0 && target.closest('.popover').length === 0){
					openPopover.popover('hide');
					openPopover = null;
				}
			}).on('click', '.submit-committee-point', function(e){
				var input = $(e.target).closest('.input-group').find('input'),
					points = input.val();

				if(points == input.data('original-value')){
					return;
				}

				$.getJSON('ajax/submitCommitteePoint.php', {
					nu_email: openPopover.data('slivkan'),
					event_name: openPopover.data('event'),
					points: points
				}, function(status){
					if(status == '1'){
						openPopover.text(parseFloat(points).toFixed(1))
							.popover('hide');
						committeeHeadquarters.updateTotal(openPopover.closest('tr'));
						openPopover = null;
					}
				});
			}).on('input', '.pts-input', committeeHeadquarters.validatePoints);

			// $('body').on('shown.bs.popover', function(e){
			// 	console.log(e);
			// });
			//$('#helper-slivkan')	.on('change', );
		},
		updateTotal: function(row){
			var total = row.find('td.pts').map(function(i, el){
				return parseFloat(el.innerText);
			}).toArray().reduce(function(a, b) {
				return a + b;
			});

			row.find('.totals').text(total.toFixed(1));
		},
		validatePoints: function(e){
			var target = $(e.target),
				control = target.closest('.form-group'),
				button = control.find('button');

			if(/^([0-2](\.\d)?|3(\.0)?)$/.test(target.val())){
				button.removeAttr('disabled');
				control.removeClass('has-error').addClass('has-success');
			}else{
				button.attr('disabled', 'disabled');
				control.addClass('has-error').removeClass('has-success');
			}
		}
	};

	return {
		breakdown: breakdown,
		table: table,
		correction: correction,
		submission: submission,
		faq: faq,
		inboundPoints: inboundPoints,
		rankings: rankings,
		updateSlivkans: updateSlivkans,
		committeeHeadquarters: committeeHeadquarters
	};
});