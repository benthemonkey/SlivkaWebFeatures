<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <meta name="viewport" content="width=1200, user-scalable=yes">
  <title>Table - Slivka Points Center</title>
  <script type="text/javascript" src="DataTables/media/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="js/pointsTable.js"></script>
  <style type="text/css">
  table.dataTable td{
  	padding: 0px;
    text-align: center;
    border-top: 1px solid gray;
  }
  table.dataTable td.name{
    text-align: left;
  }
  table.dataTable{
    margin-top: 100px;
  }
  .col{
    font-size: 10px;
    line-height: 14px;
    margin: 0px 10px;
  }
  .nolabel{
    color: #FFFFFF;
  }
  .header-row{
    min-width: 1000px;
    font-weight: bold;
    padding-left: 138px; /*193*/
    list-style: none;
    margin-top: 114px;
    padding-top: 0px;
    position: absolute;
  }
  .header-row li{
    text-align: left;
    float: left;
    width: 16px; /*42*/
    display: block;
    position: relative;
    -webkit-transform: rotate(-55deg) translate(-24px,0px);
    -moz-transform: rotate(-55deg) translate(-24px,0px);
    white-space: nowrap;
  }

  .red{
    background-color: #FF8F8F;
  }
  .green{
    background-color: #00D10E;
  }
  .blue{
    background-color: #99CCFF;
  }
  .gold{
    background-color: #FFCC66;
  }
  </style>
</head>
<body style="min-width: 1000px;">
	<div class="container-fluid">
    <div class="offset1 span12 content">
      <?php include('nav.html'); ?>
      <div class="row-fluid">
        <div class="span12 col">
          <div class="header-row" id="columns"></div>
          <table id="table"></table>
        </div>
      </div>
    </div>
	</div>
</body>
</html>
