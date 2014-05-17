(function($){
    var methods = {
        'init': function(options){
            var element = this;
            
            settings = $.extend({
                'handle': 'a.move'
            }, options);
            
            var selected = null;
            var prev = null;
            var next = null;
            
            function mousedown(e){
                selected = $($(this).parentsUntil(element).get(-1));
                
                selected.addClass('picked');
                $('html').on('mousemove', mousemove);
                $('html').on('mouseup', mouseup);
                return false;
            }
            
            function mouseup(e){
                selected.removeClass('picked');
                selected = null;
                $('html').off('mousemove', mousemove);
                $('html').off('mouseup', mouseup);
                
                return false;
            }
            
            function mousemove(e){
                
                prev = selected.prev();
                next = selected.next();
                
                prev = prev.parent().is(element) ? prev : null;
                next = next.parent().is(element) ? next : null;
                
                if(prev && e.pageY < prev.offset().top + prev.height() * 0.5){
                    prev.before(selected);
                }else if(next && e.pageY > next.offset().top + next.height() * 0.5){
                    next.after(selected);
                }
                
                $('>*:even', element).addClass('even').removeClass('odd');
                $('>*:odd', element).addClass('odd').removeClass('even');
                
                return false;
            }
            
            var handle = $(settings.handle, element);
            element.on('mousedown', settings.handle, mousedown)
            element.on('click', settings.handle,function(){ return false; });
        }
    }
    
    $.fn.sortable = function(method){
        var args  = arguments;
        return this.each(function(i, e){
            element = $(this);
            if (methods[method]){
                return methods[method].apply(element, Array.prototype.slice.call(args, 1));
            }else if(typeof method === 'object' || !method){
                return methods.init.apply(element, args);
            }else{
                $.error( 'Method ' +  method + ' does not exist on jQuery.sortable' );
            }
        });
    }
})(jQuery);

(function($){
    var methods = {
        'init': function(options){
            var element = this;
            
            settings = $.extend({
                'handle': 'a.move'
            }, options);
            
            var selected = null;
            
            var startLT = null;
            var mouseLT = null;
            
            function mousedown(e){
                element.css('outline', 'solid 1px #C00');
                
                startLT = element.offset();
                mouseLT = {left: e.pageX, top: e.pageY}; 
                
                $('html').on('mousemove', mousemove);
                $('html').on('mouseup', mouseup);
                
                return false;
            }
            
            function mouseup(e){
                element.css('outline', '');
                
                selected = null;
                $('html').off('mousemove', mousemove);
                $('html').off('mouseup', mouseup);
                
                return false;
            }
            
            function mousemove(e){
                offset = handle.offset();
                element.offset({
                    'left': startLT.left + e.pageX - mouseLT.left, 
                    'top': startLT.top + e.pageY - mouseLT.top
                });
                return false;
            }
            
            var handle = $(settings.handle, element);
            handle.css('cursor', 'pointer');
            element.on('mousedown', settings.handle, mousedown)
            element.on('click', settings.handle, function(){ return false; });
        }
    }
    
    $.fn.draggable = function(method){
        var args  = arguments;
        return this.each(function(i, e){
            element = $(this);
            if (methods[method]){
                return methods[method].apply(element, Array.prototype.slice.call(args, 1));
            }else if(typeof method === 'object' || !method){
                return methods.init.apply(element, args);
            }else{
                $.error( 'Method ' +  method + ' does not exist on jQuery.sortable' );
            }
        });
    }
})(jQuery);

