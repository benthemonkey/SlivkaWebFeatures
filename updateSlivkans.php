<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Update Slivkans - Slivka Points Center</title>
	<style type="text/css">
		.slivkan-entry-control:first-child{
			margin-top: 10px;
		}

		.slivkan-entry-control{
			width: 100%;
			margin: auto 0 10px;
		}

		.input-group-addon{
			padding: 6px 8px;
			width: 35px;
		}

		.well{
			margin-bottom: 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include('nav.html'); ?>
			<div class="col-lg-6 col-lg-offset-3">
				<div class="alert alert-warning" id="duplicate-alert" style="display: none;">
					<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>
					<small>Duplicate name!</small>
				</div>
				<div class="col-lg-6">
					<label for="committee" class="control-label">Committee:</label>
					<select id="committee" class="form-control">
						<option></option>
						<option>Exec</option>
						<option>Academic</option>
						<option>Facilities</option>
						<option>Faculty</option>
						<option>Historian</option>
						<option>IT</option>
						<option>Philanthropy</option>
						<option>Social</option>
						<option>Other</option>
					</select>
				</div>
				<div class="col-lg-6">
					<label for="suite" class="control-label">Suite:</label>
					<select id="suite" class="form-control">
						<option></option>
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
				<div class="col-lg-12" id="slivkan-entry-tab">
					<div class="form-group input-group slivkan-entry-control">
						<div class="input-group-addon">1</div>
						<input type="text" class="form-control slivkan-entry" name="slivkan-entry" placeholder="Slivkan">
					</div>
				</div>
			</div>
			<div class="well col-lg-12">
				<div class="help-block alert alert-danger col-md-6 pull-left" id="submit-error" style="display: none;"></div>
				<div class="pull-right">
					<button type="submit" class="btn btn-primary btn-lg" id="submit">Submit</button>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<?php include('footer.html'); ?>
</body>
</html>