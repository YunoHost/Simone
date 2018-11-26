<?php
    // Include markdown library
    include_once 'markdown.php';

    // Load configuration
    $config = json_decode(file_get_contents('config/config.json'), true);

    // Get language from browser
    if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER))
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    } else {
        $lang = '';
    }

    $suffix = '_'.$lang;
    if ($lang == '' || $lang == $config['defaultLanguage'] || !array_key_exists($lang, $config['languages'])) {
        $suffix = '';
    }

    // Get route
    if (isset($_GET['uri'])) {
        $rURI = $_GET['uri'];
    } else {
        $rURI = $_SERVER["REQUEST_URI"];
    }

    // If people try to access folders ?
    if (substr($rURI, -1) === '/')  {
       $rURI = substr($rURI, -1);
    }

    $force_lang = false;
    // If asked uri is '/', we want to show the index page
    if ($rURI === '/') {
       $uri = 'index';
    // If asked uri ends with _ uh, remove it ? (dunno why that would happen..)
    } elseif (substr($rURI, -1) === '_')  {
        $uri = substr($rURI, 1, -1);
    // If asked uri is something like pagename_fr, the user explictly stated the language
    // so we extract it and use it instead of the info from HTTP_ACCEPT_LANGUAGE
    } elseif (substr($rURI, -3, 1) === '_') {
        $uri = substr($rURI, 1, -3);
        $lang = substr($rURI, -2);
        $suffix = substr($rURI, -3);
        $force_lang = true;
    // Otherwise simply remove the / in front of the uri
    } else {
        $uri = substr($rURI, 1);
    }

    // Construct title
    $title = $config['siteName'].' • '.$uri;

    // Try to get markdown file
    $markdown = "";
    if (file_exists('_pages/'.$uri.$suffix.'.md'))
    {
       $markdown = file_get_contents('_pages/'.$uri.$suffix.'.md');
    // Fallback to default language
    } elseif (($force_lang == false) && (file_exists('_pages/'.$uri.'.md'))) {
       $markdown = file_get_contents('_pages/'.$uri.'.md');
    }

    // 404
    if (!$markdown) {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        die('Not Found');
    }

    // Compile HTML content
    $content = Markdown($markdown);
?><!DOCTYPE html>
<html lang="<?php echo $lang ?>">
<head>
<title><?php echo $title ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="_assets/favicon.ico">

    <link rel="stylesheet" type="text/css" href="_css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="_css/hl.css">
    <link rel="stylesheet" type="text/css" href="_css/solarized_dark.min.css">
    <link rel="stylesheet" type="text/css" href="_css/fonts.css">
    <link rel="stylesheet" type="text/css" href="_css/style.css">
    <!-- Always define js console -->
    <script type="text/javascript">if (typeof console === "undefined" || typeof console.log === "undefined") {console = {};console.log = function () {};}</script>

    <script type="text/javascript" src="_js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="_js/sammy-latest.min.js"></script>
    <script type="text/javascript" src="_js/sammy.storage.js"></script>
    <script type="text/javascript" src="_js/highlight.min.js"></script>
    <script type="text/javascript" src="_js/marked.js"></script>
    <script type="text/javascript" src="_js/bootstrap.min.js"></script>
    <script type="text/javascript" src="_js/app.js"></script>
</head>

<body>

    <div id="wrapper">
        <div id="win" class="alert alert-success" style="display: none" data-i18n="modificationSaved"></div>
        <div id="fail" class="alert alert-danger" style="display: none" data-i18n="modificationFailed"></div>
        <div id="form" style="display: none">
            <textarea cols="80" rows="40"></textarea>
        </div>
        <div id="logo"><a href="/" data-toggle="tooltip" data-placement="auto bottom" title="Index"><img src="_assets/logo.png"></a></div>
        <div id="content">
            <?php echo $content ?>
        </div>
    </div>

    <footer class="cf">
       @YunoHost on
       • <a href="https://mastodon.social/@yunohost">Mastodon</a>
       • <a href="https://framasphere.org/people/01868d20330c013459cf2a0000053625">Diaspora*</a>
       • <a href="https://twitter.com/yunohost">Twitter</a>
       • <a href="/docs" data-i18n="sitemap">Sitemap</a>
       • Datalove <span class="glyphicon glyphicon-heart"></span>
    </footer>

    <div class="actions" style="display: none">
        <a class="btn btn-default" id="edit">
            <span class="glyphicon glyphicon-pencil"></span>&nbsp; <span data-i18n="edit"></span>
        </a>
        <a class="btn btn-default" id="preview">
            <span class="glyphicon glyphicon-eye-open"></span>&nbsp; <span data-i18n="preview"></span>
        </a>
        <button type="button" class="btn btn-primary" id="send" data-toggle="modal" data-target="#sendModal">
            <span class="glyphicon glyphicon-ok"></span>&nbsp; <span data-i18n="send"></span>
        </button>
        <a class="btn btn-danger" id="back">
            <span class="glyphicon glyphicon-ban-circle"></span>&nbsp; <span data-i18n="revert"></span>
        </a>
    </div>

    <div class="languages" style="display: none">
        <div class="btn-group dropup">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-globe"></span>&nbsp; <span data-i18n="languages"></span> &nbsp;<span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
          </ul>
        </div>
        <a class="btn btn-default" id="help" target="_blank" href="/help">
            <span class="glyphicon glyphicon-comment"></span>&nbsp; <span data-i18n="needhelp"></span>
        </a>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="sendModal" tabindex="-1" role="dialog" aria-labelledby="sendModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 class="modal-title text-center" id="sendModalLabel" data-i18n="sendModifications"></h3>
          </div>
          <form class="form-horizontal" method="POST" role="form">
              <div class="modal-body">
                  <div class="form-group">
                      <label for="email" class="col-sm-4 control-label">
                          <span data-i18n="email"></span>
                          <span class="glyphicon glyphicon-question-sign" data-i18n="[title]whyEmail" title=""></span>
                      </label>

                      <div class="col-sm-8">
                          <input type="email" class="form-control" id="email" name="email" placeholder="john@doe.org" required>
                      </div>
                  </div> 
                  <div class="form-group">
                      <label for="descr" class="col-sm-4 control-label" data-i18n="description"></label>
                      <div class="col-sm-8">
                          <textarea maxlength="150" rows="2" class="form-control" id="descr" name="descr" data-i18n="[placeholder]tellUsWhatYouDid"></textarea>
                      </div>
                  </div>
                  <div class="text-center">
                      <button type="button" class="btn btn-primary" id="reallysend">
                         <span class="glyphicon glyphicon-send"></span>&nbsp;
                         <span data-i18n="sendChanges"></span>
                      </button>
                  </div>
              </div>
              <div id="sendFail" class="alert alert-danger text-center" style="width:90%; margin-left:auto; margin-right:auto; margin-top:1em; margin-bottom:1em;display: none"></div>
              <div class="alert alert-info text-center" style="width:90%; margin-left:auto; margin-right:auto; margin-top:1em; margin-bottom:1em;">
                  <span class="glyphicon glyphicon-info-sign"></span>&nbsp;<span data-i18n="considerUsingGithub"></span></div>
          </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

</body>
</html>
