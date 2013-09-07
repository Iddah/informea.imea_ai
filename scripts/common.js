$('document').ready(function() {
    /* Click handler for all close buttons of alerts */
    $('div.alert>button.close').click(function(evt) {
        $(evt.target).parent().fadeOut({duration: 600});
    });

    // Autocomplete for terms
    $("#q_term").reusableComboBox({
        select: function (evt, ob) {
            var item = ob.item;
            if($('ul#q_term_holder>li#qterm-' + item.value).length <= 0) {
                $('<li id="qterm-' + item.value + '" class="round">' + item.text + '</li>')
                    .append('<a href="javascript:void(0);" onclick="qTermRemove(' + item.value + ', this);"><i class="icon icon-remove"></i></a>')
                    .appendTo($('ul#q_term_holder'));

                if($('select#q_term option:selected').length > 1) {
                    $('#q_term_andor').show();
                }
            }
            return false;
        }
    });
});


function qTermRemove(id, T) {
    $(T).parent().remove();
    $('select#q_term>option[value=' + id + ']').removeAttr('selected');
    if($("select#q_term option:selected").length <= 1) {
        $('#q_term_andor').hide();
    }
}


/* Function called when view mode is changed. For instance: grid/list/table/etc.*/
function onChangeViewMode(T) {
    var option = $('option:selected', T)[0];
    var url = $(option).data('url');
    window.location = url;
}


function selectCountryWidgetOnChange(T) {
    onChangeViewMode(T);
}


(function ($) {
    $.widget("ui.combobox", {
        _create: function (customClass) {
            var self = this,
                select = this.element.hide(),
                selected = select.children(":selected"),
                value = selected.val() ? selected.text() : "";
            // var input = this.input = $( "<input>" ).insertAfter( select ).val( value )
            var input = this.input = $("<input>").insertAfter(select)
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: function (request, response) {
                        var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                        response(select.children("option").map(function () {
                            var text = $(this).text();
                            if (this.value && ( !request.term || matcher.test(text) ))
                                return {
                                    label: text.replace(
                                        new RegExp(
                                            "(?![^&;]+;)(?!<[^<>]*)(" +
                                                $.ui.autocomplete.escapeRegex(request.term) +
                                                ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                        ), "<strong>$1</strong>"),
                                    value: text,
                                    option: this
                                };
                        }));
                    },
                    select: function (event, ui) {
                        ui.item.option.selected = true;
                        self._trigger("selected", event, {
                            item: ui.item.option
                        });
                        explorerUISelectTerm(ui.item.option.value, ui.item.option.text);
                        ui.item.value = '';
                    },
                    change: function (event, ui) {
                        if (!ui.item) {
                            var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i");
                            var valid = false;
                            select.children("option").each(function () {
                                if ($(this).text().match(matcher)) {
                                    this.selected = valid = true;
                                    return false;
                                }
                            });
                            if (!valid) {
                                // remove invalid value, as it didn't match anything
                                $(this).val("");
                                select.val("");
                                input.data("autocomplete").term = "";
                                return false;
                            }
                        }
                    }
                })
                .addClass("ui-widget ui-widget-content ui-corner-left " + (typeof(customClass) != 'undefined' ? customClass : ''));

            input.data("autocomplete")._renderItem = function (ul, item) {
                return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append("<a>" + item.label + "</a>")
                    .appendTo(ul);
            };

            this.button = $("<button type='button'>&nbsp;</button>")
                .attr("tabIndex", -1)
                .attr("title", "Show All Terms")
                .insertAfter(input)
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass("ui-corner-all")
                .addClass("ui-corner-right ui-button-icon")
                .click(function () {
                    // close if already visible
                    if (input.autocomplete("widget").is(":visible")) {
                        input.autocomplete("close");
                        return;
                    }
                    // work around a bug (likely same cause as #5265)
                    $(this).blur();
                    // pass empty string as value to search for, displaying all results
                    input.autocomplete("search", "");
                    input.focus();
                });
        },

        destroy: function () {
            this.input.remove();
            this.button.remove();
            this.element.show();
            $.Widget.prototype.destroy.call(this);
        }
    });
})(jQuery);

// Reusable combobox
(function ($) {
    $.widget("ui.reusableComboBox", {
        _create: function (customClass) {
            var self = this,
                select = this.element.hide(),
                selected = select.children(":selected"),
                value = selected.val() ? selected.text() : "";
            // var input = this.input = $( "<input>" ).insertAfter( select ).val( value )
            var input = this.input = $("<input>").insertAfter(select)
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: function (request, response) {
                        var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                        response(select.children("option").map(function () {
                            var text = $(this).text();
                            if (this.value && ( !request.term || matcher.test(text) ))
                                return {
                                    label: text.replace(
                                        new RegExp(
                                            "(?![^&;]+;)(?!<[^<>]*)(" +
                                                $.ui.autocomplete.escapeRegex(request.term) +
                                                ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                        ), "<strong>$1</strong>"),
                                    value: text,
                                    option: this
                                };
                        }));
                    },
                    select: function (event, ui) {
                        ui.item.option.selected = true;
                        self._trigger("select", event, {
                            item: ui.item.option
                        });
                        ui.item.value = '';
                    },
                    change: function (event, ui) {
                        if (!ui.item) {
                            var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i");
                            var valid = false;
                            select.children("option").each(function () {
                                if ($(this).text().match(matcher)) {
                                    this.selected = valid = true;
                                    return false;
                                }
                            });
                            if (!valid) {
                                // remove invalid value, as it didn't match anything
                                $(this).val("");
                                select.val("");
                                input.data("autocomplete").term = "";
                                return false;
                            }
                        }
                    }
                })
                .addClass("ui-widget ui-widget-content ui-corner-left " + (typeof(customClass) != 'undefined' ? customClass : ''));

            input.data("autocomplete")._renderItem = function (ul, item) {
                return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append("<a>" + item.label + "</a>")
                    .appendTo(ul);
            };

            this.button = $("<button type='button'>&nbsp;</button>")
                .attr("tabIndex", -1)
                .attr("title", "Show All Terms")
                .insertAfter(input)
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass("ui-corner-all")
                .addClass("ui-corner-right ui-button-icon")
                .click(function () {
                    // close if already visible
                    if (input.autocomplete("widget").is(":visible")) {
                        input.autocomplete("close");
                        return;
                    }
                    // work around a bug (likely same cause as #5265)
                    $(this).blur();
                    // pass empty string as value to search for, displaying all results
                    input.autocomplete("search", "");
                    input.focus();
                });
        },

        destroy: function () {
            this.input.remove();
            this.button.remove();
            this.element.show();
            $.Widget.prototype.destroy.call(this);
        }
    });
})(jQuery);
