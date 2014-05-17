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
        <a href="javascript:void(0);" class="btn m-apply" data-action="empty">
            <span class="icon-remove-circle"></span> Empty
        </a>
        <a href="javascript:void(0);" class="btn m-apply" data-action="drop">
            <span class="icon-remove"></span> Drop
        </a>
    </fieldset>
    <table class="item-list">
        <thead>
            <tr class="item-list-header">
                <td colspan="7">
                    <a class="btn" href="{url:base i='table-item' database=site:database}" 
                        style="float:right; padding:1px 8px; font-size:12px;">Add New Table</a>
                    <span class="info">Table List</span>
                </td>
            </tr>
            <tr>
                <th width="20"><input type="checkbox" class="m-check" data-target="input.item-name" /></th>
                <th>Table Name</th>
                <th width="60">Rows</th>
                <th colspan="4">Action</th>
            </tr>
        </thead>
        <tbody>
            {+when:no a=tables}
            <tr>
                <td colspan="7" class="item-empty">(empty)</td>
            </tr>
            {-when:no}
            {+tables}
            <tr class="{tick:even}">
                <td><input type="checkbox" name="names[]" value="{name}" class="m-check" /></td>
                <td>{name}</td>
                <td>{rows}</td>
                <td width="20">
                    <a href="{url:base i='content-list' database=site:database source=name}" 
                        class="icon-list-alt" title="Browse &amp; Search"></a>
                </td>
                <td width="20">
                    <a href="{url:base i='table-item' database=site:database source=name}" 
                        class="icon-edit" title="Edit Structure"></a>
                </td>
                <td width="20">
                    <a href="javascript:void(0);"  data-action="empty" data-item="names:{name}"
                        class="icon-remove-circle m-apply" title="Empty"></a>
                </td>
                <td width="20">
                    <a  href="javascript:void(0);" data-action="drop" data-item="names:{name}"
                        class="icon-remove m-apply" title="Drop"></a>
                </td>
            </tr>
            {-tables}
        </tbody>
        <tfoot>
            <tr>
                <th width="20"><input type="checkbox" class="m-check" data-target="input.item-name" /></th>
                <th>Table Name</th>
                <th width="60">Rows</th>
                <th width="120" colspan="4">Action</th>
            </tr>
        </tfoot>
    </table>
</form>

