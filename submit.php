<?php

// FOR DEBUGGING, NOT FOR PROD !!
ini_set('display_errors', 'On');
error_reporting(E_ALL);


    $email_from  = "yunobot@some.domain.tld";
    $simone_root = "https://some.domain.tld/simone/";

    echo var_dump($_POST);

    function validateInputs()
    {
        // FIXME sanitize inputs ?
        // FIXME limit 'descr' characters and length ?


        if (($_SERVER['REQUEST_METHOD'] != 'POST') 
        ||  ! isset($_POST['email'])
        ||  ! isset($_POST['page'])
        ||  ! isset($_POST['content'])
        ||  ! isset($_POST['descr']))
        {
            return "Invalid request.";
        }

        // Get POST data
        $email = $_POST["email"];
        $page = $_POST["page"];
        $content = $_POST["content"];
        $descr = $_POST["descr"];

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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            return "Invalid email syntax.";
        } 

        // Check that the email domain has a MX record
        $email_domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($email_domain, 'MX')) 
        {
            return "Invalid email domain.";
        } 

        // Don't allow empty descriptions
        if (strlen($descr) <= 0)
        {
            return "Description empty.";
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $c = "grep ^".$ip."$ _pending/*/ip | wc -l";
        $numberOfPendingSubmissionsWithThisIp = shell_exec($c);
        if ($numberOfPendingSubmissionsWithThisIp > 5)
        {
            return "Too many submissions already ongoing with this ip.";
        }


        return "";
    }

    function saveSubmission($id, $token)
    {
        // Get POST data
        $email = $_POST["email"];
        $page = $_POST["page"];
        $content = $_POST["content"];
        $descr = $_POST["descr"];

        // Also save the submitter IP's
        $ip = $_SERVER['REMOTE_ADDR'];

        // Create submission directory
        $subdir = dirname(__FILE__)."/_pending/".$id;
    
        exec('mkdir -p '.$subdir);

        file_put_contents($subdir.'/email', $email);
        file_put_contents($subdir.'/page', $page);
        file_put_contents($subdir.'/content', $content);
        file_put_contents($subdir.'/descr', $descr);
        file_put_contents($subdir.'/token', $token);
        file_put_contents($subdir.'/ip', $ip);
    }

    function sendMail($id, $token)
    {
        global $email_from, $simone_root;

        // From, to, subject, message ...
        $email_to      = $_POST["email"];
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
        // Create an ID for this submission
        $id = date("Y-m-d_h-i-s");
        // Create a random token for validation 
        $token = md5(uniqid(rand(), true));

        saveSubmission($id, $token);
        sendMail($id, $token);
    }

?>
