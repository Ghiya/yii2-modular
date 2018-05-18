/*
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

var cPanelEnv = function (config) {
    window.cp.env = config;
    yii.initModule(window.cp);
};

window.cp = (function ($) {
    return {
        env: {
            containerId: '',
            hostInfo: ''
        },
        container: null,
        isActive: true,
        init: function () {
            this.container = $(document).find('div[id*=' + this.env.containerId + ']').first();
            $('[data-toggle=tooltip]').tooltip();
        },
        adaptedMode: function () {
            return (this.dim($(window)).width <= 768);
        },
        dim: function (el) {
            return {
                width: el.width(),
                height: el.height()
            };
        },
        fullHeight: function () {
            return (this.dim($(window)).height > this.dim($(document)).height) ?
                this.dim($(window)).height :
                this.dim($(document)).height;
        }
    };
})(window.jQuery);

window.cp.common = (function ($) {
    var pub = {
        tooltipSelector: '[data-toggle="tooltip"]',
        popoverSelect: '[data-toggle="popover"]',
        selectSelector: 'select',
        submitButtonSelector: 'button[type=submit]',
        collapsibleSelector: '[id*=cpanel][id*=collapsible]',
        triggerSelector: '[data-toggle=collapse]',
        isActive: true,
        init: function () {
            this.bind();
        },
        bind: function () {
            this.bindTooltips();
            this.bindSelects();
            this.bindPopovers();
            this.bindAjaxComplete();
            _collapsing();
            _errorFieldFocus();
        },
        bindSelects: function () {
            _getSelects().each(function () {
                $(this).niceSelect();
            });
        },
        bindTooltips: function () {
            _getTooltips().each(function () {
                $(this).tooltip()
            });
        },
        bindPopovers: function () {
            _getPopovers().each(function () {
                $(this).popover();
            });
        },
        bindAjaxComplete: function () {
            $(document).ajaxComplete(function () {
                if ($(this.activeElement).hasClass('cpanel-item-link')) {
                    var tracksImage = $(this.activeElement.offsetParent).parent().find('[class*=fa-envelope]');
                    if (tracksImage.length > 0 && tracksImage.hasClass('fa-envelope')) {
                        tracksImage.removeClass('fa-envelope');
                        tracksImage.addClass('fa-envelope-open-o');
                    }
                }
            });
        }
    };

    function _getTooltips() {
        return window.cp.container.find(pub.tooltipSelector);
    }

    function _getSelects() {
        return window.cp.container.find(pub.selectSelector);
    }

    function _getPopovers() {
        return window.cp.container.find(pub.popoverSelect);
    }

    function _getCollapsible() {
        return window.cp.container.find(pub.collapsibleSelector);
    }

    function _collapsing() {
        _getCollapsible().on('show.bs.collapse', function (event) {
            var items = $(this).find('.panel-body .cpanel-item-property');
            if (!items.length) {
                var spinner = $(this).find('.panel-body form [class*=spinner]').first();
                if (spinner.length === 0) {
                    $(this).find('.panel-body form').append('<div class="text-center spinner"><i class="fa fa-cog fa-spin"></i></div>');
                }

            }
        });
        _getCollapsible().on('shown.bs.collapse', function (event) {
            var items = $(this).find('.panel-body .cpanel-item-property');
            if (!items.length) {
                $(this).find('.panel-body form').submit();
            }
        });
    }

    function _errorFieldFocus() {
        window.cp.container.find('form [class*=form-group]').each(
            function () {
                var group = $(this);
                group.find('input[type=text], input[type=password]').first().on("focus", function () {
                    if (group.hasClass('has-error')) {
                        group.removeClass('has-error');
                    }
                });
            }
        );
    }

    return pub;
})(window.jQuery);

window.cp.navigation = (function ($) {
    var pub = {
        container: null,
        isActive: true,
        init: function () {
            this.container = $(document).find('nav').first();
            this.bind();
        },
        bind: function () {
            this.affixed.bind();
            this.scroller.bind();
            this.breadcrumbs.bind();
            this.topmenu.bind();
            this.menu.bind();
        },
        affixed: {
            selector: function () {
                return (window.cp.adaptedMode()) ? '[class*=cpanel-affixed-adapted]' : '[class*=cpanel-affixed]'
            },
            options: {
                offset: {
                    top: function () {
                        return $('body .nav').outerHeight() + pub.breadcrumbs.height() + 20;//pub.breadcrumbs.height();//window.cp.dim($(window)).height * 2 / 3;
                    }
                }
            },
            bind: function () {
                // adding twitter bootstrap affix to any related element in default mode
                // in adapted mode adding affix plugin only to breadcrumb
                if (this.exist()) {
                    this.getInstances().each(
                        function (index, el) {
                            var cssTop = 0;
                            if (index > 0) {
                                var previousInstance = pub.affixed.getInstanceByIndex(index - 1);
                                if (previousInstance.length > 0) {
                                    cssTop += previousInstance[0].clientHeight;
                                }
                            }

                            $(el).css('top', cssTop);
                            $(el).css('width', $(el).innerWidth());
                            $(el).affix(pub.affixed.options);
                        }
                    );
                }
            },
            exist: function () {
                return window.cp.container.find(this.selector()).length > 0;
            },
            getInstanceByIndex: function (index) {
                return window.cp.container.find(this.selector()).eq(index);
            },
            getInstances: function () {
                return window.cp.container.find(this.selector());
            }
        },
        breadcrumbs: {
            selector: 'ul[class*=breadcrumb]',
            activeItem: {
                selector: 'li[class*=active]',
                exist: function () {
                    return this.getInstance().length > 0;
                },
                getInstance: function () {
                    return pub.breadcrumbs.getInstance().find(this.selector).first()
                }
            },
            height: function () {
                return this.getInstance().outerHeight(true);
            },
            container: {
                defaultTopMargin: 0,
                toggleMargin: function (height) {
                    if (!window.cp.adaptedMode()) {
                        $('body').css(
                            'margin-top',
                            height ?
                                (this.defaultTopMargin + height) + 'px' :
                                this.defaultTopMargin + 'px'
                        );
                    }
                }
            },
            bind: function () {
                if (this.exist()) {
                    if (window.cp.adaptedMode()) {
                        pub.breadcrumbs.getInstance().hide();
                    }
                    // first define common container margin
                    // crop breadcrumb active element content in UI adapted mode
                    if (window.cp.adaptedMode()) {
                        if (this.activeItem.exist()) {
                            this.activeItem.getInstance().css('max-width', window.cp.dim($(window)).width / 2);
                            this.activeItem.getInstance().css('min-width', window.cp.dim($(window)).width / 3);
                        }
                    }
                    // on scroll action
                    $(window).on('scroll', function () {
                        if ($(this).scrollTop() > pub.affixed.options.offset.top()) {
                            pub.breadcrumbs.container.toggleMargin(pub.breadcrumbs.height());
                            if (window.cp.adaptedMode()) {
                                pub.breadcrumbs.getInstance().show();
                            }
                        }
                        else {
                            pub.breadcrumbs.container.toggleMargin();
                            if (window.cp.adaptedMode()) {
                                pub.breadcrumbs.getInstance().hide();
                            }
                        }
                    });
                }
            },
            exist: function () {
                return this.getInstance().length > 0;
            },
            getInstance: function () {
                return window.cp.container.find(this.selector).first();
            }
        },
        scroller: {
            selector: '[data-scroller]',
            bind: function () {
                if (this.exist()) {
                    this.getLink().on('click', function () {
                        pub.scroller.getInstance().hide();
                        pub.scroller.moveTo(0);
                    });
                    $(window).on('scroll', function () {
                        if ($(this).scrollTop() > pub.affixed.options.offset.top()) {
                            if (!pub.scroller.isScrolling) {
                                pub.scroller.getInstance().show();
                            }
                        } else {
                            if (!pub.scroller.isScrolling) {
                                pub.scroller.getInstance().hide();
                            }
                        }
                    });
                }
            },
            moveTo: function (scrollTo) {
                pub.scroller.isScrolling = true;
                $('html, body').animate({
                    scrollTop: scrollTo
                }, parseFloat(parseInt(window.cp.dim($(document)).height) / 3).toFixed(0), "swing", function () {
                    pub.scroller.isScrolling = false;
                });
            },
            getInstance: function () {
                return window.cp.container.find(this.selector).first();
            },
            getLink: function () {
                return window.cp.container.find(this.selector + ' a').first();
            },
            exist: function () {
                return this.getInstance().length > 0;
            },
            isScrolling: false
        },
        topmenu: {
            selector: '[id=w0-collapse]',
            getInstance: function () {
                return $('body').find(this.selector).first();
            },
            bind: function () {
                if (window.cp.adaptedMode()) {
                    this.getInstance().find('li a').each(
                        function () {
                            $(this).on(
                                "click",
                                function () {
                                    pub.topmenu.getInstance().collapse('hide');
                                }
                            );
                        }
                    );
                }
            }
        },
        menu: {
            selector: '[id=bundleSelector]',
            collapseMargin: 15,
            wrapper: {
                selector: 'div[id=cpanel-navigation]',
                getInstance: function () {
                    return window.cp.container.find(this.selector).first();
                }
            },
            activePanel: {
                selector: 'div[id*=bundle-switch]',
                getInstance: function () {
                    return pub.menu.wrapper.getInstance().find(
                        this.selector + '[id*=' + pub.menu.getInstance().find('option[selected]').prop('value') + ']'
                    ).first();
                },
                exist: function () {
                    return this.getInstance().length > 0;
                }
            },
            switcher: {
                selector: 'div[class*=nice-select] ul',
                firstItem: {
                    selector: 'li:first-child',
                    getInstance: function () {
                        pub.menu.switcher.getInstance().find(this.selector).first();
                    }
                },
                activeItem: {
                    selector: 'li[class*=selected]',
                    exist: function () {
                        return this.getInstance().length > 0;
                    },
                    getInstance: function () {
                        return pub.menu.switcher.getInstance().find(this.selector).first();
                    }
                },
                getInstance: function () {
                    return pub.menu.wrapper.getInstance().find(this.selector).first();
                },
                exist: function () {
                    return this.getInstance().length > 0;
                }
            },
            bind: function () {

                // nice-select plugin update
                if (this.switcher.exist()) {
                    if (this.switcher.activeItem.exist()) {
                        this.switcher.activeItem.getInstance().addClass('index');
                    } else {
                        this.switcher.firstItem.getInstance().addClass('index');
                    }
                }

                // nav items box switch
                this.getInstance().on('change', function (event) {
                    // nice-select plugin update
                    if (pub.menu.switcher.activeItem.exist()) {
                        if (event.target.value !== pub.menu.switcher.activeItem.getInstance().prop('value')) {
                            pub.menu.wrapper.getInstance().find('div[class*=nice-select]').addClass('blur');
                        } else {
                            pub.menu.wrapper.getInstance().find('div[class*=nice-select]').removeClass('blur');
                        }
                    }
                    if (event.target.value.length > 0) {
                        var _menuSelectedItem = $('[id=bundle-switch-' + event.target.value + ']');
                        $('[id*=bundle-switch]').each(function () {
                            if ($(this).prop('id') !== _menuSelectedItem.prop('id')) {
                                $(this).collapse('hide');
                            }
                        });
                        _menuSelectedItem.collapse('show');

                    } else {
                        if (window.cp.env.hostInfo !== '') {
                            window.cp.spinner.show();
                            document.location = window.cp.env.hostInfo;
                        }

                    }
                });

                // collapsing menu on scroll actions in UI adapted mode
                /*if (window.cp.adaptedMode()) {
                    $(window).on('scroll', function () {
                        if ($(this).scrollTop() > pub.affixed.options.offset.top()) {
                            pub.menu.activePanel.getInstance().collapse('hide');
                        } else if ($(this).scrollTop() < pub.menu.collapseMargin) {
                            pub.menu.activePanel.getInstance().collapse('show');
                        }
                    });
                }*/
            },
            getInstance: function () {
                return window.cp.container.find(this.selector).first();
            }
        }
    };
    return pub;
})(window.jQuery);

