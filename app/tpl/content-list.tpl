<form action="" method="post" class="m-form">
    <input type="hidden" name="action" />
    <input type="hidden" name="rowids" />
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
        <table class="data-filter">
            <tr>
                <td><a href="javascript:void(0);" class="btn m-modal"
                    data-apply="true" data-target="#filter-builder" data-action="filter"
                    title="Filter"><span class="icon-cog"></span></a>
                </td>
                <td width="100%">
                    <input type="text" readonly="readonly" value="{filter-text}" title="{filter-text}" />
                </td>
            </tr>
        </table>
{+when:ok a=/is_editable}
        <span>
            <label class="inline">Bulk Actions:</label>
            <a href="javascript:void(0);"  data-action="delete" class="btn m-apply">
                <span class="icon-remove"></span> Delete
            </a>
        </span>
{-when:ok}
    </fieldset>
    <div class="fieldset fieldset-header">
        <a class="btn" href="{url:base i='content-item' source=source  return=/self-url}">
            Add New Item
        </a>
        <span class="info">Items: {total-rows} <em>(Tip: Double click to turn on/off in-place editor.)</em></span>
    </div>
    <div class="fieldset fieldset-body">
        <div class="field-body-wrapper">
        <table id="m-item-list" class="item-list">
            <thead>
                <tr>
    {+when:ok a=/is_editable}
                    <th width="20"><input type="checkbox" class="m-check" data-target="input.item-rowid"/></th>
                    <th colspan="2"><span>Action</span></th>
    {-when:ok}
    {+columns}
                    <th nowrap="nowrap">
        {+when:eq a=type b='BLOB'}
                            {+when:ok a=pkey}<strong>{-when:ok}{name}{+when:ok a=pkey}</strong>{-when:ok}
            {+when:eq a=name b=/blob-image}
                            <a href="{url:self blob-image=''}" 
                                class="m-blob-image btn-icon btn-icon-on"><span class="icon icon-picture"></span></a>
            {-when:eq}
            {+when:ne a=name b=/blob-image}
                            <a href="{url:self blob-image=name}" 
                                class="m-blob-image btn-icon btn-icon"><span class="icon icon-picture"></span></a>
            {-when:ne}
        {-when:eq}
        {+when:ne a=type b='BLOB'}
            {+when:eq a=name b=/order-key}
                            <a href="{url:self order-key=name order-dir=/order-dir}" class="btn-order-on">
                                {+when:ok a=pkey}<strong>{-when:ok}{name}{+when:ok a=pkey}</strong>{-when:ok}
                                <span class="icon {/order-ico}"></span>
                            </a>
            {-when:eq}
            {+when:ne a=name b=/order-key}
                            <a href="{url:self order-key=name order-dir='DESC'}" class="btn-order">
                                {+when:ok a=pkey}<strong>{-when:ok}{name}{+when:ok a=pkey}</strong>{-when:ok}
                                <span class="icon icon-arrow-down"></span>
                            </a>
            {-when:ne}
        {-when:ne}
                    </th>
    {-columns}
                </tr>
            </thead>
            <tbody>
    {+when:no a=content_data}
            <tr>
                <td colspan="{columns_count}" class="item-empty">(empty)</td>
            </tr>
    {-when:no}
    {+content_data}
                <tr class="{tick:even}">
        {+when:ok a=/is_editable}
                    <td><input type="checkbox" name="rowids[]" value="{rowid}" class="m-check" /></td>
                    <td width="20">
                        <a href="{url:base i='content-item' source=/source rowid=rowid return=/self-url}" class="icon-edit" title="Edit"></a>
                    </td>
                    <td width="20">
                        <a href="javascript:void(0);"  class="m-apply icon-remove" title="Remove"
                            data-action="delete" data-item="rowids:{rowid}"></a>
                    </td>
        {-when:ok}
        {+fields}
                    <td class="m-cell data-type-{type} data-size-{size}">
                        <div class="m-cell-view">
            {+when:eq a=type b='4'}
                {+when:eq a=name b=/blob-image}
                            <img class="blob-image" src="{url:base i='content-item' source=/source rowid=../rowid col-type='img' col-name=name}" />
                {-when:eq}
                {+when:ne a=name b=/blob-image}
                            <a href="{url:base i='content-item' source=/source rowid=../rowid col-type='bin' col-name=name}">[{text}]</a>
                {-when:ne}
            {-when:eq}
            {+when:ne a=type b='4'}
                            <span>{text}</span>
            {-when:ne}
                        </div>
                        <div class="m-cell-edit hide" data-id="{../rowid}"  data-name="{name}" data-type="{type}" data-size="{size}">
                        </div>
                    </td>
        {-fields}
                </tr>
    {-content_data}
            </tbody>
            <tfoot>
                <tr>
    {+when:ok a=/is_editable}
                    <th><input type="checkbox" class="m-check" data-target="input.item-rowid"/></th>
                    <th colspan="2">Action</th>
    {-when:ok}
    {+columns}
                    <th>
                        {+when:eq a=type b='BLOB'}
                            {+when:ok a=pkey}<strong>{-when:ok}{name}{+when:ok a=pkey}</strong>{-when:ok}
                            {+when:eq a=name b=/blob-image}
                            <a href="{url:self blob-image=''}" 
                                class="m-blob-image btn-icon-on"><span class="icon icon-picture"></span></a>
                            {-when:eq}
                            {+when:ne a=name b=/blob-image}
                            <a href="{url:self blob-image=name}" 
                                class="m-blob-image btn-icon"><span class="icon icon-picture"></span></a>
                            {-when:ne}
                        {-when:eq}
                        {+when:ne a=type b='BLOB'}
                            {+when:eq a=name b=/order-key}
                            <a href="{url:self order-key=name order-dir=/order-dir}" class="btn-order-on">
                                {+when:ok a=pkey}<strong>{-when:ok}{name}{+when:ok a=pkey}</strong>{-when:ok}
                                <span class="icon {/order-ico}"></span>
                            </a>
                            {-when:eq}
                            {+when:ne a=name b=/order-key}
                            <a href="{url:self order-key=name order-dir='DESC'}" class="btn-order">
                                {+when:ok a=pkey}<strong>{-when:ok}{name}{+when:ok a=pkey}</strong>{-when:ok}
                                <span class="icon icon-arrow-down"></span>
                            </a>
                            {-when:ne}
                        {-when:ne}
                    </th>
    {-columns}
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
    
    <fieldset id="toolbar-save" class="float-toolbar hide">
        <div>
            <a href="javascript:void(0);" class="btn" id="m-btn-cancel">Cancel</a>
            <a href="javascript:void(0);" data-action="update" class="btn m-apply">Save</a>
        </div>
    </fieldset>
