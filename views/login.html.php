<?php include('header-top.html.php'); ?>          
        <main role="main" id="main">
          <aside id="notifications">
          </aside>

          <section class="login-box js-login-box">
            <form id="login" method="post" novalidate="novalidate">
                <div class="email-wrap">
                    <input class="email" type="text" placeholder="<?php _e('Username','gust'); ?>" name="email" autocapitalize="off" autocorrect="off">
                </div>
                <div class="password-wrap">
                    <input class="password" type="password" placeholder="<?php _e('Password','gust'); ?>" name="password">
                </div>
                <button class="button-save" type="submit"><?php _e('Log in','gust'); ?></button>
                <section class="meta">
                    <a class="forgotten-password" href="<?php echo GUST_ROOT; ?>/forgotten/"><?php _e('Forgotten password?','gust'); ?></a>
                </section>
            </form>
          </section>

        </main>

        <div id="modal-container"></div>
        <div class="modal-background fade"></div>
<?php require('scripts.html.php'); ?>
    </body>
</html>