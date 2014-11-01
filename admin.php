<?php
require_once "./ajax/PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

$im_leagues = array('Co-Rec', 'White');
$im_sports = array('Basketball', 'Dodgeball', 'Floor Hockey', 'Football', 'Soccer', 'Softball', 'Volleyball');

$config = $points_center->getConfig();
$qtrs = $points_center->getQuarters();
$qtr_info = $points_center->getQuarterInfo();
$slivkans = $points_center->getSlivkans();
$fellows = $points_center->getFellows();
$openCorrections = $points_center->getOpenPointsCorrections();

$date = \DateTime::createFromFormat('Y-m-d', $qtr_info['start_date']);
$start_date = $date->format('m/d/Y');

$date = \DateTime::createFromFormat('Y-m-d', $qtr_info['end_date']);
$end_date = $date->format('m/d/Y');

function getSlivkanName($slivkans, $nu_email)
{
  foreach ($slivkans as $s) {
    if ($s['nu_email'] == $nu_email) {
      return $s['full_name'];
    }
  }

  return $nu_email;
}

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include 'header.html'; ?>
	<title>Admin - Slivka Points Center</title>
	<style>
	.slivkan-entry-control:first-child{
		margin-top: 10px;
	}

	.slivkan-entry-control .input-group{
		width: 100%;
		margin: auto 0 10px;
	}

	.input-group-addon{
		padding: 6px 8px;
		width: 35px;
	}
