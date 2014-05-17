{+unit:c mode=node}
    <td><a href="javascript:void(0);" class="icon-move m-handle"></a></td>
    <td>{i}<input type="hidden" name="info[cols][0][i]" value="{i}" /></td>
    <td>
        <input type="hidden" name="info[cols][0][cons][p][enabled]" />
        <input type="checkbox"{when:ok a=cons/p/enabled mode=check} name="info[cols][0][cons][p][enabled]" />
    </td>
    <td>
        <input type="hidden" name="info[cols][0][oldn]" value="{name}" />
        <input type="text" name="info[cols][0][name]" value="{name}" />
    </td>
    <td>
        <div class="combo">
            <select class="combo-select">
                <option></option>
                <option>INTEGER</option>
                <option>TEXT</option>
                <option>BLOB</option>
                <option>REAL</option>
            </select><input type="text" class="combo-input" name="info[cols][0][type]" value="{type}" list="col-type"/>
        </div>
    </td>

    <td nowrap="nowrap">
        <input type="text" class="text" name="info[cols][0][default]" value="{default}" list="col-default" />
    </td>
    <td>
        <input type="hidden" name="info[cols][0][cons][u][enabled]" />
        <input type="checkbox"{when:ok a=cons/u/enabled mode=check} name="info[cols][0][cons][u][enabled]" />
    </td>
    <td>
        <input type="hidden" name="info[cols][0][cons][n][enabled]" />
        <input type="checkbox"{when:ok a=cons/n/enabled mode=check} name="info[cols][0][cons][n][enabled]" />
    </td>
    <td>
        <a href="javascript:void(0);" class="btn m-modal"
            data-action='update' data-target="div.m-modal-form" title="Extra">Extra</a>
        <div class="m-modal-form hide">
            <div class="m-container">
                <fieldset>
                    <legend>
                        <label class="inline">Primary Key</label>
                    </legend>
                    <table class="content-item">
                        <tr>
                            <th><label>Sort Order:</label></th>
                            <td>
                                <select name="info[cols][0][cons][p][order]">
    {+/orders}
                                <option{when:eq a=name b=../cons/p/order mode=select}>{name}</option>
    {-/orders}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label>Auto Increment:</label></th>
                            <td>
                                <input type="hidden" name="info[cols][0][cons][p][autoincr]" />
                                <input type="checkbox"{when:ok a=cons/p/autoincr mode=check} name="info[cols][0][cons][p][autoincr]" />
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>
                        <label class="inline">On coflict</label>
                    </legend>
                    <table class="content-item">
                        <tr>
                            <th><label>Primary Key:</label></th>
                            <td>
                                <select name="info[cols][0][cons][p][on_conflict]">
    {+/resolutions}
                                    <option{when:eq a=name b=../cons/p/on_conflict mode=select}>{name}</option>
    {-/resolutions}
                                </select> 
                            </td>
                        </tr>
                        <tr>
                            <th><label>Unique:</label></th>
                            <td>
                                <select name="info[cols][0][cons][u][on_conflict]">
    {+/resolutions}
                                    <option{when:eq a=name b=../cons/u/on_conflict mode=select}>{name}</option>
    {-/resolutions}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label>Not NULL:</label></th>
                            <td>
                                <select name="info[cols][0][cons][n][on_conflict]">
    {+/resolutions}
                                    <option{when:eq a=name b=../cons/n/on_conflict mode=select}>{name}</option>
    {-/resolutions}
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <table class="content-item">
                        <tr>
                            <th><label>Collation:</label></th>
                            <td>
                                <select name="info[cols][0][collation]">
    {+/collations}
                                    <option{when:eq a=name b=../collation mode=select}>{name}</option>
    {-/collations}
                                </select>
                            </td>
                    </table>
                </fieldset>
            </div>
        </div>
    </td>
    <td>
        <a href="javascript:void(0);" class="icon-remove m-swift" data-action="delete"  title="Remove"></a>
    </td>
{-unic:c}
<div class="hide">
    <datalist id="col-default">
        <option>NULL</option>
        <option>CURRENT_DATE</option>
        <option>CURRENT_TIME</option>
        <option>CURRENT_TIMESTAMP</option>
    </datalist>
    <datalist id="col-type">
        <option>INTEGER</option>
        <option>REAL</option>
        <option>TEXT</option>
        <option>BLOB</option>
    </datalist>
</div>
<form action="" method="post" class="m-form">
{+when:ok a=is_success}
    <div class="message-success">
        <span class="icon-ok-sign"></span>
        <em>Command is completed.</em>
        <a href="{url:base i='table-list'}"><em>(Return to Table List)</em></a>
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
{+info}
    <fieldset>
        <div style="padding: 4px 8px;">
            <label class="inline">Table Name: </label>
            <input type="text" style="width:200px;" name="info[name]" value="{name}" />
            <label class="inline">Using Module: </label>
                <div class="combo combo-160">
                    <select class="combo-select">
    {+types}
                        <option>{title}</option>
    {-types}
                    </select><input type="text" class="combo-input" name="info[type]" value="{type}" />
                </div>
           
        </div>
    </fieldset>
    <table class="item-list column-list m-list">
        <thead>
            <tr class="item-list-header">
                <th colspan="10"><span>Columns</span></th>
            </tr>
            <tr>
                <th width="20"></th>
                <th width="20" style="text-align:center;">ID</th>
                <th width="24" style="text-align:center;">PK</th>
                <th width="50%">Column Name</th>
                <th width="120">Type</th>
                <th width="50%">Default</th>
                <th width="24" style="text-align:center;">UQ</th>
                <th width="24" style="text-align:center;">NN</th>
                <th width="24" style="text-align:center;">Extra</th>
                <th width="24" style="text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody class="m-sortable">
    {+cols}
            <tr class="{tick:even}">
                {unit:c}
            </tr>
    {-cols}
        </tbody>
        <tfoot>
            <tr class="m-blank hide">
                {unit:c data=/item-blank}
            </tr>
            <tr>
                <td colspan="9"></td>
                <td>
                    <a href="javascript:void(0);" class="icon-plus m-swift" data-action="append" title="Add New Column"></a>
                </td>
            </tr>
        </tfoot>
    </table>
    {-info}
    <div class="toolbar"><a href="javascript:void(0);" class="btn-submit m-apply">Save</a></div>
</form>
<div id="m-dialog" class="modal hide">
    <div class="overlay"></div>
    <div class="dialog">
        <div class="dialog-header">
            <span>Constraint</span>
        </div>
        <form>
            <div class="dialog-body"></div>
        </form>
        <div class="toolbar">
            <a href="javascript:void(0);" class="btn-submit reset">Reset</a>
            <a href="javascript:void(0);" class="btn-submit save">Save</a>
        </div>
    </div>
</div>
