<form action="" method="post" enctype="multipart/form-data" class="m-form">
{+when:ok a=is_success}
    <div class="message-success">
        <span class="icon-ok-sign"></span>
        <em>Command is completed.</em>
        <a href="{return}"><em>(Return to Content List)</em></a>
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
        <legend>Content Item</legend>
        <table class="content-item">
{+cols}
            <tr>
                <th class="name"><label for="col-{name}">{name}:</label></th>
                <td class="type">
                    <select name="info[{name}][type]" data-name="{name}" data-source="{url:self image='1' col-type='txt' col-name=name}">
    {+/types}
                        <option {when:eq a=value b=../type mode=select}>{name}</option>
    {-/types}
                    </select>
                </td>
                <td class="data">
    {+when:eq a=type b='6'}
                    <div class="readonly"><em>{value}</em></div>
    {-when:eq}
    {+when:lt a=type b='3'}
                    <input id="col-{name}" type="text" name="info[{name}][value]" value="{value}" />
    {-when:lt}
    {+when:eq a=type b='3'}
                    <textarea id="col-{name}" name="info[{name}][value]">{value}</textarea>
    {-when:eq}
    {+when:eq a=type b='4'}
                    <div class="m-tabs tabs-2 blob-editor">
                        <ul>
                            <li class="active"><span>File</span></li>
                        {+when:ok a=/is_update}
                            <li><span>Image</span></li>
                        {-when:ok}
                            <li><span>Hex</span></li>
                            <li><span>Text</span></li>
                        </ul>
                        <div>
                            <div>
                            {+when:ok a=/is_update}
                                <p><label class="inline">Download File:</label><a class="btn" href="{url:self col-type='bin' col-name=name}">Download</a></p>
                            {-when:ok}
                                <input type="hidden" name="info[{name}][mode]" value="bin" />
                                <p><label class="inline">Replace with:</label><input type="file" name="files[{name}]" /></p>
                            </div>
                            {+when:ok a=/is_update}
                            <div class="hide">
                                
                                <div class="image"><img src="{url:self image='1' col-type='img' col-name=name}" /></div>
                            </div>
                            {-when:ok}
                            <div class="hide">
                                <div style="text-align:center">
                                    <a href="javascript:void(0);" class="btn m-load" 
                                        data-source="{url:self image='1' col-type='hex' col-name=name}"
                                        data-target="#hex-{name}" title="Load as Hex">Load as Hex</a>
                                </div>
                                <p>
                                    <input type="hidden" name="info[{name}][mode]" value="hex" />
                                    <textarea id="hex-{name}" class="text" name="info[{name}][value]">(no change)</textarea>
                                </p>
                            </div>
                            <div class="hide">
                                <div style="text-align:center">
                                    <a href="javascript:void(0);" class="btn m-load" 
                                        data-source="{url:self image='1' col-type='txt' col-name=name}"
                                        data-target="#txt-{name}" title="Load as Text">Load as Text</a>
                                </div>
                                <p>
                                    <input type="hidden" name="info[{name}][mode]" value="txt" />
                                    <textarea id="txt-{name}" class="text" name="info[{name}][value]">(no change)</textarea>
                                </p>
                            </div>
                        </div>
                    </div>
    {-when:eq}
                </td>
            </tr>
{-cols} 
        </table>
    </fieldset>
    <div class="toolbar">
        <a href="{return}" class="btn-submit btn-cancel">Return to List</a>
        <a href="javascript:void(0);" class="btn-submit m-apply">Save</a>
    </div>
