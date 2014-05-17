{+unit:css}
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/twilight.css" />
{-unit:css}
<form action="" method="post" class="m-form">
    <div class="console-panel">
        <div class="header">
            <div id="snippets" class="snippets">
                <div class="menu">
{+snippets}
                    <a href="javascript:void(0);" class="btn"><small>{title}</small></a>
{-snippets}
                </div>
                <div class="subs">
{+snippets}
                    <ul class="hide">
    {+value}
                        <li title="{value}">{title}</li>
    {-value}
                    </ul>
{-snippets}
                </div>
            </div>
            <span style="line-height:160%;">SQL Statement</span>
        </div>
        <textarea id="m-code" name="statement" class="m-code">{statement}</textarea>
        <div class="footer">
            <table>
                <tr>
                    <td>
                        <div class="result">Result: {result}</div>
                    </td>
                    <td width="300">
                    </td>
                    <td width="100" class="align-right">
                        <a href="javascript:void(0);" class="btn-submit m-apply" title="Execute SQL Statement">
                            <strong>Run</strong>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>    
{+when:ok a=content_data}
<div class="fieldset">
    <div class="field-body-wrapper">
    <table id="m-item-list" class="item-list">
        <thead>
            <tr>
    {+columns}
                <th nowrap="nowrap">{name}</th>
    {-columns}
            </tr>
        </thead>
        <tbody>
    {+content_data}
            <tr class="{tick:even}">
        {+fields}
                <td class="data-type-{type} data-size-{size}">
            {+when:eq a=type b='4'}
                    [{text}]
            {-when:eq}
            {+when:ne a=type b='4'}
                    {text}
            {-when:ne}
                </td>
        {-fields}
            </tr>
    {-content_data}
        </tbody>
        <tfoot>
            <tr>
    {+columns}
                <th>{name}</th>
    {-columns}
            </tr>
        </tfoot>
    </table>
    </div>
</div>
{-when:ok}
{+when:no a=content_data}
<fieldset>
    <div class="console-empty">(No data)</div>
</fieldset>
{-when:no}

<script type="text/javascript" src="{site:root}/res/codemirror/codemirror.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/matchbrackets.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/sqlite.js"></script>
<script>
    $(function(){
        $('#snippets').menu({
            'select': function(element){
                var editor = window.ceditors['m-code'];
                var value = element.attr('title');
                editor.replaceSelection(value);
                var cursor = editor.getCursor(false);
                editor.setCursor(cursor);
            }
        });
        
    });
</script>
