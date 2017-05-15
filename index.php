<!DOCTYPE html>
<html lang="fr">
<head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport" content="user-scalable=no, width=device-width, height=device-height" />

    <link rel="stylesheet" type="text/css" href="_css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="_css/hl.css">
    <link rel="stylesheet" type="text/css" href="_css/solarized_dark.min.css">
    <link rel="stylesheet" type="text/css" href="_css/fonts.css">
    <link rel="stylesheet" type="text/css" href="_css/style.css">
</head>

<body>

    <div id="wrapper">
        <div id="win" class="alert alert-success" style="display: none" data-i18n="modificationSaved"></div>
        <div id="fail" class="alert alert-danger" style="display: none" data-i18n="modificationFailed"></div>
        <div id="form">
            <textarea cols="80" rows="40"></textarea>
        </div>
        <div id="content"></div>
    </div>

    <div class="actions">
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

    <div class="languages">
        <a class="btn btn-default" id="help" target="_blank" href="#/help">?</a>
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
            <h3 class="modal-title text-center" id="sendModalLabel">Just a few more steps !</h3>
          </div>
          <form class="form-horizontal" method="POST" role="form">
              <div class="modal-body">
                  <div class="form-group">
                      <label for="user" class="col-sm-4 control-label">
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
                          <textarea rows="2" class="form-control" id="descr" name="descr" placeholder="Tell us what you did there !"></textarea>
                      </div>
                  </div>
                  <div class="text-center">
                      <button type="button" class="btn btn-primary" id="reallysend">
                         <span class="glyphicon glyphicon-send"></span>&nbsp;
                         <span data-i18n="sendChanges"></span>
                      </button>
                  </div>
              </div>
              <div class="alert alert-info text-center" style="width:90%; margin-left:auto;
              margin-right:auto; margin-top:1em; margin-bottom:1em;">
                  <span class="glyphicon glyphicon-info-sign"></span> ProTip™ : if you
                  plan to contribute often to the documentation, consider using
                  Git/Github directly !
              </div>
          </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <script type="text/javascript" src="_js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="_js/sammy-latest.min.js"></script>
    <script type="text/javascript" src="_js/sammy.storage.js"></script>
    <script type="text/javascript" src="_js/highlight.min.js"></script>
    <script type="text/javascript" src="_js/marked.js"></script>
    <script type="text/javascript" src="_js/bootstrap.min.js"></script>
    <script type="text/javascript" src="_js/app.js"></script>
</body>

</html>
