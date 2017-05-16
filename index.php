<?php
    // Include markdown library
    include_once 'markdown.php';

    // Load configuration
    $config = json_decode(file_get_contents('config.json'), true);

    // Get language from browser
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
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

    if ($rURI === '/') {
        $uri = 'index'.$suffix;
    // FIXME : for if simone is on "domain.tld/simeone/"
    } elseif ($rURI === '/simone/') {
        $uri = 'index'.$suffix;
    } elseif (substr($rURI, -1) === '_')  {
        $uri = substr($rURI, 1, -1);
    } else {
        $uri = substr($rURI, 1);
    }

    // Construct title
    $title = $config['siteName'].' • '.$uri;

    // Try to get markdown file
    $markdown = file_get_contents('_pages/'.$uri.'.md');

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
    <link rel="shortcut icon" href="/favicon.ico">

    <link rel="stylesheet" type="text/css" href="_css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="_css/hl.css">
    <link rel="stylesheet" type="text/css" href="_css/solarized_dark.min.css">
    <link rel="stylesheet" type="text/css" href="_css/fonts.css">
    <link rel="stylesheet" type="text/css" href="_css/style.css">
    <!-- Always define js console -->
    <script type="text/javascript">if (typeof console === "undefined" || typeof console.log === "undefined") {console = {};console.log = function () {};}</script>

    <!-- Piwik -->
    <script type="text/javascript">
      var _paq = _paq || [];
      _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
      _paq.push(["setCookieDomain", "*.yunohost.org"]);
      _paq.push(["trackPageView"]);
      _paq.push(["enableLinkTracking"]);

      (function() {
        var u=(("https:" == document.location.protocol) ? "https" : "http") + "://piwik.beudibox.fr/";
        _paq.push(["setTrackerUrl", u+"piwik.php"]);
        _paq.push(["setSiteId", "1"]);
        var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
        g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <!-- End Piwik Code -->

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
        <div id="logo"><a href="/" data-toggle="tooltip" data-placement="auto bottom" title="Index"><img src="logo.png"></a></div>
        <div id="content">
            <?php echo $content ?>
        </div>
    </div>

    <footer class="cf">
       @YunoHost on
       <a href="https://framasphere.org/people/01868d20330c013459cf2a0000053625">Diaspora*</a>
       • <a href="https://twitter.com/yunohost">Twitter</a>
       • <a href="/sitemap" data-i18n="sitemap">Sitemap</a>
       • <a href="/support" data-i18n="support">Support</a>
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
        <a class="btn btn-default" id="help" target="_blank" href="/help">?</a>
        <div class="btn-group dropup">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-globe"></span>&nbsp; <span data-i18n="languages"></span> &nbsp;<span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
          </ul>
        </div>
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
                          <span class="glyphicon glyphicon-question-sign"
                                title="To allow us to fight spam, you will be asked to validate you submission be email. We'll also keep you informed of it's progress ! We won't make your email public and won't use it for anything else !"></span>
                      </label>

                      <div class="col-sm-8">
                          <input type="email" class="form-control" id="email" name="email" placeholder="john@doe.org" required>
                      </div>
                  </div> 
                  <div class="form-group">
                      <label for="descr" class="col-sm-4 control-label" data-i18n="description"></label>
                      <div class="col-sm-8">
                          <textarea maxlength="150" rows="2" class="form-control" id="descr" name="descr" placeholder="Tell us what you did there !"></textarea>
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
                  <span class="glyphicon glyphicon-info-sign"></span> ProTip™ : if you
                  plan to contribute often to the documentation, consider using
                  Git/Github directly !
              </div>
          </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

</body>
</html>
