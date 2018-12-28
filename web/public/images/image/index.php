<?php

include '../../includes/dbconnect.php';

if ($_GET['code']) {
    
    $image = mysql_single('
        SELECT * FROM images WHERE BINARY name = \''.  mysql_real_escape_string($_GET['code']).'\'
        ',__LINE__.__FILE__);
    
    if ($image->id && $image->userid && $image->image) {
        $file = $image->userid.'/'.$image->image;
        
        if (file_exists($file)) {
            $imginfo = getimagesize($file);

            header('Content-type: '.$imginfo['mime']);
            header('Content-length: ' . filesize($file));

            readfile($file);
        } else {
            echo "File does not exist!";
        }
    } else {
        echo "Not found!";
    }
    
} else {
    echo "No code sent!";
}

?>