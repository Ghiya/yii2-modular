/*
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


window.UIConfig = (function ($) {
    var pub = {
        isActive: true,
        init: function () {

        }
    };

    return pub;
})(window.jQuery);


window.UIAuthorize = (function ($) {
    return {
        isActive: true,
        init: function () {
            this.bind();
        },
        bind: function () {
            _getForm().on("submit", function (e) {
                e.preventDefault();
                _getContentWrap().animate(
                    {
                        'opacity': 0
                    },
                    150,
                    function () {
                        window.cp.spinner.show('transparent', 'rgb(247,248,248)');
                        //window.cp.bubbleSpinner.show();
                        //return;
                        setTimeout(function () {
                            $.ajax(
                                _getForm().attr('action'),
                                {
                                    method: 'post',
                                    async: true,
                                    data: _getForm().serialize(),
                                    dataType: 'json',
                                    complete: function () {

                                    },
                                    success: function (data) {
                                        if (data.redirect) {
                                            window.location.href = data.redirect;
                                        } else {
                                            _getContentWrap().html(data.form);
                                            //_getContentWrap().fadeIn(150);
                                            _getContentWrap().animate(
                                                {
                                                    'opacity': 1
                                                },
                                                150
                                            );
                                            window.cp.spinner.hide();
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        window.cp.msgModal.setContent("Ошибка запроса", jqXHR.statusText);
                                        window.cp.msgModal.modal();
                                    }
                                }
                            );
                        }, 1000);
                    }
                );
                //_getContentWrap().fadeOut(150, );
            });
        },
        unbind: function () {
            _getForm().off("submit");
        }
    };

    function _getContentWrap() {
        return $(document).find('[id=cpanel-container] [class*=cpanel-content]').first();
    }

    function _getForm() {
        return $('#cpanel-container').find('form').first();
    }
})(window.jQuery);

window.UIRequester = (function ($) {
    var pub = {
        formSelector: 'form[id=requestForm]',
        formWrapperSelector: '[id=rootAccess]',
        formSendButtonSelector: '[type=submit]',
        requestProgressSelector: '[id=requestProgress]',
        paramRowsSelector: 'div[id=param-rows]',
        paramRowSelector: 'div[id=param-row]',
        isActive: true,
        init: function () {
            this.bind();
        },
        bind: function () {
            _formSubmit();
            _accessSelect();
            _paramAdd();
            _paramRemove();
        },
        rowsCount: function () {
            return _getForm().find('[id=param-row]').length;
        }
    };

    function _getUsername() {
        return _getFormWrapper().find('input[name=username]').first();
    }

    function _getPassword() {
        return _getFormWrapper().find('input[name=password]').first();
    }

    function _getFormWrapper() {
        return $('body').find(pub.formWrapperSelector).first();
    }

    function _getForm() {
        return $('body').find(pub.formSelector).first();
    }

    function _getFormSendButton() {
        return _getForm().find(pub.formSendButtonSelector).first();
    }

    function _getRequestProgress() {
        return $('body').find(pub.requestProgressSelector).first();
    }

    function _getParamRows() {
        return _getForm().find(pub.paramRowsSelector);
    }

    function _paramAdd() {
        _getParamRows().find('[id=params-add]').on('click', function (event) {
            if (pub.rowsCount() < 7) {
                var clonedParams = _getParamRows().find(pub.paramRowSelector).first().clone(true);
                clonedParams.appendTo(_getParamRows())
            }
        });
    }

    function _paramRemove() {
        _getParamRows().find('[id=params-remove]').on('click', function (event) {
            if (pub.rowsCount() > 1) {
                $(this).parent().parent().parent().fadeOut(100, function () {
                    $(this).remove();
                });
            } else {
                _getParamRows().find('input[type=text]').each(function () {
                    $(this).prop('value', '');
                });
            }
        });
    }

    function _accessSelect() {
        _getForm().find('input[name=rootAccess]').on('ifChecked', function () {
            _getFormWrapper().collapse('hide');
            _getUsername().attr('disabled', '');
            _getPassword().attr('disabled', '');
        });
        _getForm().find('input[name=rootAccess]').on('ifUnchecked', function () {
            _getFormWrapper().collapse('show');
            _getUsername().attr('disabled', null);
            _getPassword().attr('disabled', null);
        });
    }

    function _formSubmit() {
        _getFormSendButton().on('click', function (event) {
            event.preventDefault();
            if (!$(this).hasClass('disabled')) {
                _getFormSendButton().addClass('disabled');
                _getRequestProgress().find('i').first().addClass('fa-spin');
                _getRequestProgress().collapse('show');
                window.cp.msgModal.reset();
                _getForm().find('[class*=panel-footer] a').addClass('disabled');
                setTimeout(function () {
                    $.ajax(_getForm().attr('action'), {
                        async: true,
                        method: _getForm().attr('method'),
                        data: _getForm().serialize(),
                        dataType: "text",
                        timeout: 0,
                        success: function (data, textStatus, jqxhr) {
                            window.cp.msgModal.setContent("Запрос API", data);
                            window.cp.msgModal.modal();
                        },
                        error: function (jqxhr, textStatus, thrownError) {
                            window.cp.msgModal.setContent(jqxhr.responseJson.name, '<pre>' + thrownError + "\r\n\r\n" + jqxhr.responseText + "</pre>");
                            window.cp.msgModal.modal();
                        },
                        complete: function (jqxhr, textStatus) {
                            _getFormSendButton().removeClass('disabled');
                            _getRequestProgress().find('i').first().removeClass('fa-spin');
                            _getRequestProgress().collapse('hide');
                            _getForm().find('[class*=panel-footer] a').removeClass('disabled');
                        }
                    });
                }, 750);
            }
        });
    }

    return pub;
})(window.jQuery);

window.UISmsker = (function ($) {
    var pub = {
        formSelector: "form[id=smsker]",
        formWrapperSelector: "[id=smsker-wrap]",
        processSelector: "[id=smsker-process]",
        error: [],
        success: [],
        setProcess: function (processData, replaceable) {
            if (replaceable) {
                if (!_processText) {
                    _processText = _getProcess(true).html();
                }
                _getProcess(true).html(_processText + processData);
            } else {
                _getProcess(true).append(processData);
            }
        },
        charsCounter: {
            selector: '[id=chars-typed]',
            targetSelector: 'textarea',
            isActive: true,
            init: function () {
                this.getTarget().on("input propertychange", function () {
                    pub.charsCounter.update();
                });
                this.update();
            },
            update: function () {
                this.getInstance().text(this.getTarget().get(0).value.length);
            },
            getInstance: function () {
                return _getForm().find(this.selector).first();
            },
            getTarget: function () {
                return _getForm().find(this.targetSelector).first();
            }
        },
        isActive: true,
        init: function () {
            _getSendButton().on("click", function (event) {
                event.preventDefault();
                $(this).hide();
                _resetProcess();
                if (_getEmulated()) {
                    pub.setProcess("РЕЖИМ ОТЛАДКИ\nотправка сообщений не производится\n\n");
                }
                pub.setProcess('Обработка данных отправки...');
                $.ajax(
                    'default/data',
                    {
                        data: _getForm().serialize(),
                        async: true,
                        method: 'post',
                        dataType: 'json',
                        success: function (data) {
                            if (data.items.length > 0) {
                                _items = data.items;
                                _message = data.message;
                                pub.setProcess("OK\n\n");
                                pub.setProcess("Количество получателей: " + data.items.length + "\nТекст сообщения:\n>>\n" + data.message + "\n<<\n\n");
                                pub.send(_items.pop(), 1);
                            } else {
                                pub.setProcess("ПРЕРВАНО\n\nНекорректные данные формы");
                                _getFormWrapper().collapse('show');
                                _getSendButton().show();
                            }
                        },
                        error: function () {
                            pub.setProcess("ПРЕРВАНО\n\nОшибка выполнения запроса обработки данных отправки");
                            _getFormWrapper().collapse('show');
                            _getSendButton().show();
                        }
                    }
                );
            });
        },
        send: function (to, iteration) {
            if (to) {
                pub.setProcess("[ " + iteration + " ] : обработка сообщения для " + to + "...", true);
                $.ajax(
                    'default/send',
                    {
                        data: {
                            to: to,
                            text: _message
                        },
                        async: true,
                        method: 'post',
                        dataType: 'json',
                        success: function (data) {
                            if (data.errors) {
                                pub.error.push(
                                    {
                                        to: to,
                                        errors: data.errors
                                    }
                                );
                            } else {
                                pub.success.push(
                                    {
                                        to: to
                                    }
                                );
                            }
                            pub.send(_items.pop(), iteration + 1);
                        },
                        error: function () {
                            pub.setProcess("ПРЕРВАНО\n\nОшибка выполнения запроса");
                            _getFormWrapper().collapse('show');
                            _getSendButton().show();
                        }
                    }
                );
            } else {
                var i;
                pub.setProcess("Отправка сообщений...OK\n\n", true);
                pub.setProcess("Успешно: " + pub.success.length + "\n");
                for (i = 0; i < pub.success.length; i++) {
                    pub.setProcess(pub.success[i].to + "\n");
                }
                pub.setProcess("Ошибки: " + pub.error.length + "\n");
                for (i = 0; i < pub.error.length; i++) {
                    pub.setProcess(pub.error[i].to + " : " + pub.error[i].errors + "\n");
                }
                _getFormWrapper().collapse('show');
                _getSendButton().show();
            }
        }
    };

    var _processText;
    var _items = [];
    var _message = '';

    function _getEmulated() {
        return _getForm().find('input[name=emulated]').prop('checked');
    }

    function _getSendButton() {
        return _getForm().find('[type=submit]').first();
    }

    function _getForm() {
        return $('body').find(pub.formSelector).first();
    }

    function _getFormWrapper() {
        return $('body').find(pub.formWrapperSelector).first();
    }

    function _getProcess(forResult) {
        return $('body').find(
            !forResult ?
                pub.processSelector :
                pub.processSelector + ' pre'
        ).first();
    }

    function _resetProcess() {
        pub.error = [];
        pub.success = [];
        _getProcess(true).html("");
        _getFormWrapper().collapse('hide');
        _getProcess().show();
    }

    return pub;
})(window.jQuery);

window.UILogs = (function ($) {
    var pub = {
        items: null,
        formSelector: 'form[id=cp-form-jrange]',
        selectors: [
            'day',
            'month',
            'year'
        ],
        isActive: true,
        init: function () {
            for(var index in this.selectors) {
                _applyRange(this.selectors[index]);
            }
        }
    };
    function _applyRange( selector ) {
        if ( selector ) {
            $('input[id=' + selector + ']').jRange({
                theme: 'theme-black',
                from: pub.items[selector].from,
                to: pub.items[selector].to,
                step: 1,
                scale: pub.items[selector].scale,
                format: '%s',
                width: _getRangeWidth(selector),
                showLabels: true,
                snap: true
            });
        }
    }

    function _getRangeWidth( selector ) {
        return parseInt(window.cp.dim(window.cp.container.find(pub.formSelector + ' [class*=form-group][class*=field-' + selector + ']').first()).width);    }

    return pub;
})(window.jQuery)