window.cp.msgModal = (function ($) {
    var pub = {
        selector: '[id=panelModal]',
        titleSelector: '[class*=modal-header] [class*=modal-title]',
        messageSelector: '[class*=modal-body]',
        instance: null,
        isActive: true,
        init: function () {
            this.instance = $('body').find(pub.selector).first();
        },
        modal: function (options) {
            if (this.instance !== null) {
                this.instance.modal(options);
            } else {
                throw Error('Message modal node element is not specified.');
            }
        },
        reset: function () {
            this.setContent("", "");
        },
        setContent: function (title, message) {
            _getTitleBlock().html(title);
            _getMessageBlock().html(message);
        }
    };

    function _getTitleBlock() {
        return pub.instance.find(pub.titleSelector).first();
    }

    function _getMessageBlock() {
        return pub.instance.find(pub.messageSelector).first();
    }

    return pub;
})(window.jQuery);

window.cp.spinner = (function ($) {
    var pub = {
        selector: '[data-spinner]',
        wrapper: '[class*=spinner-wrapper]',
        wrapperBackground: 'rgba(252,252,252,.77)',
        spinnerColor: '#235d54',
        isActive: true,
        init: function () {
            this.bind();
        },
        show: function (wrapperBackground, spinnerColor) {
            _getWrapper().css('background-color', wrapperBackground ? wrapperBackground : this.wrapperBackground);
            _getWrapper().css('height', window.cp.fullHeight());
            _getSpinner().css('color', spinnerColor ? spinnerColor : this.spinnerColor);
            _getSpinner().css('top', window.cp.dim($(window)).height / 2 - 80);
            if (_hasIndicator()) {
                _getIndicator().addClass("fa-spin");
            }
            _getWrapper().fadeIn(150);
        },
        hide: function () {
            _getWrapper().fadeOut(150);
        },
        bind: function () {
            _getTargets().each(function () {
                $(this).on("click", function (event) {
                    var link = $(this);
                    if (link.context.nodeName === 'BUTTON') {
                        pub.show();
                    } else {
                        event.preventDefault();
                        pub.show();
                        window.location = link.attr('href');
                    }
                });
            });
        },
        unbind: function () {
            _getTargets().each(function () {
                $(this).off("click");
            });
        },
        reload: function () {
            this.unbind();
            this.bind();
        }
    };

    function _getIndicator() {
        return _getSpinner().find('i').first();
    }

    function _hasIndicator() {
        return _getIndicator().length > 0;
    }

    function _getSpinner() {
        return _getWrapper().find('div').first();
    }

    function _getWrapper() {
        return $('body').find(pub.wrapper).first();
    }

    function _getTargets() {
        return $('[id=' + window.cp.env.containerId + ']').find(pub.selector);
    }

    return pub;
})(window.jQuery);

window.cp.icheck = (function ($) {
    var pub = {
        isActive: true,
        itemSelector: 'input[type=checkbox]',
        checkboxClass: 'icheckbox_flat-red',
        radioClass: 'iradio_flat-red',
        init: function () {
            this.bind();
        },
        bind: function () {
            if (_hasItems()) {
                _getItems().each(
                    function () {
                        $(this).iCheck({
                            checkboxClass: pub.checkboxClass,
                            radioClass: pub.radioClass
                        });
                    }
                );
            }
        },
        unbind: function () {
            if (_hasItems()) {
                _getItems().iCheck('destroy');
            }
        }
    };

    function _getItems() {
        return $('body').find(pub.itemSelector);
    }

    function _hasItems() {
        return (_getItems().length > 0);
    }

    return pub;
})(window.jQuery);