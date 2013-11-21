<?php
ini_set('display_errors', 'On');

$msg = "";

function backupRec($path_val) {

    $date = date("m-d-Y");
    $dateStart = strtotime(date('Y-m-d 16:i:s') . ' -1 day');
    $dateEnd = strtotime(date('Y-m-d 04:i:s'));

    $path = $f_path = realpath($path_val);
    //$b_path = "/home/amirm/Documents/backup/";
    $b_path = "/var/www/backup/";
    $b_path = "backup/";
    $backup = $b_path . $date . "_" . basename($path_val);

    $skip_files = array('private.xml');

//    echo "Today $date <br>";
//    echo "todaysStart " . date("Y-m-d H:i:s", $dateStart) . " <br>";
//    echo "todaysEnd " . date("Y-m-d H:i:s", $dateEnd) . " <br>";

    if (!is_dir($backup)) {
        mkdir($backup, 0777);
    }

    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

    // Adding slash
    if (substr($path, -1) != "/")
        $path .= "/";

    $len = strlen($path);

    foreach ($objects as $object => $name) {
        $entry = basename($name);
        if ($entry != "." && $entry != "..") {

            $final = $backup . "/" . substr($name, $len);
            if (is_dir($name)) {
                if (!is_dir($final)) {
                    mkdir($final, 0777);
                }
            } else {
                $fileTime = filemtime($name);
                //$fileDate = date("Y-m-d H:i:s", $fileTime);
                if ($fileTime >= $dateStart && $fileTime <= $dateEnd) {
                    //echo "in :: $final :: $fileDate <br>";
                    if (!in_array($entry, $skip_files))
                        copy($name, $final);
                }
            }
        }
    }

    // changing permission
    exec("chmod -R 777 $backup");

    // checkout backup folder created
    $objects_all = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backup), RecursiveIteratorIterator::CHILD_FIRST);

    // removing all the empty folders
    foreach ($objects_all as $object => $name) {
        $entry = basename($name);
        if ($entry != "." && $entry != "..") {
            if (is_dir_empty($name)) {
                rmdir($name);
            }
        }
    }

    if (is_dir_empty($backup)) {
        rmdir($backup);
    } else {
        $zip = $backup . ".tar.gz";
        exec("tar -czvf $zip $backup");
        exec("rm -rf $backup");
        exec("chmod -R 777 $zip");
        return "UPloaded successfully";
    }
}

function is_dir_empty($dir) {
    if (!is_readable($dir))
        return NULL;
    if (!is_dir($dir))
        return NULL;
    return (count(scandir($dir)) == 2);
}

if (isset($_POST['file_input'])) {
    $msg = backupRec($_POST['file_input']);
}
?>
<br />
<h1><?php echo $msg ?></h1>
<br />
<form name="post_frm" action="" method="post" />
<input name="file_input" type="text" value="" size="100" />
<br />
<button>Submit</button>
</form>

