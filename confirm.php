<?php

include "config/config.php";
include "common.php";

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

        // Validate id format (YYYY-MM-DD_HH-MM-SS)
        if (!preg_match('/^[0-9_-]{19}$/', $id))
        {
            return "Invalid id format.";
        }

        // Validate token format
        if (!preg_match('/^[a-z0-9]{32}$/', $token))
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

    function makePullRequest($id)
    {
        $PRurl = '_pending/'.$id.'/pr';
        shell_exec("./createPR.sh ".$id);
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

    function sendMail($id, $PRurl)
    {
        global $email_from, $simone_root;

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
        $id = $_GET["id"];
        _takeLock();
        $PRurl = makePullRequest($id);
        _releaseLock();

        if ($PRurl == "")
        {
            echo "Woopsies ! Unable to create the Pull Request on Github. Please contact the Yunohost support to fix the situation.";
            _log($id, "CONFIRM", "Failed : Unable to create PR");
            return;
        }
        else
        {
            sendMail($id, $PRurl);
            _log($id, "CONFIRM", "Success ! PR ".$PRurl." created");
            deletePending();
            echo "Succesfully created a Pull Request on Github !\n".$PRurl;
        }
    }

?>
