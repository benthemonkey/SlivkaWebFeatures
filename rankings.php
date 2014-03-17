<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Rankings</title>
	<link rel="stylesheet" href="./bower_components/datatables/media/css/jquery.dataTables.css" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=yes">
	<style>
	body {
		min-width: 600px;
	}

	.legend {
		padding: 10px;
		text-align: center;
		font-size: 16px;
	}

	tr.even.red { background-color: #FFA1A1!important; }
	tr.odd.red  { background-color: #FF8F8F!important; }

	tr.even.red td.sorting_1 { background-color: #FF7070!important; }
	tr.odd.red  td.sorting_1 { background-color: #FF5050!important; }

	tr.even.green { background-color: #00D10E!important; }
	tr.odd.green { background-color: #00BF0D!important; }

	tr.even.green td.sorting_1 { background-color: #00B30C!important; }
	tr.odd.green  td.sorting_1 { background-color: #00A30B!important; }

	tr.even.yellow { background-color: #FFFA00!important; }
	tr.odd.yellow { background-color: #FFE500!important; }

	tr.even.yellow td.sorting_1 { background-color: #FFEA00!important; }
	tr.odd.yellow  td.sorting_1 { background-color: #FFD500!important; }
	</style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="col-lg-12">
 				<div class="row">
					<div class="col-lg-12 alert alert-info">
						<h4 style="text-align: center;">The Points Quarter is over. You have until Sunday, 3/23, to challenge the posted totals by emailing the VP.</h4>
						<!--<h4>Disclaimers! The following information may <strong><em>increase</em></strong> between now and Housing:</h4>
						<ul>
							<li>The number of Slivkan males/females allowed to return</li>
							<li>Additional Slivkans abstaining</li>
							<li>Committee Points</li>
							<li>Special Position Points (e.g. Eco Rep)</li>
							<li>Event Points for the rest of the quarter</li>
						</ul>-->
						<p style="margin-top: 10px; text-align: center;">
							39 Males and 39 Females will return, with 4 additional Slivkans determined based on Total w/ Multiplier.
						</p>
					</div>
				</div>
				<div class="row" style="margin-bottom:10px">
					<div class="col-lg-6 legend" style="background-color:#00D10E;">
						Above Points Cutoff <strong><em>NOT FINAL</em></strong>
					</div>
					<div class="col-lg-6 legend" style="background-color:#FF8F8F;">
						Abstaining from Housing or Below Points Minimum
					</div>
				</div>
				<ul class="nav nav-tabs" id="tabs">
					<li class="active"><a href="#males" data-toggle="tab"><span>Males</span></a></li>
					<li><a href="#females" data-toggle="tab"><span>Females</span></a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active col-md-12" id="males" >
						<table id="males_table"></table>
					</div>

					<div class="tab-pane col-md-12" id="females">
						<table id="females_table"></table>
					</div>
				</div><!--tab-content-->
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<?php include('footer.html'); ?>
</body>
</html>
