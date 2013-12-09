        <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/jquery.min.js">\x3C/script>')</script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.4.0/moment.min.js"></script>
        <script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script src="//cdn.jsdelivr.net/codemirror/3.14.0/codemirror.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"/></script>
        <script src="//cdn.jsdelivr.net/showdown/0.3.1/showdown.js"></script>
        <script src="//cdn.jsdelivr.net/showdown/0.3.1/extensions/github.js"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/ghostdown.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/jquery-fileupload.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/countable.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/misc.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/wp-ghost.js?v=0.0.1"></script>

        <script>
          jQuery(document).ready(function(){
            wpGhost.init(
              '<?php echo GUST_API_ROOT; ?>',
              '<?php echo GUST_ROOT; ?>',
              '<?php echo GUST_PLUGIN_URL; ?>'
            );
          });
        </script>
