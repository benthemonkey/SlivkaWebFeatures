<?php
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

// make a note of the current working directory, relative to root.
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

// make a note of the location of the upload form in case we need it
$uploadForm = 'http://' . $_SERVER['HTTP_HOST'] . $directory_self . 'upload.form.php';

// make a note of the location of the success page
$uploadSuccess = '/points/admin.php?success=true';

// Now let's deal with the upload

// possible PHP upload errors
$errors = array(1 => 'php.ini max file size exceeded',
                2 => 'html form max file size exceeded',
                3 => 'file upload was only partial',
                4 => 'no file was attached');

//check file size
if ($_FILES['file']['size'] > 20000) {
    error('file is too large');
}

// check the upload form was actually submitted else print the form
isset($_POST['nu_email']) or isset($_POST['fellow'])
    or error('the upload form is neaded');

// check for PHP's built-in uploading errors
($_FILES['file']['error'] == 0)
    or error($errors[$_FILES['file']['error']]);

// check that the file we are working on really was the subject of an HTTP upload
@is_uploaded_file($_FILES['file']['tmp_name'])
    or error('not an HTTP upload');

// validation... since this is an image upload script we should run a check
// to make sure the uploaded file is in fact an image. Here is a simple check:
// getimagesize() returns false if the file tested is not an image.
@getimagesize($_FILES['file']['tmp_name'])
    or error('only image uploads are allowed');

if ($_POST['type'] == 'slivkan-photo') {
    $name = $_POST['nu_email'];
} else {
    $name = $_POST['fellow'];
}

$now = time();
while (file_exists($uploadFilename = __DIR__.'/../img/slivkans/'.$name.'-'.$now)) {
    $now++;
}

$images = $_FILES['file']['tmp_name'];
$ext = resize(200, $uploadFilename, $_FILES['file']['tmp_name']);

if ($_POST['type'] == 'slivkan-photo') {
    $points_center->updateSlivkanPhoto($name, $name.'-'.$now.'.'.$ext);
} else {
    $points_center->updateFellowPhoto($name, $name.'-'.$now.'.'.$ext);
}

// If you got this far, everything has worked and the file has been successfully saved.
// We are now going to redirect the client to a success page.
header('Location: ' . $uploadSuccess);

function resize($newWidth, $targetFile, $originalFile)
{
    $info = getimagesize($originalFile);
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image_create_func = 'imagecreatefromjpeg';
            $image_save_func = 'imagejpeg';
            $new_image_ext = 'jpg';
            break;

        case 'image/png':
            $image_create_func = 'imagecreatefrompng';
            $image_save_func = 'imagepng';
            $new_image_ext = 'png';
            break;

        case 'image/gif':
            $image_create_func = 'imagecreatefromgif';
            $image_save_func = 'imagegif';
            $new_image_ext = 'gif';
            break;

        default:
            throw Exception('Unknown image type.');
    }

    $img = $image_create_func($originalFile);
    list($width, $height) = getimagesize($originalFile);

    $newHeight = ($height / $width) * $newWidth;
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if (file_exists($targetFile)) {
            unlink($targetFile);
    }
    $image_save_func($tmp, $targetFile.'.'.$new_image_ext);

    //dumb: returning the extension
    return $new_image_ext;
}

// The following function is an error handler which is used
// to output an HTML error page if the file upload fails
function error($error)
{
    header("Refresh: 5; URL=".$uploadForm."");
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"'."\n".
    '"http://www.w3.org/TR/html4/strict.dtd">'."\n".
    '<html lang="en">'."\n".
    '    <head>'."\n".
    '        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">'."\n".
    '    <title>Upload error</title>'."\n".
    '    </head>'."\n".
    '    <body>'."\n".
    '    <div id="Upload">'."\n".
    '        <h1>Upload failure</h1>'."\n".
    '        <p>An error has occurred: '."\n".
    '        <span class="red">' . $error . '...</span>'."\n".
    '         The upload form is reloading</p>'."\n".
    '     </div>'."\n".
    '</html>';
    exit;
} // end error handler
