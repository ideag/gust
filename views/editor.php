<?php include('header-top.php'); ?>
<?php include('header.php'); ?>          
        <main role="main" id="main">
            <aside id="notifications">
            </aside>


        </main>

        <div id="modal-container"></div>
        <div class="modal-background fade"></div>
        <footer id="publish-bar">
            <nav>
<!--                <section id="entry-tags" href="#" class="left">
                    <label class="tag-label" for="tags"><span class="hidden">Tags</span></label>
                    <div class="tags" style="max-width: 1040px;"><span class="tag" data-tag-id="1">Getting Started</span></div>
                    <input type="hidden" class="tags-holder" id="tags-holder">
                    <input class="tag-input" id="tags" type="text" data-input-behaviour="tag">
                    <ul class="suggestions overlay" style="display: none;"></ul>
                </section>-->
                <div class="right">

                    <section id="entry-controls">
                        <a class="entry-settings" href="#" data-toggle=".entry-settings-menu"><span class="hidden">Post Settings</span></a>
                        <ul class="entry-settings-menu menu-right overlay" style="display: none;">
                            <li class="post-setting">
                                <div class="post-setting-label">
                                    <label for="url">URL</label>
                                </div>
                                <div class="post-setting-field">
                                    <input id="url" class="post-setting-slug" type="text" value="">
                                </div>
                            </li>
                            <li class="post-setting">
                                <div class="post-setting-label">
                                    <label for="pub-date">Pub Date</label>
                                </div>
                                <div class="post-setting-field">
                                    <input id="pub-date" class="post-setting-date" type="text" value=""><!--<span class="post-setting-calendar"></span>-->
                                </div>
                            </li>
                            <li><a href="#" class="delete">Delete This Post</a></li>
                        </ul>
                    </section>

                    <section id="entry-actions" class="js-publish-splitbutton splitbutton-save">
                        <button type="button" class="js-publish-button button-save" data-status="published">Update Post</button>
                        <a class="options up" data-toggle="ul.editor-options" href="#"><span class="hidden">Options</span></a>
                        <ul class="editor-options overlay" style="display: none;">
                        </ul>
                    </section>
                </div>
            </nav>
        </footer>
<?php require('scripts.php'); ?>
    </body>
</html>