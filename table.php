<?php
#ini_set('display_errors', '1');
include_once "./ajax/PointsCenter.php";
$points_center = new PointsCenter($_GET['qtr']);

$showall = $_GET['all'] == '1';
$points_table = $points_center->getPointsTable($showall);

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
			<div class="row">
			<div class="col-lg-12">
				<div class="col-md-4">
					<div class="alert alert-info">
						<p>Hover over names for info, click arrows to sort.</p>
						<p>Showing <?php echo count($points_table['events']); ?> events.
							<strong>
							<?php
								if(!$showall){
									echo '<a href="./table.php?all=1">Show All (slow)</a>';
								}else{
									echo '<a href="./table.php">Reset</a>';
								}
							?>
							</strong>
						</p>
					</div>
					<table id="legend" class="legend">
						<tr class="odd" style="background-color: white;">
							<td>Colors: </td>
							<td class="green">Point</td>
							<td class="blue">Committee</td>
							<td class="gold">Helper</td>
							<td style="background-color: #FF8F8F;">None</td>
						</tr>
					</table>
				</div>
				<div class="col-md-8 filter-row" style="display: none;">
					<div class="filter">
						<label>Name:<br/>
							<input type="text" class="form-control" id="name-filter">
						</label>
					</div>
					<div class="filter">
						<label>Gender:<br/>
							<select class="form-control" id="gender-filter">
								<option value="">All</option>
								<option value="m">M</option>
								<option value="f">F</option>
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
					<a href="#stats" class="btn btn-default show-stats fix-top" data-toggle="modal">Stats</a>
					<a href="./table.php" id="noFilter" class="btn btn-default filter btn-sm pull-right">Disable Sorting<br/>and Filtering (fast)</a>
					<a href="./table.php" id="enableFilter" class="btn btn-default fix-top" style="display: none;">Enable Sorting and Filtering</a>
				</div>
			</div>
			</div>
			<div class="table-wrapper">
				<table id="table" class="points-table">
					<thead>
						<tr>
							<th class="nameHeader"><div><div></div></div><span>Name</span></th>
							<th style="display:none;"></th>
							<?php
								for($i=0; $i<count($points_table['events']); $i++){
									echo '<th class="eventHeader"><div class="slantedHeader"><span>' . substr($points_table['events'][$i]['event_name'],0,-11) . '</span></div><div class="sort-icon"></div></th>' . "\n";
								}
							?>
							<th class="totalsHeader">
								<div class="slantedHeader">
									<span>Events Total</span>
								</div>
								<div class="sort-icon"></div>
							</th>
							<th class="totalsHeader">
								<div class="slantedHeader">
									<span>Helper Points</span>
								</div>
								<div class="sort-icon"></div>
							</th>
							<th class="totalsHeader">
								<div class="slantedHeader">
									<span>IM Sports</span>
								</div>
								<div class="sort-icon"></div>
							</th>
							<th class="totalsHeader">
								<div class="slantedHeader">
									<span>Standing Committees</span>
								</div>
								<div class="sort-icon"></div>
							</th>
							<th class="totalsHeader">
								<div class="slantedHeader">
									<span>Other</span>
								</div>
								<div class="sort-icon"></div>
							</th>
							<th class="totalsHeader">
								<div class="slantedHeader">
									<span>Total</span>
								</div>
								<div class="sort-icon"></div>
							</th>
							<th class="endHeader">
								<div class="slantedHeader"></div>
								<div class="sort-icon"></div>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$odd = true;

							foreach($points_table['points_table'] AS $tr){
								if($odd){
									$odd = false;
									echo '<tr class="odd">';
								}else{
									$odd = true;
									echo '<tr class="even">';
								}

								$rowcount = count($tr);

								for($i=0; $i<$rowcount; $i++){
									$td = $tr[$i];

									if($i == 0){
										echo '<td class="name">' . $td . '</td>';
									}else if($i == 1){
										echo '<td class="gender gender-' . $td . '">' . $td . '</td>';
									}else if($i >= $rowcount - 6){
										echo '<td class="totals">' . $td . '</td>';
									}else if($td == 1){
										echo '<td class="green">' . $td . '</td>';
									}else if($td == 1.1 || $td == 0.1){
										echo '<td class="gold">' . $td . '</td>';
									}else if($td == 1.2 || $td == 0.2){
										echo '<td class="blue">' . $td . '</td>';
									}else{
										echo '<td>' . $td . '</td>';
									}
								}
								echo '<td class="end"></td></tr>' . "\n";
							}
						?>
					</tbody>
				</table>
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