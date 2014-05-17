<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Sqlite Web Master - Login</title>
    <link rel="stylesheet" type="text/css" href="{site:root}/res/icons.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/{site:theme}.css" />
    <script type="text/javascript" src="{site:root}/res/jquery.min.js"></script>
    <script type="text/javascript" src="{site:root}/res/common.js"></script>
</head>
<body>
<div id="topbar">
    <span>Sqlite Web Master - Login</span>
</div>
{+when:ok a=is_success}
    <div class="message-success m-jump"
        data-jump="{url:base i=ref-i database=ref-database source=ref-source}"
        style="margin: 120px auto; padding: 10px 20px; width:400px;">
        Login Success!
    </div>
    <br/>
{-when:ok}
{+when:no a=is_success}
<div class="panel">
<form action="" method="post" class="m-form">
    <div class="header">Login</div>
    {+when:ok a=errors}
    <div class="message-fail">
        <ul>
        {+errors}
            <li>{content}</li>
        {-errors}
        </ul>
    </div>
    {-when:ok}
    <p><label>Username</label> <input name="user" type="text" class="text"/></p>
    <p><label>Password</label> <input name="pass" type="password" class="text"/></p>
    <div class="footer">
        <input type="submit" class="hide-mask"
            hidefocus="true" tabindex="-1" title="Press Enter to Login"/>
        <label class="inline"><input type="checkbox" name="remember" />Remember Me</label>
        <a href="javascript:void(0);" class="btn-submit m-apply">Login</a>
    </div>
</form>
</div>
{-when:no}
</body>
</html>
