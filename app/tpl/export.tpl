{+unit:css}
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/twilight.css" />
{-unit:css}
<ul class="sub-tabs">
{+tabs}
    <li{when:ok a=active mode=active}>
        <a href="{url:base i='export' source=/source_name type=type}">{title}</a>
    </li>
{-tabs}
</ul>
{+when:no a=site:is_post}
<form action="" method="post" class="m-form">
    {+when:ok a=/sql-mode}
    <fieldset>
        <legend><a href="{url:self sql-mode=''}">Source</a> | <strong>SQL Mode</strong></legend>
        <textarea id="m-code" name="sql-code" class="m-code">{sql-code}</textarea>
    </fieldset>
    {-when:ok}
    {+when:no a=/sql-mode}
    <fieldset>
        <legend>
            <strong>Source</strong>
        {+when:eq a=/type b='csv'}
            | <a href="{url:self sql-mode='sql'}">SQL Mode</a>
         {-when:eq}
        </legend>
        <ul>
        {+when:no a=/source}
            {+when:eq a=/type b='sql'}
            <li>
                <label>
                    <input class="chk-all-subs" type="checkbox" checked="checked" />
                    All schemas: tables + views
                </label>
                <a id="epd-schemas" href="javascript:void(0);" class="icon-folder-close"></a>
                <ul class="hide">
                    <li>
                        <label><input class="chk-all-subs" type="checkbox"  checked="checked" />tables</label>
                        <div class="inner-block">
                {+tables}
                            <label><input type="checkbox" checked="checked" name="schemas[]" value="{name}" />{name}</label>
                {-tables}
                        </div>
                    </li>
                    <li>
                        <label><input class="chk-all-subs" type="checkbox" checked="checked" />views</label>
                        <div class="inner-block">
                {+views}
                            <label><input type="checkbox" checked="checked" name="schemas[]" value="{name}" />{name}</label>
                {-views}
                        </div>
                    </li>
                </ul>
            </li>
            {-when:eq}
            <li>
                <label>
                    <input class="chk-all-subs" type="checkbox" checked="checked" />
                    All table contents
                </label> 
                <a id="epd-contents" href="javascript:void(0);" class="icon-folder-close"></a>
                <ul class="hide">
                    <li>
                        <div class="inner-block">
            {+tables}
                            <label><input type="checkbox" checked="checked" name="contents[]" value="{name}" />{name}</label>
            {-tables}
                        </div>
                    </li>
                </ul>
            </li>
        {-when:no}
        {+when:eq a=/source_type b='table'}
            {+when:eq a=/type b='sql'}
            <li>
                <label><input type="checkbox" checked="checked" name="schemas[]" value="{source}" />Schema</label>
            </li>
            {-when:eq}
            <li>
                <label><input type="checkbox" checked="checked" name="contents[]" value="{source}" />Contents</label>
            </li>
        {-when:eq}
        {+when:eq a=/source_type b='view'}
            <li>
                <label><input type="checkbox" checked="checked" name="contents[]" value="{source}" />Contents</label>
            </li>
        {-when:eq}
        </ul>
    </fieldset>
    {-when:no}
    {+when:eq a=/type b='sql'}
    <fieldset>
        <legend>SQL Options</legend>
        <ul>
            <li>
                <label><input type="checkbox" name="opts[sql][add_drop]" checked="checked" />Add <em>DROP TABLE/VIEW</em> Statement</label>
            </li>
        </ul>
    </fieldset>
    {-when:eq}
    {+when:eq a=/type b='csv'}
    <fieldset>
        <legend>CSV Options</legend>
        <ul>
            <li><label><input type="checkbox" name="opts[csv][inc_header]" checked="checked" />Include Columns Header</label></li>
            <li>
                <table>
                    <tr>
                        <td><label>Separator</label></td>
                        <td><input type="text" class="text" name="opts[csv][separator]" value="," /></td>
                        <td><label>Enclosure</label></td>
                        <td><input type="text" class="text" name="opts[csv][enclosure]" value="&quot;" /></td>
                    </tr>
                    <tr>
                        <td><label>Escape</label></td>
                        <td><input type="text" class="text" name="opts[csv][escape]" value="\" /></td>
                    </tr>
                </table>
            </li>
        </ul>
    </fieldset>
    {-when:eq}
    <fieldset>
        <legend>Output</legend>
        <ul>
            <li><label><input id="chk-output-text" type="radio" name="output" value="text" />View output as text</label></li>
            <li>
                <label><input id="chk-output-file" type="radio" name="output" value="file" checked="checked" />Save output to a file</label>
                <ul>
                    <li>
                        <label>Charset:</label>
                        <select name="encode">
                                <option value="UTF-8">UTF-8</option>
                                <option value="ASCII">ASCII</option>
                                <option value="ISO-8859-1">ISO-8859-1</option>
    {+encodings}
                            <optgroup label="{title}">
        {+items}
                                <option value="{encoding}">{title} {encoding}</option>
        {-items}
                            </optgroup>
    {-encodings}
                        </select>
                    </li>
                    <li>
                        <label>Compression:</label>
                        <label><input type="radio" name="compress" value="txt" checked="checked" />txt</label>
                        <label><input type="radio" name="compress" value="zip" />zip</label>
                        <label><input type="radio" name="compress" value="bz2" />bz2</label>
                    </li>
                </ul>
            </li>
        </ul>
    </fieldset>
    <div class="toolbar"><a href="javascript:void(0);" class="btn-submit m-apply">Export</a></div>
</form>

{-when:no}

<div>
{+when:ok a=is_success}
    <div class="message-success">
        <span class="icon-ok-sign"></span>
        <em>Command is completed.</em>
    </div>
{-when:ok}
{+when:ok a=error}
    <div class="message-fail">
        <span class="icon-minus-sign"></span>
        <em>{error}</em>
    {+when:ok a=site:debug}
        <a href="javascript:void(0);" class="m-toggle" data-target="#error-trace">[&hellip;]</a>
        <pre id="error-trace" class="hide">{trace}</pre>
    {-when:ok}
    </div>
{-when:ok}
</div>
{+when:ok a=site:is_post}
<div class="fieldset">
    <pre style="padding:10px;">{export_content}</pre>
</div>
<div class="toolbar"><a href="{url:self}" class="btn">Return</a></div>
{-when:ok}
<script type="text/javascript" src="{site:root}/res/codemirror/codemirror.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/matchbrackets.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/sqlite.js"></script>
<script type="text/javascript">
    $(function(){
        $('input.chk-all-subs').click(function(){
            $(this).parentsUntil('ul').find('input[type="checkbox"]').prop('checked', this.checked);
        });
        
        $('#epd-schemas, #epd-contents').click(function(){
            if($(this).hasClass('icon-folder-close')){
                $(this).removeClass('icon-folder-close');
                $(this).addClass(' icon-folder-open');
            }else{
                $(this).addClass('icon-folder-close');
                $(this).removeClass('icon-folder-open');
            }
            $(this).next('ul').toggleClass('hide');
            return false;
        });
        
        $('#chk-output-text').click(function(){
            $('input[name="compress"]').prop('checked', false);
        });
        
        $('#chk-output-file').click(function(){
            $('input[name="compress"][value="zip"]').prop('checked', true);
        });
        
        $('input[name="compress"]').click(function(){
            $('#chk-output-file').prop('checked', true);
        });
    });
</script>
