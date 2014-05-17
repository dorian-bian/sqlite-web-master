<ul id="tabs">
    {+tabs}
    <li{when:ok a=active mode=active}>
        <a href="{url:base i=i database=site:database}">{title}</a>
    </li>
    {-tabs}
</ul>
<div id="content">
    {content encode=no}
</div>
