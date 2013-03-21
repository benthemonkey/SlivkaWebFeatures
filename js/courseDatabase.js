//from http://stackoverflow.com/questions/5223/length-of-javascript-object-ie-associative-array
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

var updateCourses = function(dataString){
    jQuery.getJSON("../ajax/getCoursesInDept.php", dataString, function(data){
        var out = '<option value="">All</option>';
        for(var i=0; i<data.length; i++){
            out += '<option value="'+data[i]+'">'+data[i]+'</option>';
        }
        
        jQuery('#course').html(out);
    })
},

updateListing = function(dataString){
    jQuery.getJSON("../ajax/getCourseListing.php", dataString, function(data){
        var s1 = Object.size(data["current"]), s2 = Object.size(data["past"]), current = 'Currently enrolled: ', past = 'Enrolled in the past: ';
        for(key in data["current"]){
            current += data["current"][key]+', ';
        }
        current = current.substring(0, current.length - 2)
        
        for(key in data["past"]){
            past += data["past"][key]+', ';
        }
        past = past.substring(0, past.length - 2)
        
        jQuery('#current').html(current);
        jQuery('#past').html(past);
    })
}

jQuery(document).ready(function(){
    jQuery('#department').change(function(){
        var department = jQuery('#department').val(),
        course = jQuery('#course').val(),
        dataString = 'department='+department+'&course='+(course == null ? '' : course);
        
        if(department.length > 0){
            updateCourses(dataString);
            updateListing(dataString);
        }
    });
    
    jQuery('#course').change(function(){
        var department = jQuery('#department').val(),
        course = jQuery('#course').val(),
        dataString = 'department='+department+'&course='+course;
        
        if(department.length > 0) updateListing(dataString);
    })
})