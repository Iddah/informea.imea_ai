$(document).ready(function () {
    var find_nfp_input = $('.find-nfp-input');
    find_nfp_input.autocomplete({
        source: function (request, response) {
            $.ajax({
                url: ajax_url + '?action=nfp_autocomplete',
                dataType: "json",
                data: { maxRows: 10, key: request.term },
                success: function (data) {
                    response($.map(data, function (item) {
                        return { label: item.label, value: item.id_country,
                            id_contact: item.id_contact, id_country: item.id_country, id_treaty: item.id_treaty
                        }
                    }));
                }
            });
        },
        delay: 100, minLength: 1,
        focus: function (event, ui) { return false; },
        select: function (ui, data) {
            $('.find-nfp-input').val(data.item.label);
            window.location = blog_dir + '/countries/' + data.item.id_country + '/nfp?showall=true&id_contact=' + data.item.id_contact + '#contact-bookmark-' + data.item.id_treaty;
            return false;
        }
    });
    find_nfp_input.keydown(function (e) { return e.keyCode != 13; });

    /* Click on country name, view Country MEA Membership */
    var ctrl2 = $('input#party-filter');
    ctrl2.keyup(function(e) {
        if (e.which == 13) {
            e.preventDefault();
        } else if (e.which == 27) {
            $(this).val('');
            filterCountriesList('');
        } else {
            filterCountriesList(ctrl2.val());
        }
    });
    /* Click on h2 to expand country */
    $('ul.countries>li>h2').click(function() {
        var content = $(this).parent().find('div.content');
        var t = this;
        content.slideToggle(200, function() {
            if($(content).is(':visible')) {
                $(t).find('i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
            } else {
                $(t).find('i').removeClass('icon-minus-sign').addClass('icon-plus-sign');
            }
        });
    });
    /* Click expand all / collapse all */
    $('div.toolbar-countries button#expand-all').click(countriesExpandAll);
    $('div.toolbar-countries button#collapse-all').click(countriesCollapseAll);

    /* Click on country grid filter checkboxes (show/hide columns in grid view mode) */
    $('body.imea-countries div.filter-treaties input').click(function() {
        var id = '.party-' + $(this).val();
        $(id).toggle();
    });
    $('div.filter-treaties>button.close').click(function(evt) { $(evt.target).parent().fadeOut({duration: 600}); });

    /* NFP */
    $('ul.nfp>li>h2').click(function() {
        var content = $(this).parent().find('div.content');
        var t = this;
        content.slideToggle(200, function() {
            if($(content).is(':visible')) {
                $(t).find('i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
            } else {
                $(t).find('i').removeClass('icon-minus-sign').addClass('icon-plus-sign');
            }
        });
    });
    $('div.toolbar-nfp button#expand-all').click(nfpExpandAll);
    $('div.toolbar-nfp button#collapse-all').click(nfpCollapseAll);
});


function nfpExpandAll() {
    $('ul.nfp>li>div.content').show();
    $('ul.nfp>li>h2>i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
}


function nfpCollapseAll() {
    $('ul.nfp>li>div.content').hide();
    $('ul.nfp>li>h2>i').removeClass('icon-minus-sign').addClass('icon-plus-sign');
}


function filterCountriesList(filter) {
    var rows = $('ul.countries li h2');
    rows.each(function(idx, item) { $(item).parent().show(); });
    if(filter.length > 0) {
        var regExp = new RegExp(filter, 'i');
        rows.each(function(idx, item) {
            var found = regExp.test($(this).text());
            if(!found) {
                $(this).parent().hide();
            } else {
                $(this).parent().show();
            }
        });
    }
}

function countriesExpandAll() {
    $('ul.countries>li>div.content').show();
    $('ul.countries>li>h2>i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
}

function countriesCollapseAll() {
    $('ul.countries>li>div.content').hide();
    $('ul.countries>li>h2>i').removeClass('icon-minus-sign').addClass('icon-plus-sign');
}

