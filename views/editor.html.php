<?php include('header-top.html.php'); ?>
<?php include('header.html.php'); ?>          
        <main role="main" id="main">
            <aside id="notifications">
            </aside>
            <section class="entry-container">
                <header>
                    <section class="box entry-title">
                        <input type="text" id="entry-title" placeholder="<?php _e('Your Post Title','gust'); ?>" value="" tabindex="1">
                    </section>
                </header>
                <section class="entry-markdown active">
                    <header class="floatingheader">
                        <?php _e('Markdown','gust'); ?>
                        <a class="markdown-help" href="#"><span class="hidden"><?php _e('What is Markdown?','gust'); ?></span></a>
                    </header>
                    <section class="entry-markdown-content">
                        <textarea id="entry-markdown"></textarea>        
                    </section>
                </section>
                <section class="entry-preview">
                    <header class="floatingheader">
                        <?php _e('Preview','gust'); ?> 
                        <span class="entry-word-count js-entry-word-count">0 words</span>
                    </header>        
                    <section class="entry-preview-content">            
                        <div class="rendered-markdown"></div>
                    </section>
                </section>
            </section>
        </main>

        <div id="modal-container"></div>
        <div class="modal-background fade"></div>
        <footer id="publish-bar">
            <nav>
                <section id="entry-categories">
                    <a class="entry-categories" href="#" data-toggle="#entry-categories .toggle" title="Categories"><i class="fa fa-tags"></i></a>
                    <div class="toggle">
                        <ul class="entry-categories-menu">
                        </ul>
                    </div>
                </section>
                <section id="entry-tags" href="#" class="left">
                    <label class="tag-label" for="tags"><span class="hidden"><?php _e('Tags','gust'); ?></span></label>
                    <div class="tags"></div>
                    <input type="hidden" class="tags-holder" id="tags-holder">
                    <input class="tag-input" id="tags" type="text" data-input-behaviour="tag">
                    <ul class="suggestions overlay" style="display: none;"></ul>
                </section>
                <div class="right">

                    <section id="entry-controls">
                        <a class="entry-settings" href="#" data-toggle=".entry-settings-menu"><span class="hidden"><?php _e('Post Settings','gust'); ?></span></a>
                        <ul class="entry-settings-menu menu-right overlay" style="display: none;">
                            <li class="post-setting">
                                <div class="post-setting-label">
                                    <label for="url"><?php _e('URL','gust'); ?></label>
                                </div>
                                <div class="post-setting-field">
                                    <input id="url" class="post-setting-slug" type="text" value="">
                                </div>
                            </li>
                            <li class="post-setting">
                                <div class="post-setting-label">
                                    <label for="pub-date"><?php _e('Pub Date','gust'); ?></label>
                                </div>
                                <div class="post-setting-field">
                                    <input id="pub-date" class="post-setting-date" type="text" value="" placeholder="<?php echo Gust::$options['main_dateformat']; ?>"><!--<span class="post-setting-calendar"></span>-->
                                </div>
                            </li>
                            <li><a href="#" class="add" id="edit-custom-fields"><i class="fa fa-pencil">&nbsp;</i> <?php _e('Edit Custom Fields','gust'); ?></a></li>
<?php if (current_theme_supports('post-thumbnails')): ?>
                            <li><a href="#" class="add" id="edit-featured-image"><i class="fa fa-picture-o">&nbsp;</i> <?php _e('Edit Featured Image','gust'); ?></a></li>
<?php endif; ?>
                            <li><a href="#" class="delete"><?php _e('Delete This Post','gust'); ?></a></li>
                        </ul>
                    </section>

                    <section id="entry-actions" class="js-publish-splitbutton splitbutton-save">
                        <button type="button" class="js-publish-button button-save" data-status="published"><?php _e('Update Post','gust'); ?></button>
                        <a class="options up" data-toggle="ul.editor-options" href="#"><span class="hidden"><?php _e('Options','gust');?></span></a>
                        <ul class="editor-options overlay" style="display: none;">
                        </ul>
                    </section>
                </div>
            </nav>
        </footer>
<?php require('scripts.html.php'); ?>
    </body>
</html>