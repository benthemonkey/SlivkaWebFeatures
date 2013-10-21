<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Points Correction - Slivka RC</title>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="row">
				<div class="col-lg-offset-3 col-lg-6 col-md-offset-2 col-md-8 col">
					<form autocomplete="off" onsubmit="return false;" class="form-horizontal">
						<legend>Points Correction</legend>
						<div class="form-group filled-by-control col-lg-12">
							<label class="col-md-3 control-label" for="filled-by">Your Name:</label>
							<div class="col-md-9">
								<input type="text" name="filled-by" id="filled-by" class="form-control" onfocus="$('.filled-by-control').addClass('has-warning')">
							</div>
						</div>
						<div class="form-group event-control col-lg-12">
							<label class="col-md-3 control-label" for="event-name">Event:</label>
							<div class="col-md-9">
								<select name="event-name" id="event-name" class="form-control">
									<option>Select One</option>
								</select>
							</div>
						</div>
						<div class="form-group col-lg-12">
							<label class="col-md-3 control-label" for="comments">Comments:</label>
							<div class="col-md-9">
								<textarea name="comments" id="comments" row="3" class="form-control"></textarea>
								<span class="help-block">The person who took points will see your comments.</span>
							</div>
						</div>
						<div class="form-group col-lg-12">
							<div class="col-md-12">
								<div class="alert alert-danger" id="submit-error" style="display: none;"></div>
								<div class="alert alert-info" id="response" style="display: none;"></div>
								<span class="pull-right" id="form-actions">
									<button type="submit" class="btn btn-primary" id="submit">Submit</button>
									<button type="button" class="btn btn-default" id="reset">Reset</button>
								</span>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php include('footer.html'); ?>
</body>
</html>