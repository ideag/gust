/*globals window, $, _, Backbone, Validator */
(function () {
    'use strict';

    var Ghost = {
        Layout      : {},
        Views       : {},
        Collections : {},
        Models      : {},
        Validate    : new Validator(),

        settings: {
            apiRoot: '/api/v0.1'
        },

        // This is a helper object to denote legacy things in the
        // middle of being transitioned.
        temporary: {},

        currentView: null,
        router: null
    };

    _.extend(Ghost, Backbone.Events);

    Backbone.oldsync = Backbone.sync;
    // override original sync method to make header request contain csrf token
    Backbone.sync = function (method, model, options, error) {
        options.beforeSend = function (xhr) {
            xhr.setRequestHeader('X-CSRF-Token', $("meta[name='csrf-param']").attr('content'));
        };
        /* call the old sync method */
        return Backbone.oldsync(method, model, options, error);
    };

    Ghost.init = function () {
        Ghost.router = new Ghost.Router();

        // This is needed so Backbone recognizes elements already rendered server side
        // as valid views, and events are bound
        Ghost.notifications = new Ghost.Views.NotificationCollection({model: []});

        Backbone.history.start({
            pushState: true,
            hashChange: false,
            root: '/ghost'
        });
    };

    Ghost.Validate.error = function (object) {
        this._errors.push(object);

        return this;
    };

    Ghost.Validate.handleErrors = function () {
        Ghost.notifications.clearEverything();
        _.each(Ghost.Validate._errors, function (errorObj) {

            Ghost.notifications.addItem({
                type: 'error',
                message: errorObj.message || errorObj,
                status: 'passive'
            });
            if (errorObj.hasOwnProperty('el')) {
                errorObj.el.addClass('input-error');
            }
        });
    };

    window.Ghost = Ghost;

}());

// # Ghost Mobile Interactions

/*global window, document, $, FastClick */

(function () {
    'use strict';

    FastClick.attach(document.body);

    // ### Show content preview when swiping left on content list
    $('.manage').on('click', '.content-list ol li', function (event) {
        if (window.matchMedia('(max-width: 800px)').matches) {
            event.preventDefault();
            event.stopPropagation();
            $('.content-list').animate({right: '100%', left: '-100%', 'margin-right': '15px'}, 300);
            $('.content-preview').animate({right: '0', left: '0', 'margin-left': '0'}, 300);
        }
    });

    // ### Hide content preview
    $('.manage').on('click', '.content-preview .button-back', function (event) {
        if (window.matchMedia('(max-width: 800px)').matches) {
            event.preventDefault();
            event.stopPropagation();
            $('.content-list').animate({right: '0', left: '0', 'margin-right': '0'}, 300);
            $('.content-preview').animate({right: '-100%', left: '100%', 'margin-left': '15px'}, 300);
        }
    });

    // ### Show settings options page when swiping left on settings menu link
    $('.settings').on('click', '.settings-menu li', function (event) {
        if (window.matchMedia('(max-width: 800px)').matches) {
            event.preventDefault();
            event.stopPropagation();
            $('.settings-sidebar').animate({right: '100%', left: '-102%', 'margin-right': '15px'}, 300);
            $('.settings-content').animate({right: '0', left: '0', 'margin-left': '0'}, 300);
            $('.settings-content .button-back, .settings-content .button-save').css('display', 'inline-block');
        }
    });

    // ### Hide settings options page
    $('.settings').on('click', '.settings-content .button-back', function (event) {
        if (window.matchMedia('(max-width: 800px)').matches) {
            event.preventDefault();
            event.stopPropagation();
            $('.settings-sidebar').animate({right: '0', left: '0', 'margin-right': '0'}, 300);
            $('.settings-content').animate({right: '-100%', left: '100%', 'margin-left': '15'}, 300);
            $('.settings-content .button-back, .settings-content .button-save').css('display', 'none');
        }
    });

    // ### Toggle the sidebar menu
    $('[data-off-canvas]').on('click', function (event) {
        if (window.matchMedia('(max-width: 650px)').matches) {
            event.preventDefault();
            $('body').toggleClass('off-canvas');
        }
    });

}());
// #Â Toggle Support

/*global document, $, Ghost */
(function () {
    'use strict';

    Ghost.temporary.hideToggles = function () {
        $('[data-toggle]').each(function () {
            var toggle = $(this).data('toggle');
            $(this).parent().children(toggle + ':visible').fadeOut();
        });

        // Toggle active classes on menu headers
        $('[data-toggle].active').removeClass('active');
    };

    Ghost.temporary.initToggles = function ($el) {

        $el.find('[data-toggle]').each(function () {
            var toggle = $(this).data('toggle');
            $(this).parent().children(toggle).hide();
        });

        $el.find('[data-toggle]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $this = $(this),
                toggle = $this.data('toggle'),
                isAlreadyActive = $this.is('.active');

            // Close all the other open toggle menus
            Ghost.temporary.hideToggles();

            if (!isAlreadyActive) {
                $this.toggleClass('active');
                $(this).parent().children(toggle).toggleClass('open').fadeToggle(200);
            }
        });

    };


    $(document).ready(function () {

        // ## Toggle Up In Your Grill
        // Allows for toggling via data-attributes.
        // ### Usage
        //       <nav>
        //         <a href="#" data-toggle=".toggle-me">Toggle</a>
        //         <ul class="toggle-me">
        //            <li>Toggled yo</li>
        //         </ul>
        //       </nav>
        Ghost.temporary.initToggles($(document));
    });

}());

