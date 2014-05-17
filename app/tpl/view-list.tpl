<form action="" method="post" class="m-form">
    <input type="hidden" name="action" />
    <input type="hidden" name="names"  />
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
        <a href="javascript:void(0);" class="btn m-apply" data-action="drop"><span class="icon-remove"></span> Drop</a>
    </fieldset>
    <table class="item-list" >
        <thead>
            <tr class="item-list-header">
                <td colspan="7">
                    <a class="btn" href="{url:base i='view-item'}">Add New View</a>
                    <span class="info">View List</span>
                </td>
            </tr>
            <tr>
                <th width="20"><input type="checkbox" class="m-check" data-target="input.item-name"/></th>
                <th>View Name</th>
                <th>Select Statement</th>
                <th width="60">Rows</th>
                <th colspan="3">Action</th>
            </tr>
        </thead>
        <tbody>
{+when:no a=views}
            <tr>
                <td colspan="6" class="item-empty">(empty)</td>
            </tr>
{-when:no}
{+views}
            <tr class="{tick:even}">
                <td><input type="checkbox" name="names[]" value="{name}" class="m-check" /></td>
                <td><span>{name}</span></td>
                <td><span>{code}</span></td>
                <td>{rows}</td>
                <td width="20">
                    <a href="{url:base i='content-list' source=name}" class="icon-list-alt" title="Browse &amp; Search"></a>
                </td>
                <td width="20">
                    <a href="{url:base i='view-item' source=name}" class="icon-edit" title="Edit Structure"></a>
                </td>
                <td width="20">
                    <a href="javascript:void(0);" class="icon-remove m-apply" 
                        data-action="drop" data-item="names:{name}" title="Drop"></a>
                </td>
            </tr>
{-views}
        </tbody>
        <tfoot>
            <tr>
                <th width="20"><input type="checkbox"  class="m-check" data-target="input.item-name" /></th>
                <th>View Name</th>
                <td>Select Statement</td>
                <th width="60">Rows</th>
                <th colspan="3">Action</th>
            </tr>
        </tfoot>
    </table>
</form>

