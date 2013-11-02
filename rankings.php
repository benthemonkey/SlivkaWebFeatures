<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Rankings</title>
  <link rel="stylesheet" href="./bower_components/datatables/media/css/jquery.dataTables.css" />
</head>
<body>
	<div class="container">
    <div class="content">
      <?php include('nav.html'); ?>
      <div class="col-lg-12">
        <ul class="nav nav-tabs" id="tabs">
          <li class="active"><a href="#males" data-toggle="tab"><span>Males</span></a></li>
          <li><a href="#females" data-toggle="tab"><span>Females</span></a></li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane active col-md-12" id="males" >
            <table id="males_table"></table>
          </div>

          <div class="tab-pane col-md-12" id="females">
            <table id="females_table"></table>
          </div>
        </div><!--tab-content-->
      </div>
    </div>
	</div>
  <?php include('footer.html'); ?>
</body>
</html>