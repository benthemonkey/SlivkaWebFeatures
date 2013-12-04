<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Rankings</title>
  <link rel="stylesheet" href="./bower_components/datatables/media/css/jquery.dataTables.css" />
  <style>
  tr.even.red { background-color: #FFA1A1!important; }
  tr.odd.red  { background-color: #FF8F8F!important; }

  tr.even.red td.sorting_1 { background-color: #FF7070!important; }
  tr.odd.red  td.sorting_1 { background-color: #FF5050!important; }

  tr.even.green { background-color: #00D10E!important; }
  tr.odd.green { background-color: #00BF0D!important; }

  tr.even.green td.sorting_1 { background-color: #00B30C!important; }
  tr.odd.green  td.sorting_1 { background-color: #00A30B!important; }
  </style>
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