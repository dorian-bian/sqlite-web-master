<ul id="tabs">
    {+tabs}
    <li{when:ok a=active mode=active}>
        <a href="{url:base i=i source=/source_name}">{title}</a>
    </li>
    {-tabs}
</ul>
<div id="content">
    <div class="source-tag"><label>{source_type}</label><span>{source_name}</span></div>
    {content encode=no}
</div>
