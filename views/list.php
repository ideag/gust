<?php include('header-top.php'); ?>
<?php include('header.php'); ?>          
        <main role="main" id="main">
            <aside id="notifications">
            </aside>
            <section class="content-view-container">
                <section class="content-list js-content-list">                  
                    <header class="floatingheader">                      
                        <section class="content-filter">                          
                            <span></span>
                        </section>                      
                        <a href="<?php echo GUST_ROOT; ?>/editor/" class="button button-add" id="add-post-list"><span class="hidden">New Post</span></a>
                    </header>                  
                    <section class="content-list-content">                      
                        <ol></ol>                  
                    </section>                
                </section>                
                <section class="content-preview js-content-preview">                
                </section>            
            </section>
        </main>

        <div id="modal-container"></div>
        <div class="modal-background fade"></div>
<?php require('scripts.php'); ?>
    </body>
</html>