(function($){
    var methods = {
        'init': function(options){
            var element = $(this);
            
            $('div.dialog', this).draggable({
                'handle': 'div.dialog-header'
            });
            
            var settings = $.extend({
                'init': function(){},
                'open': function(){},
                'save': function(){},
                'reset': function(){},
                'cancel': function(){}
            }, options);
            
            element.on('click','a.reset, button.reset, input.reset', function(){
                element.dialog('reset');
                return false;
            });
            
            element.on('click','a.save, button.save, input.save', function(){
                element.dialog('save');
                return false;
            });
            element.on('click','a.cancel, button.cancel, input.cancel', function(){
                element.dialog('cancel');
                return false;
            });
            element.data('settings', settings);
            return element;
        },
        
        'open': function(){
            var settings = $(this).data('settings');
            
            $(this).removeClass('hide');
            if(settings.open.apply(this, arguments)!==false){
                var dialog = $('div.dialog', this);
                var dx = ($(window).width() - dialog.width()) * 0.5;
                var dy = ($(window).height() - dialog.height()) * 0.5 - 40;
                if(dy < 1){
                    dy = $(window).scrollTop() + 1;
                }else{
                    dy = $(window).scrollTop() + dy; 
                }
                dialog.offset({'left': dx, 'top': dy});
            }
        },
        'save': function(){
            var settings = $(this).data('settings');
            if(settings.save.apply(this, arguments)!==false){
                $(this).addClass('hide');
            }
        },
        'cancel': function(){
            var settings = $(this).data('settings');
            if(settings.cancel.apply(this, arguments)!==false){
                $(this).addClass('hide');
            }
        },
        'reset': function(){
            var settings = $(this).data('settings');
            settings.reset.apply(this, arguments);
        }
    }
    
    $.fn.dialog = function(method){
        var args  = arguments;
        
        return this.each(function(){
            if (methods[method]){
                return methods[method].apply(this, Array.prototype.slice.call(args, 1));
            }else if(typeof method === 'object' || !method) {
                return methods.init.apply(this, args);
            }else{
                $.error( 'Method ' +  method + ' does not exist on jQuery.dialog' );
            }
        });
    }
})(jQuery);

(function($){
    var methods = {
        'init': function(options){
            var element = $(this);
            var settings = $.extend({
                'select': function(element){}
            }, options);
            
            if(!element.data('states')) element.data('states', {});
            var states = element.data('states');
            states.is_on = false;
            states.index = null;
            
            var subs = element.find('div.subs ul');
        
            $('html').click(function(){
                $('div.menu').parent().menu('close');
            });
            
            element.on('click', 'div.menu a', function(e){
                var element = $(this).parent().parent();
                var subs = element.find('div.subs ul');
                var states = element.data('states');
                
                e.stopPropagation();
                
                if(!states.is_on || states.index!=$(this).index){
                    states.is_on = true;
                    
                    subs.addClass('hide');
                    
                    element.find('a').removeClass('active');
                    $(this).addClass('active');
                    
                    var left  = $(this).position().left;
                    var item  = $(subs[$(this).index()]);
                    if(left + item.width() > item.parent().width()-1){
                        left = item.parent().width() - item.width();
                    }
                    item.removeClass('hide').css({ left: left });
                }else{
                    element.menu('close');
                }
                element.index = $(this).index();
            });
            
            element.on('mouseover', 'div.menu a', function(e){
                var element = $(this).parent().parent();
                var subs = element.find('div.subs ul');
                var states = element.data('states');
                
                if(states.is_on){
                    subs.addClass('hide');
                    
                    element.find('a').removeClass('active');
                    $(this).addClass('active');
                    
                    var left  = $(this).position().left;
                    var item  = $(subs[$(this).index()]);
                    if(left + item.width() > item.parent().width()-1){
                        left = item.parent().width() - item.width();
                    }
                    item.removeClass('hide').css({ left: left });
                }
            });
            
            element.on('click', 'div.subs li', function(){
                var element = $(this).parent().parent().parent();
                var subs = element.find('div.subs ul');
                var states = element.data('states');
                
                element.menu('select', $(this));
                return false;
            });
            
            element.data('settings', settings);
            element.data('state', states);
            return element;
        },
        
        'select': function(){
            var settings = $(this).data('settings');
            settings.select.apply(this, arguments);
            
            $(this).menu('close');
            
        },
        'close': function(){
            var state = $(this).data('states');
            
            $(this).find('div.subs ul').addClass('hide');
            $(this).find('a').removeClass('active');
            
            state.is_on = false;
            state.index = null;
           
        }
    }
    $.fn.menu = function(method){
        var args  = arguments;
        
        return this.each(function(){
            if (methods[method]){
                return methods[method].apply(this, Array.prototype.slice.call(args, 1));
            }else if(typeof method === 'object' || !method) {
                return methods.init.apply(this, args);
            }else{
                $.error( 'Method ' +  method + ' does not exist on jQuery.dialog' );
            }
        });
    }
})(jQuery);
//----------------------------------------------------------------------
function do_post(element, url){
    var form = $('<form method="POST" action="" class="hide"></form>');
    $(element).find('.m-blank').remove();
    $(element).find('table.m-list').each(function(){
        var list = $(this);
        list.find('> tbody > tr').each(function(i, e){
            $('input, select, textarea', this).each(function(j, e){
                if(e.name && $(this).parents('table.m-list').is(list)) e.name = e.name.replace('[0]', '['+i+']');
            });
        });
    });
    
    if(url) form.attr('action', url);
    
    form.append(element);
    $('body').append(form);
    form.submit();
}

