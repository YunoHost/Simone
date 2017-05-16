
<?php

function _log($id, $action, $message)
{
    global $log_file;

    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date("Y/m/d h:i:s");
    $page = file_get_contents('_pending/'.$id.'/page');
    $email = file_get_contents('_pending/'.$id.'/email');

    $full_message = "[".$time."] ".$ip." ".$action." ".$id." from ".$email." for page ".$page." : ".$message."\n";

    file_put_contents($log_file, $full_message, FILE_APPEND);
}

?>
