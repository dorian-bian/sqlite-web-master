{+unit:css}
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/twilight.css" />
{-unit:css}

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

<div id="rename" class="hide" title="Rename">
    <div class="m-container">
        <fieldset>
            <input type="hidden" name="action" value="rename" />
            <table class="content-item">
                <tr>
                    <th><label>Old Name:</label></th>
                    <td>
                        <input type="text" readonly="readonly" value="{source_name}" />
                    </td>
                </tr>
                <tr>
                    <th><label>New Name:</label></th>
                    <td>
                        <input type="text" name="option[name]" value="{source_name}" />
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
</div>
<div id="copy-to" class="hide" title="Copy to">
    <div class="m-container">
        <fieldset>
            <input type="hidden" name="action" value="copy" />
            <table class="content-item">
                <tr>
                    <th><label>Database:</label></th>
                    <td>
                        <select name="option[database]">
{+databases}
                            <option title="{path}"{when:eq a=name b=/dbname mode=select} value="{name}">{name}</option>
{-databases}
{+db_groups}
                            <optgroup label="{name}">
    {+subs}
                                <option{when:eq a=name b=/dbname mode=select}>{name}</option>
    {-subs}
                            </optgroup>
{-db_groups}
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>Table Name:</label></th>
                    <td>
                        <input name="option[table]" type="text" value="{source_name}" />
                    </td>
                </tr>
                <tr>
                    <th><label>Content:</label></th>
                    <td>
                        <label class="inline"><input type="checkbox" name="option[content][schema]" checked="checked" />Schema</label>
                        <label class="inline"><input type="checkbox" name="option[content][data]" checked="checked" />Data</label>
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
</div>
<div id="move-to" class="hide" title="Move to">
    <div class="m-container">
        <fieldset>
            <input type="hidden" name="action" value="move" />
            <input type="hidden" name="names" value="{source_name}" />
            <table class="content-item">
                <tr>
                    <th><label>Database:</label></th>
                    <td>
                        <select name="option[database]">
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
                    </td>
                </tr>
                <tr>
                    <th><label>Table Name:</label></th>
                    <td>
                        <input type="text" name="option[table]" value="{source_name}" />
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
</div>

<form action="" method="post" class="m-form">
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="names" value="" />
    <div class="tabs-2 m-tabs fieldset-block">
        <ul>
            <li class="active"><span>Action</span></li>
            <li><span>Definition SQL</span></li>
        </ul>
        <div>
            <div>
                <div class="manage-bar">
                    <a class="btn m-modal" data-action="rename" data-target="#rename" 
                        data-apply="true" href="javascript:void(0);">Rename</a>
                    <a class="btn m-modal" data-action="copy" data-target="#copy-to" 
                        data-apply="true" href="javascript:void(0);">Copy to</a>
                    <a class="btn m-modal" data-action="move" data-target="#move-to"
                        data-apply="{url:base i='table-list'}" href="javascript:void(0);">Move to</a>
                    
                    <a href="javascript:void(0)" class="btn m-apply" data-action="reindex">ReIndex</a>
                    <a href="javascript:void(0)" class="btn m-apply" data-action="empty">Empty</a>
                    <a href="javascript:void(0)" class="btn m-apply" data-action="drop" 
                        data-post="{url:base i='table-list'}" data-item="names:{source_name}"
                        data-confirm="Are you sure you want to drop it?">Drop</a>
                </div>
            </div>
            <div class="hide">
                <textarea id="schema-code" class="m-code">{code}</textarea>
            </div>
        </div>
    </div>
    <table class="item-list m-list">
        <colgroup>
            <col colspan="3"/>
            <col colspan="1" align="center" />
        </colgroup>
        <thead>
            <tr class="item-list-header">
                <td colspan="6">
                    <span>Populate</span>
                </td>
            </tr>
            <tr>
                <td width="40">ID</td>
                <td>Name</td>
                <td>Type</td>
                <td width="340">Fill</td>
            </tr>
        </thead>
        <tbody>
    {+cols}
            <tr class="{tick:even}">
                <td>{i}</td>
                <td>{name}</td>
                <td>{type}</td>
                <td>
                    <input type="hidden" name="option[cols][0][name]" value="{name}" />
                    <select name="option[cols][0][type]" style="width:280px;" class="m-modal-select">
                        <option value="ignore">(ignore)</option>
                        <optgroup label="number">
                            <option value="integer">integer: 0 - 999999</option>
                            <option value="float">float: 0.01 - 999999.99</option>
                        </optgroup>
                        <optgroup label="string">
                            <option value="title">title: 3 - 12 words</option>
                            <option value="paragraph">paragraph: 1 - 3 sentences</option>
                            <option value="article">article: 2 - 8 paragraphs</option>
                            <option value="sentence">sentence: 3 - 12 words</option>
                            <option value="name">name: 1 - 3 words</option>
                            <option value="url">url: http://www.example.com/[w]/[3-12z]</option>
                            <option value="phone">phone: ([3d]) [4d]-[3d]</option>
                            <option value="email">email: [1-3z]@[1-2z].com</option>
                            
                            <option value="chars">chars: [32A]</option>
                            <option value="uuid">uuid: [8X]-[4X]-[4X]-[12X]</option>
                            <option value="datetime">datetime: Y-m-d H:i:s</option>
                        </optgroup>
                        <optgroup label="blob">
                            <option value="image">image: 60 x 60.jpg</option>
                        </optgroup>
                        <optgroup label="misc">
                            <option value="refer">refer: [table] - [column]</option>
                            <option value="null">null</option>
                        </optgroup>
                    </select>
                    <a href="javascript:void(0);" class="m-modal hide icon-wrench"  title="Options"
                        data-action="update" data-target="div.m-modal-target"></a>
                    <div class="m-modal-target hide" title="Option">
                        <div class="m-container">
                        </div>
                    </div>
                </td>
            </tr>
    {-cols}
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" nowrap="nowrap">
                    <div class="float-right">
                        <input type="text" name="option[count]" value="1000" style="width:100px;"/>
                        <a href="javascript:void(0);" class="btn m-apply btn-submit" 
                        {+when:ok a=itemcount}data-confirm="The table is not empty. Do you still want to continue?"{-when:ok} 
                        data-action="populate">Populate</a>
                    </div>
                    <span><em>(a:[0-9a-z], c:[a-z], d:[0-9], w:word, x:[0-9a-f] z:word[-word]*)</em></span>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
