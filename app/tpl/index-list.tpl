{+unit:css}
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/twilight.css" />
{-unit:css}
{+unit:item mode=node}
<div class="m-container">
    <input type="hidden" name="info[table]" value="{table}" />
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="oldn" value="{name}" />
    <fieldset>
        <p>
            <label class="inline">Index Name: <input type="text" name="info[name]" value="{name}" /></label>
            <label class="inline">
                <input name="info[unique]" type="hidden" />
                <input name="info[unique]" type="checkbox" style="vertical-align:middle;"{when:ok a=unique mode=check} />
                Unique
            </label>
        </p>
    </fieldset>
    <table class="item-list column-list m-list">
        <thead>
            <tr class="item-list-header"><th colspan="5">Columns</th></tr>
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
                <td>{i}</td>
                <td>
                    <select name="info[cols][0][name]">
    {+/columns}
                        <option{when:eq a=name b=../name mode=select}>{name}</option>
    {-/columns}
                    </select>
                </td>
                <td>
                    <input name="info[cols][0][order]" type="hidden" value="ASC" />
                    <input name="info[cols][0][order]" type="checkbox"
                        {when:eq a=order b='DESC' mode=check}
                        value="DESC" />
                </td>
                <td>
                    <select name="info[cols][0][collation]">
    {+/collations}
                        <option{when:eq a=name b=../collation mode=select}>{name}</option>
    {-/collations}
                    </select>
                </td>
                <td>
    {+when:ok a=i}
                    <a href="javascript:void(0);" class="icon-remove m-swift" data-action="delete"></a>
    {-when:ok}
                </td>
            </tr>
{-cols}
        </tbody>
        <tfoot>
            <tr class="hide m-blank">
                <td>*</td>
                <td>
                    <select name="info[cols][0][name]">
{+/columns}
                        <option>{name}</option>
{-/columns}
                    </select>
                </td>
                <td>
                    <input name="info[cols][0][order]" type="hidden" value="ASC" />
                    <input type="checkbox" name="info[cols][0][order]"  value="DESC" />
                </td>
                <td>
                    <select name="info[cols][0][collation]">
{+/collations}
                        <option{when:eq a=name b=/collation mode=select}>{name}</option>
{-/collations}
                    </select>
                </td>
                <td>
                    <a href="javascript:void(0);" class="icon-remove m-swift" data-action="delete"></a>
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
{-unit:item}

<form action="" method="post" class="m-form">
    <input type="hidden" name="action"/>
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
    <table class="item-list">
        <thead>
            <tr class="item-list-header">
                <th colspan="8"><span>Internal Indices</span></th>
            </tr>
            <tr>
                <th width="20"></th>
                <th width="20">ID</th>
                <th width="60">Type</th>
                <th>Columns</th>
            </tr>
        </thead>
        <tbody>
{+when:no a=table_indices}
            <tr>
                <td colspan="4" class="item-empty">(empty)</td>
            </tr>
{-when:no}
{+table_indices}
            <tr class="{tick:even}">
                <td></td>
                <td>{tick:i}</td>
                <td>{type}</td>
                <td class="idx-column">
                    <table width="100%" cellspacing="1" rules="all" border="0">
                    {+cols}
                        <tr>
                            <td>{name}</td>
                            <td width="100">{order}</td>
                            <td width="100">{collation}</td>
                        </tr>
                    {-cols}
                    </table>
                </td>
            </tr>
{-table_indices}
        </tbody>
    </table>
    <table class="item-list m-list">
        <thead>
            <tr class="item-list-header">
                <th colspan="8">
                    <a href="javascript:void(0);" class="btn m-modal" 
                        data-target="#new-i" data-action="append" data-apply="true">Add New Index</a>
                    <span class="info">Normal Indices</span>
                </th>
            </tr>
            <tr>
                <th width="20"><input type="checkbox" class="m-check" data-target="input.item-name" /></th>
                <th width="20">ID</th>
                <th width="60">Type</th>
                <th>Name</th>
                <th>Columns</th>
                <th width="60">Unique</th>
                <th colspan="2">Action</th>
            </tr>
        </thead>
        <tbody>
{+when:no a=index_indices}
            <tr>
                <td colspan="8" class="item-empty"><em>(empty)</em></td>
            </tr>
{-when:no}
{+index_indices}
            <tr class="{tick:even}">
                <td><input type="checkbox" name="names[]" value="{name}" class="m-check" /></td>
                <td>{tick:i}</td>
                <td>{type}</td>
                <td>{name}</td>
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
                <td>{unique}</td>
                <td width="20">
                {+when:eq a=type b='INDEX'}
                    <a href="javascript:void(0);" class="icon-edit btn-edit m-modal"
                        data-action="update" data-target="div.m-modal-form" data-apply="true" title="Edit Structure"></a>
                    <div class="hide m-modal-form" title="INDEX - {name}">
                        {unit:item}
                    </div>
                {-when:eq}
                </td>
                <td width="20">
                    <a href="javascript:void(0);" class="m-modal end icon-remove" 
                        data-action="delete" data-target="div.m-modal-form" data-apply="true" title="Drop"></a>
                </td>
            </tr>
{-index_indices}
        </tbody>
    </table>
    <div id="new-i" class="hide" title="Index">{unit:item data=/item-blank}</div>
</form>
<div id="m-dialog" class="modal hide" >
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
<script type="text/javascript" src="{site:root}/res/codemirror/codemirror.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/matchbrackets.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/sqlite.js"></script>
