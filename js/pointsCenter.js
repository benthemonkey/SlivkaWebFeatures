define(['jquery', 'moment', 'hogan'], function($, moment, Hogan){
	'use strict';
	var slivkans, nicknames, fellows, events, qtrs, //quarter_start, quarter_end,
		type = 'Other',
		root = '/points',
		VALID_EVENT_NAME = false,
		TAB_PRESSED = false, // for convenient, fast tab-completion on submission page
		slivkanNameExists = function(name){
			if(name.length === 0){
				return null;
			}else{
				return slivkans.indexOfKey(name.indexOf(' ') != -1 ? 'full_name' : 'nu_email', name) != -1;
			}
		},
		updateValidity = function(element, valid){
			if(valid === null){
				element.removeClass('has-success has-warning has-error');
			}else if(valid){
				element.addClass('has-success').removeClass('has-error has-warning');
			}else{
				element.removeClass('has-success has-warning').addClass('has-error');
			}

			return valid;
		},
		typeaheadOpts = function(name, slivkans){
			return {
				name: name,
				valueKey: 'full_name',
				local: slivkans,
				template: ['<div class="slivkan-suggestion{{#dupe}} slivkan-dupe{{/dupe}}">{{full_name}}',
							'{{#photo}}<img src="/points/img/slivkans/{{photo}}" />{{/photo}}</div>'].join(''),
				engine: Hogan
			};
		},
		destroyTypeahead = function(event){
			var target = $(this);
			if(target.hasClass('tt-query')){
				//needs a delay because typeahead.js seems to not like destroying on focusout
				setTimeout(function(target){
					event.data.callback(target.typeahead('destroy').closest('.form-group'));

					if(TAB_PRESSED){
						target.closest('.form-group').next().find('input').focus();
					}
				}, 1, target);
			}
		};

	//add indexOfKey (useful: http://jsperf.com/js-for-loop-vs-array-indexof)
	Array.prototype.indexOfKey = function(key, value){
		for(var i = 0; i < this.length; i++){
			if(this[i][key] === value){
				return i;
			}
		}

		return -1;
	};

	var breakdown = {
		init: function(){
			$.getJSON(root + '/ajax/getSlivkans.php', { qtr: localStorage.spc_brk_qtr }, function(data){
				slivkans = data.slivkans;
				qtrs = data.qtrs;
				// quarter_start = data.quarter_info.start_date;
				// quarter_end = data.quarter_info.end_date;

				for(var i = 0; i < slivkans.length; i++){
					$('<option />').attr('value', slivkans[i].nu_email).text(slivkans[i].full_name).appendTo('#slivkan');
				}

				for(i = 0; i < qtrs.length; i++){
					$('<option />').attr('value', qtrs[i].qtr).text(qtrs[i].quarter).appendTo('#qtr');
				}

				if(localStorage.spc_brk_slivkan){
					$('#slivkan').val(localStorage.spc_brk_slivkan);
					breakdown.getSlivkanPoints();
				}

				if(localStorage.spc_brk_qtr){
					$('#qtr').val(localStorage.spc_brk_qtr);
				}

				$('#slivkan').on('change', breakdown.getSlivkanPoints);
				$('#qtr').on('change', function(){
					localStorage.spc_brk_qtr = $(this).val();
					window.location.reload();
				});
			});
		},
		getSlivkanPoints: function(){
			var nuEmail = $('#slivkan').val(),
				qtr = localStorage.spc_brk_qtr || qtrs[0].qtr,
				attendedEventsEl = $('#attendedEvents'),
				unattendedEventsEl = $('#unattendedEvents');

			if(nuEmail.length > 0){
				localStorage.spc_brk_slivkan = nuEmail;

				$('.breakdown').fadeOut(function(){
					attendedEventsEl.empty();
					unattendedEventsEl.empty();
					$('#otherPointsTableBody').empty();

					$.getJSON(root + '/ajax/getPointsBreakdown.php', { nu_email: nuEmail, qtr: qtr }, function(data){
						var i, eventData = [],
							imData = [],
							eventTotal = 0,
							imTotal = 0,
							imExtra = 0,
							hasOther = false;

						if(data.events.attended.length > 0){
							for(i = data.events.attended.length - 1; i >= 0; i--){
								attendedEventsEl
									.append($('<tr/>')
										.append($('<td/>')
											.addClass(data.events.attended[i].committee)
											.text(data.events.attended[i].event_name)));
							}
						}else{
							attendedEventsEl
								.append($('<tr/>')
									.append($('<td/>').text('None :(')));
						}

						if(data.events.unattended.length > 0){
							for(i = data.events.unattended.length - 1; i >= 0; i--){
								unattendedEventsEl
									.append($('<tr/>')
										.append($('<td/>')
											.addClass(data.events.unattended[i].committee)
											.text(data.events.unattended[i].event_name)));
							}
						}else{
							unattendedEventsEl
								.append($('<tr/>')
									.append($('<td/>').text('None :)')));
						}

						for(i = 0; i < data.other_breakdown.length; i++){
							if(data.other_breakdown[i][0]){
								$('#otherPointsTableBody')
									.append($('<tr/>')
										.append($('<td/>').text(data.other_breakdown[i][0]))
										.append($('<td/>').text(data.other_breakdown[i][1])));

								hasOther = true;
							}
						}

						if(hasOther){
							$('#otherPointsTable').show();
						}else{
							$('#otherPointsTable').hide();
						}

						for(i = 0; i < data.events.counts.length; i++){
							eventData.push([data.events.counts[i].committee, parseInt(data.events.counts[i].count, 10)]);

							eventTotal += parseInt(data.events.counts[i].count, 10);
						}

						$('.eventPoints').text(eventTotal);
						breakdown.drawChart(eventData, 'Event Points (' + eventTotal + ' Total)', 'eventsChart');

						if(data.ims.length > 0){
							$('#imsChart').show();
							for(i = 0; i < data.ims.length; i++){
								data.ims[i].count = parseInt(data.ims[i].count, 10);

								imData.push([data.ims[i].sport, data.ims[i].count]);

								if(data.ims[i].count >= 3){
									imTotal += data.ims[i].count;
								}else{
									imExtra += data.ims[i].count;
								}
							}

							if(imTotal > 15){
								imExtra += imTotal - 15;
								imTotal = 15;
							}

							breakdown.drawChart(imData,
								['IMs (', imTotal, ' Points, ', imExtra, (imExtra == 1 ? ' Doesn\'t' : ' Don\'t'), ' Count)'].join(''),
								'imsChart');
						}else{
							$('#imsChart').hide();
						}

						$('.imPoints').text(imTotal);
						$('.helperPoints').text(data.helper);
						$('.committeePoints').text(data.committee);
						$('.otherPoints').text(data.other);

						$('.totalPoints').text(
							[eventTotal, imTotal, data.helper, data.committee, data.other].map(function(n){
								return parseInt(n, 10);
							}).reduce(function(a, b){
								return a + b;
							}));

						$('.breakdown').fadeIn();
					});
				});
			}
		},
		drawChart: function(tableData, titleIn, id){
			setTimeout(function(){
				$('#' + id).highcharts({
					credits: {
						enabled: false
					},
					plotOptions: id != 'eventsChart' ? {} : {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							dataLabels: {
								enabled: true
							},
							point: {
								events: {
									select: function(){
										$('.' + this.name).css({
											'background-color': this.color,
											'color': 'white'
										});
									},
									unselect: function(){
										$('.' + this.name).removeAttr('style');
									}
								}
							}
						}
					},
					title: {
						text: titleIn,
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
		init: function(){
			var i, eventName, name, date,
				nameColWidth = $('.nameHeader').width(),//170,
				eventColWidth = $('.eventHeader').width(),//20,
				totalsColWidth = $('.totalsHeader').width(),//24,
				tableWrapper = $('.table-wrapper'),
				headers = $('th'),
				pointsTable = JSON.parse(window.points_table),
				byYear = pointsTable.by_year,
				bySuite = pointsTable.by_suite,
				lastScroll = 0,
				delay = (function(){
					var timer = 0;
					return function(callback, ms){
						clearTimeout (timer);
						timer = setTimeout(callback, ms);
					};
				})(),
				adjustWidth = function(){
					var width = (tableWrapper.width() - nameColWidth - 6 * totalsColWidth - 2) + 'px';
					$('.endHeader').css({
						'width': width,
						'min-width': width,
						'max-width': width
					});
				};

			events = pointsTable.events;

			tableWrapper.scroll(function(){
				delay(function(){
					if($('body').width() > 768){
						var scroll = tableWrapper.scrollLeft(),
							round = lastScroll < scroll ? Math.ceil : Math.floor;

						if(lastScroll != scroll){
							lastScroll = round(scroll / eventColWidth) * eventColWidth;
							tableWrapper.scrollLeft(lastScroll);
						}
					}
				}, 100);
			});

			$(window).resize(function(){
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

				$('#name-filter').on('keyup', function(){
					delay(function(){
						table.oTable.fnFilter($('#name-filter').val());
					}, 500);
				});

				$('#gender-filter').on('change', function(){
					var option = $('#gender-filter').val();
					table.oTable.fnFilter(option, 1);
				});

				$('#im-filter').on('change', table.columnFilter);

				$('.multiselect').multiselect({
					buttonClass: 'btn btn-default',
					onChange: table.columnFilter
				});

				$('#noFilter').on('click', function(){
					localStorage.spc_tab_noFilter = 1;
				});
			}else{
				$('.filter').not('.dropdown').hide();
				$('#noFilter').hide();
				$('#enableFilter').on('click', function(){
					localStorage.spc_tab_noFilter = 0;
				}).show();
				$('td').css('font-size', '12px');
			}

			for(i = 0; i < events.length; i++){
				eventName = events[i].event_name;
				name = eventName.substr(0, eventName.length - 11);
				date = eventName.substr(eventName.length - 5);

				headers.eq(i + 2).popover({
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
			byYear.sort(function(a, b){
				return b[1] - a[1];
			});

			for(i = 0; i < byYear.length; i++){
				$('<tr><td>' + byYear[i][0] + '</td><td>' + byYear[i][1] + '</td></tr>').appendTo('#years');
			}

			bySuite.sort(function(a, b){
				return b[1] - a[1];
			});

			for(i = 0; i < bySuite.length; i++){
				$('<tr><td>' + bySuite[i][0] + '</td><td>' + bySuite[i][1] + '</td></tr>').appendTo('#suites');
			}
		},
		oTable: null,
		columnFilter: function(){
			var i,
				committees = $('#committee-filter').find('option:selected').map(function(){ return this.innerHTML; }).get(),
				ims = $('#im-filter').val(),
				n = 0;

			if(ims === '2'){
				committees = [];
				$('#committee-filter').parent().find('.dropdown-toggle').attr('disabled', 'disabled');
			}else{
				$('#committee-filter').parent().find('.dropdown-toggle').removeAttr('disabled');
			}

			for(i = 0; i < events.length; i++){
				if(committees.indexOf(events[i].committee) !== -1 &&
					(ims !== '1' || events[i].type !== 'im') || (ims === '2' && events[i].type === 'im')){
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
		init: function(){
			$.getJSON(root + '/ajax/getSlivkans.php', function(data){
				var i, ind;

				slivkans = data.slivkans;
				nicknames = data.nicknames;

				//tack on nicknames to slivkans
				for(i = 0; i < nicknames.length; i++){
					ind = slivkans.indexOfKey('nu_email', nicknames[i].nu_email);

					if(ind !== -1){
						slivkans[ind].tokens.push(nicknames[i].nickname);
					}
				}

				$('#filled-by').typeahead(typeaheadOpts('slivkans', slivkans));
			});

			$.getJSON(root + '/ajax/getRecentEvents.php', function(events){
				for(var i = events.length - 1; i >= 0; i--){
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
		validatePointsCorrectionForm: function(){
			var valid = true,
			errors = [];

			if(!correction.validateFilledBy()){
				valid = false;
				errors.push('Your Name');
			}
			if($('#event-name').val() == 'Select One'){
				valid = false;
				errors.push('Event Name');
			}
			if($('#comments').val() === ''){
				valid = false;
				errors.push('Comments');
			}

			if(valid){
				$('#submit-error').fadeOut();
				correction.submitPointsCorrection();
			}else{
				$('#submit-error').text('Validation errors in: ' + errors.join(', ')).fadeIn();
			}
		},
		validateFilledBy: function(){
			return updateValidity($('.filled-by-control'), slivkanNameExists($('#filled-by').val()));
		},
		resetForm: function(){
			$('#filled-by').val('');
			$('.filled-by-control').removeClass('has-success has-error');
			$('#event-name').val('Select One');
			$('#comments').val('');
			$('#submit-error').fadeOut();
		},
		submitPointsCorrection: function(){
			var data = {
				event_name: $('#event-name').val(),
				name: $('#filled-by').val(),
				sender_email: slivkans[slivkans.indexOfKey('full_name', $('#filled-by').val())].nu_email,
				comments: $('#comments').val()
			};
			$('#response').fadeOut();

			$.post(root + '/ajax/sendPointsCorrection.php', data, function(response){
				$('#response').text('Response: ' + response.message);
				$('#form-actions').html('<a class="btn btn-primary" href="../table/">View Points</a>' +
					'<a class="btn btn-default" href="../correction/">Submit Another</a>');
				$('#response').fadeIn();
			}, 'json');
		}
	},

	submission = {
		init: function(){
			var i, date;

			$(window).on('keydown',	function(event){
				if(event.keyCode == 9 && !event.shiftKey){
					TAB_PRESSED = true;
				}else if(event.keyCode == 13){ //prevent [Enter] from causing form submit
					event.preventDefault();
					return false;
				}
			}).on('keyup', function(event){
				if(event.keyCode == 9){
					TAB_PRESSED = false;
				}
			});

			$.getJSON(root + '/ajax/getSlivkans.php', function(data){
				var i, ind, attendees, fellowEntry, fellowAttendees,
					imTeams = data.im_teams;
				slivkans = data.slivkans;
				nicknames = data.nicknames;
				fellows = data.fellows;

				//tack on nicknames to slivkans
				for(i = 0; i < nicknames.length; i++){
					ind = slivkans.indexOfKey('nu_email', nicknames[i].nu_email);

					if(ind !== -1){
						slivkans[ind].tokens.push(nicknames[i].nickname);
					}
				}

				//initialization
				submission.appendSlivkanInputs(14);
				submission.appendFellowInputs(9);

				//im teams
				for(i = 0; i < imTeams.length; i++){
					$('<option />').text(imTeams[i]).appendTo('#im-team');
				}

				//loading saved values
				if(localStorage.spc_sub_committee){
					$('#committee').val(localStorage.spc_sub_committee);
					//submission.validateCommittee();
				}
				if(localStorage.spc_sub_attendees){
					attendees = localStorage.spc_sub_attendees.split(', ');
					if(attendees.length > 14){ submission.appendSlivkanInputs(attendees.length - 14); }
					submission.addSlivkans(attendees);
				}
				if(localStorage.spc_sub_filledby){
					$('#filled-by').val(localStorage.spc_sub_filledby);
					submission.validateFilledBy();
				}
				if(localStorage.spc_sub_type && localStorage.spc_sub_type != 'Other'){
					$('input[value="' + localStorage.spc_sub_type + '"]:radio').parent().click();
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
					fellowAttendees = localStorage.spc_sub_fellows.split(', ');

					if(fellowAttendees.length > 9){
						submission.appendFellowInputs(fellowAttendees.length - 9);
					}

					for(i = 0; i < fellowAttendees.length; i++){
						fellowEntry = $('.fellow-entry').eq(i);
						fellowEntry.val(fellowAttendees[i]);
						submission.validateFellowName(fellowEntry.closest('.form-group'));
					}
				}

				//autocomplete and events for slivkan/fellow inputs
				$('#filled-by').typeahead(typeaheadOpts('slivkans', slivkans));

				$('#slivkan-entry-tab')	.on('focus', '.slivkan-entry', submission.handlers.slivkanTypeahead)
										.on('typeahead:closed', '.slivkan-entry.tt-query',
											{ callback: submission.validateSlivkanName },
											destroyTypeahead)
										.on('typeahead:selected', '.slivkan-entry.tt-query', function(){
												$(this).closest('.form-group').next().find('input').focus();
											});

				$('#fellow-entry-tab')	.on('focus', '.fellow-entry', submission.handlers.fellowTypeahead)
										.on('typeahead:closed', '.fellow-entry.tt-query',
											{ callback: submission.validateFellowName },
											destroyTypeahead)
										.on('typeahead:selected', '.fellow-entry.tt-query', function(){
												$(this).closest('.form-group').next().find('input').focus();
											});
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
			for(i = 0; i < 5; i++){
				date = moment().subtract('days', i).format('YYYY-MM-DD');
				$('<option />').text(moment(date).format('ddd, M/D')).attr('value', date).appendTo('#date');
			}

			//event handlers for inputs
			$('#filled-by')			.on('focus',	submission.handlers.addClassWarning)
									.on('focusout',	submission.validateFilledBy);
			$('#type')				.on('click',	submission.toggleType);
			$('#event')				.on('focus',	submission.handlers.addClassWarning)
									.on('focusout',	submission.validateEventName);
			$('#date')				.on('change',	function(){
														localStorage.spc_sub_date = $(this).val();
														submission.validateEventName();
													});
			$('#im-team')			.on('change',	submission.validateIMTeam);
			$('#committee')			.on('change',	submission.validateCommittee);
			$('#description')		.on('focusout',	submission.validateDescription);
			$('#comments')			.on('focusout',	function(){ localStorage.spc_sub_comments = $(this).val(); });
			$('#close-sort-alert')	.on('click',	function(){ $('#sort-alert').slideUp(); });
			$('#close-dupe-alert')	.on('click',	function(){ $('#duplicate-alert').slideUp(); });
			$('#sort-entries')		.on('click',	submission.sortEntries);
			$('#submit')			.on('click',	submission.validatePointsForm);
			$('#reset')				.on('click',	submission.resetForm);
			$('#bulk-names')		.on('keyup',	submission.processBulkNames);
			$('#add-bulk-names')	.on('click',	submission.addBulkNames);

			$('#tabs').find('a:first').tab('show');
		},
		handlers: {
			addClassWarning: function(){
				$(this).closest('.form-group').addClass('has-warning');
			},
			slivkanTypeahead: function(){
				var ind, committee, numInputs,
					target = $(this),
					slivkansTmp = JSON.parse(JSON.stringify(slivkans));

				if(localStorage.spc_sub_attendees){
					localStorage.spc_sub_attendees.split(', ').forEach(function(el){
						ind = slivkansTmp.indexOfKey('full_name', el);
						if(ind !== -1){
							slivkansTmp[ind].dupe = true;
						}
					});
				}

				if(type == 'Committee Only'){
					committee = $('#committee').val();

					slivkansTmp = slivkansTmp.filter(function(item){
						return item.committee == committee;
					});
				}

				if(target.closest('.slivkan-entry-control').addClass('has-warning').is(':last-child')){
					numInputs = $('#slivkan-entry-tab').find('.slivkan-entry').length;
					if(numInputs < 120){
						submission.appendSlivkanInputs(1);
					}
				}

				if(!target.hasClass('tt-query')){
					target.typeahead(typeaheadOpts('slivkans' + Math.random(), slivkansTmp)).focus();
				}
			},
			fellowTypeahead: function(){
				var numInputs,
					target = $(this);
				if(target.closest('.fellow-entry-control').addClass('has-warning').is(':last-child')){
					numInputs = $('#fellow-entry-tab').find('.fellow-entry').length;
					if(numInputs < 20){ submission.appendFellowInputs(1); }
				}
				if(!target.hasClass('tt-query')){
					target.typeahead(typeaheadOpts('fellows', fellows)).focus();
				}
			}
		},
		appendSlivkanInputs: function(n){
			//2-4ms per insertion. Slow but acceptable.
			var i,
				cloned = $('#slivkan-entry-tab').find('.slivkan-entry-control').last(),
				start = parseInt(cloned.find('.input-group-addon').text(), 10);

			for(i = 0; i < n; i++){
				cloned.clone().appendTo('#slivkan-entry-tab')
				.removeClass('has-warning')
				.find('.input-group-addon').text(start + i + 1);
			}
		},
		appendFellowInputs: function(n){
			var i,
				cloned = $('#fellow-entry-tab').find('.fellow-entry-control').last(),
				start = parseInt(cloned.find('.input-group-addon').text(), 10);

			for(i = 0; i < n; i++){
				cloned.clone().appendTo('#fellow-entry-tab')
				.removeClass('has-warning')
				.find('.input-group-addon').text(start + i + 1);
			}
		},
		toggleType: function(event){
			type = $(event.target).closest('label').find('input').val();

			//store value
			localStorage.spc_sub_type = type;

			//clear description if **previous** type was IM
			var previousType = $('.type-btn.active').find('input').val();
			if(previousType == 'IM'){
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
				$('#event').val('');
				$('#committee').removeAttr('disabled');
				break;
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
		validatePointsForm: function(){
			var valid = true, validSlivkans = true, validFellows = true,
			errors = [];

			if(!submission.validateFilledBy()){
				valid = false;
				errors.push('Filled By');
			}

			if(!VALID_EVENT_NAME){
				valid = false;
				updateValidity($('.event-control'), valid);
				errors.push('Name');
			}

			if(!submission.validateCommittee()){
				valid = false;
				errors.push('Committee');
			}

			if(!submission.validateDescription()){
				valid = false;
				errors.push('Description');
			}

			$('.slivkan-entry-control').each(function(index){
				if(!submission.validateSlivkanName($(this), (index !== 0))){
					validSlivkans = false;
				}
			});

			if(!validSlivkans){
				valid = false;
				errors.push('Attendees');
			}

			$('.fellow-entry-control').each(function(){
				if(!submission.validateFellowName($(this))){
					validFellows = false;
				}
			});

			if(!validFellows){
				valid = false;
				errors.push('Fellows');
			}

			if(valid){
				$('#submit-error').fadeOut();
				submission.submitPointsForm();
			}else{
				$('#submit-error').text('Validation errors in: ' + errors.join(', ')).fadeIn();
			}

			return valid;
		},
		validateEventName: function(){
			var valid = false,
				eventEl = $('#event'),
				eventName = eventEl.val(),
				eventNameTrimmed = eventName.replace(/^\s+|\s+$/g, '');

			//errors abound in the PHP with trailing whitespace
			if(eventName.length > eventNameTrimmed.length){
				$('#event').val(eventNameTrimmed);
				eventName = eventNameTrimmed;
			}

			//store value
			localStorage.spc_sub_name = eventName;

			VALID_EVENT_NAME = false;

			if(eventName.length === 0){
				updateValidity($('.event-control'), null);
			}else if((eventName.length <= 32 && eventName.length >= 8) || (type == 'P2P' && eventName == 'P2P')){
				eventName += ' ' + $('#date').val();

				$.getJSON(root + '/ajax/getRecentEvents.php', function(events){
					if(events.length > 0 && events.indexOfKey('event_name', eventName) != -1){
						if(type == 'IM'){
							var last = parseInt(eventEl.val().slice(-1), 10);
							eventEl.val(eventEl.val().slice(0, -1) + (last + 1).toString());
							submission.validateEventName();
						}else{
							VALID_EVENT_NAME = false;
							$('#event-name-error').fadeIn();
						}
					}else{
						VALID_EVENT_NAME = true;
						$('#event-name-error').fadeOut();
					}

					$('#event-name-length-error').fadeOut();
					updateValidity($('.event-control'), VALID_EVENT_NAME);
				});
			}else{
				$('#event-name-length-error-count').html('Currently ' + eventName.length + ' characters');
				$('#event-name-length-error').fadeIn();
				updateValidity($('.event-control'), VALID_EVENT_NAME);
			}

			return valid;
		},
		validateIMTeam: function(){
			var imTeam = $('#im-team').val();
			$.getJSON(root + '/ajax/getIMs.php', { team: imTeam }, function(events){
				$('#event').val(imTeam + ' ' + (events.length + 1));
				$('#description').val(imTeam.split(' ')[1]);
				submission.validateEventName();
			});
		},
		validateCommittee: function(){
			var committee = $('#committee').val(),
				valid = committee != 'Select One';

			updateValidity($('.committee-control'), valid);

			if(valid){
				localStorage.spc_sub_committee = committee;

				$('.slivkan-entry-control').each(function(index){
					submission.validateSlivkanName($(this), (index !== 0));
				});
			}

			return valid;
		},
		validateDescription: function(){
			var valid = true, description = $('#description').val();

			//store value
			localStorage.spc_sub_description = description;

			if(description.length < 10 && type == 'Other'){
				valid = false;
				$('#description-length-error').fadeIn();
			}else{
				$('#description-length-error').fadeOut();
			}

			updateValidity($('.description-control'), valid);

			return valid;
		},
		validateFilledBy: function(){
			var valid = true,
				name = $('#filled-by').val(),
				nicknameInd = nicknames.indexOfKey('nickname', name);

			$('.filled-by-control').removeClass('has-warning');

			if(name.length === 0){
				return false;
			}

			if(nicknameInd != -1){
				name = slivkans[slivkans.indexOfKey('nu_email', nicknames[nicknameInd].nu_email)].full_name;
				$('#filled-by').typeahead('setQuery', name);
			}

			//store value
			localStorage.spc_sub_filledby = name;

			valid = slivkans.indexOfKey('full_name', name) != -1;
			updateValidity($('.filled-by-control'), valid);

			return valid;
		},
		validateSlivkanName: function(entry, inBulk){
			var ind, committee,
				valid = true,
				slivkanEntry = entry.find('.slivkan-entry'),
				name = slivkanEntry.val(),
				nameArray = [],
				nicknameInd = nicknames.indexOfKey('nickname', name);

			if(nicknameInd != -1){
				name = slivkans[slivkans.indexOfKey('nu_email', nicknames[nicknameInd].nu_email)].full_name;
				slivkanEntry.val(name);
			}

			//only process individually
			if(!inBulk){
				//clear duplicates
				$('#slivkan-entry-tab').find('.slivkan-entry').each(function(){
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
				name = slivkanEntry.val();
			}

			if(name.length > 0){
				ind = slivkans.indexOfKey('full_name', name);

				valid &= ind != -1;

				if(type == 'Committee Only'){
					committee = $('#committee').val();

					valid &= committee == slivkans[ind].committee;
				}

				updateValidity(entry, valid);
			}else{
				updateValidity(entry, null);
			}

			return valid;
		},
		validateFellowName: function(entry){
			var valid = true,
				nameArray = [],
				fellowEntry = entry.find('.fellow-entry'),
				name = fellowEntry.val();

			//clear duplicates
			$('.fellow-entry').each(function(){
				if(nameArray.indexOf($(this).val()) != -1){
					$(this).val('');
					name = '';
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
				updateValidity(entry, valid);
			}else{
				entry.removeClass('has-success has-error');
			}

			return valid;
		},
		processBulkNames: function(){
			var names = $('#bulk-names').val();

			//remove '__ mins ago' and blank lines
			names = names.replace(/(\d+ .+ago[\r\n]?$)|(^[\r\n])/gm, '');

			$('#bulk-names').val(names);
		},
		addBulkNames: function(){
			var i, k, n, nameArray, slivkanEntries, len, name, wildcardInd, ind,
				slots = [],
				freeSlots = 0,
				names = $('#bulk-names').val();

			$('#slivkan-entry-tab').find('.slivkan-entry').each(function(){
				if($(this).val().length > 0){
					slots.push(1);
				}else{
					slots.push(0);
					freeSlots++;
				}
			});

			//if there's a hanging newline, remove it for adding but leave it in the textarea
			if(names[names.length - 1] == '\r' || names[names.length - 1] == '\n'){
				names = names.slice(0, names.length - 1);
			}

			nameArray = names.split(/[\r\n]/gm);

			if(nameArray.length >= freeSlots){
				n = nameArray.length - freeSlots + 1;
				submission.appendSlivkanInputs(n);
				for(k = 0; k < n; k++){
					slots.push(0);
				}
			}

			slivkanEntries = $('#slivkan-entry-tab').find('.slivkan-entry');
			len = nameArray.length;
			for(i = 0; i < len; i++){
				name = nameArray[i];

				//check if wildcard
				wildcardInd = slivkans.indexOfKey('wildcard', name);
				if(wildcardInd != -1){
					name = slivkans[wildcardInd].full_name;
				}

				ind = slots.indexOf(0);
				slots[ind] = 1;
				slivkanEntries.eq(ind).val(name);
				submission.validateSlivkanName(slivkanEntries.eq(ind).closest('.slivkan-entry-control'), (i < len - 1));
			}
		},
		sortEntries: function(){
			var nameArray = submission.saveSlivkans();

			//clear slivkans
			$('#slivkan-entry-tab').find('.slivkan-entry').val('');

			nameArray = nameArray.sort();

			submission.addSlivkans(nameArray);

			$('#sort-alert').slideDown();
		},
		saveSlivkans: function(){
			var nameArray = [];

			//forming name array, but appending values corresponding to the helper/committee buttons:
			//0 - unpressed, 1 - pressed
			$('#slivkan-entry-tab').find('.slivkan-entry-control').each(function(){
				var self = $(this),
					name = self.find('.slivkan-entry').val();
				if(name.length > 0){
					nameArray.push(name);
				}
			});

			localStorage.spc_sub_attendees = nameArray.join(', ');

			return nameArray;
		},
		addSlivkans: function(nameArray){
			var i, name, entry,
				entries = $('#slivkan-entry-tab').find('.slivkan-entry-control'),
				len = nameArray.length;

			for(i = 0; i < len; i++){
				name = nameArray[i];
				entry = entries.eq(i);
				entry.find('.slivkan-entry').val(name);
				submission.validateSlivkanName(entry, (i < len - 1));
			}

			for(i; i < entries.length; i++){
				submission.validateSlivkanName(entries.eq(i), true);
			}
		},
		resetForm: function(force){
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

				$('#slivkan-entry-tab').find('.slivkan-entry').each(function(){
					$(this).val('');
					submission.validateSlivkanName($(this).closest('.form-group'), true);
				});
				submission.validateSlivkanName($('.slivkan-entry-control').last());

				$('.fellow-entry').each(function(){
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
		submitPointsForm: function(){
			var name, nuEmail, val, ind, obj, realSubmit,
				data = {
					date: $('#date').val(),
					type: type.toLowerCase().replace(' ', '_'),
					committee: $('#committee').val(),
					event_name: $('#event').val(),
					description: $('#description').val(),
					filled_by: slivkans[slivkans.indexOfKey('full_name', $('#filled-by').val())].nu_email,
					comments: $('#comments').val(),
					attendees: [],
					committee_members: [],
					fellows: []
				};

			$('#slivkan-entry-tab').find('.slivkan-entry').each(function(){
				name = $(this).val();
				if(name.length > 0){
					ind = slivkans.indexOfKey('full_name', name);
					nuEmail = slivkans[ind].nu_email;

					data.attendees.push(nuEmail);
					if(slivkans[ind].committee == data.committee && data.committee != 'Exec' && type != 'p2p' && type != 'im'){
						data.committee_members.push(nuEmail);
					}
				}
			});

			$('.fellow-entry').each(function(){
				name = $(this).val();

				if(name.length > 0){
					data.fellows.push(name);
				}
			});

			//clear receipt:
			$('#receipt').empty();

			for(obj in data){
				if(data.hasOwnProperty(obj)){
					if(obj == 'attendees' || obj == 'committee_members' || obj == 'fellows'){
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

			realSubmit = $('#real-submit');

			realSubmit.off('click');
			realSubmit.on('click', function(){
				realSubmit.button('loading');

				$.post(root + '/ajax/submitPointsForm.php', data, function(dataIn){
					realSubmit.button('reset');
					$('#results-status').parent().removeClass('has-warning');
					if(dataIn.error){
						$('#results-status').text('Error in Step ' + dataIn.step).parent().removeClass('warning').addClass('error');
					}else{
						$('#unconfirmed').fadeOut({ complete: function(){ $('#confirmed').fadeIn(); } });

						//reset buttons once modal closes
						$('#submit-results').on('hidden.bs.modal', function(){
							$('#confirmed').hide();
							$('#unconfirmed').show();
						});

						$('#results-status').text('Success!').parent().removeClass('warning').addClass('success');

						submission.resetForm('force');
					}
				}, 'json');
			});
		}
	},

	inboundPoints = {
		init: function(){
			$.ajax({
				url: root + '/ajax/getCalendar.php',
				type: 'xml',
				async: true,
				success: function(xml){
					xml = $.parseXML(xml);

					var i, events = [];

					$(xml).find('entry').each(function(i, el){
						var dt,
							title = el.childNodes[4].textContent,
							date = el.childNodes[5].textContent;
						date = date.slice(6, date.indexOf('to') - 1);

						dt = parseInt(moment(date, ['ddd MMM DD, YYYY h:mma', 'ddd MMM DD, YYYY ha']).format('X'), 10);

						events.push([title, date, dt]);
					});

					events = events.sort(function(a, b){ return a[2] - b[2]; });

					for(i = 0; i < events.length; i++){
						$('<li />').html(events[i][0] + ' ' + events[i][1]).appendTo('#events');
							// + ' ' + moment(events[i][2]+'', 'X').format('ddd MMM DD, YYYY h:mma')
					}
				}
			});
		}
	},

	rankings = {
		init: function(){
			$.getJSON(root + '/ajax/getRankings.php', function(data){
				var males = [], females = [], underCutoff = [], tmp, row, i, j, mtable, ftable, mj, fj,
					numQtrs = data.qtrs.length,
					cutoffNum = 39,
					colDefs = [
						{ sTitle: '#', sClass: 'num', sWidth: '5px' },
						{ sTitle: 'Name', sClass: 'name', sWidth: '140px' }
					];

				for(i = 0; i < numQtrs; i++){
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

				for(i = 0; i < data.rankings.length; i++){
					row = data.rankings[i];
					tmp = ['', row.full_name];

					for(j = 0; j < numQtrs; j++){
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
				males.sort(function(a, b){ return b[numQtrs + 4] - a[numQtrs + 4]; });
				females.sort(function(a, b){ return b[numQtrs + 4] - a[numQtrs + 4]; });

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

				mj = 0;
				fj = 0;
				row = mtable.find('tr');

				for(i = 0; i < males.length; i++){
					if(males[i][numQtrs + 5]){
						row.eq(i + 1).addClass('red');
					}else if(mj < cutoffNum){
						row.eq(i + 1).addClass('green').find('.num').text(mj + 1);
						mj++;
					}else if(mj == cutoffNum){
						underCutoff.push(['m', row.eq(i + 1), males[i][numQtrs + 4]]);
					}
				}

				row = ftable.find('tr');

				for(i = 0; i < females.length; i++){
					if(females[i][numQtrs + 5]){
						row.eq(i + 1).addClass('red');
					}else if(fj < cutoffNum){
						row.eq(i + 1).addClass('green').find('.num').text(fj + 1);
						fj++;
					}else if(fj == cutoffNum){
						underCutoff.push(['f', row.eq(i + 1), females[i][numQtrs + 4]]);
					}
				}

				underCutoff.sort(function(a, b){ return b[2] - a[2]; });

				if(underCutoff.length > 0){
					for(i = 0; i < Math.min(4, underCutoff.length); i++){
						if(underCutoff[i][0] == 'm'){
							mj++;
							underCutoff[i][1].addClass('green').find('.num').text(mj);
						}else{
							fj++;
							underCutoff[i][1].addClass('green').find('.num').text(fj);
						}
					}
				}
			});
		},

		qtrToQuarter: function(qtr){
			var yr = Math.floor(qtr / 100),
				q = qtr - yr * 100;

			switch(q){
				case 1:
					return 'Winter 20' + yr;
				case 2:
					return 'Spring 20' + yr;
				case 3:
					return 'Fall 20' + yr;
			}
		}
	},

	committeeHeadquarters = {
		openPopover: null,
		init: function(){
			var i, date,
				self = this,
				ptsInputTemplate = Hogan.compile($('#pts-input-template').html());

			$('.committee-points-table td.pts').popover({
				placement: 'bottom auto',
				html: true,
				container: 'body',
				trigger: 'manual',
				content: function(){
					var contributions = [],
						el = $(this),
						isBonus = el.data('event') == 'bonus',
						check = function(value){
							return contributions.indexOf(value) != -1;
						};

					if(isBonus){
						return ptsInputTemplate.render({
							value: el.text(),
							contributions: false,
							comments: el.data('comments')
						});
					}

					contributions = el.data('contributions').split(',');

					return ptsInputTemplate.render({
						value: el.text(),
						contributions: true,
						contributions_list: [
							/*{ pts: 0.0, title: 'Attended',		disabled: 'disabled', selected: el.hasClass('green') || el.hasClass('blue') },
							{ pts: 0.5, title: 'Took Points',	disabled: 'disabled', selected: el.hasClass('blue') },*/
							{ pts: 1.5, title: 'Planned event',	value: 'plan', selected: check('plan') },
							{ pts: 2.0, title: 'Ran event',		value: 'ran', selected: check('ran') },
							{ pts: 1.0, title: 'Poster',		value: 'poster', selected: check('poster') },
							{ pts: 0.5, title: 'Set up',		value: 'setup', selected: check('setup') },
							{ pts: 0.5, title: 'Clean up',		value: 'clean', selected: check('clean') },
							{			title: 'Other',			value: 'other', selected: check('other') }
						],
						comments: el.data('comments')
					});
				}
			});

			$('body').on('shown.bs.popover', function(){
				$('.multiselect').multiselect({
					buttonClass: 'btn btn-default',
					buttonWidth: '153px',
					numberDisplayed: 2,
					onChange: function(e, checked){
						if(!e.data('pts')){
							return;
						}

						var ptsInput = $('.pts-input').last(),
							newVal = parseFloat(ptsInput.val()) + (checked ? 1 : -1) * parseFloat(e.data('pts'));

						if(newVal < -3){
							ptsInput.val(-3);
						}else if(newVal > 3){
							ptsInput.val(3);
						}else{
							ptsInput.val(newVal);
						}

						self.validatePoints(ptsInput);
					}
				});
			}).on('click', function(e){
				var modified,
					target = $(e.target),
					clickedOutsidePopover = target.closest('.popover').length === 0;

				if(!self.openPopover && target.hasClass('pts')){
					self.openPopover = target.popover('show');
				}else if(self.openPopover){
					modified = self.isModified();

					if(target.hasClass('pts-input-submit')){
						if(modified){
							self.submitCommitteePoint();
						}else{
							self.openPopover.popover('hide');
							self.openPopover = null;
						}
					}else if((!modified && clickedOutsidePopover) || target.hasClass('pts-input-cancel')){
						self.openPopover.popover('hide');

						if(target.hasClass('pts') && !self.openPopover.is(target)){
							self.openPopover = target.popover('show');
						}else{
							self.openPopover = null;
						}
					}
				}
			}).on('input', '.pts-input', function(){
				self.validatePoints($(this));
			});

			//dates for no-show form
			for(i = 0; i < 5; i++){
				date = moment().subtract('days', i).format('YYYY-MM-DD');
				$('<option />').text(moment(date).format('ddd, M/D')).attr('value', date).appendTo('#no-show-date');
			}

			$('#helper-form').on('submit', function(){
				committeeHeadquarters.submitModalForm('submitHelperPoint', 'helper', 'event');
			});
			$('#no-show-form').on('submit', function(){
				committeeHeadquarters.submitModalForm('submitNoShow', 'no-show', 'date');
			});
		},
		updateTotal: function(row){
			var total = row.find('td.pts').map(function(i, el){
				return parseFloat(el.innerText);
			}).toArray().reduce(function(a, b){
				return a + b;
			});

			row.find('.totals').text(total.toFixed(1));
		},
		validatePoints: function(target){
			var control = target.closest('.form-group'),
				button = control.find('button'),
				valid = true;

			if(/^-?(\.[1-9]|[0-2](\.\d)?|3(\.0)?)$/.test(target.val())){
				button.removeAttr('disabled');
			}else{
				valid = false;
				button.attr('disabled', 'disabled');
			}

			updateValidity(control, valid);
		},
		isModified: function(){
			var isBonus = this.openPopover.data('event') == 'bonus',
				ptsInput = $('#pts-input');

			return parseFloat(ptsInput.val()) != parseFloat(ptsInput.data('original-value')) ||
					$('#comments').val() != this.openPopover.data('comments') ||
					(!isBonus && ($('#contributions').val() || []).sort().join() != this.openPopover.data('contributions'));
		},
		submitCommitteePoint: function(){
			this.points = $('#pts-input').val();
			this.contributions = ($('#contributions').val() || []).sort().join();
			this.comments = $('#comments').val();

			$.ajax({
				type: 'POST',
				url: root + '/ajax/submitCommitteePoint.php',
				context: this,
				data: {
					nu_email: this.openPopover.parent().data('slivkan'),
					event_name: this.openPopover.data('event'),
					points: this.points,
					contributions: this.contributions,
					comments: this.comments
				},
				success: function(status){
					if(status == '1'){
						var pointValue = parseFloat(this.points).toFixed(1);
						this.openPopover.text(pointValue);
						this.openPopover.data({
							contributions: this.contributions,
							comments: this.comments
						});

						if(!this.openPopover.hasClass('green') && !this.openPopover.hasClass('blue')){
							if(pointValue > 0){
								this.openPopover.addClass('yellow');
							}else{
								this.openPopover.removeClass('yellow');
							}
						}

						committeeHeadquarters.updateTotal(this.openPopover.closest('tr'));
						this.openPopover.popover('hide');
						this.openPopover = null;
					}
				}
			});
		},
		submitModalForm: function(form, id, extra){
			var data = {
				full_name:	$('#' + id + '-slivkan option:selected').text(),
				nu_email:	$('#' + id + '-slivkan').val(),
				comments:	$('#' + id + '-comments').val()
			};

			data[extra] = $('#' + id + '-' + extra).val();

			$('#' + id + '-form').find('button[type=submit]').button('loading');
			$.post(root + '/ajax/' + form + '.php', data, function(status){
				$('#' + id + '-form').find('button[type=submit]').button('reset');
				if(status == '1'){
					window.alert('Success!');
				}else{
					window.alert('Something went wrong. Ask the VP.');
				}

				$('#' + id + '-modal').modal('hide');
				$('#' + id + '-slivkan').val('');
				$('#' + id + '-comments').val('');
				$('#' + id + '-' + extra).val('');
			});
		}
	},

	admin = {
		init: function(){
			var quarter = $('[data-current-quarter]').text();

			$('.multiselect').multiselect({
				buttonClass: 'btn btn-default'
			});

			$('[data-toggle="popover"]').popover().on('click', function(){
				return false;
			});

			$('#fellow-photo').parent().on('click', function(){
				$('select[name="fellow"]').show().siblings().hide();
			});

			$('#slivkan-photo').parent().on('click', function(){
				$('select[name="nu_email"]').show().siblings().hide();
			});

			$('[data-edit-qtr]').on('click', function(){
				var el = $(this).closest('tr'),
					val = el.find('.view:eq(0)').text().split(' ');

				el.find('.view').hide().siblings().show();

				$('#qtr-season').val((val[0] == 'Fall' ? '03' : (val[0] == 'Winter' ? '01' : '02')));
				$('#qtr-year').val(val[1]);

				el.find('[data-save]').on('click', function(){
					var qtr = $('#qtr-year').val().substr(2) + $('#qtr-season').val();

					admin.submitConfigOrQuarterInfo('qtr', qtr, 'Update current quarter?');

					return false;
				});

				el.find('[data-cancel]').on('click', function(){
					el.find('.view').show().siblings().hide();

					return false;
				});

				return false;
			});

			$('[data-edit-ims]').on('click', function(){
				var el = $(this).closest('tr');

				el.find('.view').hide().siblings().show();

				el.find('[data-save]').on('click', function(){
					admin.submitConfigOrQuarterInfo(
						'im_teams',
						JSON.stringify($('#im-select').val()),
						'Update IM Teams for ' + quarter + '?'
					);

					return false;
				});

				el.find('[data-cancel]').on('click', function(){
					el.find('.view').show().siblings().hide();

					return false;
				});

				return false;
			});

			$('body').on('click', '[data-edit-toggle]', function(){
				var name = $(this).data('edit-toggle'),
					value = !$(this).data('value'); //flip value

				admin.submitConfigOrQuarterInfo(name, value, 'Toggle value?');

				return false;
			});

			$('body').on('click', '[data-edit]', function(){
				var inputEl,
					thisEl = $(this),
					el = thisEl.closest('tr'),
					original = el.find('td:eq(1)').text(),
					type = thisEl.data('type') || 'text',
					field = thisEl.data('edit');

				thisEl.hide();
				$(['<span class="edit">',
						'<a href="#" data-save>Save</a><br>',
						'<a href="#" data-cancel>Cancel</a>',
					'</span>'].join('')).appendTo(el.find('td:eq(2)'));

				el.find('td:eq(1)').html('<input type="' + type + '" class="form-control">');
				inputEl = el.find('input');
				inputEl.val(thisEl.data('value') || original);

				el.find('[data-save]').on('click', function(){
					var val = inputEl.val(),
						isConfig = el.closest('table').data('config');

					if(val == original){
						el.find('[data-cancel]').click();
					}else{
						admin.submitConfigOrQuarterInfo(
							field,
							val,
							'Set ' + field + ' = "' + val + '"' + (isConfig ? '' : ' for ' + quarter) + '?'
						);
					}

					return false;
				});

				el.find('[data-cancel]').on('click', function(){
					el.find('.edit').remove();
					el.find('td:eq(1)').html(original);
					thisEl.show();

					return false;
				});

				return false;
			});

			$.getJSON(root + '/ajax/getSlivkans.php', function(data){
				var i, ind;

				slivkans = data.slivkans;
				nicknames = data.nicknames;

				//tack on nicknames to slivkans
				for(i = 0; i < nicknames.length; i++){
					ind = slivkans.indexOfKey('nu_email', nicknames[i].nu_email);
					if(ind !== -1){
						slivkans[ind].tokens.push(nicknames[i].nickname);
					}
				}

				submission.appendSlivkanInputs(9);

				$('#slivkan-entry-tab')	.on('focus', '.slivkan-entry', admin.slivkanTypeahead)
										.on('typeahead:closed', '.slivkan-entry.tt-query',
											{ callback: admin.validateSlivkanName },
											destroyTypeahead);

				$('[data-edit-committee]').on('click', function(){
					var committee = $('#edit-committee').val();

					$('.committee-points').val(0).show()
						.attr('max', (committee == 'Exec' ? 40 : 20));

					$('#editCommitteeOrSuite').data('is-committee', true);
					$.getJSON(root + '/ajax/getCommitteeOrSuite.php', { committee: committee }, admin.addSlivkans);
				});

				$('[data-edit-suite]').on('click', function(){
					$('.committee-points').hide();
					$('#editCommitteeOrSuite').data('is-committee', false);
					$.getJSON(root + '/ajax/getCommitteeOrSuite.php', { suite: $('#edit-suite').val() }, admin.addSlivkans);
				});

				$('[data-copy-suites]').on('click', function(){
					if(window.confirm('Are you sure? This can only be done once per quarter.')){
						$.getJSON(root + '/ajax/copySuites.php', function(response){
							if(response == '1'){
								window.alert('Success!');
							}else{
								window.alert(response);
							}
						});
					}

					return false;
				});

				$('#editCommitteeOrSuite form').on('submit', function(){
					var i, name, pts, formData,
						entries = $('.slivkan-entry', '#slivkan-entry-tab'),
						committeePoints = $('.committee-points'),
						committee = $('#edit-committee').val(),
						suite = $('#edit-suite').val(),
						isCommittee = $('#editCommitteeOrSuite').data('is-committee'),
						nuEmailArray = [],
						committeePointsArray = [];

					for(i = 0; i < entries.length; i++){
						name = entries.eq(i).val();
						if(name.length > 0){
							nuEmailArray.push(slivkans[slivkans.indexOfKey('full_name', name)].nu_email);
						}

						if(isCommittee){
							pts = committeePoints.eq(i).val();
							committeePointsArray.push(pts);
						}
					}

					if(isCommittee){
						formData = {
							committee: committee,
							slivkans: nuEmailArray,
							points: committeePointsArray
						};
					}else{
						formData = {
							suite: suite,
							slivkans: nuEmailArray
						};
					}
					$.post(
						root + '/ajax/submitCommitteeOrSuite.php',
						formData,
						function(response){
							if(response == '1'){
								$('#editCommitteeOrSuite').modal('hide');
								window.alert('Success!');
							}else{
								window.alert(response);
							}
						}
					);

					return false;
				});
			});
		},
		submitConfigOrQuarterInfo: function(name, value, confirmMessage){
			if(window.confirm(confirmMessage)){
				$.post(root + '/ajax/submitConfigOrQuarterInfo.php', { name: name, value: value }, function(status){
					if(status == '1'){
						window.location.reload();
					}else{
						window.alert(status);
					}
				});
			}
		},
		slivkanTypeahead: function(){
			var numInputs,
				target = $(this);

			if(target.closest('.slivkan-entry-control').addClass('has-warning').is(':last-child')){
				numInputs = $('#slivkan-entry-tab').find('.slivkan-entry').length;
				if(numInputs < 20){
					submission.appendSlivkanInputs(1);
				}
			}
			if(!target.hasClass('tt-query')){
				target.typeahead(typeaheadOpts('slivkans', slivkans)).focus();
			}

			return false;
		},
		validateSlivkanName: function(entry){
			var self,
				valid = true,
				slivkanEntry = entry.find('.slivkan-entry'),
				name = slivkanEntry.val(),
				nameArray = [];

			//clear duplicates
			$('#slivkan-entry-tab').find('.slivkan-entry').each(function(){
				self = $(this);
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

			if(name.length > 0){
				if(slivkans.indexOfKey('full_name', name) == -1){ valid = false; }
				updateValidity(entry, valid);
			}else{
				updateValidity(entry, null);
			}

			return valid;
		},
		addSlivkans: function(data){
			var i, entry, name,
				entries = $('.slivkan-entry-control', '#slivkan-entry-tab'),
				len = data.length;

			entries.find('.slivkan-entry').val('');

			if(entries.length <= len){
				submission.appendSlivkanInputs(len - entries.length + 1);
				entries = $('.slivkan-entry-control', '#slivkan-entry-tab');
			}

			for(i = 0; i < len; i++){
				entry = entries.eq(i);
				name = slivkans[slivkans.indexOfKey('nu_email', data[i].nu_email)].full_name;
				entry.find('.slivkan-entry').val(name);
				if(data[i].points){
					entry.find('.committee-points').val(data[i].points);
				}
				admin.validateSlivkanName(entry);
			}

			for(i; i < entries.length; i++){
				admin.validateSlivkanName(entries.eq(i));
			}
		}
	};

	return {
		breakdown: breakdown,
		table: table,
		correction: correction,
		submission: submission,
		inboundPoints: inboundPoints,
		rankings: rankings,
		'committee-headquarters': committeeHeadquarters,
		admin: admin
	};
});
