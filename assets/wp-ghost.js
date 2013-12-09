  wpGhost = {
    api_base : '/api/v0.1',
    ghost_base : '/ghost',
    plugin_base : '/wp-content/plugins/wp-ghost',
    init : function(api_base,ghost_base,plugin_base) {
      if (api_base) wpGhost.api_base = api_base;
      if (ghost_base) wpGhost.ghost_base = ghost_base;
      if (plugin_base) wpGhost.plugin_base = plugin_base;
      var address = document.location.pathname;
      address = address.split(wpGhost.ghost_base);
      address = address[1].slice(1);
      address = address.split('/');
      jQuery('.subnav a').click(function(){
        var toggle_object = jQuery(jQuery(this).attr('data-toggle'));
        toggle_object.fadeIn(500);
        jQuery(document).mouseup(wpGhost.hide_sub);
      });
      switch(address[0]) {
        case 'editor' :
          wpGhost.init_editor(address[1]);
        break;
        case 'page'  :
          wpGhost.init_list('page', 'All Pages');
        break;
        case 'attachment'  :
          wpGhost.init_list('attachment', 'All Files');
        break;
        case 'forgotten'  : 
          wpGhost.init_forgotten();
        break;
        case 'login'  : 
          wpGhost.init_login();
        break;
        case 'post'  :
        default       :  
          wpGhost.init_list('post', 'All Posts');
        break;
      }
      setInterval('wpGhost.update_times()',30000);
    },
    hide_sub : function(e){
          var toggle_object = jQuery(jQuery('.subnav a').data('toggle'));
          if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
            toggle_object.fadeOut(200);
            jQuery(document).off('mouseup',wpGhost.hide_sub);
          }
    },
    init_login  : function() {
      jQuery('#login').submit(function(event){
        event.preventDefault();
        if (jQuery('input[name=email]:invalid').size()>0) {
          wpGhost.throw_error('Invalid/empty username');
        } else if (jQuery('input[name=email]').val()==''){
          wpGhost.throw_error('Invalid/empty username');
        } else if (jQuery('input[name=password]:invalid').size()>0) {
          wpGhost.throw_error('Invalid/empty password');
        } else if (jQuery('input[name=password]').val()==''){
          wpGhost.throw_error('Invalid/empty password');
        } else {
          wpGhost.api(
            '/session',
            'POST',
            {
              'username':jQuery('input[name=email]').val(),
              'password':jQuery('input[name=password]').val()
            },
            function(resp){
              console.log(resp);
              if (typeof resp.error != 'undefined') {
                wpGhost.throw_error(resp.error);
              } else if (typeof resp.success != 'undefined') {
                wpGhost.go_to(wpGhost.ghost_base);
              }
            }
          );
        }
     });
    },
    init_forgotten  : function() {
      jQuery('#forgotten').submit(function(event){
        event.preventDefault();
        if (jQuery('input[name=email]:invalid').size()>0) {
          wpGhost.throw_error('Invalid/empty username');
        } else if (jQuery('input[name=email]').val()==''){
          wpGhost.throw_error('Invalid/empty username');
        } else {
          wpGhost.api(
            '/password',
            'POST',
            {
              'username':jQuery('input[name=email]').val()
            },
            function(resp){
              console.log(resp);
              if (typeof resp.error != 'undefined') {
                wpGhost.throw_error(resp.error);
              } else if (typeof resp.success != 'undefined') {
                wpGhost.throw_success('Password reset link sent to your e-mail address.');
                setTimeout('wpGhost.go_to(wpGhost.ghost_base+\'/login\')',3000);
              }
            }
          );
        }
      });
    },
    throw_error : function(text) {
      var a = jQuery('#notifications')
      .append(wpGhost.templates.notice_error.replace('%text%',text))
      .children()
      .last()
      .find('.close')
      .click(wpGhost.close_error);
    },
    throw_success : function(text) {
      var a = jQuery('#notifications')
      .append(wpGhost.templates.notice_success.replace('%text%',text))
      .children()
      .last();
      a
      .find('.close')
      .click(wpGhost.close_error);
      a.delay(4000).queue(function(){
        jQuery(this).remove();
      });
    },
    close_error : function(e) {
      jQuery(this).parent().parent().remove();
      e.preventDefault();
    },
    update_editor :function(entry) {
      var id = entry.id;
      jQuery('.post-setting-slug').val(entry.slug);
      jQuery('.post-setting-slug').attr('data-slug',entry.slug);
      jQuery('body').data('id',entry.id);
      if (entry.published_at) {
        jQuery('.post-setting-date').val(moment(entry.published_at).format('YYYY MMM DD HH:mm'));
        jQuery('.post-setting-date').attr('data-date',moment(entry.published_at).format('YYYY MMM DD HH:mm'));
      }
      jQuery('.entry-settings').click(function(event){
        event.preventDefault();
        var toggle_object = jQuery(jQuery(this).data('toggle'));
        toggle_object.show();
        jQuery(document).mouseup(wpGhost.hide_settings_editor);
      });
      jQuery('.button-save').attr('data-status',entry.status);
      switch (entry.status) {
        case 'draft' : 
          jQuery('.button-save').html('Save Draft');
          jQuery('ul.editor-options').html('');
          if (moment(entry.published_at).unix() > moment().unix() ) {
            jQuery('ul.editor-options').append('<li data-status="published"><a href="#">Schedule Post</a></li>');
          } else  {
            jQuery('ul.editor-options').append('<li data-status="published"><a href="#">Publish Post</a></li>');          
          }
          jQuery('ul.editor-options').append('<li data-status="draft" class="active"><a href="#">Save Draft</a></li>');
        break;
        case 'published' : 
          jQuery('.button-save').html('Update Post');
          jQuery('ul.editor-options').html('');
          jQuery('ul.editor-options').append('<li data-status="published" class="active"><a href="#">Update Post</a></li>');
          if (moment(entry.published_at).unix() > moment().unix() ) {
            jQuery('ul.editor-options').append('<li data-status="draft"><a href="#">Unschedule Post</a></li>');
          } else  {
            jQuery('ul.editor-options').append('<li data-status="draft"><a href="#">Unpublish Post</a></li>');
          }
        break;
        case 'pending' : 
          jQuery('.button-save').html('Save Changes');
          jQuery('ul.editor-options').html('');
          jQuery('ul.editor-options').append('<li data-status="published"><a href="#">Confirm Post</a></li>');
          jQuery('ul.editor-options').append('<li data-status="pending" class="active"><a href="#">Save Changes</a></li>');
        break;
        case 'scheduled' : 
          jQuery('.button-save').html('Save Changes');
          jQuery('ul.editor-options').html('');
          jQuery('ul.editor-options').append('<li data-status="scheduled" class="active"><a href="#">Save Changes</a></li>');
          jQuery('ul.editor-options').append('<li data-status="draft"><a href="#">Unshedule Post</a></li>');
        break;
      }
      jQuery('ul.editor-options li').click(wpGhost.save_post);
      jQuery('.button-save').click(wpGhost.save_post);
      jQuery('a.delete').attr('href',wpGhost.ghost_base+'/delete/'+entry.id);
      jQuery('a.delete').click(function(event){
        event.preventDefault();
        wpGhost.show_dialog(
          'Are you sure you want to delete this post?',
          function(){
            wpGhost.api(
              '/post/'+id,
              'DELETE',
              {},
              function(resp){
                wpGhost.hide_dialog();
                if (typeof resp.id != 'undefined') {
                  window.location = wpGhost.ghost_base+'/';
                } else {
                  wpGhost.throw_error(resp.error);
                }
              }
            );
          }
        );
      });
    },
    init_editor : function(id){
      jQuery('#main').html(wpGhost.templates.aside + wpGhost.templates.editor);
      jQuery('body').attr('class','editor');
      jQuery('body').data('id',id);
      wpGhost.api(
        '/post/'+id,
        'GET',
        {},
        function(resp){
          var entry = resp.post;
          var id = entry.id;
          jQuery('#entry-title').val(entry.title);
          jQuery('#entry-markdown').html(entry.markdown);
          wpGhost.editor = CodeMirror.fromTextArea(document.getElementById('entry-markdown'), {
            mode: 'gfm',
            tabMode: 'indent',
            tabindex: "2",
            lineWrapping: true,
            dragDrop: false
          });
          wpGhost.uploadMgr = new UploadManager(wpGhost.editor);
          wpGhost.converter = new Showdown.converter({extensions: ['ghostdown', 'github']});
          wpGhost.converter_backend = new Showdown.converter({extensions: ['github']});
          wpGhost.editor.setOption("readOnly", false);
          wpGhost.editor.on('change', function () {
            wpGhost.render_preview();
          });
          wpGhost.render_preview();
          wpGhost.editor.focus();
          jQuery('.CodeMirror-scroll').scroll(wpGhost.sync_scroll);
          jQuery('.CodeMirror-scroll').scrollClass({target: '.entry-markdown', offset: 10});
          jQuery('.entry-preview-content').scrollClass({target: '.entry-preview', offset: 10});
          jQuery('.options.up').click(function(){
            var toggle_object = jQuery(jQuery(this).attr('data-toggle'));
            toggle_object.show();
            jQuery(document).on('mouseup',wpGhost.hide_save);
          })
          wpGhost.update_editor(resp.post);
        }
      );
    },
    save_post : function(e){
      e.preventDefault();
      var data = {};
      data.markdown = wpGhost.editor.getValue();
      data.html = wpGhost.converter_backend.makeHtml(wpGhost.editor.getValue());
      data.title = jQuery('#entry-title').val();
      data.id = jQuery('body').data('id');
      data.status = jQuery(this).attr('data-status');
      if (data.status == 'published')
        data.status = 'publish';
      if (data.status == 'scheduled')
        data.status = 'future';
      wpGhost.api(
        '/post/'+data.id,
        'POST',
        data,
        function(resp){
          wpGhost.update_editor(resp.post);
          var toggle_object = jQuery(jQuery('.options.up').data('toggle'));
          toggle_object.fadeOut(200);
          jQuery(document).off('mouseup',wpGhost.hide_save);
          wpGhost.throw_success('Post updated');
        }
      );
    },
    sync_scroll : function (e) {
            var $codeViewport = $(e.target),
                $previewViewport = $('.entry-preview-content'),
                $codeContent = $('.CodeMirror-sizer'),
                $previewContent = $('.rendered-markdown'),

                // calc position
                codeHeight = $codeContent.height() - $codeViewport.height(),
                previewHeight = $previewContent.height() - $previewViewport.height(),
                ratio = previewHeight / codeHeight,
                previewPostition = $codeViewport.scrollTop() * ratio;

            // apply new scroll
            $previewViewport.scrollTop(previewPostition);
    },
    update_times : function(){
      jQuery('time').each(function(){
        jQuery(this).find('.display').text(moment(jQuery(this).attr('datetime')).fromNow());
      });
    },
    init_list : function(type,title){
      jQuery('body').attr('class','manage');
      jQuery('.content-filter span').html(title);
      jQuery('#menu-'+type).addClass('active');
      jQuery('.content-list-content ol').data('type',type);
      jQuery('.content-list-content ol').data('loaded',false);
      jQuery('.content-list-content ol').data('current',1);
      jQuery('.content-list-content ol').data('next',1);
      wpGhost.api(
        '/posts',
        'GET',
        {'type':type,'page':1},
        function(resp){
          resp.posts.forEach(function(entry){
            wpGhost.add_item(entry);
            wpGhost.update_item(entry);
          });
          jQuery('.content-list-content ol').data('current',1);
          jQuery('.content-list-content ol').data('next',resp.next);
          jQuery('.content-list-content ol').data('loaded',true);
          jQuery('.content-list-content ol li:first-child').click();
          watchScrollPosition(
            '.content-list-content',
            '.content-list-content ol',
            wpGhost.append_list, 
            100, 200
          );
        }
      );
    },
    append_list : function() {
      var page = jQuery('.content-list-content ol').data('next');
      var current = jQuery('.content-list-content ol').data('current');
      if (page!=current && jQuery('.content-list-content ol').data('loaded')) {
        var type = jQuery('.content-list-content ol').data('type');
        jQuery('.content-list-content ol').data('loaded',false);
        wpGhost.api(
          '/posts',
          'GET',
          {'type':type,'page':page},
          function(resp){
            resp.posts.forEach(function(entry){
              wpGhost.add_item(entry);
            });
            jQuery('.content-list-content ol').data('current',page);
            jQuery('.content-list-content ol').data('next',resp.next);
            jQuery('.content-list-content ol').data('loaded',true);
          }
        );
      }
    },
    add_item: function(entry,position){
      if (!position) position = 'after';
      if (position=='before') {
        jQuery('.content-list-content ol').prepend(wpGhost.templates.list_item);
        var item = jQuery('.content-list-content ol li:first-child');
      } else {
        jQuery('.content-list-content ol').append(wpGhost.templates.list_item);
        var item = jQuery('.content-list-content ol li:last-child');
      }
      item.attr('data-id',entry.id);
      item.click(wpGhost.item_click);
    },
    update_item: function(entry){
      var item = jQuery('.content-list-content ol li[data-id='+entry.id+']');
      item.find('.entry-title').html(entry.title?entry.title:'- untitled -');
      item.data('id',entry.id);
      item.data('entry',entry);
      item.find('time').html('<span class="status status-'+entry.status+'">'+entry.status+'</span> <span class="display"></span>');
      item.find('time').attr('datetime',entry.published_at);
      item.find('time .display').html(moment(entry.published_at).fromNow());
      if (entry.status=='draft') {
        item.find('time').attr('datetime',entry.created_at);
        item.find('time .display').html(moment(entry.created_at).fromNow());
      }
      item.find('a').attr('href',wpGhost.ghost_base+'/editor/'+entry.id);
      item.dblclick(function(event) {
        window.location = jQuery(this).find('a').attr('href');
      });
    },
    render_preview : function() {
      var self = this,
      preview = document.getElementsByClassName('rendered-markdown')[0];
      preview.innerHTML = this.converter.makeHtml(this.editor.getValue());
      wpGhost.init_uploads();
      Countable.once(preview, function (counter) {
        jQuery('.entry-word-count').text($.pluralize(counter.words, 'word'));
        jQuery('.entry-character-count').text($.pluralize(counter.characters, 'character'));
        jQuery('.entry-paragraph-count').text($.pluralize(counter.paragraphs, 'paragraph'));
      });
    },
    init_uploads: function(){
          jQuery('.js-drop-zone').upload({editor: true});
            jQuery('.js-drop-zone').on('uploadstart', $.proxy(this.disableEditor, this));
            jQuery('.js-drop-zone').on('uploadfailure', $.proxy(this.enableEditor, this));
            jQuery('.js-drop-zone').on('uploadsuccess', $.proxy(this.enableEditor, this));
            jQuery('.js-drop-zone').on('uploadsuccess', this.uploadMgr.handleUpload);
    },
    item_click :function(e) {
      e.preventDefault();
      var id = jQuery(this).data('id');
      var data = jQuery(this).data('entry');
      jQuery('.content-list-content ol li').removeClass('active');
      jQuery(this).addClass('active');
      jQuery('.content-preview').html(wpGhost.templates.preview_item);

      var viewer = jQuery('.content-preview');
      viewer.find('.status').html(data.status);
      viewer.find('.author').html(data.author.name);
      viewer.find('.floatingheader>a').attr('class',data.featured==true?'featured':'unfeatured');
      viewer.find('.floatingheader>a').click(wpGhost.toggle_featured);
      viewer.find('.post-edit').attr('href',wpGhost.ghost_base+'/editor/'+data.id);
      viewer.find('.post-setting-slug').val(data.slug);
      viewer.find('.post-setting-slug').attr('data-slug',data.slug);
      if (data.published_at) {
        viewer.find('.post-setting-date').val(moment(data.published_at).format('YYYY MMM DD HH:mm'));
        viewer.find('.post-setting-date').attr('data-date',moment(data.published_at).format('YYYY MMM DD HH:mm'));
      }
      viewer.find('.post-settings').unbind();
      viewer.find('.post-settings').click(function(event){
        event.preventDefault();
        var toggle_object = jQuery(jQuery(this).data('toggle'));
        toggle_object.show();
        jQuery(document).mouseup(wpGhost.hide_settings);
      })
      viewer.find('a.delete').attr('href',wpGhost.ghost_base+'/delete/'+data.id);
      viewer.find('a.delete').click(function(event){
        event.preventDefault();
        wpGhost.show_dialog(
          'Are you sure you want to delete this post?',
          function(){
            wpGhost.api(
              '/post/'+id,
              'DELETE',
              {},
              function(resp){
                wpGhost.hide_dialog();
                if (typeof resp.id != 'undefined') {
                  var item = jQuery('.content-list-content ol li[data-id='+resp.id+']');
                  var new_item = item.next();
                  item.fadeOut('fast', function() {
                    this.remove();
                    new_item.click();
                    wpGhost.throw_success('Post #'+resp.id+' deleted');
                  });
                } else {
                  wpGhost.throw_error(resp.error);
                }
              }
            );
          }
        );
      });
      jQuery('.content-preview-content .wrapper').html('<h1>'+data.title+'</h1>'+data.html);
    },
    hide_settings : function (e) {
      var toggle_object = jQuery(jQuery('.post-settings,.entry-settings').data('toggle'));
      if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
        if (wpGhost.validate_settings()) {
          toggle_object.fadeOut(200);
          jQuery(document).off('mouseup',wpGhost.hide_settings);
        }
      }
    },
    hide_save : function (e) {
      var toggle_object = jQuery(jQuery('.options.up').data('toggle'));
      if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
        toggle_object.fadeOut(200);
        jQuery(document).off('mouseup',wpGhost.hide_save);
      }
    },
    hide_settings_editor : function (e) {
      var toggle_object = jQuery(jQuery('.post-settings,.entry-settings').data('toggle'));
      if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
        if (wpGhost.validate_settings_editor()) {
          toggle_object.fadeOut(200);
          jQuery(document).off('mouseup',wpGhost.hide_settings_editor);
        }
      }
    },
    validate_settings : function() {
      var n = moment(jQuery('.post-setting-date').val());
      var o = moment(jQuery('.post-setting-date').attr('data-date'));
      var id = jQuery('.content-list-content ol li.active').attr('data-id');
      if (jQuery('.post-setting-date').val() && !n.isValid()) {
        wpGhost.throw_error('Invalid date');
        return false;
      } 
      if (jQuery('.post-setting-date').val() && n.unix()!=o.unix()) {
        var data = {'published_at':n.unix()};
        wpGhost.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              wpGhost.throw_error(resp.error);
            } else {
              jQuery('.post-setting-date').val(moment(resp.post.published_at).format('YYYY MMM DD HH:mm'));  
              jQuery('.post-setting-date').attr('data-date',moment(resp.post.published_at).format('YYYY MMM DD HH:mm'));  
              wpGhost.update_item(resp.post);
              jQuery('.content-list-content ol li[data-id='+resp.post.id+']').click();
              wpGhost.throw_success('Date updated');
            }
          }
        );
      }
      if (jQuery('.post-setting-slug').val()!=jQuery('.post-setting-slug').attr('data-slug')) {
        var data = {'slug':jQuery('.post-setting-slug').val()};
        wpGhost.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              wpGhost.throw_error(resp.error);
            } else {
              jQuery('.post-setting-slug').val(resp.post.slug);  
              jQuery('.post-setting-slug').attr('data-slug',resp.post.slug);  
              wpGhost.update_item(resp.post);
              jQuery('.content-list-content ol li[data-id='+resp.post.id+']').click();
              wpGhost.throw_success('URL updated');
            }
          }
        );
      }
      return true;
    },
    validate_settings_editor : function() {
      var n = moment(jQuery('.post-setting-date').val());
      var o = moment(jQuery('.post-setting-date').attr('data-date'));
      var id = jQuery('body').data('id');
      if (jQuery('.post-setting-date').val() && !n.isValid()) {
        wpGhost.throw_error('Invalid date');
        return false;
      } 
      if (jQuery('.post-setting-date').val() && n.unix()!=o.unix()) {
        var data = {'published_at':n.unix()};
        wpGhost.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              wpGhost.throw_error(resp.error);
            } else {
              jQuery('.post-setting-date').val(moment(resp.post.published_at).format('YYYY MMM DD HH:mm'));  
              jQuery('.post-setting-date').attr('data-date',moment(resp.post.published_at).format('YYYY MMM DD HH:mm'));  
              wpGhost.update_item(resp.post);
              jQuery('.content-list-content ol li[data-id='+resp.post.id+']').click();
              wpGhost.throw_success('Date updated');
            }
          }
        );
      }
      if (jQuery('.post-setting-slug').val()!=jQuery('.post-setting-slug').attr('data-slug')) {
        var data = {'slug':jQuery('.post-setting-slug').val()};
        wpGhost.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              wpGhost.throw_error(resp.error);
            } else {
              jQuery('.post-setting-slug').val(resp.post.slug);  
              jQuery('.post-setting-slug').attr('data-slug',resp.post.slug);  
              wpGhost.update_item(resp.post);
              jQuery('.content-list-content ol li[data-id='+resp.post.id+']').click();
              wpGhost.throw_success('URL updated');
            }
          }
        );
      }
      return true;
    },
    show_dialog : function(text,confirm,deny) {
      jQuery('#modal-container, .modal-background').show();
      jQuery('#modal-container').html(wpGhost.templates.dialog.replace('%text%',text));
      jQuery('#modal-container article, .modal-background').addClass('in');
      jQuery('#modal-container .button-add').click(confirm);
      jQuery('#modal-container .button-delete').click(deny?deny:wpGhost.hide_dialog);
    },
    hide_dialog : function(){
      jQuery('#modal-container, .modal-background').hide();
      jQuery('#modal-container article, .modal-background').removeClass('in');
    },
    toggle_featured : function(e) {
      e.preventDefault();
      var id = jQuery('.content-list-content ol li.active').attr('data-id');
      var data = jQuery('.content-list-content ol li.active').data('entry');
      var val = data.featured?0:1;
      wpGhost.api(
        '/post/'+id,
        'POST',
        {'featured':val},
        function(resp){
          if (typeof resp.error != 'undefined' ) {
            wpGhost.throw_error(resp.error);
          } else {
            wpGhost.update_item(resp.post);
            jQuery('.content-preview').find('.floatingheader>a').attr('class',val==true?'featured':'unfeatured');
          }
        }
      );
    },
    go_to : function(location) {
      document.location = location;
    },
    api : function(point,method,data,response) {
      return jQuery.ajax({
        url: wpGhost.api_base+point,
        type: method,
        dataType: 'json',
        'data': data,
      })
      .done(response)
      .fail(function(a,b){console.log(a,b);});      
    },
    templates : {
      'list_item'     : '<li><a class="permalink" href="#"><h3 class="entry-title"></h3><section class="entry-meta"><time datetime="2013-01-04" class="date"><span class=""></span></time></section></a></li>',
      'preview_item'  : '<header class="floatingheader"><button class="button-back" href="#">Back</button><a class="unfeatured" href="#"><span class="hidden">Star</span></a><span class="status"></span><span class="normal">by</span><span class="author"></span><section class="post-controls"><a class="post-edit" href="#"><span class="hidden">Edit Post</span></a><a class="post-settings" href="#" data-toggle=".post-settings-menu"><span class="hidden">Post Settings</span></a><ul class="post-settings-menu menu-drop-right overlay" style="display: none;"><li class="post-setting">                <div class="post-setting-label">                    <label for="url">URL</label>                </div>                <div class="post-setting-field">                    <input class="post-setting-slug" type="text" value="sdf-sdfsdf-sd-fsd-fsd">                </div>            </li>            <li class="post-setting">                <div class="post-setting-label">                    <label for="url">Pub Date</label>                </div>                <div class="post-setting-field">                    <input class="post-setting-date" type="text" value="">                </div>            </li>            <li><a href="#" class="delete">Delete</a></li>        </ul>    </section></header><section class="content-preview-content">    <div class="wrapper"></div></section>',
      'aside'         : '<aside id="notifications"></aside>',
      'editor'        : '<section class="entry-container">    <header>        <section class="box entry-title">            <input type="text" id="entry-title" placeholder="Your Post Title" value="" tabindex="1">        </section>    </header>    <section class="entry-markdown active">        <header class="floatingheader">            Markdown            <!--<a class="markdown-help" href="#"><span class="hidden">What is Markdown?</span></a>-->        </header>                <section class="entry-markdown-content">            <textarea id="entry-markdown"></textarea>        </section>    </section>    <section class="entry-preview">        <header class="floatingheader">            Preview <span class="entry-word-count js-entry-word-count">0 words</span>        </header>        <section class="entry-preview-content">            <div class="rendered-markdown"></div>        </section>    </section></section>',
      'notice_error'  : '<div class="js-bb-notification" style="display: block; height: auto;"><section class="notification-error notification-passive js-notification">    %text%    <a class="close" href="#"><span class="hidden">Close</span></a></section></div>',
      'notice_success': '<div class="js-bb-notification" style="display: block; height: auto;"><section class="notification-success notification-passive js-notification">    %text%    <a class="close" href="#"><span class="hidden">Close</span></a></section></div>',
      'dialog'        : '<article class="modal-action modal-style-wide modal-style-centered fade js-modal">    <section class="modal-content">        <header class="modal-header"><h1>%text%</h1></header>                <section class="modal-body"><div></div></section>                <footer class="modal-footer">            <button class="js-button-accept button-add">Yes</button>            <button class="js-button-reject button-delete">No</button>        </footer>            </section></article>'
    }
  };