// # Surrounds given text with Markdown syntax

/*global $, window, CodeMirror, Showdown, moment */
(function () {
    'use strict';
    var Markdown = {
        init : function (options, elem) {
            var self = this;
            self.elem = elem;

            self.style = (typeof options === 'string') ? options : options.style;

            self.options = $.extend({}, CodeMirror.prototype.addMarkdown.options, options);

            self.replace();
        },
        replace: function () {
            var text = this.elem.getSelection(), pass = true, md, cursor, line, word, letterCount, converter;
            switch (this.style) {
            case 'h1':
                cursor = this.elem.getCursor();
                line = this.elem.getLine(cursor.line);
                this.elem.setLine(cursor.line, '# ' + line);
                this.elem.setCursor(cursor.line, cursor.ch + 2);
                pass = false;
                break;
            case 'h2':
                cursor = this.elem.getCursor();
                line = this.elem.getLine(cursor.line);
                this.elem.setLine(cursor.line, '## ' + line);
                this.elem.setCursor(cursor.line, cursor.ch + 3);
                pass = false;
                break;
            case 'h3':
                cursor = this.elem.getCursor();
                line = this.elem.getLine(cursor.line);
                this.elem.setLine(cursor.line, '### ' + line);
                this.elem.setCursor(cursor.line, cursor.ch + 4);
                pass = false;
                break;
            case 'h4':
                cursor = this.elem.getCursor();
                line = this.elem.getLine(cursor.line);
                this.elem.setLine(cursor.line, '#### ' + line);
                this.elem.setCursor(cursor.line, cursor.ch + 5);
                pass = false;
                break;
            case 'h5':
                cursor = this.elem.getCursor();
                line = this.elem.getLine(cursor.line);
                this.elem.setLine(cursor.line, '##### ' + line);
                this.elem.setCursor(cursor.line, cursor.ch + 6);
                pass = false;
                break;
            case 'h6':
                cursor = this.elem.getCursor();
                line = this.elem.getLine(cursor.line);
                this.elem.setLine(cursor.line, '###### ' + line);
                this.elem.setCursor(cursor.line, cursor.ch + 7);
                pass = false;
                break;
            case 'link':
                md = this.options.syntax.link.replace('$1', text);
                this.elem.replaceSelection(md, 'end');
                cursor = this.elem.getCursor();
                this.elem.setSelection({line: cursor.line, ch: cursor.ch - 8}, {line: cursor.line, ch: cursor.ch - 1});
                pass = false;
                break;
            case 'image':
                cursor = this.elem.getCursor();
                md = this.options.syntax.image.replace('$1', text);
                if (this.elem.getLine(cursor.line) !== '') {
                    md = "\n\n" + md;
                }
                this.elem.replaceSelection(md, "end");
                cursor = this.elem.getCursor();
                this.elem.setSelection({line: cursor.line, ch: cursor.ch - 8}, {line: cursor.line, ch: cursor.ch - 1});
                pass = false;
                break;
            case 'uppercase':
                md = text.toLocaleUpperCase();
                break;
            case 'lowercase':
                md = text.toLocaleLowerCase();
                break;
            case 'titlecase':
                md = text.toTitleCase();
                break;
            case 'selectword':
                cursor = this.elem.getCursor();
                word = this.elem.getTokenAt(cursor);
                if (!/\w$/g.test(word.string)) {
                    this.elem.setSelection({line: cursor.line, ch: word.start}, {line: cursor.line, ch: word.end - 1});
                } else {
                    this.elem.setSelection({line: cursor.line, ch: word.start}, {line: cursor.line, ch: word.end});
                }
                break;
            case 'copyHTML':
                converter = new Showdown.converter();
                if (text) {
                    md = converter.makeHtml(text);
                } else {
                    md = converter.makeHtml(this.elem.getValue());
                }

                $(".modal-copyToHTML-content").text(md).selectText();
                pass = false;
                break;
            case 'list':
                md = text.replace(/^(\s*)(\w\W*)/gm, '$1* $2');
                this.elem.replaceSelection(md, 'end');
                pass = false;
                break;
            case 'currentDate':
                md = moment(new Date()).format('D MMMM YYYY');
                this.elem.replaceSelection(md, 'end');
                pass = false;
                break;
            default:
                if (this.options.syntax[this.style]) {
                    md = this.options.syntax[this.style].replace('$1', text);
                }
            }
            if (pass && md) {
                this.elem.replaceSelection(md, 'end');
                if (!text) {
                    letterCount = md.length;
                    cursor = this.elem.getCursor();
                    this.elem.setCursor({line: cursor.line, ch: cursor.ch - (letterCount / 2)});
                }
            }
        }
    };

    CodeMirror.prototype.addMarkdown = function (options) {
        var markdown = Object.create(Markdown);
        markdown.init(options, this);
    };

    CodeMirror.prototype.addMarkdown.options = {
        style: null,
        syntax: {
            bold: "**$1**",
            italic: "*$1*",
            strike: "~~$1~~",
            code: "`$1`",
            link: "[$1](http://)",
            image: "![$1](http://)",
            blockquote: "> $1"
        }
    };

}());
/*globals Handlebars, moment
*/
(function () {
    'use strict';
    Handlebars.registerHelper('date', function (context, block) {
        var f = block.hash.format || 'MMM Do, YYYY',
            timeago = block.hash.timeago,
            date;
        if (timeago) {
            date = moment(context).fromNow();
        } else {
            date = moment(context).format(f);
        }
        return date;
    });
}());