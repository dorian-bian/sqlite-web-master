{+unit:css}
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="{site:root}/res/codemirror/twilight.css" />
{-unit:css}
<form action="" method="post" class="m-form">
{+when:ok a=is_success}
    <div class="message-success">
        <span class="icon-ok-sign"></span>
        <em>Command is completed.</em>
        <a href="{url:base i='view-list'}"><em>(Return to View List)</em>em></a>
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
        <table class="view-item">
            <tr>
                <th><label>View Name: </label></th>
                <td><input type="text" name="name" value="{name}" /><br/></td>
            </tr>
            <tr>
                <th><label>Statement:</label></th>
                <td><textarea name="select" class="m-code">{select}</textarea></td>
            </tr>
        </table>
    </fieldset>
{+when:ok a=is_edit}
    <table class="item-list column-list">
        <thead>
            <tr>
                <th width="30">ID</th>
                <th>Column Name</th>
                <th width="120">Type</th>
            </tr>
        </thead>
        <tbody id="items">
    {+columns}
            <tr class="{tick:even}">
                <td>{tick:i}</td>
                <td>{name}</td>
                <td>{type}</td>
            </tr>
    {-columns}
        </tbody>
    </table>
{-when:ok}
    <div class="toolbar">
        <a href="{url:base i='view-list'}" class="btn-submit btn-cancel">Return to List</a>
        <a href="javascript:void(0);" class="btn-submit m-apply">Save</a>
    </div>
</form>
<script type="text/javascript" src="{site:root}/res/codemirror/codemirror.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/matchbrackets.js"></script>
<script type="text/javascript" src="{site:root}/res/codemirror/sqlite.js"></script>
