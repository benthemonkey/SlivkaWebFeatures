<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Breakdown - Slivka Points Center</title>
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  <script type="text/javascript" src="js/pointsBreakdown.js"></script>
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
	<div class="container-fluid">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="row-fluid">
				<div class="span3">
					<legend>&nbsp;&nbsp;Filters</legend>
					<div class="col">
						<form>
							<fieldset>
								<div class="control-group">
									<label class="control-label">Slivkan:</label>
									<select id="slivkan" style="width: 100%" onchange="getSlivkanPoints()">
										<option value="">Select One</option>
									</select>
								</div>
								<div class="control-group">
						            <label for="start" class="control-label" onclick="$('#start').datepicker('show');">From:</label>
						            <input type="text" id="start-val" style="display: none">
						            <div class="input-append">
						              	<input type="text" id="start" name="start" class="input text-center" style="position: relative; z-index: 10; color: #000000; width: 50px;" disabled>
						            </div>
						        </div>
						        <div class="control-group">
						            <label for="end" class="control-label" onclick="$('#end').datepicker('show');">To:</label>
						            <input type="text" id="end-val" style="display: none">
						            <div class="input-append">
						              	<input type="text" id="end" name="end" class="input text-center" style="position: relative; z-index: 10; color: #000000; width: 50px;" disabled>
						            </div>
						        </div>
						        <div class="control-group">
						        	Other:
						        	<label for="showUnattended" class="checkbox">
						        		<input type="checkbox" name="showUnattended" id="showUnattended" checked> Show Unattended
						        	</label>
						        </div>
							</fieldset>
						</form>
					</div>
				</div>
				<div class="span9">
					<legend>&nbsp;&nbsp;Points Breakdown</legend>
					<div class="col">
						<div class="row-fluid hide" id="breakdown">
							<div class="span6">
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
							<div class="span6" id="unattended-col">
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
						</div><!-- row-fluid -->
					</div><!--col-->
				</div>
			</div>
		</div>
	</div>
</body>
</html>