function update_url(url, args){
    var parts = url.split('?',2);
    var pairs = parts[1].split('&');
    for (var i=0; i<pairs.length; i++){
        pair = pairs[i].replace(/\+/g, ' ').split('=', 2);
        pair[0] = decodeURIComponent(pair[0]);
        pair[1] = decodeURIComponent(pair[1]);
        if(typeof(args[pair[0]])=='undefined') args[pair[0]] = pair[1];
    }
    
    var items = [];
    for(var i in args){
        if(typeof args[i] === 'string'){
            items.push(encodeURIComponent(i)+'='+encodeURIComponent(args[i]));
        }
    }
    
    return items.length > 0 ? parts[0]+'?'+items.join('&') : parts[0];
}
//----------------------------------------------------------------------
$(function(){
    if($('#m-dialog').length){
        var dialog = $('#m-dialog').dialog({
            'open': function(target, submit){
                dialog.target = target;
                dialog.submit = submit;
                
                dialog.find('div.dialog-header span').text(target.attr('title'));
                
                dialog.find('div.dialog-body').empty()
                    .append(target.children('div.m-container').clone(true, true));
                dialog.find('[value="random"]:radio').each(function(){  // fix IE bug
                    $(this).prop('checked', true).attr('checked', 'checked');
                });
                
                dialog.editors = [];
                $('textarea.m-code-small', this).each(function(){
                    dialog.editors.push(CodeMirror.fromTextArea(this, {
                        mode: 'sqlite',
                        lineNumbers: true,
                        matchBrackets: true
                    }));
                });
            },
            'save': function(){
                dialog.find('select').each(function(){
                    var val = $(this).val();
                    $(this).find('option').each(function(){
                        if($(this).val()==val){
                            $(this).attr('selected','selected');
                        }else{
                            $(this).removeAttr('selected');
                        }
                    });
                });
                
                if(dialog.editors){
                    for(var i=0; i<dialog.editors.length; i++){
                        dialog.editors[i].toTextArea();
                    }
                }
                
                if(dialog.submit){
                    var url = dialog.submit=='true' ? window.location.href : dialog.submit;
                    do_post(dialog.find('div.m-container')[0], url);
                }else{
                    dialog.target.empty().append(dialog.find('div.m-container')[0]);
                    
                    var m_select = $(dialog.target.parents('td')[0]).find('select.m-modal-select');
                    if(m_select.length){
                        var g = dialog.target.find('p label input:checked').parents('p');
                        if(g && g.attr('data-summary')){
                            var s = g.attr('data-summary');
                            s = s.replace(/\[[$\-\w]+\]/g, function(a){
                                return g.find('*.mm-'+a.slice(1, -1)).val();
                            });
                            
                            if(s.length > 42) s = s.substr(0,42) + '...';
                            m_select.find('option:selected').text(s);
                        }
                    }
                }
            },
            'reset': function(){
                dialog.find('form')[0].reset();
            }
        });
    }
    // m-form
    $('form.m-form').each(function(){
        var form = $(this);
        // m-sort
        form.find('tbody.m-sortable').sortable({'handle': 'a.m-handle'});
        // m-check
        form.on('click', 'input.m-check', function(){
            var $this = $(this);
            if($this.parents('tfoot, thead').length > 0 ){
                var table = $(this).parents('table')[0];
                $(table).find('input.m-check').prop('checked', this.checked);
                
                if(this.checked){
                    $(table).find('> tbody > tr').addClass('selected');
                }else{
                    $(table).find('> tbody > tr').removeClass('selected');
                }
            }else{
                var $tr = $this.parents('tr');
                if(this.checked){
                    $tr.addClass('selected');
                }else{
                    $tr.removeClass('selected');
                }
            }
        });
        
        // m-apply
        form.on('click', 'a.m-apply', function(){
            var sender = $(this);
            $('input[name="action"]', form).val(sender.attr('data-action'));
            if(sender.attr('data-item')){
                var item = sender.attr('data-item').split(':'); 
                $('input[name="'+item[0]+'"]').val(item[1]);
            }
            
            $('.m-blank', form).remove();
            
            
            $('table.m-list > tbody > tr', form).each(function(i, e){
                $('input, select, textarea', this).each(function(j, e){
                    if(e.name) e.name = e.name.replace('[0]', '['+i+']');
                });
            });
            
            if(sender.attr('data-confirm')){
                if(!confirm(sender.attr('data-confirm'))) return false;
            }
            if(sender.attr('data-post')){
                form.attr('action', sender.attr('data-post'));
            }
            
            $(form).submit();
            return false;
        });
        
        form.on('click', 'a.m-swift', function(){
            var table = $($(this).parents('table')[0]);
            switch($(this).attr('data-action')){
            case 'append':
                var item = table.find('> tfoot > tr.m-blank').clone(true, true);
                item.removeAttr('class').removeAttr('id');
                table.find('> tbody').append(item);
                break;
            case 'delete':
                $($(this).parents('tr')[0]).remove();
                break;
            }
            table.find('> tbody > tr:even').removeClass('odd').addClass('even');
            table.find('> tbody > tr:odd').removeClass('even').addClass('odd');
            return false;
        });
        
        form.on('click', 'a.m-modal', function(){
            var action = $(this).attr('data-action');
            var sender = $(this);
            
            switch(action){
            case 'update':
                var target = $($(this).parents('tr')[0]).find($(this).attr('data-target'));
                dialog.dialog('open', target, $(this).attr('data-apply'));
                dialog.find('input[name="action"]').val('update');
                break;
            case 'delete':
                var target = $($(this).parents('tr')[0]).find($(this).attr('data-target'));
                target.find('input[name="action"]').val('delete');
                
                do_post(target[0]);
                break;
            default:
                var target = $($(this).attr('data-target'));
                dialog.dialog('open', target, $(this).attr('data-apply'));
                dialog.find('input[name="action"]').val(action);
                if(sender.attr('data-item')){
                    var item = sender.attr('data-item').split(':'); 
                    dialog.find('input[name="'+item[0]+'"]').val(item[1]);
                }
                break;
            }
        });
        
        $('select.m-modal-select').each(function(){
            $(this).find('option').each(function(){
                $(this).attr('data-title', $(this).text());
            });
        });
        
        form.on('change', 'select.m-modal-select', function(){
            $(this).find('option').each(function(){
                $(this).text($(this).attr('data-title'));
            });
            var container = $(this).parent().find('div.m-modal-target div.m-container').empty();
            var target = $('#m-modal-target-'+$(this).val());
            
            if(target.length > 0){
                $(this).parent().find('a.m-modal').removeClass('hide');
                container.append(target.clone());
                var g = target.find('p label input:checked').parents('p');
                if(g && g.attr('data-summary')){
                    var s = g.attr('data-summary');
                    
                    s = s.replace(/\[[$\-\w]+\]/g, function(a){
                        return g.find('*.mm-'+a.slice(1, -1)).val();
                    });
                    $(this).find('option:selected').text(s);
                }
            }else{
                $(this).parent().find('a.m-modal').addClass('hide');
            }
        });
    });
    
    //m-code
    $('textarea.m-code').each(function(){
        var editor = CodeMirror.fromTextArea(this, {
            mode: 'sqlite',
            lineNumbers: true,
            matchBrackets: true
        });
        
        if(!window.ceditors) window.ceditors = {};
        window.ceditors[this.id] = editor;
    });
    
    // combobox
    $('html').on('change', '.combo select', function(){
        $('input[type="text"]',$(this).parent()).val($(this).val());
    });
    
    $('.combo').each(function(){
        var $this = $(this);
        var val = $this.find('input').val();
        $this.find('select option').each(function(){
            if(val==$(this).prop('value')){
                $(this).attr('selected', 'selected');
                $(this).prop('selected', true);
            }
        });
    });
    
    // m-jump
    if($('div.m-jump').length){
        setTimeout(function(){
            window.location = $('div.m-jump').attr('data-jump');
        }, 1000);
    }
    
    // fix mlist width for ie
    if($('#m-item-list').length){
        var mlist = $('#m-item-list');
        mlist.css('width', 'auto');
        mlist.parent().css('width', '1000000px');
        mlist.css('width', 'auto').css('min-width','0');
        mlist.parent().css('width', mlist.outerWidth() + 8 + 'px');
        if(mlist.outerWidth() < mlist.parent().parent().width()){
            mlist.parent().css('width', 'auto');
            mlist.css('width', '98%');
        }
    }
    
    $('html').on('click', '.m-toggle', function(){
        $($(this).attr('data-target')).toggleClass('hide');
    });
    
    // inline edit
    $('#m-item-list').on('dblclick', 'tbody td',function(){
        
        
        var $this = $(this);
        var $edit = $this.children('div.m-cell-edit');
        var $view = $this.children('div.m-cell-view');
        var $chck = $(this).parent().find('td:first-child input.m-check');
        
        if($edit.attr('data-type')=='4') return;
        
        if($edit.hasClass('hide')){
            var width = $this.width();
            var height = $this.height();
            $this.css('width', width+'px').css('height', height+'px');
            
            var pl = parseInt($view.css('padding-left'));
            var pr = parseInt($view.css('padding-right'));
            var pt = parseInt($view.css('padding-top'));
            var pb = parseInt($view.css('padding-bottom'));
            
            $edit.css('width', width + 'px')
                .css('height', height  + 'px');
            
            var $item = null;
            var name = 'items[' + $edit.attr('data-id') + 
                '][' + $edit.attr('data-name') + ']';
            var type = $edit.attr('data-type');
            if($edit.attr('data-size')=='3'){
                $item = $('<textarea name="' + name + '"></textarea>');
            }else{
                $item = $('<input name="' + name + '" type="text" />');
            }
            $item.css('width', width - pl - pr + 'px')
                .css('height', height - pt - pb + 'px');
                
            if(parseInt($edit.attr('data-type')) < 4){
                var value =  $view.children('span').text();
                if($edit.attr('data-size')=='3' && value.substr(value.length-3)=='...'){
                    if(!confirm('This text is the first part of a longer text. If you edit it, the rest part will lose. Do you still want to edit it?')) return; 
                }
                $item.val(value);
            }
            
            $edit.append('<input type="hidden" name="'+ name.replace('items', 'metas') + '" value="'+ type +'" />');
            $edit.append($item);
            
            $chck.prop('checked', true);
            $this.parent().addClass('selected');
            
            $edit.removeClass('hide');
            $view.addClass('hide');
            
            $('#toolbar-save').show('fast');
            $item.focus();
        }else{
            $edit.addClass('hide').empty();
            $view.removeClass('hide');
            if($this.parent().find('div.m-cell-edit :input').length==0){
                $chck.prop('checked', false);
                $this.parent().removeClass('selected');
            }
            
            if($('#m-item-list .m-cell-edit :input').length==0){
                $('#toolbar-save').hide('slow');
            }
        }
    }); 
//----------------------------------------------------------------------
    $('select.m-change-database').change(function(){
        window.location = update_url($(this).attr('data-url-base'), {'database':$(this).val()});
    });

    $('#sidebar').on('click','ul li span.icon', function(){
        var a = $(this).parent();
        var span = $(this);
        if(span.hasClass('icon-folder-open')){
            span.removeClass('icon-folder-open');
            span.addClass('icon-folder-close');
            $('ul', a.parent()).slideUp();
            return false;
        }else if(span.hasClass('icon-folder-close')){
            span.removeClass('icon-folder-close');
            span.addClass('icon-folder-open');
            $('ul', a.parent()).slideDown();
            return false;
        }
    });
    $('body').on('click', ' div.m-tabs > ul > li', function(){
        var i = $(this).index();
        var menu = $(this).parent();
        var body = menu.next();
        $(menu.find('li').removeClass('active').get(i)).addClass('active');
        $(body.children('div').addClass('hide').get(i)).removeClass('hide');
        $(body.find('textarea,input')).attr('disabled', true);
        $(body.children('div').get(i)).find('textarea[disabled],input[disabled]').removeAttr('disabled');
        
        if(typeof window.ceditors != 'undefined'){
            for(var i in window.ceditors){
                window.ceditors[i].refresh();
            }
        }
    });
    
    setInterval(function(){
        $.get('index.php?i=fresh&rand='+Math.random()); // update session per 60s
    }, 60000); 
//----------------------------------------------------------------------
});
