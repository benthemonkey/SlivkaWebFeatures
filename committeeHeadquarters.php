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
					<div class="col-md-4">
						<a href="#helper-point-modal" class="btn btn-warning" data-toggle="modal">Give Helper Point</a>
						<a href="#no-show-modal" class="btn btn-danger" data-toggle="modal">Submit No-Show</a>
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
			echo $indent . "<tr data-slivkan=\"" . $s . "\" class=\"odd\">\n";
		}else{
			$odd = true;
			echo $indent . "<tr data-slivkan=\"" . $s . "\" class=\"even\">\n";
		}

		echo $indent . '<td class="name">' . getFullName($slivkans, $s) . "</td>\n";

		$rowcount = count($tr);

		for($i=0; $i<$rowcount; $i++){
			$td = $tr[$i];

			echo $indent . "\t";

			if($i == $rowcount - 2){
				echo '<td data-slivkan="'.$s.'" data-event="bonus" data-comments="'.$td['comments'].'" class="pts white">';
			}else if($i == $rowcount - 1){
				echo '<td class="totals">';
			}else{
				echo '<td data-event="'.$points_table['events'][$i].'" data-contributions="'.$td['contributions'].'" ';
				echo 'data-comments="'.$td['comments'].'" class="pts';

				if($td['filled_by']){
					echo ' blue';
				}else if($td['attended']){
					echo ' green';
				}

				echo '">';
			}

			printf("%.1f", $td['points']);
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
	<div id="helper-point-modal" class="modal fade" role="dialog" >
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Give Helper Point</h3>
				</div>
				<div class="modal-body">
					<form id="helper-point-form" role="form" onsubmit="return false;">
						<div class="form-group">
							<label for="helper-event" class="control-label">Event</label>
							<select id="helper-event" class="form-control">
								<option value="">Select One</option>
								<?php
									foreach(array_reverse($points_table['events']) as $e){
										echo "<option>" . $e . "</option>\n";
									}
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="helper-slivkan" class="control-label">Slivkan</label>
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
						<button type="submit" class="btn btn-primary btn-block">Submit</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="no-show-modal" class="modal fade" role="dialog" >
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Submit No-Show</h3>
				</div>
				<div class="modal-body">
					<form id="no-show-form" role="form" onsubmit="return false;">
						<div class="form-group">
							<label for="no-show-slivkan" class="control-label">Slivkan</label>
							<select id="no-show-slivkan" class="form-control">
								<option value="">Select One</option>
								<?php
									foreach($slivkans as $s){
										echo '<option value="' . $s['nu_email'] . '">' . $s['full_name'] . "</option>\n";
									}
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="no-show-date" class="control-label">Date</label>
							<select id="no-show-date" class="form-control"></select>
						</div>
						<div class="form-group">
							<label for="no-show-comments" class="control-label">Comments</label>
							<textarea id="no-show-comments" maxlength="200" rows="5" class="form-control"></textarea>
						</div>
						<button type="submit" class="btn btn-primary btn-block">Submit</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script id="pts-input-template" type="text/template">
		<div class="row">
			<div class="form-group has-success col-sm-4">
				<label class="control-label" for="pts-input">Points:</label>
				<input type="number" id="pts-input" value="{{value}}" data-original-value="{{value}}"
					min="-3.0" max="3.0" step="0.1" class="form-control pts-input">
			</div>
			{{#contributions}}
			<div class="form-group col-sm-8">
				<label class="control-label" for="contributions">Contributions:</label>
				<select id="contributions" class="multiselect" multiple="multiple" style="height: 34px;">
				{{#contributions_list}}
					<option value={{value}} {{#pts}}data-pts="{{pts}}"{{/pts}} {{disabled}} {{#selected}}selected{{/selected}}>
						{{title}}{{#pts}} ({{pts}}){{/pts}}
					</option>
				{{/contributions_list}}
				</select>
			</div>
			{{/contributions}}

			<div class="form-group col-sm-12">
				<label for="comments" class="control-label">Comments:</label>
				<textarea id="comments" maxlength="200" rows="5" class="form-control">{{comments}}</textarea>
			</div>

			<div class="col-sm-6">
				<button class="btn btn-primary btn-block pts-input-submit">Submit</button>
			</div>
			<div class="col-sm-6">
				<button class="btn btn-default btn-block pts-input-cancel">Cancel</button>
			</div>
		</div>
	</script>
	<?php include('footer.html'); ?>
</body>
</html>