</form>


<div class="fieldset" style="background:transparent; border:none;">
{+when:ok a=content_data}
    {+pagination}
    <div class="pagination">
        <a href="{url:self p=first}">&laquo;</a>
        <a href="{url:self p=prev}">&lsaquo;</a>
        {+__num__}
        <a{when:eq a=num b=../current mode=active} href="{url:self p=num}">{num}</a>
        {-__num__}
        <a href="{url:self p=next}">&rsaquo;</a>
        <a href="{url:self p=last}">&raquo;</a>
    </div>
    {-pagination}
{-when:ok}
</div>

<div id="filter-builder" title="Filter" class="hide">
    <div class="m-container">
        <input type="hidden" name="action" value="filter" />
        <table class="item-list column-list m-list">
            <thead>
                <tr>
                    <th width="162">Column</th>
                    <th width="200">Operator</th>
                    <th>Values</th>
                </tr>
            </thead>
            <tbody>
{+filter-cols}
            <tr>
                <td>
                    <div class="combo combo-160">
                        <select class="combo-select">
                            <option></option>
    {+/columns}
                            <option value="[{name}]"{when:eq a=name b=../key mode=select}>{name}</option>
    {-/columns}
                        </select><input type="text" class="combo-input" name="filter[cols][0][key]" value="{key}" list="col-type"/>
                    </div>
                </td>
                <td>
                    <select name="filter[cols][0][op]" class="filter-op">
    {+/operators}
                        <option value="{name}"{when:eq a=name b=../op mode=select}>{title}</option>
    {-/operators}
                    </select>
                </td>
                <td><input type="text" name="filter[cols][0][val]" class="filter-val" value="{val}" /></td>
            </tr>
{-filter-cols}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">
                        <input type="text" class="filter-extra" name="filter[extra]" value="{filter-extra}" style="width:99.2%" />
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
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
            <a href="javascript:void(0);" class="btn float-left" id="btn-clear">Clear</a>
            <a href="javascript:void(0);" class="btn-submit cancel">Cancel</a>
            <a href="javascript:void(0);" class="btn-submit save">Save</a>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function(){
    $('html').on('click', 'select.filter-op', 'change', function(){
        var $filter_val = $(this).parents('tr').find('input.filter-val');
        switch($(this).val()){
        case 'like':
        case 'not_like':
            $filter_val.val('"%...%"');
            break;
        case 'between':
        case 'not_between':
            $filter_val.val('1, 10');
            break;
        case 'in':
        case 'not_in':
            $filter_val.val('"a", "b", "c"');
            break;
        default:
            $filter_val.val('0');
            break;
        }
    });
    
    $('#btn-clear').click(function(){
        var $dialog = $('#m-dialog');
        $dialog.find('.combo input').val('');
        $dialog.find('.combo option').removeAttr('selected');
        $dialog.find('.filter-op').removeAttr('selected').val();
        $dialog.find('.filter-val').val('0');
        $dialog.find('.filter-extra').val('');
    });
    
    $('#m-btn-cancel').click(function(){
        $('table.item-list td.m-cell div.m-cell-view.hide').trigger('dblclick');
        return false;
    });
});
</script>
