{+unit:css}
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/twilight.css" />
{-unit:css}
{+unit:item mode=node}
<div class="m-container">
    <input type="hidden" name="info[table]" value="{/source}" />
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="oldn" value="{name}" />
    
    <fieldset>
        <table class="content-item">
            <tr>
                <th><label>Name:</label></th>
                <td><input type="text" name="info[name]" value="{name}" /></td>
            </tr>
            <tr>
                <th><label>Event: </label></th>
                <td>
                    <select name="info[moment]" style="width:100px;">
    {+/moments}
                        <option value="{name}"{when:eq a=name b=../moment mode=select}>{name}</option>
    {-/moments}
                    </select>
                    <select class="mm-event" name="info[event]" style="width:100px;">
    {+/events}
                        <option value="{name}"{when:eq a=name b=../event mode=select}>{name}</option>
    {-/events}
                    </select>
                    <span>OF</span>
                    <div class="input-text">
    {+cols}
                        <span><input type="hidden" name="info[cols][][name]" value="{name}" /><em>{name}</em><a href="javascript:void(0);">&times;</a></span>
    {-cols}
                    </div>
                    <div class="input-opts" data-name="info[cols][][name]">
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
                <th><label>For Each Row: </label></th>
                <td>
                    <input name="info[each]" type="hidden" />
                    <input name="info[each]" type="checkbox"{when:ok a=each mode=check} /> 
                </td>
            </tr>
            <tr>
                <th><label>When:</label></td>
                <td><textarea name="info[when]" class="m-code-small">{when}</textarea></td>
            </tr>
            <tr>
                <th><label>Action:</label></td>
                <td><textarea name="info[action]" class="m-code-small">{action}</textarea></td>
            </tr>
        </table>
    </fieldset>
</div>
{-unit:item}

<form action="" method="post" class="m-form">
    <input type="hidden" name="action" />
    <input type="hidden" name="names" />
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
    <fieldset>
        <label class="inline">Bulk Actions:</label>
        <a href="javascript:void(0);" class="btn m-apply" data-action="batch-delete">
            <span class="icon-remove"></span> Drop
        </a>
    </fieldset>
    <table class="item-list m-list" >
        <thead>
            <tr class="item-list-header">
                <th colspan="10">
                    <a href="javascript:void(0);" class="btn m-modal" 
                        data-action="append"  data-target="#new-item" data-apply="true">Add New Trigger</a>
                    <span class="info">Triggers</span>
                </th>
            </tr>
            <tr>
                <th width="20"><input type="checkbox" class="m-check" data-target="input.item-name" /></th>
                <th>Name</th>
                <th>Table</th>
                <th colspan="2">Event</th>
                <th>Each</th>
                <th>When</th>
                <th>action</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
{+when:no a=triggers}
            <tr>
                <td colspan="8" class="item-empty">(empty)</td>
            </tr>
{-when:no}
{+triggers}
            <tr class="{tick:even}">
                <td><input type="checkbox" name="names[]" value="{name}" class="m-check" /></td>
                <td width="160">{name}</td>
                <td width="160">{tbl_name}</td>
                <td width="60">{moment}</td>
                <td width="60">{event}</td>
                <td width="60">{each}</td>
                <td width="80">{when}</td>
                <td>{action}</td>
                <td width="20">
                    <a href="javascript:void(0);"  class="m-modal icon-edit"
                        data-target="div.m-modal-form" data-action='update' data-apply='true' title="Edit Structure"></a>
                    <div class="hide m-modal-form" title="TRIGGER - {name}">
                        {unit:item}
                    </div>
                </td>
                <td width="20">
                    <a href="javascript:void(0);" class="m-modal icon-remove" 
                        data-target="div.m-modal-form" data-action="delete" data-apply="true" title="Drop"></a>
                </td>
            </tr>
{-triggers}
        </tbody>
    </table>
    <div id="new-item" class="hide" title="Trigger"><div></div>{unit:item data=/trigger-blank}</div>
</form>
<div id="m-dialog" class="modal hide m-dialog" >
    <div class="overlay"></div>
    <div class="dialog" style="width: 780px;">
        <div class="dialog-header">
            <span>Constraint</span>
        </div>
        <form id="form" action="" method="post">
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
