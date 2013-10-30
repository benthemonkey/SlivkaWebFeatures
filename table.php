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
        <table id="table"></table>
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
                <tr><th>Year</th><th>Avg Pts</th></tr>
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
  <?php include('footer.html'); ?>
</body>
</html>