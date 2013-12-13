<?php
#ini_set('display_errors', '1');
include_once "./ajax/PointsCenter.php";
$points_center = new PointsCenter($_GET['qtr']);
$points_table = $points_center->getPointsTable();
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<meta name="viewport" content="user-scalable=yes">
	<title>Table - Slivka Points Center</title>
	<link rel="stylesheet" href="css/pointsTable.css" />
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="tablecol">
				<div class="row">
					<div class="col-md-4">
						<div class="alert alert-info">
							<div>Hover of names for info, click arrows to sort.</div>
						</div>
						<table id="legend" class="legend">
							<tr class="odd">
								<td style="background-color: white;">Colors: </td>
								<td class="green">Point</td>
								<td class="blue">Committee</td>
								<td class="gold">Helper</td>
								<td style="background-color: #FF8F8F;">None</td>
							</tr>
						</table>
					</div>
					<div class="col-md-8">
						<div class="filter">
							<label>Gender:<br/>
								<select class="form-control" id="gender-filter">
									<option value="">All</option>
									<option value="m">Male</option>
									<option value="f">Female</option>
								</select>
							</label>
						</div>
						<div class="filter">
							<label>IMs:<br/>
								<select class="form-control" id="im-filter">
									<option value="0">All</option>
									<option value="1">No IMs</option>
									<option value="2">Only IMs</option>
								</select>
							</label>
						</div>
						<div class="filter">
							<label>Committee:<br/>
								<select class="multiselect" multiple="multiple" id="committee-filter">
									<option selected>Exec</option>
									<option selected>Academic</option>
									<option selected>Facilities</option>
									<option selected>Faculty</option>
									<option selected>Historian</option>
									<option selected>IT</option>
									<option selected>Philanthropy</option>
									<option selected>Social</option>
									<option selected>CA</option>
									<option selected>Other</option>
								</select>
							</label>
						</div>
					</div>
				</div>
				<div class="table-wrapper">
					<ul class="hr">
						<?php
							for($i=0; $i<count($points_table['events']); $i++){
								echo '<li>' . substr($points_table['events'][$i]['event_name'],0,-10) . '</li>';
							}
						?>
					</ul>
					<table id="table" class="dataTable">
						<thead>
							<tr>
								<th class="nameHeader">Name</th>
								<th style="display:none;"></th>
								<?php
									for($i=0; $i<count($points_table['points_table'][0])-8; $i++){
										echo '<th class="eventHeader">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>';
									}

									for($i=0; $i<6; $i++){
										echo '<th class="totalsLabel">tot</th>';
									}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
								$odd = true;

								foreach($points_table['points_table'] AS $tr){
									$first = true;
									$second = false;

									if($odd){
										$odd = false;
										echo '<tr class="odd">';
									}else{
										$odd = true;
										echo '<tr class="even">';
									}
									foreach($tr AS $td){
										if($first){
											$first = false;
											$second = true;
											echo '<td class="name">' . $td . '</td>';
										}else if($second){
											$second = false;
											echo '<td class="gender">' . $td . '</td>';
										}else if($td == 1){
											echo '<td class="green">1</td>';
										}else if($td == 1.1 || $td == 0.1){
											echo '<td class="blue">1</td>';
										}else if($td == 1.2 || $td == 0.2){
											echo '<td class="gold">1</td>';
										}else{
											echo '<td>' . $td . '</td>';
										}
									}
									echo "</tr>\n";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div id="stats" class="modal fade" role="dialog" >
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Statistics</h3>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-4">
							<table class="table table-striped" id="years">
								<tr><th>Class Year</th><th>Avg Pts</th></tr>
							</table>
						</div>
						<div class="col-sm-4 col-offset-sm-5">
							<table class="table table-striped" id="suites">
								<tr><th>Suite</th><th>Avg Pts</th></tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		window.points_table = '<?php echo wordwrap(addslashes(json_encode(array("events"=>$points_table['events'], "by_year"=>$points_table['by_year'], "by_suite"=>$points_table['by_suite']))),800,"'+\n'")?>'
	</script>
	<?php include('footer.html'); ?>
</body>
</html>