<ul class="sub-tabs">
{+tabs}
    <li{when:ok a=active mode=active}>
        <a href="{url:base i='import' source=data:/source_name type=type}">{title}</a>
    </li>
{-tabs}
</ul>
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
<form action="" method="post" enctype="multipart/form-data" class="m-form">
    <fieldset>
        <ul>
            <li>File may be compressed (gzip, bzip2, zip) or uncompressed.<br/>
                A compressed file's name must end in .[compression]. Example: .zip
            </li>
{+when:no a=is_strict}
            <li>
                <label><input type="radio" name="mode" value="path" />Physical Path:</label>
                <input type="text" style="width:320px;" name="path" />
            </li>
{-when:no a=is_strict}
            <li>
                
                <label><input type="radio" name="mode" value="file" checked="checked" />Upload File:</label>
                    <input type="file" name="file" /> (max: {upload_max_filesize})
            </li>
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
        </ul>
    </fieldset>
{+when:eq a=type b='csv'}
    <fieldset>
        <legend>CSV Options</legend>
        <ul>
            <li>
                <label><input type="radio" name="opts[csv][create]" value="1"  checked="checked"/>Create new table(using filename as table name)</label>
    {+when:ok a=/source_name}
                <label><input type="radio" name="opts[csv][create]" value="0" />Use current table: {/source_name}</label>
    {-when:ok}
            </li>
            <li>
                
                <label><input type="checkbox" name="opts[csv][inc_header]" checked="checked" />First row is header</label>
            </li>
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
{+when:eq a=type b='mdb'}
    <fieldset>
        <legend>MDB Options</legend>
        <ul>
            <li>
                <table>
                    <tr>
                        <td><label>Username</label></td>
                        <td><input type="text" class="text" name="opts[mdb][user]" value="Admin" /></td>
                        <td><label>Password</label></td>
                        <td><input type="text" class="text" name="opts[mdb][pass]" value="" /></td>
                    </tr>
                </table>
            </li>
        </ul>
    </fieldset>
{-when:eq}
{+when:eq a=type b='xls'}
    <fieldset>
        <legend>XLS Options</legend>
        <ul>
            <li><label>Excel Version: </label><input type="text" name="opts[xls][ver]" value="Excel 12.0 xml" /></li>
            <li><label><input type="checkbox" name="opts[xls][hdr]" checked="checked" />First row is header</label></li>
            <li><label><input type="checkbox" name="opts[xls][imex]" checked="checked" />The column data is numeric.(safer)</label></li>
        </ul>
    </fieldset>
{-when:eq}
    <fieldset>
        <legend>Execution Options</legend>
        <ul>
{+when:ne a=type b='sql'}
            <li><label><input type="checkbox" name="opts[exe][empty]" checked="checked" />Override existed tables.</label></li>
{-when:ne}
            <li><label><input type="checkbox" name="opts[exe][transaction]" checked="checked" />Enclose in a Transaction.</label></li>
        </ul>
    </fieldset>
    <div class="toolbar"><a href="javascript:void(0);" class="btn-submit m-apply">Import</a></div>
</form>
