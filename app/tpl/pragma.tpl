<form action="" method="post" class="m-form content-item">
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
{+groups}
    <fieldset>
        <legend>{title}</legend>
        <table class="content-item">
    {+items}
            <tr title="{name}">
                <th class="th-wide"><label for="{name}">{title}: </label></th>
                <td>
        {+when:eq a=field b='input.check'}
                    <input type="checkbox" value="1"{when:ok a=active mode=check}  name="{name}" id="{name}" />
        {-when:eq}
        {+when:eq a=field b='select'}
                    <select name="{name}" id="{name}">
            {+options}
                        <option value="{value}"{when:ok a=active mode=select}>{title}</option>
            {-options}
                    </select>
        {-when:eq}
        {+when:eq a=field b='input.int'}
                    <input type="text" class="text" id="{name}" name="{name}" value="{value}" />
        {-when:eq}
        {+when:eq a=field b='text'}
                    <span>{value}</span>
        {-when:eq}
                </td>
            </tr>
    {-items}
        </table>
    </fieldset>
{-groups}
    <div class="toolbar"><a href="javascript:void(0);" class="btn-submit m-apply">Save</a></div>
</form>
