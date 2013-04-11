var slivkans, nicknames, fellows, type = "Other",
valid_event_name = false;

jQuery(document).ready(function(){
    //nav
    $('.nav li').eq(3).addClass('active');

    $.getJSON("ajax/getSlivkans.php",function(data){
        slivkans = data.slivkans;
        nicknames = data.nicknames;
        fellows = data.fellows;

        slivkans.autocomplete = slivkans.full_name.concat(nicknames.nickname);

        //initialization
        appendNameInputs(14);
        appendFellowInputs(5);

        //loading saved values
        if(localStorage.spc_sub_attendees){
            var attendees = localStorage.spc_sub_attendees;
            attendees = attendees.split(", ");
            if(attendees.length > 14){ appendNameInputs(attendees.length - 14); }
            addSlivkans(attendees);
        }
        if(localStorage.spc_sub_filledby){
            $('#filled-by').val(localStorage.spc_sub_filledby);
            validateFilledBy();
        }
        if(localStorage.spc_sub_type && localStorage.spc_sub_type != "Other"){
            $('.type-btn[value="'+localStorage.spc_sub_type+'"]').click();
        }
        if(localStorage.spc_sub_date){
            $("#date").datepicker("setDate", localStorage.spc_sub_date);
        }
        if(localStorage.spc_sub_name){
            $('#event').val(localStorage.spc_sub_name);
            validateEventName();
        }
        if(localStorage.spc_sub_committee){
            $('#committee').val(localStorage.spc_sub_committee);
            validateCommittee();
        }
        if(localStorage.spc_sub_comments){
            $('#comments').val(localStorage.spc_sub_comments);
        }

        //autocomplete and other events
        $('#filled-by').typeahead({source: slivkans.full_name.concat(nicknames.nickname)})
        .on('focus',makeHandler.addClassWarning())
        .on('focusout',function(){validateFilledBy();});

        $('#slivkan-entry-tab').on('focus','.slivkan-entry',makeHandler.addClassWarning())
        .on('focusout','.slivkan-entry',makeHandler.validateSlivkanName())
        .on('click','.helper-point',makeHandler.toggleActive())
        .on('click','.committee-point',makeHandler.toggleActive());

        $('#fellow-entry-tab').find('.fellow-entry').on('focus',makeHandler.addClassWarning())
        .on('focusout',makeHandler.validateFellowName());
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
            validateEventName();
            localStorage.spc_sub_date = date;
        }
    });
    $("#date").datepicker("setDate", new Date());
    $('button.ui-datepicker-trigger').addClass("btn").html('<i class="icon-calendar"></i>');

    //event handler for type
    $('.type-btn').click(function(event){toggleType(event);});

    //event handler for comments
    $('#comments').on('keyup',function(){
        localStorage.spc_sub_comments = $('#comments').val();
    });

    $('#tabs a:first').tab('show');
});


var makeHandler = {
    addClassWarning : function(){
        return function(){ $(this).parent().addClass("warning"); };
    },
    validateSlivkanName : function(){
        return function(){ validateSlivkanName($(this).parent()); };
    },
    validateFellowName : function(){
        return function(){ validateFellowName($(this).parent()); };
    },
    toggleActive : function(){
        return function(){ $(this).toggleClass("active"); saveSlivkans(); };
    }
};

function appendNameInputs(n){
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
        if(num_inputs < 120){ appendNameInputs(1); }
    });
}

function appendFellowInputs(n){
    for (var i=0; i<n; i++){
        next = $('.fellow-entry-control').last().clone().appendTo('#fellow-entry-tab')
        .removeClass("warning");

        //autocomplete
        next.find('.fellow-entry').typeahead({source: fellows});
    }

    $('.fellow-entry').last().on('focus',function(){
        $(this).parent().addClass("warning");
        var num_inputs = $('.fellow-entry').length;
        $(this).off('focus');
        if(num_inputs < 20){appendFellowInputs(1);}
    });
}

function toggleType(event){
    type = event.target.value;

    //store value
    localStorage.spc_sub_type = type;

    //clear description if **previous** type was IM
    if($('.type-btn.active').val() == "IM"){
        $('#description').val("");
        validateDescription();
    }

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
        $("#event").attr("disabled","disabled");
    }else{
        $(".im-team-control").slideUp();
        $("#event").removeAttr("disabled");
    }

    if(type == "Other"){
        $(".description-control").slideDown();
        $("#committee").removeAttr("disabled");
    }else{
        $(".description-control").slideUp();
        $("#committee").attr("disabled","disabled");
    }
}

function validatePointsForm(){
    var valid = true,
    errors = [];

    if (!validateFilledBy()){ valid = false; errors.push("Filled By"); }
    if (!valid_event_name){ valid = false; updateValidity($(".event-control"),valid); errors.push("Name"); }
    if (!validateCommittee()){ valid = false; errors.push("Committee"); }
    if (!validateDescription()){ valid = false; errors.push("Description"); }

    var valid_slivkans = true;

    $('.slivkan-entry-control').each(function(){
        if(!validateSlivkanName($(this),true)){ valid_slivkans = false; }
    });

    if(!valid_slivkans){ valid = false; errors.push("Attendees"); }


    var valid_fellows = true;

    $('.fellow-entry-control').each(function(){
        if(!validateFellowName($(this))){ valid_fellows = false; }
    });

    if(!valid_fellows){ valid = false; errors.push("Fellows"); }


    if(valid){
        $('#submit-error').fadeOut();
        submitPointsForm();
    }else{
        $("#submit-error").text("Validation errors in: "+errors.join(", ")).fadeIn();
    }

    return valid;
}

