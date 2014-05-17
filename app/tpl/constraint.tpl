{+unit:css}
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/twilight.css" />
{-unit:css}
{+unit:u mode=node}
    <div class="m-container">
        <input type="hidden" name="action" value="" class="action" />
        <input type="hidden" name="type" value="u" />
        <input type="hidden" name="rel" value="{rel?}" />
        <input type="hidden" name="i" value="{i?}" />
        <input type="hidden" name="item[enabled]" value="1" />
        <fieldset>
            <p>
                <label class="inline">On Conflict</label>
                <select name="item[on_conflict]">
    {+/resolutions}
                    <option{when:eq a=name b=../on_conflict mode=select}>{name}</option>
    {-/resolutions}
                </select>
            </p>
        </fieldset>
        <table class="item-list column-list idx-list m-list">
            <thead>
                <tr>
                    <th width="20">ID</th>
                    <th width="424">Column</th>
                    <th width="40">Desc</th>
                    <th width="80">Collation</th>
                    <th width="20"></th>
                </tr>
            </thead>
            <tbody>
    {+cols}
                <tr class="{tick:even}">
                    <td>{tick:i}</td>
                    <td>
                        <select name="item[cols][0][name]">
        {+/columns}
                            <option{when:eq a=name b=../name mode=select}>{name}</option>
        {-/columns}
                        </select>
                    </td>
                    <td>
                        <input name="item[cols][0][order]" type="hidden" value="ASC" />
                        <input name="item[cols][0][order]" type="checkbox"
                            {when:eq a=order b='DESC' mode=check}
                            value="DESC" />
                    </td>
                    <td>
                        <select name="item[cols][0][collation]">
        {+/collations}
                            <option{when:eq a=name b=../collation mode=select}>{name}</option>
        {-/collations}
                        </select>
                    </td>
                    <td>
        {+when:gt a=i b="0"}
                        <a href="javascript:void(0);" class="icon-remove m-swift" data-action="delete"></a>
        {-when:gt}
                    </td>
                </tr>
    {-cols}
            </tbody>
            <tfoot>
                <tr class="m-blank hide">
                    <td>*</td>
                    <td>
                        <select name="item[cols][0][name]">
    {+/columns}
                            <option>{name}</option>
    {-/columns}
                        </select>
                    </td>
                    <td>
                        <input name="item[cols][0][order]" type="hidden" value="ASC" />
                        <input type="checkbox" name="item[cols][0][order]" value="DESC" />
                    </td>
                    <td>
                        <select name="item[cols][0][collation]">
    {+/collations}
                            <option{when:eq a=name b=/cons_u/cols/0/collation mode=select}>{name}</option>
    {-/collations}
                        </select>
                    </td>
                    <td>
                        <a href="javascript:void(0);"  class="icon-remove m-swift" data-action="delete"></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td>
                        <a href="javascript:void(0);" class="icon-plus m-swift" data-action="append" title="Add New Column"></a>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
{-unit:u}
{+unit:f mode=node}
<div class="m-container">
        <input type="hidden" name="action" value="" class="action"/>
        <input type="hidden" name="type" value="f" />
        <input type="hidden" name="rel" value="{rel?}" />
        <input type="hidden" name="i" value="{i?}" />
        <input type="hidden" name="item[enabled]" value="1" />
        
        <input type="hidden" name="item[match]" value="{match}" />
        <fieldset>
            <table class="content-item">
                <tr>
                    <th><label>Apply to:</label></th>
                    <td>
                        <div class="input-text">
    {+cols}
                            <span><input type="hidden" name="item[cols][][name]" value="{name}" /><em>{name}</em><a href="javascript:void(0);">&times;</a></span>
    {-cols}
                        </div>
                        <div class="input-opts" data-name="item[cols][][name]">
                            <div class="menu">
                                <a href="javascript:void(0);" class="btn">+</a>
                            </div>
                            <div class="subs">
                                <ul class="hide">
    {+/columns}
                                    <li>{name}</li>
    {-/columns}
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><label>Refer:</label></th>
                    <td>
                        <select name="item[refer][name]" class="m-table-list">
    {+/tables}
                            <option value="{name}"{when:eq a=name b=../refer/name mode=select}>{name}</option>
    {-/tables}
                        </select><span>.</span>
                        <div class="input-text">
    {+refer/cols}
                            <span><input type="hidden" name="item[refer][cols][][name]" value="{name}" /><em>{name}</em><a href="javascript:void(0);">&times;</a></span>
    {-refer/cols}
                        </div>
                        <div class="input-opts" data-name="item[refer][cols][][name]">
                            <div class="menu">
                                <a href="javascript:void(0);" class="btn">+</a>
                            </div>
                            <div class="subs">
                                <ul class="hide m-table-columns">
    {+/columns}
                                    <li>{name}</li>
    {-/columns}
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><label>On Update</label></th>
                    <td>
                        <select name="item[on_update]">
    {+/update_actions}
                            <option{when:eq a=name b=../on_update mode=select}>{name}</option>
    {-/update_actions}
                        </select>
                    </td>
                    
                </tr>
                <tr>
                    <th><label>On Delete</label></th>
                    <td>
                        <select name="item[on_delete]">
    {+/delete_actions}
                            <option{when:eq a=name b=../on_delete mode=select}>{name}</option>
    {-/delete_actions}
                        </select>
                    </td>

                </tr>
                <tr>
                    <th><label>Deferred</label></th>
                    <td>
                        <input type="hidden" name="item[deferred]" />
                        <input type="checkbox" name="item[deferred]"{when:ok a=deferred mode=check} />
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
{-unit:f}
{+unit:c mode=node}
    <div class="m-container">
        <input type="hidden" name="action" value="" class="action"/>
        <input type="hidden" name="type" value="c" />
        <input type="hidden" name="rel" value="{rel?}" />
        <input type="hidden" name="i" value="{i?}" />
        <input type="hidden" name="item[enabled]" value="1" />
        <fieldset>
            <table width="100%" class="content-item">
                <tr>
                    <th class="align-top">
                        <label>Expression:</label> 
                    </th>
                    <td>
                        <textarea class="text m-code-small" name="item[expr]">{expr}</textarea>
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
{-unit:c}
<form action="" method="POST" class="m-form">
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

