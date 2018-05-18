/*
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

window.cp.bubbleSpinner = (function ($) {
    var pub = {
        backgroundColor: 'transparent',
        isActive: false,
        isBubbling: false,
        init: function () {
            // create bubbles
            for (i = 0; i < 4; i++) {
                _bubbles.push(_getBubble(i));
                _timings.push(parseInt(Math.random() * (1000 - 150) + 150));
            }
            console.log(_timings);
            //console.log(_getWrapperCss());
            //console.log(_getContainerCss());
            // append spinner
            this.bind();
        },
        show: function () {
            this.isBubbling = true;
            for(index in _bubbles) {
                _bubbles[index].animate({
                    'opacity': 1,
                    'font-size': 150 + 'px'
                }, _timings[index], function () {
                    $(this).animate({
                        'opacity': 0,
                        'font-size': 0 + 'px'
                    }, _timings[index]);
                });
            }
            _getSpinner().fadeIn(150);
        },
        hide: function () {
            this.isBubbling = false;
            _getSpinner().fadeOut(150);
        },
        bind: function () {
            $('body').prepend(_getSpinner());
        },
        unbind: function () {
            $('body').find(_getSpinner().attr('class')).remove();
        }
    };

    var _wrapper;
    var _container;
    var _bubbles = [];
    var _timings = [];

    function _getWrapperCss() {
        return 'display:none;width:100%;' +
            'height:' + window.cp.fullHeight() + 'px;' +
            'background-color:' + pub.backgroundColor;
    }

    function _getContainerCss() {
        return 'display:block;position:absolute;' +
            'width:300px;height:300px;' +
            'left:' + parseInt(window.cp.dim($(window)).width / 2 - 150) + 'px;' +
            'top:' + parseInt(window.cp.dim($(window)).height / 2 - 150) + 'px;';
    }

    function _getSpinner() {
        if (!_wrapper) {
            _wrapper = $('<div></div>')
                .attr({
                    'style': _getWrapperCss(),
                    'class': 'bs-wrapper'
                })
                .append(_getContainer());
            _wrapper.on("click", function () {pub.hide();});
        }
        return _wrapper;
    }

    function _getContainer() {
        if (!_container) {
            _container = $('<div></div>')
                .attr(
                    {
                        'style': _getContainerCss(),
                        'class': 'bs-container'
                    }
                );

            for (bubbleIndex in _bubbles) {
                _container
                    .append(_bubbles[bubbleIndex]);
            }
        }
        return _container;
    }

    function _getBubbleCss(index) {
        switch (index) {
            case 0 :
                return 'font-size:1px;opacity:0;position:absolute;left:75px;top:75px';
                break;
            case 1 :
                return 'font-size:1px;opacity:0;position:absolute;left:225px;top:75px';
                break;
            case 2 :
                return 'font-size:1px;opacity:0;position:absolute;left:75px;top:225px';
                break;
            case 3 :
                return 'font-size:1px;opacity:0;position:absolute;left:225px;top:225px';
                break;
        }
    }

    function _getBubble(index) {
        return $('<i></i>')
            .attr(
                {
                    'class': (index % 2) === 0 ? 'fa fa-circle black' : 'fa fa-circle red',
                    'style': _getBubbleCss(index)
                }
            );
    }

    return pub;
})(window.jQuery);

window.UIDashboard = (function ($) {
    var pub = {
        wrapperSelector: '[data-refresh]',
        actionsListSelector: '[class*=panel-group][role=tablist]',
        emptyText: 'За текущий день событий не зарегистрировано.',
        refreshInterval: 30000,
        refreshTimeout: 3000,
        spinner: {
            selector: '[class*=page-header] a',
            errorTextSelector: '[class*=error-text]',
            disable: function (errorText) {
                this.getInstance().addClass('disabled');
                this.stop();
                if (errorText) {
                    this.getErrorText().html(errorText);
                    this.getErrorText().fadeIn(150);
                }
            },
            enable: function () {
                this.getInstance().removeClass('disabled');
                this.getErrorText().fadeOut(150);
                this.start();
            },
            active: false,
            bind: function () {
                this.getInstance().on("click", function () {
                    if ($(this).hasClass('disabled')) {
                        pub.enable();
                    } else {
                        pub.disable();
                    }
                });
            },
            getErrorText: function () {
                return _getWrapper().find(this.errorTextSelector).first();
            },
            getInstance: function () {
                return _getWrapper().find(this.selector).first();
            },
            start: function () {
                this.active = true;
                this.getInstance().find('i').first().addClass('fa-spin');
            },
            stop: function () {
                this.active = false;
                this.getInstance().find('i').first().removeClass('fa-spin');
            }
        },
        disable: function (errorText) {
            _disabled = true;
            clearTimeout(_refreshTimerId);
            this.spinner.disable(errorText);
        },
        enable: function () {
            _disabled = false;
            this.spinner.enable();
            pub.requestData();
        },
        isActive: true,
        init: function () {
            if (_getWrapper().data('refresh').length > 0) {
                this.spinner.bind();
                this.refresh();
            }
        },
        bindOnCollapse: function () {
            _getWrapper().find('[class*=collapse]').each(function () {
                $(this).off('show.bs.collapse');
                $(this).on('show.bs.collapse', function () {
                    pub.disable();
                });
                $(this).off('hidden.bs.collapse');
                $(this).on('hidden.bs.collapse', function () {
                    if (_canRefresh()) {
                        pub.refresh();
                    }
                });
            });
        },
        refresh: function () {
            _refreshTimerId = setTimeout(function () {
                pub.requestData();
            }, _onload ? 0 : this.refreshInterval);
        },
        requestData: function () {
            if (_canRefresh()) {
                this.spinner.start();
                setTimeout(function () {
                    if (_onload) _onload = false;
                    if (_canRefresh()) {
                        $.ajax(
                            _getWrapper().data('refresh'),
                            {
                                async: true,
                                dataType: 'json',
                                method: 'get',
                                complete: function () {
                                    pub.spinner.stop();
                                },
                                success: function (data) {
                                    pub.spinner.stop();
                                    if (data.length > 0) {
                                        // render actions result
                                        _renderActionsList(data);
                                        // bind on collapse event
                                        pub.bindOnCollapse();
                                    } else {
                                        if (_dataItems.length === 0) {
                                            _renderEmptyActionsList();
                                        }
                                    }
                                    // resume refreshing process
                                    pub.refresh();
                                    // reload panel spinner events
                                    if (window.cp.spinner.isActive) {
                                        window.cp.spinner.reload();
                                    }
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    pub.disable("Ошибка обновления:<br/>" + jqXHR.responseJSON.message);
                                }
                            }
                        );
                    } else {
                        pub.spinner.stop();
                    }
                }, _onload ? 0 : this.refreshTimeout);
            } else {
                pub.spinner.stop();
            }
        }
    };

    var _dataItems = [];
    var _disabled = false;
    var _onload = true;
    var _refreshTimerId;

    function _renderEmptyActionsList() {
        // prevent duplication of empty actions list message
        if (_getActionsList().find('p').first().length === 0) {
            var empty;
            empty = document.createElement('p');
            empty.className = "text-center green";
            empty.appendChild(document.createTextNode(pub.emptyText));
            _getActionsList().append(empty);
        }
    }

    function _renderActionsList(data) {
        if (_dataItems.length === 0) {
            $(data).each(function () {
                _dataItems.unshift(this);
                var rendered = _renderItem($(this).get(0));
                _getActionsList().append(rendered);
                rendered.animate({
                    opacity: 1
                }, 300);
            });
        } else {
            var _countUpdated = 0;
            $(data).each(function () {
                // if action item doesn't exist add it to the list
                if (!_actionExists($(this).attr('id'))) {
                    _countUpdated++;
                    _dataItems.push(this);
                    var rendered = _renderItem($(this).get(0));
                    _getActionsList().prepend(rendered);
                    rendered.animate({
                        opacity: 1
                    }, 300);
                }
            });
            // remove actions of index bigger than 10 in the list and in the _dataItems array
            if (_countUpdated > 0) {
                _getActions().each(function (index, el) {
                    if (index > 7) {
                        $(el).remove();
                        _dataItems.shift();
                    }
                });
            }
        }
    }

    function _actionExists(id) {
        var _actionExists = false;
        $(_dataItems).each(function () {
            if ($(this).attr('id') === id) {
                _actionExists = true;
            }
        });
        return _actionExists;
    }

    function _canRefresh() {
        return _getWrapper().find('[class*=collapse][class*=in]').first().length === 0
            && _getWrapper().find('[class*=collapsing]').first().length === 0 &&
            _disabled === false;
    }

    function _getActionsList() {
        return _getWrapper().find(pub.actionsListSelector).first();
    }

    function _getActions() {
        return _getWrapper().find('[class*=panel-action][class*=panel-default]');
    }

    function _getWrapper() {
        return $('body').find(pub.wrapperSelector).first();
    }

    function _renderItemHeading(id, title, subtitle, created_at) {
        return $('<div></div>')
            .attr(
                {
                    'class': 'panel-heading',
                    'role': 'tab',
                    'id': "cpanel-item-action-id" + id
                }
            )
            .append(
                [
                    $('<a></a>')
                        .attr(
                            {
                                'class': 'panel-title revert yellow',
                                'href': "#cpanel-item-action-collapsible-id" + id,
                                'data-toggle': 'collapse',
                                'data-parent': '#cpanel-item-actions-accordion',
                                'aria-expanded': 'false',
                                'aria-controls': 'cpanel-item-action-collapsible-id' + id
                            }
                        )
                        .append(
                            [
                                $('<i></i>')
                                    .attr('class', 'fa fa-chevron-right'),
                                $('<span></span>')
                                    .attr('class', 'black')
                                    .text(created_at),
                                '<br/>',
                                $('<span></span>')
                                    .attr('class', 'font-book red')
                                    .text(title)
                            ]
                        ),
                    '<br/>',
                    $('<span></span>')
                        .attr('class', 'font-book text-backwards')
                        .text(subtitle)
                ]
            );
    }

    function _renderItemLink(linkData, attrName, title) {
        return $(linkData).length > 0 && $(linkData[attrName]).length > 0 ?
            $('<a></a>')
                .text(title)
                .attr(
                    {
                        'class': "font-book revert green",
                        'data-spinner': 'true',
                        'href': linkData[attrName].href
                    }
                ) :
            $('<span></span>')
                .text(title)
                .attr(
                    {
                        'class': "font-book"
                    }
                );
    }

    function _renderItemBody(id, index, description, links, user_agent, user_ip) {
        return $('<ul></ul>')
            .attr(
                {
                    'class': "list-group panel-collapse collapse",
                    'id': 'cpanel-item-action-collapsible-id' + id,
                    'aria-labelledby': 'cpanel-item-action-id' + id,
                    'role': 'tablist'
                }
            )
            .append(
                $('<li></li>')
                    .attr('class', "list-group-item")
                    .append(
                        $('<p></p>')
                            .attr('class', "list-group-item-text")
                            .append(
                                [
                                    "Абонент<br/>",
                                    _renderItemLink(
                                        ( links.length === 2 ) ?
                                            links[1] : [],
                                        'subscriber',
                                        index
                                    ),
                                    "<br/><br/>Событие<br/>",
                                    _renderItemLink(
                                        ( links.length > 0 ) ?
                                            links[0] : [],
                                        'self',
                                        description
                                    ),
                                    ( user_agent ) ?
                                        $('<span></span>')
                                            .attr('class', "text-backwards font-light")
                                            .text(user_agent)
                                            .prepend("<br/><br/>") : '',
                                    ( user_ip ) ?
                                        $('<span></span>')
                                            .attr('class', "text-backwards font-light")
                                            .text(user_ip)
                                            .prepend("<br/><br/>") : ''
                                ]
                            )
                    )
            );
    }

    function _renderItem(dataItem) {
        return $('<div></div>')
            .attr(
                {
                    'class': 'panel panel-action panel-default',
                    'style': 'opacity:0'
                }
            )
            .append(
                [
                    _renderItemHeading(
                        dataItem.id,
                        dataItem.resource,
                        dataItem.description,
                        dataItem.created_at
                    ),
                    _renderItemBody(
                        dataItem.id,
                        dataItem.index,
                        dataItem.description,
                        dataItem._links,
                        dataItem.user_agent,
                        dataItem.user_ip
                    )
                ]
            );
    }

    return pub;
})(window.jQuery);

window.cp.selectAll = (function ($) {

    var pub = {
        isActive: true,
        checkboxSelector: '[type=checkbox][name*=selected]',
        selectAllSelector: '[data-toggle=selection]',
        init: function () {
            if (_hasSelectAll()) {
                _getSelectAll().on("ifChecked", function () {
                    _getChechboxes().each(
                        function () {
                            $(this).iCheck('check');
                        }
                    );
                });
                _getSelectAll().on("ifUnchecked", function () {
                    _getChechboxes().each(
                        function () {
                            $(this).iCheck('uncheck');
                        }
                    );
                });
            }
        }
    };

    function _getChechboxes() {
        return window.cp.container.find(pub.checkboxSelector);
    }

    function _getSelectAll() {
        return window.cp.container.find(pub.selectAllSelector).first();
    }

    function _hasSelectAll() {
        return _getSelectAll().length > 0;
    }

    return pub;
})(window.jQuery);