</style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include 'nav.html'; ?>
			<legend>Admin Dashboard</legend>
			<div class="col">
				<div class="row">
					<div class="col-sm-6">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">Quarter</h4>
							</div>
							<table class="table">
								<tbody>
									<tr>
										<td>Current Quarter</td>
										<td>
											<span class="view" data-current-quarter><?= $qtr_info['quarter'] ?></span>
											<span class="edit" style="display: none;">
												<div class="row">
													<div class="col-xs-6">
														<select id="qtr-season" class="form-control">
															<option value="01">Winter</option>
															<option value="02">Spring</option>
															<option value="03">Fall</option>
														</select>
													</div>
													<div class="col-xs-6">
														<select id="qtr-year" class="form-control">
															<?php
                                                            $date = (int) date('Y');
                                                            echo '<option>' . ($date - 1) . '</option>';
                                                            echo '<option>' . $date . '</option>';
                                                            echo '<option>' . ($date + 1) . '</option>';
                                                            ?>
														</select>
													</div>
												</div>
											</span>
										</td>
										<td>
											<span class="view"><a href="#" data-edit-qtr>Edit</a></span>
											<span class="edit" style="display: none;">
												<a href="#" data-save>Save</a><br>
												<a href="#" data-cancel>Cancel</a>
											</span>
										</td>
									</tr>
									<tr>
										<td>Start Date</td>
										<td><?= $start_date ?></td>
										<td><a href="#" data-edit="start_date" data-type="date" data-value="<?= $qtr_info['start_date'] ?>">Edit</a></td>
									</tr>
									<tr>
										<td>End Date</td>
										<td><?= $end_date ?></td>
										<td><a href="#" data-edit="end_date" data-type="date" data-value="<?= $qtr_info['end_date'] ?>">Edit</a></td>
									</tr>
									<tr>
										<td>IM Teams</td>
										<td>
											<span class="view"><?= implode('<br>', $qtr_info['im_teams']) ?></span>
											<span class="edit" style="display:none;">
												<select id="im-select" class="multiselect" multiple="multiple">
													<?php
                                                    foreach ($im_sports as $sport) {
                                                        foreach ($im_leagues as $league) {
                                                            echo '<option ' .
                                                                (in_array($league.' '.$sport, $qtr_info['im_teams']) ? 'selected' : '') . '>' .
                                                                $league.' '.$sport.
                                                                '</option>' . "\n";
                                                        }
                                                    }
                                                    ?>
												</select>
											</span>
										</td>
										<td>
											<span class="view"><a href="#" data-edit-ims>Edit</a></span>
											<span class="edit" style="display: none;">
												<a href="#" data-save>Save</a><br>
												<a href="#" data-cancel>Cancel</a>
											</span>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">Configuration</h4>
							</div>
							<table class="table" data-config="true">
								<tbody>
									<tr>
										<td>Housing Selection</td>
										<td><?= $config['is_housing'] == 'true' ? 'Enabled' : 'Disabled' ?></td>
										<td><a href="#" data-edit-toggle="is_housing" data-value="<?= $config['is_housing'] ?>">Toggle</a></td>
									</tr>
									<tr>
										<td>VP Name</td>
										<td><?= $config['vp_name'] ?></td>
										<td><a href="#" data-edit="vp_name">Edit</a></td>
									</tr>
									<tr>
										<td>VP Email</td>
										<td><?= $config['vp_email'] ?></td>
										<td><a href="#" data-edit="vp_email" data-type="email">Edit</a></td>
									</tr>
									<tr>
										<td>VP Copies Email</td>
										<td><?= $config['vp_email_copies'] ?></td>
										<td><a href="#" data-edit="vp_email_copies" data-type="email">Edit</a></td>
									</tr>
									<tr>
										<td>Point Submission<br>Notifications</td>
										<td><?= $config['vp_email_notifications'] == 'true' ? 'Enabled' : 'Disabled' ?></td>
										<td><a href="#" data-edit-toggle="vp_email_notifications" data-value="<?= $config['vp_email_notifications'] ?>">Toggle</a></td>
									</tr>
									<tr>
										<td>Mailbot Email</td>
										<td><?= $config['mailbot_email'] ?></td>
										<td><a href="#" data-edit="mailbot_email" data-type="email">Edit</a></td>
									</tr>
									<tr>
										<td>Mailbot Password</td>
										<td><?= $config['mailbot_password'] ?></td>
										<td><a href="#" data-edit="mailbot_password">Edit</a></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="col-sm-6">
						<?php if (count($openCorrections) > 0) { ?>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									Open Points Corrections
								</h4>
							</div>
							<table class="table">
								<thead>
									<tr>
										<th>NU Email</th>
										<th>Event</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($openCorrections as $c) { ?>
									<tr>
										<td><?= getSlivkanName($slivkans, $c['nu_email']) ?></td>
										<td><?= $c['event_name'] ?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<?php } ?>
						<form id="upload-photo" role="form" method="post" action="./ajax/submitPhoto.php" enctype="multipart/form-data">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										Add/Update Photo
									</h4>
								</div>
								<div class="panel-body">
									<?php
                                    if ($_GET['success']) {
                                        ?>
										<div class="alert alert-success">
											<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
											Successfully uploaded photo!
										</div>
										<?php
                                    }
                                    ?>
									<div class="form-group">
										<div class="btn-group" data-toggle="buttons">
											<label class="btn btn-default active">
												<input type="radio" name="type" value="slivkan-photo" id="slivkan-photo" checked> Slivkan
											</label>
											<label class="btn btn-default">
												<input type="radio" name="type" value="fellow-photo" id="fellow-photo"> Fellow
											</label>
										</div>
									</div>
									<div class="form-group">
										<select class="form-control" name="nu_email">
											<?php foreach ($slivkans as $s) { ?>
												<option value="<?= $s['nu_email'] ?>"><?= $s['full_name'] ?></option>
											<?php } ?>
										</select>
										<select class="form-control" name="fellow" style="display:none;">
											<?php foreach ($fellows as $f) { ?>
												<option><?= $f['full_name'] ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label>Photo</label>
										<input type="file" name="file" id="file">
									</div>
								</div>
								<div class="panel-footer text-right">
									<input type="submit" class="btn btn-primary" value="Submit">
								</div>
							</div>
						</form>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">Committee</h4>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-8">
										<select id="edit-committee" class="form-control">
											<option>Exec</option>
											<option>Academic</option>
											<option>Facilities</option>
											<option>Faculty</option>
											<option>IT</option>
											<option>Philanthropy</option>
											<option>Publications</option>
											<option>Social</option>
											<option>Other</option>
										</select>
									</div>
									<div class="col-xs-4">
										<a href="#editCommitteeOrSuite" class="btn btn-default btn-block" data-toggle="modal" data-edit-committee>Edit</a>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">Suite</h4>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-8">
										<select id="edit-suite" class="form-control">
											<option>101</option>
											<option>119</option>
											<option>201</option>
											<option>220</option>
											<option>226</option>
											<option>235</option>
											<option>254</option>
											<option>301</option>
											<option>320</option>
											<option>326</option>
											<option>335</option>
											<option>354</option>
											<option>358</option>
											<option>401</option>
											<option>420</option>
											<option>426</option>
											<option>435</option>
											<option>454</option>
											<option>458</option>
											<option>NonRes</option>
										</select>
									</div>
									<div class="col-xs-4">
										<a href="#editCommitteeOrSuite" class="btn btn-default btn-block" data-toggle="modal" data-edit-suite>Edit</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="editCommitteeOrSuite" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit</h3>
				</div>
				<form>
					<div id="slivkan-entry-tab" class="modal-body">
						<div class="row form-group slivkan-entry-control">
							<div class="col-sm-10">
								<div class="input-group">
									<div class="input-group-addon">1</div>
									<input type="text" class="form-control slivkan-entry" name="slivkan-entry" placeholder="Slivkan">
								</div>
							</div>
							<div class="col-sm-2">
								<input type="number" min="0" max="20" value="0" class="form-control committee-points" name="committee-points" style="display: none;">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Save</button>
						<a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php include 'footer.html'; ?>
</body>
</html>
