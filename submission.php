<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Submission - Slivka Points Center</title>
	<!--<script type="text/javascript" src="js/pointsSubmission.js"></script>-->
	<style type="text/css">
		#slivkan-entry-tab-buttons{
			margin: 10px 0;
			display: block;
		}

		.slivkan-entry-control{
			width: 100%;
			margin-bottom: 10px;
		}

		.fellow-entry-control:first-child{
			margin-top: 10px;
		}

		.committee-point{
			border-top-left-radius: 0;
			border-bottom-left-radius: 0;
		}

		.input-group-addon{
			padding: 6px 8px;
			width: 35px;
		}

		/*.input-group{
			padding-bottom: 10px;
		}*/
	</style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<form autocomplete="off" onsubmit="return false;" role="form">
				<fieldset>
					<legend>&nbsp;&nbsp;Points Submission Form</legend>
					<div class="row">
						<div class="col-md-5 col-md-offset-1 col">
							<div class="form-group filled-by-control col-md-12">
								<label class="control-label" for="filled-by">Points filled out by:</label>
								<input type="text" name="filled-by" id="filled-by" class="form-control">
							</div>

							<div class="form-group col-md-12">
								<label class="control-label" for="type">Type:</label>
								<div class="btn-group btn-group-justified" id="type" data-toggle="buttons" style="display: block;">
									<label class="btn btn-default type-btn">
										<input type="radio" name="type" value="P2P"> P2P
									</label>
									<label class="btn btn-default type-btn">
										<input type="radio" name="type" value="IM"> IM
									</label>
									<label class="btn btn-default type-btn">
										<input type="radio" name="type" value="House Meeting"> House Meeting
									</label>
									<label class="btn btn-default type-btn active">
										<input type="radio" name="type" value="Other"> Other
									</label>
								</div>
							</div>

							<div class="form-group im-team-control col-md-6" style="display: none">
								<label class="control-label" for="im-team">Team:</label>
								<select id="im-team" class="form-control"></select>
							</div>

							<div class="form-group event-control col-md-12">
								<label class="control-label" for="event">Event name:</label>
								<input type="text" name="event" id="event" class="form-control">
								<div class="help-block hide" id="event-name-error">Event name + date combination taken</div>
								<div class="help-block hide" id="event-name-length-error">Event name must be between 8 and 40 characters.<br/><span id="event-name-length-error-count"></span></div>
							</div>

							<div class="form-group col-xs-4">
								<label id="date-label" for="date">Date:</label>
								<input type="text" id="date-val" name="actual-date" style="display: none">
								<div class="input-group">
									<input type="text" id="date" name="date" class="form-control text-center" style="position: relative; z-index: 10; color: #000000;" disabled></input>
								</div>
							</div>

							<div class="clearfix"></div>

							<div class="form-group committee-control col-md-4">
								<label class="control-label">Committee:</label>
								<select id="committee" class="form-control">
									<option>Select One</option>
									<option>Exec</option>
									<option>Academic</option>
									<option>Facilities</option>
									<option>Faculty</option>
									<option>Historian</option>
									<option>IT</option>
									<option>Philanthropy</option>
									<option>Social</option>
									<option>CA</option>
									<option>Other</option>
								</select>
							</div>

							<div class="clearfix"></div>

							<div class="form-group description-control col-md-12">
								<label class="control-label" for="description">Description:</label>
								<textarea rows="3" name="description" id="description" class="form-control"></textarea>
								<div class="help-block hide" id="description-length-error">Be enthusiastic and descriptive!</div>
							</div>

							<div class="form-group col-md-12">
								<label class="control-label" for="comments">Comments:</label>
								<small class="help-block">No-shows, issues with form, explanation of helper points, etc.</small>
								<textarea name="comments" id="comments" class="form-control" rows="3"></textarea>
							</div>
						</div>
						<div class="col-md-5 col">
							<ul class="nav nav-tabs" id="tabs">
								<li class="active"><a href="#slivkan-entry-tab" data-toggle="tab"><span>Attendees</span></a></li>
								<li><a href="#fellow-entry-tab" data-toggle="tab"><span>Fellows</span></a></li>
							</ul>

							<div class="tab-content">
								<div class="tab-pane active" id="slivkan-entry-tab" >
									<div class="alert alert-success" id="sort-alert" style="display: none;">
										<button type="button" class="close" id="close-sort-alert">&times;</button>
										<small>Sorted names!</small>
									</div>

									<div class="alert alert-warning" id="duplicate-alert" style="display: none;">
										<button type="button" class="close" id="close-dupe-alert">&times;</button>
										<small>Duplicate name!</small>
									</div>

									<div id="slivkan-entry-tab-buttons">
										<div class="btn btn-default" role="button" data-toggle="modal" data-target="#QR-entry" title="add QR Codes / Wildcards"><i class="glyphicon glyphicon-qrcode"></i> Bulk Entry <i class="glyphicon glyphicon-barcode"></i></div>
										<div class="btn btn-default" id="sort-entries"><i class="glyphicon glyphicon-sort-by-alphabet" title="Sort"></i><i class="glyphicon glyphicon-arrow-down"></i></div>
									</div>

									<div class="form-group input-group slivkan-entry-control">
										<div class="input-group-addon">1</div>
										<input type="text" class="slivkan-entry form-control" name="slivkan-entry" placeholder="Slivkan">
										<div class="input-group-btn" style="display: none">
											<div class="btn btn-default committee-point" title="Committee Member"><i class="glyphicon glyphicon-user"></i></div>
										</div>
										<div class="input-group-btn" style="display: none">
											<div class="btn btn-default helper-point" title="Helper Point"><i class="glyphicon glyphicon-thumbs-up"></i></div>
										</div>
									</div>
								</div>

								<div class="tab-pane" id="fellow-entry-tab">
									<div class="form-group input-group fellow-entry-control">
										<div class="input-group-addon">1</div>
										<input type="text" class="fellow-entry form-control" name="fellow-entry" placeholder="Fellow">
									</div>
								</div>
							</div><!--tab-content-->
							<div class="help-block col-md-12"><small>Additional inputs appear automatically.</small></div>
						</div>
					</div>
					<div class="well col-md-12">
						<div class="help-block alert alert-danger col-md-6 pull-left" id="submit-error" style="display: none;"></div>
						<div class="pull-right">
							<button type="submit" class="btn btn-primary btn-lg" id="submit">Validate</button>
							<button type="button" class="btn btn-default btn-lg" id="reset">Reset</button>
						</div>
						<div class="clearfix"></div>
					</div>
				</fieldset>
			</form>
		</div><!--content-->
	</div>
	<div id="QR-entry" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4>Paste Names / Wildcard Barcodes</h4>
				</div>
				<div class="modal-body">
					<textarea name="bulk-names" id="bulk-names" rows="12" class="form-control"></textarea>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a>
					<a href="#" class="btn btn-primary" id="add-bulk-names" data-dismiss="modal" >Add Names</a>
				</div>
			</div>
		</div>
	</div>
	<div id="submit-results" class="modal fade" role="dialog" >
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Points Form Submission</h3>
				</div>
				<div class="modal-body">
					<table class="table table-bordered table-condensed" id="receipt">
						<tbody>
							<tr class="warning">
								<td>Status</td>
								<td id="results-status">Unsubmitted</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="modal-footer">
					<div id="unconfirmed">
						<button type="button" class="btn btn-primary" id="real-submit">Submit</button>
						<a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a>
					</div>
					<div id="confirmed" class="hide">
						<span>If an error occurs, email the receipt to Ben Rothman.</span>
						<a href="table.php" class="btn btn-primary">View Points</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include('footer.html'); ?>
	<script type="text/javascript">
		$(document).ready(function(){ pointsCenter.submission.init() });
	</script>
</body>
</html>