<div class="hide">
    <div id="m-modal-target-integer">
        <fieldset>
            <legend>integer</legend>
            <p data-summary="integer: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="number mm-value" value="0" />
            </p>
            <p data-summary="integer: [min] - [max]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][min]" class="number mm-min" value="0" /> - 
                <input type="text" name="option[cols][0][max]" class="number mm-max" value="999999" />
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-float">
        <fieldset>
            <legend>float</legend>
            <p data-summary="float: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="number mm-value" value="0.00" />
            </p>
            <p data-summary="float: [min] - [max]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][min]" class="number mm-min" value="0.00" /> - 
                <input type="text" name="option[cols][0][max]" class="number mm-max" value="999999.99" />
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-title">
        <fieldset>
            <legend>title</legend>
            <p data-summary="title: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="string mm-value" value="This Is A Title" />
            </p>
            <p data-summary="title: [min] ~ [max] words">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][min]" class="number mm-min" value="3" /> - 
                <input type="text" name="option[cols][0][max]" class="number mm-max" value="12" /> Words
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-paragraph">
        <fieldset>
            <legend>paragraph</legend>
            <p data-summary="paragraph: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <textarea  name="option[cols][0][value]" class="string mm-value">This is a paragraph.</textarea>
            </p>
            <p data-summary="paragraph: [min] ~ [max] words">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][min]" class="number mm-min" value="1" /> - 
                <input type="text" name="option[cols][0][max]" class="number mm-max" value="3" /> Sentences
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-article">
        <fieldset>
            <legend>article</legend>
            <p data-summary="article: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <textarea  name="option[cols][0][value]" class="string mm-value" >This is a paragraph.</textarea>
            </p>
            <p data-summary="article: [min] ~ [max] words">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][min]" class="number mm-min" value="2" /> - 
                <input type="text" name="option[cols][0][max]" class="number mm-max" value="8" /> Paragraphs
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-sentence">
        <fieldset>
            <legend>sentence</legend>
            <p data-summary="sentence: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="string mm-value" value="This is a sentence." />
            </p>
            <p data-summary="sentence: [min] ~ [max] words">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][min]" class="number mm-min" value="3" /> - 
                <input type="text" name="option[cols][0][max]" class="number mm-max" value="12" /> Words
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-name">
        <fieldset>
            <legend>name</legend>
            <p data-summary="name: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="string mm-value" value="A Name" />
            </p>
            <p data-summary="name: [min] ~ [max] words">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][min]" class="number mm-min" value="1" /> - 
                <input type="text" name="option[cols][0][max]" class="number mm-max" value="3" /> Words
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-url">
        <fieldset>
            <legend>url</legend>
            <p data-summary="url: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="mm-value string" value="http://www.example.com/page.html" />
            </p>
            <p data-summary="url: [format]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][format]" class="mm-format string" value="http://www.example.com/[w]/[3-12z]" /> 
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-phone">
        <fieldset>
            <legend>phone</legend>
            <p data-summary="phone: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="mm-value string" value="(000) 0000-000" />
            </p>
            <p data-summary="phone: [format]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][format]" class="mm-format string" value="([3d]) [4d]-[3d]" /> 
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-email">
        <fieldset>
            <legend>email</legend>
            <p data-summary="email: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="mm-value string" value="user@example.com" />
            </p>
            <p data-summary="phone: [format]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][format]" class="mm-format string" value="[1-3z]@[1-2z].com" /> 
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-chars">
        <fieldset>
            <legend>chars</legend>
            <p data-summary="chars: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="mm-value string" value="abc">
            </p>
            <p data-summary="chars: [format]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][format]" class="mm-format string" value="[32A]" /> 
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-uuid">
        <fieldset>
            <legend>uuid</legend>
            <p data-summary="uuid: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="mm-value string" value="0000-000-000-00">
            </p>
            <p data-summary="uuid: [format]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][format]" class="mm-format string" value="[8X]-[4X]-[4X]-[12X]" /> 
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-datetime">
        <fieldset>
            <legend>datetime</legend>
            <p data-summary="datetime: [value]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="static" />Static:
                </label>
                <input type="text"  name="option[cols][0][value]" class="mm-value string" value="{datetime}" />
            </p>
            <p data-summary="datetime: [format]">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" checked="checked" value="random" />Random:
                </label>
                <input type="text" name="option[cols][0][format]" class="mm-format string" value="Y-m-d H:i:s"> 
            </p>
        </fieldset>
    </div>
    <div id="m-modal-target-image">
        <fieldset>
            <legend>image</legend>
            <p data-summary="image: [width]x[height].jpg">
                <label class="inline-block-80">
                    <input type="radio" name="option[cols][0][mode]" value="default" checked="checked" class="hide" />Size:
                </label>
                <input type="text" name="option[cols][0][width]" class="number mm-width" value="60" /> x 
                <input type="text" name="option[cols][0][height]" class="number mm-height" value="60" />
            </p>
            
        </fieldset>
    </div>
    <div id="m-modal-target-refer">
        <fieldset>
            <legend>Refer</legend>
            <p>
                <label class="inline-block-80">Table:</label>
                <select class="m-table-list mm-table" name="option[cols][0][table]">
                    <option></option>
            {+tables}
                    <option>{name}</option>
            {-tables}
                </select>
            </p>
            <p>
                <label class="inline-block-80">Column:</label>
                <select class="m-table-columns mm-column" name="option[cols][0][column]"></select>
            </p>
        </fieldset>
    </div>
</div>
<div id="m-dialog" class="modal hide">
    <div class="overlay"></div>
    <div class="dialog" style="width:580px;">
        <div class="dialog-header">
            <span>Constraint</span>
        </div>
        <form class="m-form">
            <div class="dialog-body"></div>
        </form>
        <div class="toolbar">
            <a href="javascript:void(0);" class="btn-submit cancel">Cancel</a>
            <a href="javascript:void(0);" class="btn-submit save">Save</a>
        </div>
    </div>
</div>
<script type="text/javascript" src="{site:root}/res/codemirror/codemirror.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/matchbrackets.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/sqlite.js"></script>
<script type="text/javascript">
    $(function(){
        $('body').on('change', 'select.m-table-list', function(){
            var columns = $($(this).parents('fieldset')[0]).find('select.m-table-columns');
            $.get('{url-0:self}&get-columns='+$(this).val(), function(data){
                columns.empty().append(data);
                columns.addClass('hide').removeClass('hide');
            });
        });
    });
</script>
