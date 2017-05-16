<?php

// FOR DEBUGGING, NOT FOR PROD !!
ini_set('display_errors', 'On');
error_reporting(E_ALL);

$email_from  = "yunobot@some.domain.tld";
$hub = "/var/www/Simone/hub/bin/hub";

    function validateInputs()
    {
        // FIXME sanitize inputs ?

        if (($_SERVER['REQUEST_METHOD'] != 'GET')
        ||  ! isset($_GET['id'])
        ||  ! isset($_GET['token']))
        {
            return "Invalid request.";
        }

        // Get POST data
        $id = $_GET["id"];
        $token = $_GET["token"];

        // Validate id format
        $d = datetime::createfromformat('Y-m-d_h-i-s', $id)->format("Y-m-d_h-i-s");
        if ($d != $id)
        {
            return "Invalid id format.";
        }

        // Validate token format
        if (!preg_match('/^[a-z0-9_]+$/', $token))
        {
            return "Invalid token format.";
        }

        // Confirm that id and token are right
        if (!is_dir("_pending/".$id)
        || file_get_contents('_pending/'.$id.'/token') != $token)
        {
            return "Invalid id or token.";
        }

        return "";
    }

    function makePullRequest()
    {
        global $email_from, $hub;

        // Get POST data
        $id = $_GET["id"];
        $page = file_get_contents('_pending/'.$id.'/page').".md";
        $descr = file_get_contents('_pending/'.$id.'/descr');
        $PRurl = '_pending/'.$id.'/pr';

        // Create submission directory
        $sshkey = dirname(__FILE__)."/.ssh/id_rsa";

        $branch = 'anonymous-'.$id;

        $c = 'cd _botclone && '.
             'git checkout master && '.
             'sudo git pull && '.
             'git checkout -b '.$branch.' && '.
             'cd .. && '.
             'cp _pending/'.$id.'/content _botclone/'.$page.' && '.
             'cd _botclone/ && '.
             'git add '.$page.' && '.
             'export GIT_AUTHOR_NAME="Yunobot" && '.
             'export GIT_AUTHOR_EMAIL="'.$email_from.'" && '.
             'export GIT_COMMITTER_NAME="Yunobot" && '.
             'export GIT_COMMITTER_EMAIL="'.$email_from.'" && '.
             'git commit '.$page.' -m "'.$descr.'" && '.
             'sudo git push origin '.$branch.' && '.
             'sudo '.$hub.' pull-request -m "[Anonymous contrib] '.$descr.'" > ../'.$PRurl;

        shell_exec($c);
        shell_exec("cd _botclone && git checkout master");

        if (file_exists($PRurl))
        {
            return file_get_contents($PRurl);
        }
        else
        {
            return "";
        }
    }

    function sendMail($PRurl)
    {
        global $email_from, $simone_root;

        $id = $_GET["id"];
        $email = file_get_contents('_pending/'.$id.'/email');

        // From, to, subject, message ...
        $email_to      = $email;
        $email_subject = "[Yunohost documentation] Submission awaiting approval !";
        $email_message = "Your submission is now awaiting approval on github.\nYou can follow its status here :\n".$PRurl;

        // Create email headers
        $headers = 'From: '         .$email_from."\r\n".
                   'Reply-To: '     .$email_from."\r\n".
                   'X-Mailer: PHP/' .phpversion();

        // Actually send the mail
        @mail($email_to, $email_subject, $email_message, $headers);
    }

    function rrmdir($dir)
    {
        if (is_dir($dir))
        {
            $objects = scandir($dir);
            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (is_dir($dir."/".$object))
                        rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    function deletePending()
    {
        $id = $_GET["id"];
        rrmdir('_pending/'.$id);
    }

    $inputErrors = validateInputs();
    if ($inputErrors != "")
    {
        header($_SERVER['SERVER_PROTOCOL'].' 403 FORBIDDEN');
        echo $inputErrors;
        return;
    }
    else
    {
        $PRurl = makePullRequest();

        if ($PRurl == "")
        {
            echo "Woopsies ! Unable to create the Pull Request on Github. Please contact the Yunohost support to fix the situation.";
            return;
        }
        else
        {
            sendMail($PRurl);
            deletePending();
            echo "Succesfully created a Pull Request on Github !\n".$PRurl;
        }
    }

?>
