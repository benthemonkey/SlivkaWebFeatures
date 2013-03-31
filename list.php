<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>List - Slivka Points Center</title>
  <script type="text/javascript" src="js/pointsList.js"></script>
  <style type="text/css">
  table{
  	font-size: 12px;
  }
  </style>
</head>
<body>
	<div class="container-fluid">
		<div class="offset1 span12 content">
			<?php include('nav.html'); ?>
			<legend>&nbsp;&nbsp;Points List</legend>
			<div class="row-fluid">
				<div class="span6 col">
					<form>
						<fieldset>
							<div class="control-group">
								<div class="row-fluid">
									<div class="span6">
										<label class="control-label" for="start">From: </label>
										<div id="start-val" class="uneditable-input text-center span2"></div>
										<div style="min-width: 250px;">
											<div id="start" class="span6"></div>
										</div>
									</div>
									<div class="span6 pull-right">
										<label class="control-label" for="end">To: </label>
										<div id="end-val" class="uneditable-input text-center span2"></div>
										<div id="end"></div>
									</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Slivkan:</label>
								<select id="slivkan" onchange="getSlivkanPoints()">
									<option value="">Select One</option>
								</select>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="span6 col">
					<div class="row-fluid hide" id="tables">
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
						</div>
						<div class="span6">
							<table class="table table-bordered table-condensed table-striped">
								<thead>
									<tr>
										<th>Events <span class="slivkan-submit">Ford</span> Didn't</th>
									</tr>
								</thead>
								<tbody id="missed" class="fade in">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>