var   markerRegex = /\{<([\w\W]*?)>\}/,
      imageMarkdownRegex = /^(?:\{<(.*?)>\})?!(?:\[([^\n\]]*)\])(?:\(([^\n\]]*)\))?$/gim;
 MarkerManager = function (editor) {
        var markers = {},
            uploadPrefix = 'image_upload',
            uploadId = 1;

        function addMarker(line, ln) {
            var marker,
                magicId = '{<' + uploadId + '>}';
            editor.setLine(ln, magicId + line.text);
            marker = editor.markText(
                {line: ln, ch: 0},
                {line: ln, ch: (magicId.length)},
                {collapsed: true}
            );

            markers[uploadPrefix + '_' + uploadId] = marker;
            uploadId += 1;
        }

        function getMarkerRegexForId(id) {
            id = id.replace('image_upload_', '');
            return new RegExp('\\{<' + id + '>\\}', 'gmi');
        }

        function stripMarkerFromLine(line) {
            var markerText = line.text.match(markerRegex),
                ln = editor.getLineNumber(line);

            if (markerText) {
                editor.replaceRange('', {line: ln, ch: markerText.index}, {line: ln, ch: markerText.index + markerText[0].length});
            }
        }

        function findAndStripMarker(id) {
            editor.eachLine(function (line) {
                var markerText = getMarkerRegexForId(id).exec(line.text),
                    ln;

                if (markerText) {
                    ln = editor.getLineNumber(line);
                    editor.replaceRange('', {line: ln, ch: markerText.index}, {line: ln, ch: markerText.index + markerText[0].length});
                }
            });
        }

        function removeMarker(id, marker, line) {
            delete markers[id];
            marker.clear();

            if (line) {
                stripMarkerFromLine(line);
            } else {
                findAndStripMarker(id);
            }
        }

        function checkMarkers() {
            _.each(markers, function (marker, id) {
                var line;
                marker = markers[id];
                if (marker.find()) {
                    line = editor.getLineHandle(marker.find().from.line);
                    if (!line.text.match(imageMarkdownRegex)) {
                        removeMarker(id, marker, line);
                    }
                } else {
                    removeMarker(id, marker);
                }
            });
        }

        function initMarkers(line) {
            var isImage = line.text.match(imageMarkdownRegex),
                hasMarker = line.text.match(markerRegex);

            if (isImage && !hasMarker) {
                addMarker(line, editor.getLineNumber(line));
            }
        }

        // public api
        _.extend(this, {
            markers: markers,
            checkMarkers: checkMarkers,
            addMarker: addMarker,
            stripMarkerFromLine: stripMarkerFromLine,
            getMarkerRegexForId: getMarkerRegexForId
        });

        // Initialise
        editor.eachLine(initMarkers);
    };

