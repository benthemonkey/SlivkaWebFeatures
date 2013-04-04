<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Points Correction - Slivka RC</title>
	<script type="text/javascript" src="js/pointsCorrection.js"></script>
</head>
<body>
	<div class="container-fluid">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="row-fluid">
				<div class="span3"></div>
				<div class="span6 col">
					<form autocomplete="off" onsubmit="return false;" class="form-horizontal">
						<fieldset>
							<legend>Points Correction Form</legend>
							<div class="control-group filled-by-control">
								<label class="control-label" for="filled-by">Your Name:</label>
								<div class="controls">
									<input type="text" name="filled-by" id="filled-by" class="input" onfocus="$('.filled-by-control').addClass('warning')" onfocusout="validateFilledBy()">
								</div>
							</div>
							<div class="control-group event-control">
								<label class="control-label" for="event-name">Event:</label>
								<div class="controls">
									<select name="event-name" id="event-name" class="input">
										<option>Select One</option>
									</select>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="comments">Comments:</label>
								<div class="controls">
									<textarea name="comments" id="comments" row="3" class="span10"></textarea>
									<span class="help-block">The person who took points will see your comments.</span>
								</div>
							</div>
							<div class="controls">
								<button type="submit" class="btn btn-primary" onclick="validatePointsCorrectionForm()" >Submit</button>
								<button type="button" class="btn" onclick="resetForm()">Reset</button>
								<span class="help-inline hide" id="submit-error"></span>
							</div>
							<div class="controls" id="response"></div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>
</body>
</html>