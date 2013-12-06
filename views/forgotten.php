<?php include('header-top.php'); ?>          
        <main role="main" id="main">
          <aside id="notifications">
          </aside>

          <section class="forgotten-box js-forgotten-box"><form id="forgotten" method="post" novalidate="novalidate">
              <div class="email-wrap">
                  <input class="email" type="text" placeholder="Username" name="email" autocapitalize="off" autocorrect="off">
              </div>
              <button class="button-save" type="submit">Send new password</button>
          </form>
          </section>

        </main>

        <div id="modal-container"></div>
        <div class="modal-background fade"></div>
<?php require('scripts.php'); ?>
    </body>
</html>