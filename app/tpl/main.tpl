<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="robots" content="noindex, nofollow" />
    <title>{title}</title>
    <link rel="stylesheet" type="text/css" href="{site:root}/res/icons.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/{site:theme}.css" />
{unit:css}
    <script type="text/javascript" src="{site:root}/res/jquery.min.js"></script>
    <script type="text/javascript" src="{site:root}/res/common.js"></script>
{unit:js}
</head>
<body>
    <noscript>
        <div>Please enable javascript, or this application won't work properly.</div>
    </noscript>
<div id="topbar">
    <a href="{url:base i='login' out='1'}" title="Exit" class="icon-white icon-off"></a>
    <a href="{url:base i='pass'}" title="Password Tool" class="icon-white icon-lock"></a>
    <span>{title}</span>
</div>
<div id="sidebar">
    <ul>
        <li>
            <select class="m-change-database" data-url-base="{url:base}">
{+databases}
                <option title="{path}"{when:eq a=name b=/dbname mode=select} value="{name}">{name}</option>
{-databases}
{+db_groups}
                <optgroup label="{name}">
        {+subs}
                    <option{when:eq a=name b=/dbname mode=select}>{name}</option>
        {-subs}
                    
        {+when:no a=subs}
                    <option disabled="disabled">(empty)</option>
        {-when:no}
                </optgroup>
{-db_groups}
            </select>
        </li>
{+menu}
        <li>
            <a href="{url:base i=i database=site:database}"{when:ok a=active mode=active}><span class="icon {icon}"></span> {title}</a>
    {+when:ok a=subs}
            <ul class="{+when:eq a=icon b='icon-folder-close'}hide{-when:eq}">
        {+subs}
                <li><a href="{url:base i=i database=site:database source=name}"{when:ok a=active mode=active} title="{name}"><span class="{icon}"></span> {title}</a></li>
        {-subs}
            </ul>
    {-when:ok}
        </li>
{-menu}
    </ul>
</div>
<div id="main">
    {content encode=no}
</div>
<div id="footer">
    <span>Execute time: {consume_time}</span> <br/>
    <span>
        <a href="http://glyphicons.com">Glyphicons Free</a>
        (licensed under <a href="http://creativecommons.org/licenses/by/3.0/">CC BY 3.0</a>)
        &ndash; <a href="http://codemirror.net">Codemirror</a>
    </span>
</div>
</body>
</html>
