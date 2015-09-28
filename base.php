<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'index';
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="/points/build/points-center.css" />
    <style type="text/css">
        body {
            background: transparent url('/points/img/slivka-wallpaper-compressed.jpg') repeat;
        }

        .content {
            background-color: #FFFFFF;
            -webkit-border-radius: 4px;
               -moz-border-radius: 4px;
                    border-radius: 4px;
            -webkit-box-shadow: 7px 7px 7px rgba(50, 50, 50, 1);
               -moz-box-shadow: 7px 7px 7px rgba(50, 50, 50, 1);
                    box-shadow: 7px 7px 7px rgba(50, 50, 50, 1);
        }
    </style>
    <title><?= ucwords($page) ?> - Slivka Points Center</title>
</head>
<body>
<div class="container">
    <div class="content">
        <?php include('nav.html'); ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?php
                if ($page == 'index') {
                    echo '<script type="text/javascript">window.location.href = "/points/breakdown/";</script>';
                } else {
                    include('spc-' . $_GET['page'] . '.php');
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php include('footer.html'); ?>
</body>
</html>