</form>
<div id="data-blank" class="hide">
    <div class="dt-integer">
        <input id="col-#name#" type="text" name="info[#name#][value]" value="#value#" />
    </div>
    <div class="dt-float">
        <input id="col-#name#" type="text" name="info[#name#][value]" value="#value#" />
    </div>
    <div class="dt-text">
        <textarea id="col-#name#" name="info[#name#][value]">#value#</textarea>
    </div>
    <div class="dt-blob">
        <div class="m-tabs tabs-2 blob-editor">
            <ul>
                <li class="active"><span>File</span></li>
    {+when:ok a=/is_update}
                <li><span>Image</span></li>
    {-when:ok}
                <li><span>Hex</span></li>
                <li><span>Text</span></li>
            </ul>
            <div>
                <div>
                    {+when:ok a=/is_update}
                        <p><label class="inline">Download File:</label><a class="btn" href="{url:self col-type='bin' col-name='#name#'}">Download</a></p>
                    {-when:ok}
                    <input type="hidden" name="info[#name#][mode]" value="bin" />
                    <p><label class="inline">Replace with:</label><input type="file" name="files[#name#]" /></p>
                </div> 
        {+when:ok a=/is_update}
                <div class="hide">
                    <div class="image"><img src="{url:self image='1' col-type='img' col-name='#name#'}" /></div>
                </div>
        {-when:ok}
                <div class="hide">
                    <div style="text-align:center">
                        <a href="javascript:void(0);" class="btn m-load" 
                            data-source="{url:self image='1' col-type='hex' col-name='#name#'}"
                            data-target="#hex-#name#" title="Load as Hex">Load as Hex</a>
                    </div>
                    <p>
                        <input type="hidden" name="info[#name#][mode]" value="hex" />
                        <textarea id="hex-#name#" class="text" name="info[#name#][value]">(no change)</textarea>
                    </p>
                </div>
       
                <div class="hide">
                    <div style="text-align:center">
                        <a href="javascript:void(0);" class="btn m-load" 
                            data-source="{url:self image='1' col-type='txt' col-name='#name#'}"
                            data-target="#txt-#name#" title="Load as Text">Load as Text</a>
                    </div>
                    <p>
                        <input type="hidden" name="info[#name#][mode]" value="txt" />
                        <textarea id="txt-#name#" class="text" name="info[#name#][value]">(no change)</textarea>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="dt-null">
        <input type="hidden" name="info[#name#][value]" />
        <div class="readonly"><em>(NULL)</em></div>
    </div>
    <div class="dt-auto">
        <input type="hidden" name="info[#name#][value]" />
        <div class="readonly"><em>AUTO</em></div>
    </div>
    <div class="dt-expr">
        <input id="col-#name#" type="text" name="info[#name#][value]" list="col-expr" />
    </div>
</div>
<div class="hide">
    <datalist id="col-expr">
        <option>NULL</option>
        <option>CURRENT_DATE</option>
        <option>CURRENT_TIME</option>
        <option>CURRENT_TIMESTAMP</option>
    </datalist>
</div>
<script type="text/javascript">
    $(function(){
        $('form').on('click', 'a.m-load', function(){
            var sender = $(this);
            sender.text('Loading...');
            sender.prop('disabled', true);
            $.get(sender.attr('data-source'), function(data){
                $(sender.attr('data-target')).val(data);
                sender.text(sender.attr('title'));
                sender.prop('disabled', false);
            });
        });
        
        $('form').on('change', 'td.type select', function(){
            var sender = $(this);
            
            var name = sender.attr('data-name');
            var html = $('#data-blank div.dt-'+$(this).val().toLowerCase()).html();
            
            var item = sender.parent().next('td.data');
            html = html.replace(/#name#|%23name%23/g, name);
            
            // item.empty().append('<div class="readonly"><em>Loading...</em></div>');
            $.get(sender.attr('data-source'), function(data){
                switch(sender.val()){
                case 'INTEGER':
                    data = parseInt(data).toString();
                    if(data=='NaN') data='0';
                    break;
                case 'FLOAT':
                    data = parseFloat(data).toString();
                    if(data=='NaN') data='0';
                    break;
                case 'TEXT':
                    data = escape(data);
                    break;
                default:
                    data = '';
                    break;
                }
                html = html.replace(/#value#|%23value%23/g, data);
                item.empty().append(html);
            });
        });
    });
</script>