function validateEventName(){
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
            updateValidity($(".event-control"),valid_event_name);
        });
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
        validateSlivkanName($(this),true);
    });

    //store values
    saveSlivkans();
    localStorage.spc_sub_committee = committee;

    return valid;
}

function validateDescription(){
    var valid = true, description = $("#description").val();

    //store value
    localStorage.spc_sub_description = description;

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
        updateValidity($('.filled-by-control'),valid);
    }else{
        $('.filled-by-control').removeClass('error');
    }

    return valid;
}

function validateSlivkanName(entry,inBulk){
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
                    validateSlivkanName(self.parent(),true);
                }
            }
        });

        //no names = invalid
        if(nameArray.length === 0){ valid=false; }

        //store values
        saveSlivkans();

        //update name in case it changed
        name = slivkan_entry.val();
    }


    entry.removeClass("warning");

    if (name.length > 0){
        name_ind = slivkans.full_name.indexOf(name);
        if(name_ind != -1){
            if(slivkans.committee[name_ind] == $('#committee').val() && type != 'IM'){
                showCommitteeMember(helper,committee,inBulk);
            }else if(type == 'IM' || slivkans.committee[name_ind] == 'Facilities' || slivkans.committee[name_ind] == 'Exec'){
                hideButtons(helper,committee,inBulk);
            }else{
                showHelperPoint(helper,committee,inBulk);
            }
        }else{ valid=false; }
        updateValidity(entry,valid);
    }else{
        entry.removeClass("success").removeClass("error");
        hideButtons(helper,committee,inBulk);
    }

    return valid;
}

function showHelperPoint(helper,committee,inBulk){ //quick: 46.15
    committee.removeClass('active');
    if(inBulk){
        committee.hide();
        helper.show();
    }else{
        committee.hide('slide',function(){
            helper.show('slide');
        });
    }

    /*if(helper.css('display') == 'none'){
        
    }*/
}

function showCommitteeMember(helper,committee,inBulk){
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
}

function hideButtons(helper,committee,inBulk){
    helper.removeClass('active');
    committee.removeClass('active');
    if(inBulk){
        helper.hide();
        committee.hide();
    }else{
        helper.hide('slide');
        committee.hide('slide');
    }
}

function validateFellowName(entry){
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
        updateValidity(entry,valid);
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
        appendNameInputs(n);
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
        validateSlivkanName(slivkan_entries.eq(ind).parent(),(i < len-1));
    }
}

function sortEntries(){
    var nameArray = [];

    nameArray = saveSlivkans();

    //clear slivkans
    $('#slivkan-entry-tab').find('.slivkan-entry').val("");

    //reset buttons
    $('.committee-point').removeClass('active');
    $('.helper-point').removeClass('active');

    nameArray = nameArray.sort();

    addSlivkans(nameArray);

    $('#sort-alert').slideDown();
}

function saveSlivkans(){
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
}

function addSlivkans(nameArray){
    var entries = $('#slivkan-entry-tab').find('.slivkan-entry-control'),
    len = nameArray.length;

    for(var i=0; i<len; i++){
        var name = nameArray[i].slice(0,nameArray[i].length-2);
        h = nameArray[i].slice(nameArray[i].length-2,nameArray[i].length-1);
        c = nameArray[i].slice(nameArray[i].length-1);

        entry = entries.eq(i);
        entry.find('.slivkan-entry').val(name);
        validateSlivkanName(entry,(i < len-1));
        if(h=="1"){ entry.find('.helper-point').addClass("active"); }
        if(c=="0"){ entry.find('.committee-point').removeClass("active"); }
    }

    for(i; i<entries.length; i++){
        validateSlivkanName(entries.eq(i),true);
    }
}

function updateValidity(element,valid){
    if (valid){
        element.addClass("success").removeClass("error");
    }else{
        element.removeClass("success").addClass("error");
    }
}

function resetForm(force){
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
            validateSlivkanName($(this),true);
        });
        validateSlivkanName($('.slivkan-entry-control').last());

        $('.fellow-entry').each(function(index){
            $(this).val("");
            validateFellowName($(this).parent());
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
}

function submitPointsForm(){
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


    console.log(JSON.stringify(data));

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

    //$('#submit-results').modal('show');

    $('#real-submit').off('click');
    $('#real-submit').on('click',function(){
        $.getJSON('./ajax/submitPointsForm.php',data,function(data_in){
            $('#results-status').parent().removeClass("warning");
            if(data_in.error){
                $('#results-status').html("Error in Step "+data_in.step).parent().addClass("error");
            }else{
                $('#unconfirmed').fadeOut({complete: function(){$('#confirmed').fadeIn();}});
                $('#results-status').html("Success!").parent().addClass("success");

                resetForm("force");
            }
        });
    });
}