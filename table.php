<?php
#ini_set('display_errors', '1');
include_once "./ajax/PointsCenter.php";
$points_center = new PointsCenter($_GET['qtr']);

$showall = $_GET['all'] == '1';
$qtr = $_GET['qtr'];
$quarters = array_reverse($points_center->getQuarters());
$points_table = $points_center->getPointsTable($showall);

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<!--meta name="viewport" content="user-scalable=yes"-->
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
						<p><?php
							echo "Showing " . count($points_table['events']) . " events. ";
							if(count($points_table['events']) >= 20){
								if(!$showall){
									echo '<a href="./table.php?all=1&qtr=' . $qtr . '"><strong>Show All</strong></a>';
								}else{
									echo '<a href="./table.php?qtr=' . $qtr . '"><strong>Reset</strong></a>';
								}
							}
						?></p>
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
					<div style="float:right;height:0;">
						<a href="./table.php" id="noFilter" class="btn btn-link btn-sm pull-right">Disable Sorting and Filtering</a>
						<a href="./table.php" id="enableFilter" class="btn btn-link btn-sm pull-right" style="display: none;">Enable Sorting and Filtering</a>
					</div>
					<div class="col-xs-12 visible-xs">&nbsp;</div>
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
					<div class="filter" style="width:62px;">
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
								<option selected>IT</option>
								<option selected>Philanthropy</option>
								<option selected>Publications</option>
								<option selected>Social</option>
								<option selected>CA</option>
								<option selected>Other</option>
							</select>
						</label>
					</div>
					<div class="filter dropdown fix-top">
						<a class="btn btn-default" data-toggle="dropdown" href="#">Quarter <b class="caret"></b></a>
						<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
<?php
	$indent = "\t\t\t\t\t\t\t";

	echo $indent . '<li><a href="./table.php">' . $quarters[0]['quarter'] . "</a></li>\n";

	for($i=1; $i<count($quarters); $i++){
		echo $indent . '<li><a href="./table.php?all=1&qtr=' . $quarters[$i]['qtr'] . '">' . $quarters[$i]['quarter'] . "</a></li>\n";
	}
?>
						</ul>
					</div>
					<a href="#stats" class="btn btn-default show-stats fix-top" data-toggle="modal">Stats</a>
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
	$indent = "\t\t\t\t\t\t\t";

	for($i=0; $i<count($points_table['events']); $i++){
		echo $indent . "<th class=\"eventHeader\">\n" .
			$indent . "\t<div class=\"slantedHeader\">\n" .
			$indent . "\t\t<span>" . substr($points_table['events'][$i]['event_name'],0,-11) . "</span>\n" .
			$indent . "\t</div>\n" .
			$indent . "\t<div class=\"sort-icon\"></div>\n" .
			$indent . "</th>\n";
	}

	$totalsColumns = array("Events Total", "Helper Points", "IM Sports", "Standing Committees", "Other", "Total");

	for($i=0; $i<count($totalsColumns); $i++){
		echo $indent . "<th class=\"totalsHeader\">\n" .
			$indent . "\t<div class=\"slantedHeader\">\n" .
			$indent . "\t\t<span>" . $totalsColumns[$i] . "</span>\n" .
			$indent . "\t</div>\n" .
			$indent . "\t<div class=\"sort-icon\"></div>\n" .
			$indent . "</th>\n";
	}
?>
							<th class="endHeader">
								<div class="slantedHeader"></div>
								<div class="sort-icon"></div>
							</th>
						</tr>
					</thead>
					<tbody>
<?php
	$odd = true;
	$indent = "\t\t\t\t\t\t";

	foreach($points_table['points_table'] AS $tr){
		if($odd){
			$odd = false;
			echo $indent . "<tr class=\"odd\">\n";
		}else{
			$odd = true;
			echo $indent . "<tr class=\"even\">\n";
		}

		$rowcount = count($tr);

		for($i=0; $i<$rowcount; $i++){
			$td = $tr[$i];

			echo $indent . "\t";

			if($i == 0){
				echo '<td class="name">';
			}else if($i == 1){
				echo '<td class="gender">';
			}else if($i >= $rowcount - 6){
				echo '<td class="totals">';
			}else if($td == 1){
				echo '<td class="green">';
			}else if($td == 1.1 || $td == 0.1){
				$td = floor($td);
				echo '<td class="gold">';
			}else if($td == 1.2 || $td == 0.2){
				$td = floor($td);
				echo '<td class="blue">';
			}else{
				echo '<td>';
			}

			echo $td . "</td>\n";
		}
		echo $indent . "\t<td class=\"end\"></td>\n" .
			$indent . "</tr>\n";
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
		window.qtr = <?php if($qtr){echo $qtr;}else{echo 'null';} ?>;
		window.points_table = '<?php echo wordwrap(addslashes(json_encode(array("events"=>$points_table['events'], "by_year"=>$points_table['by_year'], "by_suite"=>$points_table['by_suite']))),800,"'+\n'")?>'
	</script>
	<?php include('footer.html'); ?>
</body>
</html>