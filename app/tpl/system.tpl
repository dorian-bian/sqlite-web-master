
<fieldset>
    <p><label>Web Application Path:</label> <span>{physical_path}</span></p>
</fieldset>
<table class="status-list">
<tr>
    <td class="status-left">
        <fieldset>
            <legend>Server Information</legend>
            <table class="content-item">
                <tr><th><label>Operating System:</label></th><td>{php_os}</td></tr>
                <tr><th><label>Machine Type:</label></th><td>{php_arch}</td></tr>
                <tr><th><label>PHP Version:</label></th><td>{php_version}</td></tr>
                <tr><th><label>PHP SAPI Name:</label></th><td>{php_sapi_name}</td></tr>
                <tr><th><label>Max Memory Limit:</label></th><td>{memory_limit}</td></tr>
                <tr><th><label>Max Post Limit:</label></th><td>{post_max_size}</td></tr>
                <tr><th><label>Max Upload Limit:</label></th><td>{upload_max_filesize}</td></tr>
            </table>
        </fieldset>
    </td>
    <td class="status-right">
        <fieldset>
            <legend>SQLite3 Infomation</legend>
            <table class="content-item">
                <tr>
                    <th><label>Module Version:</label></th>
                    <td>{sqlite_module_version}</td>
                </tr>
                <tr>
                    <th><label>Library Version:</label></th>
                    <td>{sqlite_library_version}</td>
                </tr>
                <tr>
                    <th><label>Compile Options:</label></th>
                    <td>
{+sqlite_compile_options}
                        <span class="status-option">{compile_option}</span>
{-sqlite_compile_options}
                    </td>
                </tr>
                <tr>
                    <th><label>Collation List:</label></th>
                    <td>
{+sqlite_collation_list}
                        <span class="status-option">{name}</span>
{-sqlite_collation_list}
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>
</table>
{+unit:item mode=node}
<div class="m-container">
    <fieldset>
        <input type="hidden" name="action" value="" />
        <input type="hidden" name="group"  value="{../name}" />
        <input type="hidden" name="base[0]"  value="{base?}" />
        <p>
            <label class="inline">Filename:</label>
            <input type="text" name="base[1]" value="{base?}" />
            <span class="m-tail">{tail?}</span>
        </p>
    </fieldset>
</div>
{-unit:item}
<div id="new-database" class="hide" title="New Database">{unit:item}</div>

<form action="" method="post" class="m-form">
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
        <a href="javascript:void(0);" class="btn m-apply" data-action="vacuum" >
            <span class="icon-resize-small"></span> Vacuum
        </a>
    </fieldset>
    <table class="item-list">
    <thead>
        <tr class="item-list-header">
            <td colspan="8">
                <span class="info">(Link to):</span>
                <input type="hidden" name="action" />
                <input type="hidden" name="names" /> 
            </td>
        </tr>
        <tr>
            <th width="20"><input type="checkbox" class="m-check" data-target="input.item-name" /></th>
            <th>Name</th>
            <th>Path</th>
            <th width="80">Size</th>
            <th width="80">Free</th>
            <th colspan="3"></th>
        </tr>
    </thead>
    <tbody>
{+databases}
        <tr class="{tick:even}">
            <td><input type="checkbox" name="names[]" class="m-check" value="{name}" /></td>
            <td>{name}</td>
            <td>{path}</td>
            <td>{size}</td>
            <td>{free}</td>
            <td width="20">
                <a href="javascript:void(0);" class="icon-resize-small m-apply" 
                    title="vacuum" data-action="vacuum" data-item="names:{name}"></a>
            </td>
            <td width="20">
                <a href="javascript:void(0);" class="icon-download-alt m-apply" 
                    title="download" data-action="download" data-item="names:{name}"></a>
            </td>
            <td width="20">
                
            </td>
        </tr>
{-databases}
    </tbody>
    <tfoot>
        <tr><td colspan="8"></td></tr>
    </tfoot>
    </table>
{+db_groups}
    <table class="item-list">
    <thead>
        <tr class="item-list-header">
            <td colspan="9">
                <a href="javascript:void(0);" class="btn m-modal m-create" 
                    data-action="create" data-target="#new-database" data-tail="{tail}"
                    data-apply="true" data-item="group:{name}">New Database</a>
                <span class="info">{name}</span>
                <input type="hidden" name="action" />
                <input type="hidden" name="names" /> 
            </td>
        </tr>
        <tr>
            <th width="20"><input type="checkbox" class="m-check" data-target="input.item-name" /></th>
            <th>Name</th>
            <th>Path</th>
            <th width="80">Size</th>
            <th width="80">Free</th>
            <th colspan="3"></th>
        </tr>
    </thead>
    <tbody>
    {+subs}
        <tr class="{tick:even}">
            <td><input type="checkbox" name="names[]" class="m-check" value="{name}" /></td>
            <td>
                <a href="javascript:void(0);" class="m-modal"
                    data-action="update" data-target="div.rename-database" data-apply="true">{name}</a>
                <div class="rename-database hide" title="Rename to">{unit:item}</div>
            </td>
            <td>{path}</td>
            <td>{size}</td>
            <td>{free}</td>
            <td width="20">
                <a href="javascript:void(0);" class="icon-resize-small m-apply" 
                    title="vacuum" data-action="vacuum" data-item="names:{name}"></a>
            </td>
            <td width="20">
                <a href="javascript:void(0);" class="icon-download-alt m-apply" 
                    title="download" data-action="download" data-item="names:{name}"></a>
            </td>
            <td width="20">
                <a href="javascript:void(0);" class="icon-remove m-apply" 
                    title="remove" data-action="remove" data-item="names:{name}"></a>
            </td>
        </tr>
    {-subs}
    {+when:no a=subs}
        <tr><td colspan="9" class="item-empty">(empty)</td></tr>
    {-when:no}
    </tbody>
    <tfoot>
        <tr><td colspan="9"></td></tr>
    </tfoot>
    </table>
{-db_groups}
</form>
<div id="m-dialog" class="modal hide">
    <div class="overlay"></div>
    <div class="dialog" style="width:580px;">
        <div class="dialog-header">
            <span></span>
        </div>
        <form>
            <div class="dialog-body"></div>
        </form>
        <div class="toolbar">
            <a href="javascript:void(0);" class="btn-submit cancel">Cancel</a>
            <a href="javascript:void(0);" class="btn-submit save">Save</a>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        $('html').on('click', 'a.btn.m-create', function(){
            $('#m-dialog span.m-tail').text($(this).attr('data-tail'));
        });
    });
</script>
