<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Sqlite Web Master - Pass</title>
    <link rel="stylesheet" type="text/css" href="{site:root}/res/icons.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/{site:theme}.css" />
    <script type="text/javascript" src="{site:root}/res/jquery.min.js"></script>
    <script type="text/javascript" src="{site:root}/res/common.js"></script>
</head>
<body>
<div id="topbar">
    <a href="{url:base i='login' out='1'}" title="Exit" class="icon-white icon-off"></a>
    <a href="{url:base}" title="Password Tool" class="icon-white icon-home"></a>
    <span>{title}</span>
</div>
<div class="panel" style="width:600px;">
<form action="" method="post" class="m-form">
    <div class="header">Sqlite Web Master - Generate Pass Code</div>
    <p>
        <label class="inline">User</label><input type="text" class="text" name="user" value="{user}" />
        <label class="inline">Pass</label><input type="text" class="text" name="pass" value="{pass}" />
        <a href="javascript:void(0);" class="btn-submit m-apply">Generate</a>
    </p>
    <fieldset style="width: 96%;">
        <p><strong>* Manually: Replace SEC_* parts in etc/config.php with the follows:</strong></p>
{+when:ok a=errors}
        <div class="message-fail">
            <ul>
    {+errors}
            <li>{content}</li>
    {-errors}
            </ul>
        </div>
    
{-when:ok}
        <div class="code">
            <pre>{code encode=no}</pre>
        </div>
    </fieldset>
</form>
</div>
</body>
</html>
