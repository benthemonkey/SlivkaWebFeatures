<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Course Database Form</title>
</head>
<body style="background: none;">
	<img class="pull-right" title="This Week's Schedule" src="http://slivka.northwestern.edu/wp-content/uploads/2013/03/Screen-Shot-2013-03-04-at-1.23.09-PM1.png" width="290" height="285" />
	<ol>
		<li>Sign into Caesar: <a title="http://www.northwestern.edu/caesar" href="http://www.northwestern.edu/caesar" target="_blank">http://www.northwestern.edu/caesar/</a></li>
		<li>Click "Enrollment" and select the upcoming quarter.</li>
		<li>Find and copy the table labeled "My [Current Quarter] Class Schedule" (See right)</li>
		<li>Paste the table and press submit!</li>
	</ol>
	<p>Example:</p>
	<blockquote> EECS 321-0-20 LEC<br/>
	(23328)<br/>
	MoFr 2:00PM - 3:20PM<br/>
	Tech Institute Lecture Room 5<br/>
	<br/>
	EECS 473-1-20 LEC<br/>
	(23366)<br/>
	...</blockquote>
	<p>Note: If you run into any difficulties, please email the IT chair.</p>

	<form action="ajax/submitCourseDatabase.php" method="post" onsubmit="return courseDatabaseFormValidate();">
		<div class="form-group">
			<label for="name">Full Name:</label>
			<input id="name" onfocusout="validateName();" type="text" name="name" class="form-control" />
			<input id="nu_email" type="text" name="nu_email" style="display: none;" />
			<div id="name-error" class="alert alert-warning" style="display: none;">Name isn't in directory.</div>
		</div>
		<div class="form-group">
			<label for="courses">Pasted "This Week's Schedule": (currently <span id="matched">0</span> matched courses)</label>
			<textarea id="courses" onkeyup="validateCourses();" name="courses" rows="12" cols="50" class="form-control"></textarea>
			<div id="courses-error" class="alert alert-warning" style="display: none;">No matched courses.</div>
		</div>
		<div class="form-group">
			<label for="qtr">Quarter:</label>
			<select name="qtr" class="form-control">
				<option value="1402">Spring 2014</option>
				<option value="1401">Winter 2014</option>
				<option value="1303">Fall 2013</option>
				<option value="0">Previous Quarter</option>
			</select>
		</div>

		<div class="pull-right">
			<input id="submit" type="submit" value="Submit" class="btn btn-primary" />
		</div>

	</form>
	</div>
	<script type="text/javascript" src="bower_components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="bower_components/typeahead.js/dist/typeahead.min.js"></script>
	<script type="text/javascript">
	//add indexOfKey (useful: http://jsperf.com/js-for-loop-vs-array-indexof)
	Array.prototype.indexOfKey = function (key, value) {
		for(var i=0; i < this.length; i++){
			if(this[i][key] === value){
				return i;
			}
		}

		return -1;
	};

	window.slivkans = [];

	$(document).ready(function(){
	    $.getJSON("ajax/getSlivkans.php",function(data){
	        slivkans = data.slivkans;

	        $("#name").typeahead({
				name: "slivkans",
				valueKey: "full_name",
				local: slivkans
			});
	    });
	});

	function courseDatabaseFormValidate(){
	    var isvalid = true;

	    if (!validateName()){ isvalid = false; }
	    if (!validateCourses()) { isvalid = false; }
	    return isvalid;
	}

	function validateName(){
	    var valid = false,
	    name = $("#name").val(),
	    ind = slivkans.indexOfKey("full_name",name);

	    valid = ind != -1;

	    if (valid){
	    	$("#nu_email").val(slivkans[ind].nu_email);
	        $("#name-error").fadeOut();
	    }else{
	        $("#name-error").fadeIn();
	    }

	    return valid;
	}

	function validateCourses(){
	    var valid = false,
	    patt = /([A-Z_]{4,9} \d{3}-\d-\d{2})/gm,

	    matched = $("#courses").val().match(patt);

	    if (matched){
	        valid = true;
	        $("#courses").val(matched.join("; "));
	        $("#matched").html(matched.length);
	        $("#courses-error").fadeOut();
	    }else{
	        $("#courses-error").fadeIn();
	    }

	    return valid;
	}
	</script>
</body>
</html>
