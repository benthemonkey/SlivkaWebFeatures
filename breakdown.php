<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Breakdown - Slivka Points Center</title>
  <style type="text/css">
  table{
	font-size: 12px;
  }

  .chart{
	height: 250px;
  }
  </style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="row">
				<div class="col-md-3">
					<legend>Filters</legend>
					<div class="col-lg-12">
						<form>
							<fieldset>
								<div class="row">
									<div class="form-group col-md-12">
										<label for="slivkan" class="control-label">Slivkan:</label>
										<select id="slivkan" class="form-control">
											<option value="">Select One</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-12">
										<label for="daterange" class="control-label">Date Range:</label>
										<input id="daterange" class="form-control text-center">
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-12">
										<label for="showUnattended">Show Unattended:</label>
										<div class="make-switch">
											<input type="checkbox" name="showUnattended" id="showUnattended" checked>
										</div>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
				<div class="col-md-9">
					<legend>Points Breakdown</legend>
					<div class="col-lg-12">
						<div class="row" id="breakdown" style="display: none">
							<div class="col-md-6">
								<div id="attendedByCommittee" class="chart"></div>
								<table class="table table-bordered table-condensed table-striped">
									<thead>
										<tr>
											<th>Events <span class="slivkan-submit">Ford</span> Attended</th>
										</tr>
									</thead>
									<tbody id="attended">
									</tbody>
								</table>
							</div>
							<div class="col-md-6" id="unattended-col">
								<div id="unattendedByCommittee" class="chart"></div>
								<table class="table table-bordered table-condensed table-striped">
									<thead>
										<tr>
											<th>Events <span class="slivkan-submit">Ford</span> Didn't</th>
										</tr>
									</thead>
									<tbody id="unattended" class="fade in">
									</tbody>
								</table>
							</div>
						</div><!-- row -->
					</div><!--col-->
				</div>
			</div>
		</div>
	</div>
  	<?php include('footer.html'); ?>
</body>
</html>