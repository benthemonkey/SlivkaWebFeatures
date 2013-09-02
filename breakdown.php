<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Breakdown - Slivka Points Center</title>
  <!--<script type="text/javascript" src="js/pointsBreakdown.js"></script>-->
  <style type="text/css">
  table{
	font-size: 12px;
  }

  .chartWrapper{
	overflow: hidden;
  }

  .chart{
	margin: 0 auto;
	height: 270px;
	width: 270px;
  }
  </style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="row">
				<div class="col-md-3">
					<legend>&nbsp;&nbsp;Filters</legend>
					<div class="col">
						<form>
							<fieldset>
								<div class="row">
									<div class="form-group col-md-12">
										<label class="control-label">Slivkan:</label>
										<select id="slivkan" class="form-control">
											<option value="">Select One</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-xs-6">
										<label for="start" class="control-label">From:</label>
										<input type="text" id="start-val" style="display: none">
										<div class="input-group">
											<input type="text" id="start" class="form-control text-center" style="position: relative; z-index: 10; color: #000000;" disabled>
											<div class="input-group-btn start-btn">
												<button type="button" name="start" class="btn btn-default"><i class="glyphicon glyphicon-calendar"></i></button>
											</div>
										</div>
									</div>
									<div class="form-group col-xs-6">
										<label for="end" class="control-label">To:</label>
										<input type="text" id="end-val" style="display: none">
										<div class="input-group">
											<input type="text" id="end" name="end" class="form-control text-center" style="position: relative; z-index: 10; color: #000000;" disabled>
											<div class="input-group-btn end-btn">
												<button type="button" name="end" class="btn btn-default"><i class="glyphicon glyphicon-calendar"></i></button>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-12">
										<div class="btn-group btn-group-justified" data-toggle="buttons">
											<label class="range btn btn-default btn-sm" id="today">
												<input type="radio" name="range"> Today
											</label>
											<label class="range btn btn-default btn-sm" id="week">
												<input type="radio" name="range"> Week
											</label>
											<label class="range btn btn-default btn-sm" id="month">
												<input type="radio" name="range"> Month
											</label>
											<label class="range btn btn-default btn-sm" id="quarter">
												<input type="radio" name="range"> All
											</label>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-12">
										Other:
										<label for="showUnattended" class="checkbox">
											<input type="checkbox" name="showUnattended" id="showUnattended" checked> Show Unattended
										</label>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
				<div class="col-md-9">
					<legend>&nbsp;&nbsp;Points Breakdown</legend>
					<div class="col">
						<div class="row" id="breakdown" style="display: none	">
							<div class="col-md-6">
								<table class="table table-bordered table-condensed table-striped">
									<thead>
										<tr>
											<th>Events <span class="slivkan-submit">Ford</span> Attended</th>
										</tr>
									</thead>
									<tbody id="attended">
									</tbody>
								</table>
								<div class="chartWrapper">
									<div id="attendedByCommittee" class="chart"></div>
								</div>
							</div>
							<div class="col-md-6" id="unattended-col">
								<table class="table table-bordered table-condensed table-striped">
									<thead>
										<tr>
											<th>Events <span class="slivkan-submit">Ford</span> Didn't</th>
										</tr>
									</thead>
									<tbody id="unattended" class="fade in">
									</tbody>
								</table>
								<div class="chartWrapper">
									<div id="unattendedByCommittee" class="chart"></div>
								</div>
							</div>
						</div><!-- row -->
					</div><!--col-->
				</div>
			</div>
		</div>
	</div>
	<?php include('footer.html'); ?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
  	<script type="text/javascript">google.load("visualization", "1", {packages:["corechart"]});</script>
	<script type="text/javascript">
		$(document).ready(function(){ pointsCenter.breakdown.init(); });
	</script>
</body>
</html>