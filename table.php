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
      <div class="col">
        <table id="table"></table>
      </div>
    </div>
	</div>
  <?php include('footer.html'); ?>
  <script type="text/javascript" src="DataTables/media/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function(){ pointsCenter.table.init(); });
  </script>
</body>
</html>
