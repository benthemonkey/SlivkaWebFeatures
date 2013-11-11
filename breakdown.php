<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Breakdown - Slivka Points Center</title>
  <style type="text/css">
  .event-list{
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
				<div class="col-lg-12">
					<legend>Points Breakdown</legend>
					<div class="row">
						<div class="col">
							<div class="form-group col-md-3">
								<label for="slivkan" class="control-label">Slivkan:</label>
								<select id="slivkan" class="form-control">
									<option value="">Select One</option>
								</select>
							</div>
							<div class="col-md-6 pull-right">
								<table class="table table-bordered table-condensed">
									<thead>
										<tr>
											<th>Type</th>
											<th>Events</th>
											<th>IMs</th>
											<th>Helper</th>
											<th>Committee</th>
											<th>Position</th>
											<th>Total</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<th>Subtotal</th>
											<td id="eventPoints"></td>
											<td id="imPoints"></td>
											<td id="helperPoints"></td>
											<td id="committeePoints"></td>
											<td id="positionPoints"></td>
											<td id="totalPoints"></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="col breakdown" style="display:none;">
						<div class="row">
							<div class="col-md-4">
								<div class="chart" id="eventsChart"></div>
								<div class="chart" id="imsChart"></div>
							</div>
							<div class="col-md-4 event-list">
								<table class="table table-bordered table-condensed table-striped">
									<thead>
										<tr>
											<th>Events Attended</th>
										</tr>
									</thead>
									<tbody id="attendedEvents">
									</tbody>
								</table>
							</div>
							<div class="col-md-4 event-list">
								<table class="table table-bordered table-condensed table-striped">
									<thead>
										<tr>
											<th>Events Unattended</th>
										</tr>
									</thead>
									<tbody id="unattendedEvents">
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