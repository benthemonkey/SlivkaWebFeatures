var slivkans = new Array();

jQuery(document).ready(function(){    
    jQuery.getJSON("../ajax/getSlivkans.php",function(data){
        for (i in data){
            slivkans.push(data[i]);
        }
    })
    
    jQuery('#name').autocomplete({source: slivkans});
})

function courseDatabaseFormValidate(){
    var isvalid = true;
    
    if (!validateName()){ isvalid = false; }
    if (!validateCourses()) { isvalid = false; }
    return isvalid;
}

function validateName(){
    var valid = false,
    name = jQuery('#name').val();
    
    valid = slivkans.indexOf(name) != -1;
    
    if (valid){
        jQuery('#name-error').fadeOut();
    }else{
        jQuery('#name-error').fadeIn();
    }
    
    return valid;
}

function validateCourses(){
    var valid = false;
    var patt = /([A-Z_]{4,9} \d{3}-\d-\d{2})/gm;
    
    matched = jQuery('#courses').val().match(patt);
    
    if (matched){
        valid = true;
        jQuery('#matched').html(matched.length);
        jQuery('#courses-error').fadeOut();
    }else{
        jQuery('#courses-error').fadeIn();
    }
    
    return valid;
}