<fieldset class="align-right fieldset-header">
    <a class="btn m-modal" data-action="append" data-target="#new-c" data-apply="true" href="javascript:void(0);">Add Check</a>
    <a class="btn m-modal" data-action="append" data-target="#new-u" data-apply="true" href="javascript:void(0);">Add Unique</a>
    <a class="btn m-modal" data-action="append" data-target="#new-f" data-apply="true" href="javascript:void(0);">Add Foreign Key</a>
</fieldset>

{+when:ok a=empty}
<fieldset>
    <div class="console-empty">(No constraints)</div>
</fieldset>
{-when:ok}

{+when:ok a=cons/p}
    <table class="item-list column-list m-list">
        <thead>
            <tr>
                <td width="60">Type</td>
                <td>Columns</td>
                <td width="60">AutoInc</td>
                <td width="80">On Conflict</td>
                <th width="20"></th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
            {+cons/p}
            <tr class="{tick:even}">
                <td width="60">P</td>
                <td class="idx-column">
                    <table>
                        {+cols}
                        <tr>
                            <td>{name}</td>
                            <td width="100">{order}</td>
                            <td width="100">{collation}</td>
                        </tr>
                        {-cols}
                    </table>
                </td>
                <td>
                    {autoincr}
                </td>
                <td>
                    {on_conflict}
                </td>
                <td>
                </td>
                <td>
                </td>
            </tr>
            {-cons/p}
        </tbody>
    </table>
{-when:ok}
{+when:ok a=cons/u}
    <table class="item-list column-list m-list" >
        <thead>
            <tr>
                <td>Type</td>
                <td>Columns</td>
                <td width="80">On Conflict</td>
                <th width="20"></th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
        {+cons/u}
            <tr class="{tick:even}">
                <td width="60">U</td>
                <td class="idx-column">
                    <table>
                        {+cols}
                        <tr>
                            <td>{name}</td>
                            <td width="100">{order}</td>
                            <td width="100">{collation}</td>
                        </tr>
                        {-cols}
                    </table>
                </td>
                <td>
                    {on_conflict}
                </td>
                <td>
                    <a href="javascript:void(0);" class="icon-edit m-modal" title="Edit"
                        data-action="update" data-target="div.m-modal-u"  data-apply="true"></a>
                    <div class="hide m-modal-u" title="UNIQUE: {rel?}{when:ok a=rel mode=' - '}{i?}">
                        {unit:u}
                    </div>
                </td>
                <td>
                    <a href="javascript:void(0);"  class="icon-remove m-modal" title="Remove"
                        data-action="delete" data-target="div.m-modal-u" data-apply="true"></a>
                </td>
            </tr>
        {-cons/u}
        </tbody>
    </table>
{-when:ok}
{+when:ok a=cons/f}
    <table class="item-list column-list m-list" >
        <thead>
            <tr>
                <th>Type</th>
                <th>Columns</th>
                <th>Refer</th>
                <th>On Update</th>
                <th>On Delete</th>
                <th width="60">Deferred</th>
                <th width="20"></th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
            {+cons/f}
            <tr class="{tick:even}">
                <td width="60">FK</td>
                <td class="idx-column">
                    <table>
                        <tr>
                            <td>
                                <table>
                                    {+cols}
                                    <tr><td>{name}</td></tr>
                                    {-cols}
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="idx-column">
                    <table>
                        <tr>
                            <td width="40%">{refer/name}</td>
                            <td>
                                <table>
                                    {+refer/cols}
                                    <tr><td>{name}</td></tr>
                                    {-refer/cols}
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="100">{on_update}</td>
                <td width="100">{on_delete}</td>
                <td width="30">{deferred}</td>
                <td>
                    <a href="javascript:void(0);" class="icon-edit m-modal" title="Edit"
                        data-action="update" data-target="div.m-modal-f"  data-apply="true"></a>
                    <div class="hide m-modal-f" title="FOREIGN KEY: {rel?}{when:ok a=rel mode=' - '}{i?}">
                        {unit:f}
                    </div>
                </td>
                <td>
                    <a href="javascript:void(0);"  class="icon-remove m-modal" title="Remove"
                        data-action="delete" data-target="div.m-modal-f" data-apply="true"></a>
                </td>
            </tr>
            {-cons/f}
        </tbody>
    </table>
{-when:ok}
{+when:ok a=cons/c}
    <table class="item-list column-list m-list" >
        <thead>
            <tr>
                <th>Type</th>
                <th>Condition</th>
                <th width="20"></th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
        {+cons/c}
            <tr class="{tick:even}">
                <td width="60">C</td>
                <td>
                    {expr}
                </td>
                <td>
                    <a href="javascript:void(0);" class="icon-edit m-modal" title="Edit"
                        data-action="update" data-target="div.m-modal-c"  data-apply="true"></a>
                    <div class="hide m-modal-c" title="CHECK: {when:ok a=rel mode=' - '}{rel?}{when:ok a=i mode=' - '}{i?}">
                        {unit:c}
                    </div>
                </td>
                <td>
                    <a href="javascript:void(0);"  class="icon-remove m-modal" title="Remove"
                        data-action="delete" data-target="div.m-modal-c" data-apply="true"></a>
                </td>
            </tr>
        {-cons/c}
        </tbody>
    </table>
{-when:ok}
</form>

