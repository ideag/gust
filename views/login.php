<?php include('header-top.php'); ?>          
        <main role="main" id="main">
          <aside id="notifications">
          </aside>

          <section class="login-box js-login-box">
            <form id="login" method="post" novalidate="novalidate">
                <div class="email-wrap">
                    <input class="email" type="text" placeholder="Username" name="email" autocapitalize="off" autocorrect="off">
                </div>
                <div class="password-wrap">
                    <input class="password" type="password" placeholder="Password" name="password">
                </div>
                <button class="button-save" type="submit">Log in</button>
                <section class="meta">
                    <a class="forgotten-password" href="/ghost/forgotten/">Forgotten password?</a>
                </section>
            </form>
          </section>

        </main>

        <div id="modal-container"></div>
        <div class="modal-background fade"></div>
<?php require('scripts.php'); ?>
    </body>
</html>