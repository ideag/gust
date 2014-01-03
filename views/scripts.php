        <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/jquery.min.js">\x3C/script>')</script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.4.0/moment.min.js"></script>
        <script>window.moment || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/moment.min.js">\x3C/script>')</script>
        <script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script>window.jQuery.ui || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/jquery-ui.js">\x3C/script>')</script>
        <script src="//cdn.jsdelivr.net/codemirror/3.14.0/codemirror.js"></script>
        <script>window.CodeMirror || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/codemirror.js">\x3C/script>')</script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"/></script>
        <script>window._ || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/underscore-min.js">\x3C/script>')</script>
        <script src="//cdn.jsdelivr.net/showdown/0.3.1/showdown.js"></script>
        <script>window.Showdown || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/showdown.js">\x3C/script>')</script>
        <script src="//cdn.jsdelivr.net/showdown/0.3.1/extensions/github.js"></script>
        <script>window.Showdown.extensions.github || document.write('<script src="<?php echo GUST_PLUGIN_URL; ?>assets/github.js">\x3C/script>')</script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/ghostdown.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/jquery-fileupload.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/countable.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/misc.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/shortcuts.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/jquery.nouislider.min.js?v=0.3.3"></script>
        <script src="<?php echo GUST_PLUGIN_URL; ?>assets/gust.js?v=0.0.1"></script>

        <script>
          jQuery(document).ready(function(){
            Gust.init(
              '<?php echo GUST_SUBPATH.GUST_API_ROOT; ?>',
              '<?php echo GUST_ROOT; ?>',
              '<?php echo GUST_PLUGIN_URL; ?>'
            );
          });
        </script>
