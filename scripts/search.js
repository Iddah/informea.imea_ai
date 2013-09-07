$(document).ready(function() {
    $('ul.sidebar h3').click(function() {
        var H3 = this;
        $(this).siblings('div.content').slideToggle(100, function() {
            var icon = $(H3).find('i.icon');
            if($(this).is(':visible')) {
                icon.removeClass('icon-plus-sign').addClass('icon-minus-sign');
            } else {
                icon.removeClass('icon-minus-sign').addClass('icon-plus-sign');
            }
        })
    });

    $("#q_term_filters").reusableComboBox({
        select: function (evt, ob) {
            var item = ob.item;
            var span_class = 'term-content span-term-' + item.value;
            if (!$('.span-term-' + item.value).length) {
                var label = item.text;
                if (label.length > 20) {
                    label = label.substring(0, 17) + '...';
                }
                if ($('#selected-terms-holder').text().length > 0) {
                    $('#filter_and_or_radiobuttons').show();
                }
                $('<li title="' + item.text + '" onclick="javascript:explorerIndexUIDeselectTerm(' + item.value + ');">' + label + '</li>').attr({'class': span_class})
                    .append('<span class="ui-icon ui-icon-close"></span>')
                    .appendTo($('#selected-terms-holder'));
            }
            return false;
        }
    });

    $('.explorer-treaty-click-children').click(function (e) {
        if ($(e.target).is(':checked')) {
            $(e.target).siblings('ul.sublist').find('[type=checkbox]').attr('checked', 'checked');
        } else {
            $(e.target).siblings('ul.sublist').find('[type=checkbox]').removeAttr('checked');
        }
    });

    //
    $('#sort-order').change(function() {
        var dir = $(this).val();
        if(dir == 'desc') {
            sort_descending();
        } else {
            sort_ascending();
        }
    });

    $('#tab-mode').change(function() {
        var tab = $('#tab-mode>option:selected').val();
        $('#q_tab_filters').val(tab);
        $('#filter').submit();
    });

    $.each($('a.ajax-expand'), function (i, item) {
        toggleResultAjax(this);
    });

    $.each($('a.toggle-result'), function (i, item) {
        $(this).click(function (e) {
            e.preventDefault();
            toggleResult(this);
        });
    });

    $('#expand-all').click(function() {
        if(confirm('This operation may take a very long time and even freeze your browser. Do you want to continue?')) {
            $('ul.search-results a.toggle-result').trigger('click');
            $('ul.search-results a.ajax-expand').trigger('click');
            $('ul.search-results i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
        }
    });

    $('#collapse-all').click(function() {
        $('ul.search-results div').hide();
        $('ul.search-results i').removeClass('icon-minus-sign').addClass('icon-plus-sign');
    });
});

function explorerIndexUIDeselectTerm(id) {
    $('.span-term-' + id).remove();
    $('#q_term_filters option:selected').each(function (idx, el) {
        if ($(el).attr('value') == id) {
            $(el).removeAttr('selected');
        }
    });
}


function toggleResult(T) {
    var target = $('#' + $(T).data('toggle'));
    if (target.is(':visible')) {
        $('i', T).removeClass('icon-minus-sign').addClass('icon-plus-sign');
    } else {
        $('i', T).removeClass('icon-plus-sign').addClass('icon-minus-sign');
    }
    target.toggle(100);
}


function toggleResultAjax(T) {
    if (!$(T).hasClass('processed')) {
        $(T).addClass('processed');
        $(T).click(function (e) {
            e.preventDefault();
            var id = $(T).data('id');
            var entity = $(T).data('role');
            var target = $('#' + $(T).data('toggle'));
            if (target.is(':visible')) {
                $('i', T).removeClass('icon-minus-sign').addClass('icon-plus-sign');
                target.toggle(100);
            } else {
                var data = { action: 'search_highlight', 'q_freetext': $('#q_freetext').val(), entity: entity, id: id };
                if (target.text() == '') {
                    $('i', T).removeClass('icon-plus-sign').addClass('icon-refresh');
                    $.post(ajax_url, data, function (response) {
                        if (response == '') {
                            target.append('This treaty has decisions listed here');
                        } else {
                            target.append(response);
                        }
                    });
                    $('i', T).removeClass('icon-refresh').removeClass('icon-plus-sign').addClass('icon-minus-sign');
                    target.toggle(100);
                } else {
                    $('i', T).removeClass('icon-plus-sign').addClass('icon-minus-sign');
                    target.toggle(100);
                }
            }
        });
    }
}