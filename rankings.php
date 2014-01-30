<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Rankings</title>
	<link rel="stylesheet" href="./bower_components/datatables/media/css/jquery.dataTables.css" />
	<style>
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
	</style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="col-lg-12">
<!-- 				<div class="row">
					<div class="col-lg-12">
						<p>These are the rankings as they would look if we were doing housing at the beginning of next quarter instead of at the beginning of Spring. Being below the points cutoff right now does not mean you can't live in Slivka next year.</p>
						<p>This page won't show newly-added points immediately, so please be patient. The page will be available until the first day of classes after winter break.</p>
					</div>
				</div> -->
				<div class="row" style="margin-bottom:10px">
					<div class="col-lg-5 legend" style="background-color:#00D10E;">
						Above Points Cutoff (<strong>*NOT FINAL*</strong>)
					</div>
					<div class="col-lg-5 legend" style="background-color:#FF8F8F;">
						Abstaining from Housing or Below Points Minimum
					</div>
					<div class="col-lg-2">
						<a class="btn btn-block btn-default" id="update" data-loading-text="Calculating...">Update Data</a>
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