<div id="new-f" class="hide" title="FOREIGN KEY">{unit:f data=/cons_f}</div>
<div id="new-c" class="hide" title="CHECK">{unit:c data=/cons_c}</div>
<div id="new-u" class="hide" title="UNIQUE">{unit:u data=/cons_u}</div>

<div class="hide">
    <datalist id="columns">
    {+/columns}
        <option>{name}</option>
    {-/columns}
    </datalist>
</div>
<div id="m-dialog" class="modal hide">
    <div class="overlay"></div>
    <div class="dialog">
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

<div id="input-text-blank" class="hide">
    <span><input type="hidden" name="" value="" /><em></em><a href="javascript:void(0);">&times;</a></span>
</div>

<script type="text/javascript" src="{site:root}/res/codemirror/codemirror.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/matchbrackets.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/sqlite.js"></script>
<script type="text/javascript">
    $(function(){
        $('html').on('change', 'select.m-table-list', function(){
            var $this = $(this);
            var columns = $this.parent().find('ul.m-table-columns');
            if($this.attr('data-inited')){
                $this.parent().find('div.input-text').empty();
            }else{
                $this.attr('data-inited', 1);
            }
            $.get('{url-0:self}&get-columns='+$(this).val(), function(data){
                if(data!='' && data.substr(0,4)=="<li>"){
                    columns.empty().append(data);
                }
            });
        });
        $('select.m-table-list').change();
        
        $('div.input-opts').menu({
            'select': function(element){
                var target = element.parents('div.input-opts').parent().find('div.input-text');
                var blank = $('#input-text-blank span').clone();
                blank.find('em').text(element.text());
                blank.find('input').val(element.text());
                blank.find('input').attr('name', $(this).attr('data-name'));
                target.append(blank);
            }
        });
        $('html').on('click', 'div.input-text a', function(){
            $(this).parent().remove();
        });
    });
</script>