UploadManager = function (editor) {
        var markerMgr = new MarkerManager(editor);

        function findLine(result_id) {
            // try to find the right line to replace
            if (markerMgr.markers.hasOwnProperty(result_id) && markerMgr.markers[result_id].find()) {
                return editor.getLineHandle(markerMgr.markers[result_id].find().from.line);
            }

            return false;
        }

        function checkLine(ln, mode) {
            var line = editor.getLineHandle(ln),
                isImage = line.text.match(imageMarkdownRegex),
                hasMarker;

            // We care if it is an image
            if (isImage) {
                hasMarker = line.text.match(markerRegex);

                if (hasMarker && mode === 'paste') {
                    // this could be a duplicate, and won't be a real marker
                    markerMgr.stripMarkerFromLine(line);
                }

                if (!hasMarker) {
                    markerMgr.addMarker(line, ln);
                }
            }
            // TODO: hasMarker but no image?
        }

        function handleUpload(e, result_src) {
            /*jslint regexp: true, bitwise: true */
            var line = findLine($(e.currentTarget).attr('id')),
                lineNumber = editor.getLineNumber(line),
                match = line.text.match(/\([^\n]*\)?/),
                replacement = '(http://)';
            /*jslint regexp: false, bitwise: false */

            if (match) {
                // simple case, we have the parenthesis
                editor.setSelection({line: lineNumber, ch: match.index + 1}, {line: lineNumber, ch: match.index + match[0].length - 1});
            } else {
                match = line.text.match(/\]/);
                if (match) {
                    editor.replaceRange(
                        replacement,
                        {line: lineNumber, ch: match.index + 1},
                        {line: lineNumber, ch: match.index + 1}
                    );
                    editor.setSelection(
                        {line: lineNumber, ch: match.index + 2},
                        {line: lineNumber, ch: match.index + replacement.length }
                    );
                }
            }
            editor.replaceSelection(result_src);
        }

        function getEditorValue() {
            var value = editor.getValue();

            _.each(markerMgr.markers, function (marker, id) {
                value = value.replace(markerMgr.getMarkerRegexForId(id), '');
            });

            return value;
        }

        // Public API
        _.extend(this, {
            getEditorValue: getEditorValue,
            handleUpload: handleUpload
        });

        // initialise
        editor.on('change', function (cm, changeObj) {
            var linesChanged = _.range(changeObj.from.line, changeObj.from.line + changeObj.text.length);

            _.each(linesChanged, function (ln) {
                checkLine(ln, changeObj.origin);
            });

            // Is this a line which may have had a marker on it?
            markerMgr.checkMarkers();
        });
    };

  function watchScrollPosition(container,list,callback, distance, interval) {
    var $window = $(container),
        $document = $(list);

    var checkScrollPosition = function() {
        var top = $document.height() - $window.height() - distance;

        if ($window.scrollTop() >= top) {
            callback();
        }
    };

    setInterval(checkScrollPosition, interval);
  }
