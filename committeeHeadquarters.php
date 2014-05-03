<?php
#ini_set('display_errors', '1');
include_once "./ajax/PointsCenter.php";
$points_center = new PointsCenter();

$committee = $_GET['committee'];
if ($committee) {
	$points_table = $points_center->getCommitteePointsTable($committee);

	$slivkans = $points_center->getSlivkans();
}

function getFullName($slivkans, $nu_email){
	foreach($slivkans as $s){
		if($s['nu_email'] == $nu_email){
			return $s['full_name'];
		}
	}

	return '';
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Committee Headquarters - Slivka Points Center</title>
	<link rel="stylesheet" href="css/pointsTable.css" />
	<style>
		.committee-points-table td{
			padding: 2px;
		}

		th:hover{
			cursor: auto;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="dropdown pull-right" style="margin-right: 10px; height: 0;">
				<a class="btn btn-default btn-sm" data-toggle="dropdown" href="#">Select Committee <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
<?php
	$indent = "\t\t\t\t\t";
	$committees = array("Academic", "Facilities", "Faculty", "IT", "Philanthropy", "Publications", "Social");

	for($i=0; $i<count($committees); $i++){
		echo $indent . '<li><a href="./committeeHeadquarters.php?committee=' . $committees[$i] . '">' . $committees[$i] . "</a></li>\n";
	}
?>
				</ul>
			</div>
			<legend><?php echo $committee ?> Committee Headquarters</legend>
<?php if ($committee) { ?>
			<div class="row">
				<div class="col-lg-12">
					<div class="col-lg-2 col-md-3 col-sm-6">
						<div class="alert alert-info">
							<p>Click to edit values.</p>
						</div>
					</div>
					<div class="col-md-3 col-sm-6">
						<div>Colors:</div>
						<table id="legend" class="legend text-center" style="width: 100%;">
							<tr class="odd">
								<td class="green">Attendee</td>
								<td class="blue">Point Taker</td>
								<td>None</td>
							</tr>
						</table>
					</div>
					<div class="col-md-2">
						<a href="#helper-point-modal" class="btn btn-warning btn-lg" data-toggle="modal">Give Helper Point</a>
					</div>
				</div>
			</div>
			<div class="table-wrapper">
				<table id="table" class="points-table committee-points-table">
					<thead>
						<tr>
							<th class="nameHeader"><div><div></div></div><span>Name</span></th>
<?php for($i=0; $i<count($points_table['events']); $i++){ ?>
							<th class="eventHeader">
								<div class="slantedHeader">
									<span><?= substr($points_table['events'][$i], 0, -10) . substr($points_table['events'][$i], -5)?></span>
								</div>
								<div class="sort-icon"></div>
							</th>
<?php }

$totalsColumns = array("Bonus", "Total");//, "Total (adjusted)");

for($i=0; $i<count($totalsColumns); $i++){ ?>
							<th class="totalsHeader">
								<div class="slantedHeader">
									<span><?= $totalsColumns[$i] ?></span>
								</div>
								<div class="sort-icon"></div>
							</th>
<?php } ?>
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

	foreach($points_table['points_table'] AS $s => $tr){
		if($odd){
			$odd = false;
			echo $indent . "<tr class=\"odd\">\n";
		}else{
			$odd = true;
			echo $indent . "<tr class=\"even\">\n";
		}

		echo $indent . '<td class="name">' . getFullName($slivkans, $s) . "</td>\n";

		$rowcount = count($tr);

		for($i=0; $i<$rowcount; $i++){
			$td = $tr[$i];

			echo $indent . "\t";

			if($i == $rowcount - 2){
				echo '<td data-slivkan="'.$s.'" data-event="bonus" class="pts white">';
				printf("%.1f", $td['points']);
			}else if($i == $rowcount - 1){
				echo '<td class="totals">';
				printf("%.1f", $td['points']);
			}else{
				echo '<td data-slivkan="'.$s.'" data-event="'.$points_table['events'][$i].'" class="pts';

				if($td['filled_by']){
					echo ' blue';
				}else if($td['attended']){
					echo ' green';
				}

				echo '">';
				printf("%.1f", $td['points']);
			}

			echo "</td>\n";
		}
		echo $indent . "\t<td class=\"end\"></td>\n" .
			$indent . "</tr>\n";
	}
?>
					</tbody>
				</table>
			</div>
<?php } else { ?>
			<div class="alert alert-info text-center"><h4>Select a Committee</h4></div>
<?php } ?>
		<?php include('credits.html'); ?>
		</div><!--content-->
	</div>
	<div id="pts-input-template" style="display: none;">
		<button type="button" class="close" aria-hidden="true">&times;</button>
		<div class="form-group has-success">
			<label class="control-label" for="pts-input">Edit Points:</label>
			<div class="input-group">
				<input type="number" id="pts-input" min="0.0" max="3.0" step="0.1" class="form-control pts-input">
				<span class="input-group-btn">
					<button class="btn btn-primary submit-committee-point">
						<span class="glyphicon glyphicon-ok"></span>
					</button>
				</span>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="contributions">Contributions (not saved yet):</label>
			<div class="clearfix"></div>
			<select id="contributions" class="multiselect" multiple="multiple" style="height: 34px;">
				<option data-pts="0.5" disabled>Attended (0.5)</option>
				<option data-pts="0.5" disabled>Took Points (0.5)</option>
				<option data-pts="2">Ran event (2)</option>
				<option data-pts="1">Poster (1)</option>
				<option data-pts="0.5">Set up (0.5)</option>
				<option data-pts="0.5">Clean up (0.5)</option>
				<option data-pts="0">Other</option>
			</select>
		</div>
	</div>
	<div id="helper-point-modal" class="modal fade" role="dialog" >
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Give Helper Point</h3>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-12">
							<form id="helper-point-form" class="form-horizontal" role="form" onsubmit="return false;">
								<div class="form-group">
									<label for="helper-event" class="col-sm-2 control-label">Event</label>
									<div class="col-sm-10">
										<select id="helper-event" class="form-control">
											<option value="">Select One</option>
											<?php
												foreach(array_reverse($points_table['events']) as $e){
													echo "<option>" . $e . "</option>\n";
												}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label for="helper-slivkan" class="col-sm-2 control-label">Slivkan</label>
									<div class="col-sm-10">
										<select id="helper-slivkan" class="form-control">
											<option value="">Select One</option>
											<?php
												foreach($slivkans as $s){
													echo '<option value="' . $s['nu_email'] . '" ';
													if($s['committee'] == $committee || $s['committee'] == 'Facilities'){
														echo 'disabled';
													}
													echo '>' . $s['full_name'] . "</option>\n";
												}
											?>
										</select>
									</div>
								</div>
								<button type="submit" class="btn btn-primary btn-block">Submit</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include('footer.html'); ?>
</body>
</html>