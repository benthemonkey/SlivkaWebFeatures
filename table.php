<?php
include_once "./ajax/PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

$showall = isset($_GET['all']) ? $_GET['all'] == '1' : false;

$qtr = $points_center->getQuarter();
$quarters = $points_center->getQuarters();
$points_table = $points_center->getPointsTable($showall);

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include 'header.html'; ?>
	<meta name="viewport" content="user-scalable=yes">
	<title>Table - Slivka Points Center</title>
	<link rel="stylesheet" href="css/pointsTable.css" />
</head>
<body>
	<div class="container">
		<div class="content">
			<?php include 'nav.html'; ?>
			<div class="row">
			<div class="col-lg-12">
				<div class="col-md-4">
					<div class="alert alert-info">
						<p>Hover over names for info, click arrows to sort.</p>
						<p><?php
                            echo "Showing " . count($points_table['events']) . " events. ";
                            if (count($points_table['events']) >= 20) {
                                if (!$showall) {
                                    echo '<a href="./table.php?all=1&qtr=' . $qtr . '"><strong>Show All</strong></a>';
                                } else {
                                    echo '<a href="./table.php?qtr=' . $qtr . '"><strong>Reset</strong></a>';
                                }
                            }
                        ?></p>
					</div>
					<table id="legend" class="legend">
						<tr class="odd">
							<td style="background-color: white;">Colors: </td>
							<td class="green">Point</td>
							<td class="blue">Committee</td>
							<td class="gold">Helper</td>
							<td>None</td>
						</tr>
					</table>
				</div>
				<div class="col-md-8 filter-row" style="display: none;">
					<div style="float:right;height:0;">
						<a href="./table.php" id="noFilter" class="btn btn-link btn-sm pull-right" style="position:relative;top:-8px;">Disable Sorting and Filtering</a>
						<a href="./table.php" id="enableFilter" class="btn btn-link btn-sm pull-right" style="position:relative;top:-8px;display: none;">Enable Sorting and Filtering</a>
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
							<li><a href="./table.php"><?= $quarters[0]['quarter'] ?></a></li>
<?php for ($i=1; $i<count($quarters); $i++) { ?>
							<li><a href="./table.php?all=1&qtr=<?= $quarters[$i]['qtr'] ?>"><?= $quarters[$i]['quarter'] ?></a></li>
<?php } ?>
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
<?php for ($i=0; $i<count($points_table['events']); $i++) { ?>
							<th class="eventHeader">
								<div class="slantedHeader">
									<span><?= substr($points_table['events'][$i]['event_name'],0,-11) ?></span>
								</div>
								<div class="sort-icon"></div>
							</th>
<?php }

$totalsColumns = array("Events Total", "Helper Points", "IM Sports", "Standing Committees", "Other", "Total");

for ($i=0; $i<count($totalsColumns); $i++) { ?>
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

    foreach ($points_table['points_table'] as $tr) {
        if (end($tr) == "0") {
            continue;
        }

        if ($odd) {
            $odd = false;
            echo $indent . "<tr class=\"odd\">\n";
        } else {
            $odd = true;
            echo $indent . "<tr class=\"even\">\n";
        }

        $rowcount = count($tr);

        for ($i=0; $i<$rowcount; $i++) {
            $td = $tr[$i];

            echo $indent . "\t";

            if ($i == 0) {
                echo '<td class="name">';
            } elseif ($i == 1) {
                echo '<td class="gender">';
            } elseif ($i >= $rowcount - 6) {
                echo '<td class="totals">';
            } elseif ($td == 1) {
                echo '<td class="green">';
            } elseif ($td == 1.1 || $td == 0.1) {
                $td = floor($td);
                echo '<td class="gold">';
            } elseif ($td == 1.2 || $td == 0.2) {
                $td = floor($td);
                echo '<td class="blue">';
            } else {
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
			<?php include 'credits.html'; ?>
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
		window.qtr = <?php if ($qtr) {echo $qtr;} else {echo 'null';} ?>;
		window.points_table = '<?php echo wordwrap(addslashes(json_encode(array("events"=>$points_table['events'], "by_year"=>$points_table['by_year'], "by_suite"=>$points_table['by_suite']))),800,"'+\n'")?>'
	</script>
	<?php include 'footer.html'; ?>
</body>
</html>
