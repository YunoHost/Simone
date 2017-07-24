<?php

include "config/config.php";
include "common.php";

    function validateInputs()
    {
        if (($_SERVER['REQUEST_METHOD'] != 'POST') 
        ||  ! isset($_POST['email'])
        ||  ! isset($_POST['page'])
        ||  ! isset($_POST['content'])
        ||  ! isset($_POST['descr']))
        {
            return "Invalid request.";
        }

        // Get POST data
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $descr = filter_var($_POST["descr"], FILTER_SANITIZE_STRING);
        $page  = filter_var($_POST["page"],  FILTER_SANITIZE_STRING);
        $content = $_POST["content"];

        // Validate page name
        if (!preg_match('/^[A-Za-z0-9_]+$/', $page)) 
        {
            return "Invalid page name.";
        }

        // Don't allow contents larger than 50k characters
        if (strlen($content) > 50000)
        {
            return "Content too long.";
        }

        // Check the email syntax is valid
        if (($email == "") || (!filter_var($email, FILTER_VALIDATE_EMAIL)))
        {
            return "Invalid email syntax.";
        } 

        // Check that the email domain has a MX record
        $email_domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($email_domain, 'MX')) 
        {
            return "Invalid email domain.";
        } 

        // Don't allow empty descriptions or decriptions too long
        if (strlen($descr) <= 0)
        {
            return "Description empty.";
        }
        if (strlen($descr) > 150)
        {
            return "Description too long.";
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date("Y-m-d");

        $c = "grep ^".$ip."$ _pending_contrib/*/ip | wc -l";
        $numberOfPendingSubmissionsWithThisIp = shell_exec($c);
        if ($numberOfPendingSubmissionsWithThisIp > 5)
        {
            return "Too many submissions already ongoing with this ip.";
        }

        global $log_file;
        //    grep " 111.222.333.444 SUBMIT 201x_MM_DD" /var/log/simone.log | wc -l
        $c = "grep \" ".$ip." SUBMIT ".$today."\" ".$log_file." | wc -l";
        $numberOfSubmissionsToday = shell_exec($c);
        if ($numberOfSubmissionsToday > 10)
        {
            return "Too many submissions from this IP today! Please try again tomorrow or consider using Git/GitHub directly.";
        }

        return "";
    }

    function saveSubmission($id, $token)
    {
        // Get POST data
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $descr = filter_var($_POST["descr"], FILTER_SANITIZE_STRING);
        $page  = filter_var($_POST["page"],  FILTER_SANITIZE_STRING);
        $content = $_POST["content"];

        // Also save the submitter IP's
        $ip = $_SERVER['REMOTE_ADDR'];

        // Create submission directory
        $subdir = dirname(__FILE__)."/_pending_contrib/".$id;
    
        exec('mkdir -p '.$subdir);

        file_put_contents($subdir.'/email',   $email);
        file_put_contents($subdir.'/page',    $page);
        file_put_contents($subdir.'/content', $content);
        file_put_contents($subdir.'/descr',   $descr);
        file_put_contents($subdir.'/token',   $token);
        file_put_contents($subdir.'/ip',      $ip);
    }

    function sendMail($id, $token)
    {
        global $email_from, $simone_root;

        // From, to, subject, message ...
        $email_to      = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $email_subject = "[Yunohost doc] Please confirm your submission !";
        $email_message = "Thank you for your recent contribution on the Yunohost documentation !\n\n In order to be able to validate it, we just need you to click on the following link to confirm that your email is valid :\n".$simone_root."confirm.php?id=".$id."&token=".$token."\n";

        // Create email headers
        $headers = 'From: '         .$email_from."\r\n".
                   'Reply-To: '     .$email_from."\r\n" .
                   'X-Mailer: PHP/' .phpversion();

        // Actually send the mail
        @mail($email_to, $email_subject, $email_message, $headers);  
    }

    $inputError = validateInputs();
    if ($inputError != "")
    {
        header($_SERVER['SERVER_PROTOCOL'].' 403 FORBIDDEN');
        echo $inputError;
        return;
    }
    else
    {
        if (! _takeLock())
        {
            header($_SERVER['SERVER_PROTOCOL'].' 403 FORBIDDEN');
            echo "Could not acquire lock.";
            return;
        }

        // Create an ID for this submission
        $id = date("Y-m-d_h-i-s");
        // Create a random token for validation 
        $token = md5(uniqid(rand(), true));

        saveSubmission($id, $token);
        _releaseLock();
        
        _log($id, "SUBMIT", "Pending");
        sendMail($id, $token);
    }

?>
