<?php
include_once __dir__ . "/ajax/PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

$showall = isset($_GET['all']) ? $_GET['all'] == '1' : false;

$qtr = $points_center->getQuarter();
$quarters = $points_center->getQuarters();
$points_table = $points_center->getPointsTable($showall);

?>
<div class="row points-table-controls">
	<div class="col-md-4">
		<div class="alert alert-info points-table-info">
			<p>Hover over names for info, click arrows to sort.</p>
			<p><?php
				echo "Showing " . count($points_table['events']) . " events. ";
				if(count($points_table['events']) >= 20){
					if(!$showall){
						echo '<a href="./?all=1&qtr=' . $qtr . '"><strong>Show All</strong></a>';
					}else{
						echo '<a href="./?qtr=' . $qtr . '"><strong>Reset</strong></a>';
					}
				}
			?></p>
		</div>
		<table class="legend">
			<tr class="odd">
				<td style="background-color: white;">Colors: </td>
				<td class="green">Point</td>
				<td class="blue">Committee</td>
				<td class="gold">Helper</td>
				<td>None</td>
			</tr>
		</table>
	</div>
	<div class="col-md-8">
		<div class="form-group">
			<label for="nameFilter">Name</label>
			<input type="text" class="form-control" id="nameFilter">
		</div>
		<div class="form-group">
			<label for="genderFilter">Gender</label>
			<select class="form-control" id="genderFilter">
				<option value="">All</option>
				<option value="m">M</option>
				<option value="f">F</option>
			</select>
		</div>
		<div class="form-group">
			<label for="imFilter">IMs</label>
			<select class="form-control" id="imFilter">
				<option value="0">All</option>
				<option value="1">No IMs</option>
				<option value="2">Only IMs</option>
			</select>
		</div>
		<div class="form-group">
			<div class="dropdown">
				<a class="btn btn-default" data-toggle="dropdown" href="#">Committees <b class="caret"></b></a>
				<form class="dropdown-menu" id="committeeFilter">
					<ul class="list-unstyled">
						<li><label><input type="checkbox" value="Exec" checked> Exec</label></li>
						<li><label><input type="checkbox" value="Academic" checked> Academic</label></li>
						<li><label><input type="checkbox" value="Facilities" checked> Facilities</label></li>
						<li><label><input type="checkbox" value="Faculty" checked> Faculty</label></li>
						<li><label><input type="checkbox" value="IT" checked> IT</label></li>
						<li><label><input type="checkbox" value="Philanthropy" checked> Philanthropy</label></li>
						<li><label><input type="checkbox" value="Publications" checked> Publications</label></li>
						<li><label><input type="checkbox" value="Social" checked> Social</label></li>
						<li><label><input type="checkbox" value="CA" checked> CA</label></li>
						<li><label><input type="checkbox" value="Other" checked> Other</label></li>
					</ul>
				</form>
			</div>
		</div>
		<div class="form-group">
			<div class="dropdown">
				<a class="btn btn-default" data-toggle="dropdown" href="#">Quarter <b class="caret"></b></a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
					<li><a href="./"><?= $quarters[0]['quarter'] ?></a></li>
		<?php for($i=1; $i<count($quarters); $i++){ ?>
					<li><a href="./?all=1&qtr=<?= $quarters[$i]['qtr'] ?>"><?= $quarters[$i]['quarter'] ?></a></li>
		<?php } ?>
				</ul>
			</div>
		</div>
		<div class="form-group">
			<a href="#stats" class="btn btn-default show-stats" data-toggle="modal">Stats</a>
		</div>
	</div>
</div>
<div class="points-table-wrapper">
	<table id="table" class="points-table">
		<thead>
			<tr>
				<th class="name"><div><div></div></div><span>Name</span></th>
				<th style="display:none;"></th>
<?php for($i=0; $i<count($points_table['events']); $i++){ ?>
				<th class="event">
					<div class="slanted">
						<span><?= substr($points_table['events'][$i]['event_name'],0,-11) ?></span>
					</div>
					<div class="sort-icon"></div>
				</th>
<?php }

$totalsColumns = array("Events Total", "Helper Points", "IM Sports", "Standing Committees", "Other", "Total");

for($i=0; $i<count($totalsColumns); $i++){ ?>
				<th class="total">
					<div class="slanted">
						<span><?= $totalsColumns[$i] ?></span>
					</div>
					<div class="sort-icon"></div>
				</th>
<?php } ?>
				<th class="end">
					<div class="slanted"></div>
					<div class="sort-icon"></div>
				</th>
			</tr>
		</thead>
		<tbody>
<?php
	$odd = true;
	$indent = "\t\t\t";

	foreach($points_table['points_table'] AS $tr){
		if(end($tr) == "0"){
			continue;
		}

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
<?php include('credits.html'); ?>

<div id="stats" class="modal fade" role="dialog" >
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Statistics</h3>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-4 col-sm-offset-2">
						<table class="table table-striped" id="years">
							<tr><th>Class Year</th><th>Avg Pts</th></tr>
						</table>
					</div>
					<div class="col-sm-4">
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
	window.qtr = <?= (isset($qtr) ? $qtr : 'null') ?>;
	window.points_table = '<?= wordwrap(addslashes(json_encode(array("events"=>$points_table['events'], "by_year"=>$points_table['by_year'], "by_suite"=>$points_table['by_suite']))),800," '+\n'")?>'
</script>
