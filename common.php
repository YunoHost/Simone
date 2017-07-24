<?php

function _log($id, $action, $message)
{
    global $log_file;

    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date("Y/m/d h:i:s");
    $page = file_get_contents('_pending_contrib/'.$id.'/page');
    $email = file_get_contents('_pending_contrib/'.$id.'/email');

    $full_message = "[".$time."] ".$ip." ".$action." ".$id." from ".$email." for page ".$page." : ".$message."\n";

    file_put_contents($log_file, $full_message, FILE_APPEND);
}

function _takeLock()
{
    $token = "./lock";
    for ($i = 1; $i <= 5; $i++) 
    {
        if (file_exists($token))
            sleep(2);
    }

    if (file_exists($token))
        return false;
    else
    {
        exec("touch ".$token);
        return true;
    }
}

function _releaseLock()
{
    $token = "./lock";
    exec("rm -f ".$token);
}

?>
