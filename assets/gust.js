  Gust = {
    api_base : '/api/v0.1',
    ghost_base : '/ghost',
    plugin_base : '/wp-content/plugins/wp-ghost',
    all_tags : [],
    show_amount : function(val) {
      jQuery('.count2').text('€'+val);
      jQuery('#tiny_amount').val(val);
      jQuery('.count').html('');
      for (var i = 0; i<val/2; ++i) {
        jQuery('.count').append('<i class="fa fa-coffee"></i>');
      }
    },
    init : function(api_base,ghost_base,plugin_base) {
      if (api_base) Gust.api_base = api_base;
      if (ghost_base) Gust.ghost_base = ghost_base;
      if (plugin_base) Gust.plugin_base = plugin_base;
      var address = document.location.pathname;
      address = address.split(Gust.ghost_base);
      address = address[1].slice(1);
      address = address.split('/');
      jQuery('.subnav>a').click(function(e){
        e.preventDefault();
        var toggle_object = jQuery(jQuery(this).attr('data-toggle'));
        toggle_object.fadeIn(500);
        jQuery(document).mouseup(Gust.hide_sub);
      });
      jQuery('#coffee').click(function(e){
        e.preventDefault();
        Gust.show_dialog_coffee();
        jQuery('#amount_slider').css({'margin':'0px 16px'});
        jQuery('#amount_slider').noUiSlider({
          'range':[2,20],
          'step':2,
          'start':4,
          'handles':1,
          'serialization':{
            to: [
              [jQuery('.count2'),Gust.show_amount],
              [jQuery('#tiny_amount')]
            ],
            resolution:1
          }
        });
      });
      switch(address[0]) {
        case 'editor' :
          Gust.init_editor(address[1]);
        break;
        case 'page'  :
          Gust.init_list('page', 'All Pages');
        break;
        case 'attachment'  :
          Gust.init_list('attachment', 'All Files');
        break;
        case 'forgotten'  : 
          Gust.init_forgotten();
        break;
        case 'login'  : 
          Gust.init_login();
        break;
        case 'post'  :
        default       :  
          Gust.init_list('post', 'All Posts');
        break;
      }
      var get = Gust.detect_get();
      if (typeof get.coffee != 'undefined' && get.coffee == 'confirm') {
        Gust.throw_success('Thank you for the coffee!');
      }
      if (typeof get.message != 'undefined' && get.message=='subscriber') {
        Gust.throw_error('As a subscriber, you do not have permission to edit this blog');        
      }
      if (typeof get.error != 'undefined') {
        Gust.throw_error(decodeURI(get.error));        
      }
      setInterval('Gust.update_times()',30000);
    },
    detect_get : function() {
      var str = window.location.search.replace( "?", "" );
      var arr = str.split('&');
      var ret = {};
      for (var i = 0; i< arr.length;++i) {
        arr[i] = arr[i].split('=');
        ret[arr[i][0]] = arr[i][1];
      }
      return ret;
    },
    hide_sub : function(e){
          var toggle_object = jQuery(jQuery('.subnav a').data('toggle'));
          if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
            toggle_object.fadeOut(200);
            jQuery(document).off('mouseup',Gust.hide_sub);
          }
    },
    init_login  : function() {
      jQuery('#login').submit(function(event){
        event.preventDefault();
        if (jQuery('input[name=email]:invalid').size()>0) {
          Gust.throw_error('Invalid/empty username');
        } else if (jQuery('input[name=email]').val()==''){
          Gust.throw_error('Invalid/empty username');
        } else if (jQuery('input[name=password]:invalid').size()>0) {
          Gust.throw_error('Invalid/empty password');
        } else if (jQuery('input[name=password]').val()==''){
          Gust.throw_error('Invalid/empty password');
        } else {
          Gust.api(
            '/session',
            'POST',
            {
              'username':jQuery('input[name=email]').val(),
              'password':jQuery('input[name=password]').val()
            },
            function(resp){
              if (typeof resp.error != 'undefined') {
                Gust.throw_error(resp.error);
              } else if (typeof resp.success != 'undefined') {
                Gust.go_to(Gust.ghost_base);
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
          Gust.throw_error('Invalid/empty username');
        } else if (jQuery('input[name=email]').val()==''){
          Gust.throw_error('Invalid/empty username');
        } else {
          Gust.api(
            '/password',
            'POST',
            {
              'username':jQuery('input[name=email]').val()
            },
            function(resp){
              if (typeof resp.error != 'undefined') {
                Gust.throw_error(resp.error);
              } else if (typeof resp.success != 'undefined') {
                Gust.throw_success('Password reset link sent to your e-mail address.');
                setTimeout('Gust.go_to(Gust.ghost_base+\'/login\')',3000);
              }
            }
          );
        }
      });
    },
    throw_error : function(text) {
      var a = jQuery('#notifications')
      .append(Gust.templates.notice_error.replace('%text%',text))
      .children()
      .last()
      .find('.close')
      .click(Gust.close_error);
    },
    throw_success : function(text) {
      var a = jQuery('#notifications')
      .append(Gust.templates.notice_success.replace('%text%',text))
      .children()
      .last();
      a
      .find('.close')
      .click(Gust.close_error);
      a.delay(4000).queue(function(){
        jQuery(this).remove();
      });
    },
    close_error : function(e) {
      jQuery(this).parent().parent().remove();
      e.preventDefault();
    },
    update_editor_button : function(entry){
      if (entry.published_at) {
        jQuery('.post-setting-date').val(moment(entry.published_at).format('YYYY MMM DD HH:mm'));
        jQuery('.post-setting-date').attr('data-date',moment(entry.published_at).format('YYYY MMM DD HH:mm'));
      }
      jQuery('.entry-settings').unbind('click');
      jQuery('.entry-settings').click(function(event){
        event.preventDefault();
        var toggle_object = jQuery(jQuery(this).data('toggle'));
        toggle_object.show();
        jQuery(document).mouseup(Gust.hide_settings_editor);
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
      jQuery('ul.editor-options li').unbind('click',Gust.save_post);
      jQuery('ul.editor-options li').click(Gust.save_post);
      jQuery('.button-save').unbind('click',Gust.save_post)
      jQuery('.button-save').click(Gust.save_post);
    },
    update_editor :function(entry) {
      var id = entry.id;
      var type = entry.type;
      jQuery('.post-setting-slug').val(entry.slug);
      jQuery('.post-setting-slug').attr('data-slug',entry.slug);
      jQuery('body').data('id',entry.id);
      Gust.update_editor_button(entry);
      jQuery('a.delete').attr('href',Gust.ghost_base+'/delete/'+entry.id);
      jQuery('a.delete').click(function(event){
        event.preventDefault();
        Gust.show_dialog(
          'Are you sure you want to delete this post?',
          function(){
            Gust.api(
              '/post/'+id,
              'DELETE',
              {},
              function(resp){
                localStorage.removeItem('list_position_'+type);
                Gust.hide_dialog();
                if (typeof resp.id != 'undefined') {
                  window.location = Gust.ghost_base+'/'+type;
                } else {
                  Gust.throw_error(resp.error);
                }
              }
            );
          }
        );
      });
    },
    store_position : function(id,type){
      localStorage.setItem('list_position_'+type,id);
    },
    load_position : function(type){
      var position = localStorage.getItem('list_position_'+type);
      if (position){
        localStorage.removeItem('list_position_'+type);
        return position;
      } else {
        return false;
      }
    },
    init_editor : function(id){
      jQuery('body').attr('class','editor');
      jQuery('body').data('id',id);
      Gust.api(
        '/tags',
        'GET',
        {},
        function(resp){
          Gust.all_tags = resp;
        }
      );
      Gust.api(
        '/categories',
        'GET',
        {},
        function(resp){
          Gust.all_categories = resp;
          jQuery('.entry-categories-menu').html('');
          jQuery.each(Gust.all_categories,function(){
            Gust.add_category(this.name,this.term_id,this);
          });
          jQuery('.entry-categories-menu li>div').click(Gust.toggle_checkbox);
          jQuery('.entry-categories-menu').bind('wheel',function(event) {
            var delta = event.originalEvent.deltaY;
            jQuery(this).scrollTop(jQuery(this).scrollTop()+delta);
          });
          Gust.init_toggle(
            'a.entry-categories',
            function(){
            },
            function(){
              return true;
            }
          );
          Gust.api(
            '/post/'+id,
            'GET',
            {},
            function(resp){
              if(typeof resp.error != 'undefined') {
                window.location = document.referrer.split("?")[0]+ '?error='+resp.error;
              }
              var entry = resp.post;
              var id = entry.id;
              Gust.store_position(id,entry.type);
              jQuery('#entry-title').val(entry.title);
              jQuery('#entry-markdown').html(entry.markdown);
              Gust.editor = CodeMirror.fromTextArea(document.getElementById('entry-markdown'), {
                mode: 'gfm',
                tabMode: 'indent',
                tabindex: "2",
                lineWrapping: true,
                dragDrop: false
              });
              Gust.uploadMgr = new UploadManager(Gust.editor);
              Gust.converter = new Showdown.converter({extensions: ['ghostdown', 'github']});
              Gust.converter_backend = new Showdown.converter({extensions: ['github']});
              Gust.editor.setOption("readOnly", false);
              if(resp.post.type=='page') {
                jQuery('#entry-tags').hide();
              }
              jQuery.each(resp.post.tags,function(){
                Gust.add_tag(this.name,this.term_id);
              });
              if(resp.post.type=='page') {
                jQuery('#entry-categories').hide();
              }
              jQuery.each(resp.post.categories,function(){
                var item = jQuery('#entry-categories li[data-category-id='+this.term_id+']');
                item.children('div').first().find('i.fa').click();
                item.parent('ul').prepend(item);
              });

              Gust.editor.on('change', function () {
                Gust.render_preview();
              });
              Gust.render_preview();
              Gust.editor.focus();
              jQuery('.CodeMirror-scroll').scroll(Gust.sync_scroll);
              jQuery('.CodeMirror-scroll').scrollClass({target: '.entry-markdown', offset: 10});
              jQuery('.entry-preview-content').scrollClass({target: '.entry-preview', offset: 10});
              jQuery('.options.up').click(function(e){
                e.preventDefault();
                var toggle_object = jQuery(jQuery(this).attr('data-toggle'));
                toggle_object.show();
                jQuery(document).on('mouseup',Gust.hide_save);
              });
    /*          shortcut.add("Ctrl+Alt+C", function () {
                    self.showHTML();
              });
                shortcut.add("Ctrl+Alt+C", function () {
                    self.showHTML();
                });*/

                _.each(MarkdownShortcuts, function (combo) {
                    shortcut.add(combo.key, function () {
                        return Gust.editor.addMarkdown({style: combo.style});
                    });
                });          
              Gust.update_editor(resp.post);
            }
          );
        }
      );
      jQuery('#tags').keyup(function(e){
        var triggers = [188,13];
        var tag = jQuery.trim(jQuery(this).val());
        if (e.keyCode==38) {
          if (jQuery('#entry-tags ul li.selected').size()>0) {
            jQuery('#entry-tags ul li.selected').removeClass('selected').prev('li').addClass('selected');
          }
        } else if (e.keyCode==40){
          if (jQuery('#entry-tags ul li.selected').size()>0) {
            jQuery('#entry-tags ul li.selected').removeClass('selected').next('li').addClass('selected');
          }
        } else if(triggers.indexOf(e.keyCode)>-1) {
          e.preventDefault();
          if (jQuery('#entry-tags ul li.selected').size()>0) {
            tag = jQuery('#entry-tags ul li.selected').text();
          }
          tag = tag.replace(',','');
          Gust.add_tag(tag,'');
          jQuery(this).val('').focus();
        } else {
          e.preventDefault();
          Gust.show_suggestions(jQuery('#tags'),tag);
        }
      }).keydown(function(e){
        var tag = jQuery.trim(jQuery(this).val());
        if (e.keyCode==8 && !tag) {
          e.preventDefault();
          Gust.remove_tag(jQuery('div.tags span').last());
        }
      });
      jQuery('.markdown-help').click(function(e){
        e.preventDefault();
        Gust.show_dialog_md();
      });
    },
    find_tags: function (searchTerm) {
      var matchingTagModels,
      self = this;
      if (!Gust.all_tags) {
        return [];
      }
      searchTerm = searchTerm.toString().toUpperCase();
      matchingTagModels = _.filter(Gust.all_tags, function (tag) {
        var tagNameMatches,
        hasAlreadyBeenAdded;

        tagNameMatches = tag.name.toString().toUpperCase().indexOf(searchTerm) !== -1;
//        hasAlreadyBeenAdded = _.some(self.model.get('tags'), function (usedTag) {
//          return tag.name.toUpperCase() === usedTag.name.toUpperCase();
//        });
        return tagNameMatches && !hasAlreadyBeenAdded;
      });
      return matchingTagModels;
    },    
    show_suggestions: function (target, _searchTerm) {
      jQuery('#entry-tags ul').show();
      var searchTerm = _searchTerm.toString().toLowerCase(),
      matchingTags = Gust.find_tags(searchTerm),
      maxSuggestions = 5, // Limit the suggestions number
      regexTerm = searchTerm.toString().replace(/(\s+)/g, "(<[^>]+>)*$1(<[^>]+>)*"),
      regexPattern = new RegExp("(" + regexTerm + ")", "i");

      jQuery('#entry-tags ul').css({left: jQuery(target).position().left});
      jQuery('#entry-tags ul').html('');

      matchingTags = _.first(matchingTags, maxSuggestions);
      _.each(matchingTags, function (matchingTag) {
        var highlightedName,
        suggestionHTML;

        highlightedName = matchingTag.name.toString().replace(regexPattern, "<mark>$1</mark>");
        highlightedName = highlightedName.toString().replace(/(<mark>[^<>]*)((<[^>]+>)+)([^<>]*<\/mark>)/, "$1</mark>$2<mark>$4");

        suggestionHTML = "<li data-tag-id='" + matchingTag.term_id + "' data-tag-name='" + matchingTag.name + "'><a href='#'>" + highlightedName + "</a></li>";
        jQuery('#entry-tags ul').append(suggestionHTML);
      }, this);
      jQuery('#entry-tags ul li:first-child').addClass('selected');
      jQuery('#entry-tags ul li').click(function(){
        Gust.add_tag(
          jQuery(this).attr('data-tag-name'),
          jQuery(this).attr('data-tag-id')
        );
        jQuery('#tags').val('').focus();
      });
    },
    add_category : function(tag,id,term) {
      var tag_id = id;
//      tags[tags.length] = tag_id?tag_id:tag;
      var new_tag = Gust.templates.category.replace('%id%',tag_id);
      new_tag = new_tag.replace('%title%',tag);
      if (term.parent!=0) {
        var parent = jQuery('.entry-categories-menu li[data-category-id='+term.parent+']>ul');
      } else {
        var parent = jQuery('.entry-categories-menu');
      }
      parent.append(new_tag);
    },
    toggle_checkbox: function(){
      var box = jQuery(this).parent().find('i.fa').first();
      if (box.hasClass('fa-square-o')) {
        box.removeClass('fa-square-o').addClass('fa-check-square-o');
        var parent = box.parent().parent().parent();
        if (parent.hasClass('submenu')) {
          parent = parent.parent();
          parent.children('div').click();
        }
      } else {
        box.removeClass('fa-check-square-o').addClass('fa-square-o');
        box.parent().parent().find('i.fa').removeClass('fa-check-square-o').addClass('fa-square-o');
      }
    },
    add_tag : function(tag,id) {
      var tags = [];
      jQuery('div.tags span').each(function(){
        tags[tags.length] = jQuery(this).attr('data-tag-id')?jQuery(this).attr('data-tag-id'):jQuery(this).text();
      })
      if (tags.indexOf(tag)==-1) {
        var tag_id = id;
        tags[tags.length] = tag_id?tag_id:tag;
        var new_tag = Gust.templates.tag.replace('%id%',tag_id);
        new_tag = new_tag.replace('%title%',tag);
        jQuery('div.tags').append(new_tag);
        jQuery('div.tags span:last-child').click(function(){
          Gust.remove_tag(jQuery(this));
        });
      }
      jQuery('#tags-holder').val(tags.join(','));
      jQuery('#entry-tags ul').hide();
    },
    remove_tag : function(el){
      el.remove();
      var tags = [];
      jQuery('div.tags span').each(function(){
        tags[tags.length] = jQuery(this).attr('data-tag-id')?jQuery(this).attr('data-tag-id'):jQuery(this).text();
      })
      jQuery('#tags-holder').val(tags.join(','));
    },
    save_post : function(e){
      e.preventDefault();
      var data = {};
      data.markdown = Gust.uploadMgr.getEditorValue();
      data.html = Gust.converter_backend.makeHtml(Gust.uploadMgr.getEditorValue());
      data.title = jQuery('#entry-title').val();
      data.id = jQuery('body').data('id');
      data.tags = jQuery('#tags-holder').val();
      var cats = [];
      jQuery('#entry-categories li .fa-check-square-o').each(function(index, val) {
         cats.push(jQuery(val).parent().parent().attr('data-category-id'));
      });
      cats = cats.join(',');
      data.categories = cats;
      data.status = jQuery(this).attr('data-status');
      if (data.status == 'published')
        data.status = 'publish';
      if (data.status == 'scheduled')
        data.status = 'future';
      Gust.api(
        '/post/'+data.id,
        'POST',
        data,
        function(resp){
          if (typeof resp.error != 'undefined') {
            Gust.throw_error(resp.error);
          } else {
            Gust.update_editor(resp.post);
            var toggle_object = jQuery(jQuery('.options.up').data('toggle'));
            toggle_object.fadeOut(200);
            jQuery(document).off('mouseup',Gust.hide_save);
            Gust.throw_success('Post updated');
          }
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
      jQuery('#add-post-list').attr('href',Gust.ghost_base+'/editor/'+type);
      jQuery('.content-list-content ol').data('type',type);
      jQuery('.content-list-content ol').data('loaded',false);
      jQuery('.content-list-content ol').data('current',1);
      jQuery('.content-list-content ol').data('next',1);
      Gust.api(
        '/posts',
        'GET',
        {'type':type,'page':1},
        function(resp){
          if (typeof resp.posts != 'undefined') {
            resp.posts.forEach(function(entry){
              Gust.add_item(entry);
              Gust.update_item(entry);
            });
            jQuery('.content-list-content ol').data('current',1);
            jQuery('.content-list-content ol').data('next',resp.next);
            jQuery('.content-list-content ol').data('loaded',true);
            var position = Gust.load_position(resp.posts[0].type);
            if (position) {
              if (jQuery('li[data-id='+position+']').size()>0) {
                jQuery('li[data-id='+position+']').click();
                jQuery('.content-list-content').scrollTop(jQuery('li[data-id='+position+']').position().top-jQuery('li[data-id='+position+']').parent().position().top);
              } else {
                Gust.append_list(position);
              } 
            } else {
              jQuery('.content-list-content ol li:first-child').click();              
            }
            watchScrollPosition(
              '.content-list-content',
              '.content-list-content ol',
              Gust.append_list, 
              100, 200
            );            
          }
        }
      );
    },
    append_list : function(position) {      
      var page = jQuery('.content-list-content ol').data('next');
      var current = jQuery('.content-list-content ol').data('current');
      if (page!=current && jQuery('.content-list-content ol').data('loaded')) {
        var type = jQuery('.content-list-content ol').data('type');
        jQuery('.content-list-content ol').data('loaded',false);
        Gust.api(
          '/posts',
          'GET',
          {'type':type,'page':page},
          function(resp){
            resp.posts.forEach(function(entry){
              Gust.add_item(entry);
            });
            if (position) {
              if (jQuery('li[data-id='+position+']').size()>0) {
                jQuery('li[data-id='+position+']').click();
                jQuery('.content-list-content').scrollTop(jQuery('li[data-id='+position+']').position().top-jQuery('li[data-id='+position+']').parent().position().top);
              } else {
                Gust.append_list(position);
              } 
            }
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
        jQuery('.content-list-content ol').prepend(Gust.templates.list_item);
        var item = jQuery('.content-list-content ol li:first-child');
      } else {
        jQuery('.content-list-content ol').append(Gust.templates.list_item);
        var item = jQuery('.content-list-content ol li:last-child');
      }
      item.attr('data-id',entry.id);
      item.click(Gust.item_click);
      Gust.update_item(entry);
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
      item.find('a').attr('href',Gust.ghost_base+'/editor/'+entry.id);
      item.dblclick(function(event) {
        window.location = jQuery(this).find('a').attr('href');
      });
    },
    render_preview : function() {
      var self = this,
      preview = document.getElementsByClassName('rendered-markdown')[0];
      preview.innerHTML = this.converter.makeHtml(this.editor.getValue());
      Gust.init_uploads();
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
      jQuery('.content-preview').html(Gust.templates.preview_item);

      var viewer = jQuery('.content-preview');
      viewer.find('.status').html(data.status);
      viewer.find('.author').html(data.author.name);
      viewer.find('.floatingheader>a').attr('class',data.featured==true?'featured':'unfeatured');
      viewer.find('.floatingheader>a').click(Gust.toggle_featured);
      viewer.find('.post-edit').attr('href',Gust.ghost_base+'/editor/'+data.id);
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
        jQuery(document).mouseup(Gust.hide_settings);
      })
      viewer.find('a.delete').attr('href',Gust.ghost_base+'/delete/'+data.id);
      viewer.find('a.delete').click(function(event){
        event.preventDefault();
        Gust.show_dialog(
          'Are you sure you want to delete this post?',
          function(){
            Gust.api(
              '/post/'+id,
              'DELETE',
              {},
              function(resp){
                Gust.hide_dialog();
                if (typeof resp.id != 'undefined') {
                  var item = jQuery('.content-list-content ol li[data-id='+resp.id+']');
                  var new_item = item.next();
                  item.fadeOut('fast', function() {
                    this.remove();
                    new_item.click();
                    Gust.throw_success('Post #'+resp.id+' deleted');
                  });
                } else {
                  Gust.throw_error(resp.error);
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
        if (Gust.validate_settings()) {
          toggle_object.fadeOut(200);
          jQuery(document).off('mouseup',Gust.hide_settings);
        }
      }
    },
    hide_save : function (e) {
      var toggle_object = jQuery(jQuery('.options.up').data('toggle'));
      if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
        toggle_object.fadeOut(200);
        jQuery(document).off('mouseup',Gust.hide_save);
      }
    },
    hide_settings_editor : function (e) {
      var toggle_object = jQuery(jQuery('.post-settings,.entry-settings').data('toggle'));
      if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
        if (Gust.validate_settings_editor()) {
          toggle_object.fadeOut(200);
          jQuery(document).off('mouseup',Gust.hide_settings_editor);
        }
      }
    },
    validate_settings : function() {
      var n = jQuery('.post-setting-date').val()?moment(jQuery('.post-setting-date').val()):0;
      var o = jQuery('.post-setting-date').attr('data-date')?moment(jQuery('.post-setting-date').attr('data-date')):0;
      var id = jQuery('.content-list-content ol li.active').attr('data-id');
      if (jQuery('.post-setting-date').val() && !n.isValid()) {
        Gust.throw_error('Invalid date');
        return false;
      } 
      if (n &&o && n.unix()!=o.unix()) {
        var data = {'published_at':n.unix()};
        Gust.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              Gust.throw_error(resp.error);
            } else {
              jQuery('.post-setting-date').val(moment(resp.post.published_at).format('YYYY MMM DD HH:mm'));  
              jQuery('.post-setting-date').attr('data-date',moment(resp.post.published_at).format('YYYY MMM DD HH:mm'));  
              Gust.update_item(resp.post);
              jQuery('.content-list-content ol li[data-id='+resp.post.id+']').click();
              Gust.throw_success('Date updated');
            }
          }
        );
      }
      if (jQuery('.post-setting-slug').val()!=jQuery('.post-setting-slug').attr('data-slug')) {
        var data = {'slug':jQuery('.post-setting-slug').val()};
        Gust.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              Gust.throw_error(resp.error);
            } else {
              jQuery('.post-setting-slug').val(resp.post.slug);  
              jQuery('.post-setting-slug').attr('data-slug',resp.post.slug);  
              Gust.update_item(resp.post);
              jQuery('.content-list-content ol li[data-id='+resp.post.id+']').click();
              Gust.throw_success('URL updated');
            }
          }
        );
      }
      return true;
    },
    validate_settings_editor : function() {
      var n = jQuery('.post-setting-date').val()?moment(jQuery('.post-setting-date').val()):0;
      var o = jQuery('.post-setting-date').attr('data-date')?moment(jQuery('.post-setting-date').attr('data-date')):0;
      var id = jQuery('body').data('id');
      if (jQuery('.post-setting-date').val() && !n.isValid()) {
        Gust.throw_error('Invalid date');
        return false;
      } 
      if (/*jQuery('.post-setting-date').val() && */n &&o && n.unix()!=o.unix()) {
        var data = {'published_at':n.unix()};
        Gust.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              Gust.throw_error(resp.error);
            } else {
              var date_show = resp.post.published_at?resp.post.published_at:resp.post.created_at;
              Gust.update_editor_button(resp.post);
//             jQuery('.post-setting-date').val(moment(date_show).format('YYYY MMM DD HH:mm'));  
//              jQuery('.post-setting-date').attr('data-date',moment(date_show).format('YYYY MMM DD HH:mm'));  
              Gust.throw_success('Date updated');
            }
          }
        );
      }
      if (jQuery('.post-setting-slug').val()!=jQuery('.post-setting-slug').attr('data-slug')) {
        var data = {'slug':jQuery('.post-setting-slug').val()};
        Gust.api(
          '/post/'+id,
          'POST',
          data,
          function(resp){          
            if ( typeof resp.error != 'undefined') {
              Gust.throw_error(resp.error);
            } else {
              jQuery('.post-setting-slug').val(resp.post.slug);  
              jQuery('.post-setting-slug').attr('data-slug',resp.post.slug);  
              Gust.update_item(resp.post);
              jQuery('.content-list-content ol li[data-id='+resp.post.id+']').click();
              Gust.throw_success('URL updated');
            }
          }
        );
      }
      return true;
    },
    hide_toggle : function(e){
      e.preventDefault();
      var toggle_object = jQuery(jQuery(document).attr('data-toggle-object'));
      if (!toggle_object.is(e.target) && toggle_object.has(e.target).length === 0) {
        if (document.callback_hide()){
          toggle_object.fadeOut(200);
          jQuery(document).off('mouseup',Gust.hide_toggle);
        }
      }
    },
    init_toggle : function(el,callback_show,callback_hide){
      var el = jQuery(el);
      el.click(function(e){
        e.preventDefault();
        if(jQuery(document).attr('data-toggle-flag')=='1'){
          jQuery(document).mouseup();
          jQuery(document).attr('data-toggle-flag','0');
        } else {
          jQuery(el.attr('data-toggle')).fadeIn(250);
          callback_show();
          jQuery(document).attr('data-toggle-flag','1');
          jQuery(document).attr('data-toggle-object',el.attr('data-toggle'));
          document.callback_hide = callback_hide;
          jQuery(document).mouseup(Gust.hide_toggle);
        }
      });
    },
    show_dialog : function(text,confirm,deny) {
      jQuery('#modal-container, .modal-background').show();
      jQuery('#modal-container').html(Gust.templates.dialog.replace('%text%',text));
      jQuery('#modal-container article, .modal-background').addClass('in');
      jQuery('#modal-container .button-add').click(confirm);
      jQuery('#modal-container .button-delete').click(deny?deny:Gust.hide_dialog);
    },
    show_dialog_md : function() {
      jQuery('#modal-container, .modal-background').show();
      jQuery('#modal-container').html(Gust.templates.dialog_markdown);
      jQuery('#modal-container article, .modal-background').addClass('in');
      jQuery('#modal-container .close, .modal-background').click(Gust.hide_dialog_md);
    },
    show_dialog_coffee : function() {
      jQuery('#modal-container, .modal-background').show();
      jQuery('#modal-container').html(Gust.templates.dialog_coffee);
      jQuery('#modal-container article, .modal-background').addClass('in');
      jQuery('#modal-container .close, .modal-background').click(Gust.hide_dialog_md);
    },
    hide_dialog_md : function(e){
      e.preventDefault();
      jQuery('#modal-container, .modal-background').hide();
      jQuery('#modal-container article, .modal-background').removeClass('in');
      jQuery('.modal-background').unbind('click',Gust.hide_dialog_md);
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
      Gust.api(
        '/post/'+id,
        'POST',
        {'featured':val},
        function(resp){
          if (typeof resp.error != 'undefined' ) {
            Gust.throw_error(resp.error);
          } else {
            Gust.update_item(resp.post);
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
        url: Gust.api_base+point,
        type: method,
        dataType: 'json',
        'data': data,
      })
      .done(function(resp){
        if (typeof resp.error != 'undefined') {
          Gust.throw_error(resp.error);
        } else {
          response(resp);
        }
      })
      .fail(function(a,b){console.log(a,b);Gust.throw_error('API error: '+b);});      
    },
    templates : {
      'list_item'       : '<li><a class="permalink" href="#"><h3 class="entry-title"></h3><section class="entry-meta"><time datetime="2013-01-04" class="date"><span class=""></span></time></section></a></li>',
      'preview_item'    : '<header class="floatingheader"><button class="button-back" href="#">Back</button><a class="unfeatured" href="#"><span class="hidden">Star</span></a><span class="status"></span><span class="normal">by</span><span class="author"></span><section class="post-controls"><a class="post-edit" href="#"><span class="hidden">Edit Post</span></a><a class="post-settings" href="#" data-toggle=".post-settings-menu"><span class="hidden">Post Settings</span></a><ul class="post-settings-menu menu-drop-right overlay" style="display: none;"><li class="post-setting">                <div class="post-setting-label">                    <label for="url">URL</label>                </div>                <div class="post-setting-field">                    <input class="post-setting-slug" type="text" value="sdf-sdfsdf-sd-fsd-fsd">                </div>            </li>            <li class="post-setting">                <div class="post-setting-label">                    <label for="url">Pub Date</label>                </div>                <div class="post-setting-field">                    <input class="post-setting-date" type="text" value="" placeholder="YYYY Mon DD hh:mm">                </div>            </li>            <li><a href="#" class="delete">Delete</a></li>        </ul>    </section></header><section class="content-preview-content">    <div class="wrapper"></div></section>',
      'notice_error'    : '<div class="js-bb-notification" style="display: block; height: auto;"><section class="notification-error notification-passive js-notification">    %text%    <a class="close" href="#"><span class="hidden">Close</span></a></section></div>',
      'notice_success'  : '<div class="js-bb-notification" style="display: block; height: auto;"><section class="notification-success notification-passive js-notification">    %text%    <a class="close" href="#"><span class="hidden">Close</span></a></section></div>',
      'dialog'          : '<article class="modal-action modal-style-wide modal-style-centered fade js-modal">    <section class="modal-content">        <header class="modal-header"><h1>%text%</h1></header>                <section class="modal-body"><div></div></section>                <footer class="modal-footer">            <button class="js-button-accept button-add">Yes</button>            <button class="js-button-reject button-delete">No</button>        </footer>            </section></article>',
      'dialog_markdown' : '<article class="modal-info modal-style-wide fade js-modal in">    <section class="modal-content">        <header class="modal-header"><h1>Markdown Help</h1></header>        <a class="close" href="#"><span class="hidden">Close</span></a>        <section class="modal-body"><div><section class="markdown-help-container">    <table class="modal-markdown-help-table">        <thead>            <tr>                <th>Result</th>                <th>Markdown</th>                <th>Shortcut</th>            </tr>        </thead>        <tbody>            <tr>                <td><strong>Bold</strong></td>                <td>**text**</td>                <td>Ctrl / Cmd + B</td>            </tr>            <tr>                <td><em>Emphasize</em></td>                <td>__text__</td>                <td>Ctrl / Cmd + I</td>            </tr>            <tr>                <td><code>Inline Code</code></td>                <td>`code`</td>                <td>Cmd + K / Ctrl + Shift + K</td>            </tr>            <tr>                <td>Strike-through</td>                <td>~~text~~</td>                <td>Ctrl + Alt + U</td>            </tr>            <tr>                <td><a href="#">Link</a></td>                <td>[title](http://)</td>                <td>Ctrl + Shift + L</td>            </tr>            <tr>                <td>Image</td>                <td>![alt](http://)</td>                <td>Ctrl + Shift + I</td>            </tr>            <tr>                <td>List</td>                <td>* item</td>                <td>Ctrl + L</td>            </tr>            <tr>                <td>Blockquote</td>                <td>&gt; quote</td>                <td>Ctrl + Q</td>            </tr>            <tr>                <td>H1</td>                <td># Heading</td>                <td>Ctrl + Alt + 1</td>            </tr>            <tr>                <td>H2</td>                <td>## Heading</td>                <td>Ctrl + Alt + 2</td>            </tr>            <tr>                <td>H3</td>                <td>### Heading</td>                <td>Ctrl + Alt + 3</td>            </tr>            <tr>                <td>H4</td>                <td>#### Heading</td>                <td>Ctrl + Alt + 4</td>            </tr>            <tr>                <td>H5</td>                <td>##### Heading</td>                <td>Ctrl + Alt + 5</td>            </tr>            <tr>                <td>H6</td>                <td>###### Heading</td>                <td>Ctrl + Alt + 6</td>            </tr>       <!--     <tr>                <td>Select Word</td>                <td></td>                <td>Ctrl + Alt + W</td>            </tr>            <tr>                <td>Uppercase</td>                <td></td>                <td>Ctrl + U</td>            </tr>            <tr>                <td>Lowercase</td>                <td></td>                <td>Ctrl + Shift + U</td>            </tr>            <tr>                <td>Titlecase</td>                <td></td>                <td>Ctrl + Alt + Shift + U</td>            </tr> -->           <tr>                <td>Insert Current Date</td>                <td></td>              <td>Ctrl + Shift + 1</td>            </tr>        </tbody>    </table>    For further Markdown syntax reference: <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">Markdown Documentation</a></section></div></section>            </section></article>',
      'dialog_coffee'   : '<article class="modal-info modal-style-wide fade js-modal in">    <section class="modal-content">        <header class="modal-header"><h1>Buy Arūnas a cup of coffee</h1></header>        <a class="close" href="#"><span class="hidden">Close</span></a>        <section class="modal-body"><p class="note">A ridiculous amount of coffee was consumed in the process of building Gust. Add some fuel if you\'d like to keep me going!</p><form action="/gust/coffee" method="post" class="tiny_form" data-icon="coffee" data-price="600" data-currency="%s Lt ">  <p></p> <div id="amount_slider"></div> <p>   <input type="hidden" id="tiny_amount" name="tiny_amount" value="200"/>  </p><div id="right"><span class="count"></span>    <small class="count2"></small></div><input type="hidden" name="tiny_currency" value="EUR"/><input type="hidden" name="tiny_text" value="Coffee to Arunas for Gust development"/><button type="submit" name="tiny_paypal" value="1"><i class="fa fa-shopping-cart"></i></button></form></section>            </section></article>',
      'tag'             : '<span class="tag" data-tag-id="%id%">%title%</span>',
      'category'        : '<li class="post-setting" data-category-id="%id%"><div class="category-title"><i class="fa fa-square-o"></i> %title%</div><ul class="submenu"></ul></li>'
    }
  };



var   

MarkdownShortcuts = [
            {'key': 'Ctrl+B', 'style': 'bold'},
            {'key': 'Meta+B', 'style': 'bold'},
            {'key': 'Ctrl+I', 'style': 'italic'},
            {'key': 'Meta+I', 'style': 'italic'},
            {'key': 'Ctrl+Alt+U', 'style': 'strike'},
            {'key': 'Ctrl+Shift+K', 'style': 'code'},
            {'key': 'Meta+K', 'style': 'code'},
            {'key': 'Ctrl+Alt+1', 'style': 'h1'},
            {'key': 'Ctrl+Alt+2', 'style': 'h2'},
            {'key': 'Ctrl+Alt+3', 'style': 'h3'},
            {'key': 'Ctrl+Alt+4', 'style': 'h4'},
            {'key': 'Ctrl+Alt+5', 'style': 'h5'},
            {'key': 'Ctrl+Alt+6', 'style': 'h6'},
            {'key': 'Ctrl+Shift+L', 'style': 'link'},
            {'key': 'Ctrl+Shift+I', 'style': 'image'},
            {'key': 'Ctrl+Q', 'style': 'blockquote'},
            {'key': 'Ctrl+Shift+1', 'style': 'currentDate'},
//            {'key': 'Ctrl+U', 'style': 'uppercase'},
//            {'key': 'Ctrl+Shift+U', 'style': 'lowercase'},
//            {'key': 'Ctrl+Alt+Shift+U', 'style': 'titlecase'},
//            {'key': 'Ctrl+Alt+W', 'style': 'selectword'},
            {'key': 'Ctrl+L', 'style': 'list'}//,
            //{'key': 'Ctrl+Alt+C', 'style': 'copyHTML'},
            //{'key': 'Meta+Alt+C', 'style': 'copyHTML'}
        ],
markerRegex = /\{<([\w\W]*?)>\}/,
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
