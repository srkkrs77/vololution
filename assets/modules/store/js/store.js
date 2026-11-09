var url = decodeURIComponent(window.location.href);
var _GET = decodeURIComponent(window.location.search.slice(1))
    .split('&')
    .reduce(function _reduce(a, b) {
        b = b.split('=');
        if (a[b[0]]) {
            if (is_array(a[b[0]])) { a[b[0]].push(b[1]); }
            else { var arr = []; arr.push(a[b[0]]); arr.push(b[1]); a[b[0]] = arr; }
        } else { a[b[0]] = b[1]; }
        return a;
    }, {});

function link() {
    mass = location.href.split('?');
    return mass[0] + '?id=' + _GET['id'] + '&a=' + _GET['a'];
}

function store_search(val) {
    $('.item_list .catalog_item').each(function() {
        var search_name = $(this).find('h3').html();
        search_name = search_name.toLowerCase();
        if (search_name.indexOf(val.toLowerCase()) < 0) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
}

store = {
    types: {},

    extend: function(obj1) {
        param = {
            lang: $('[name="language"]').val()
        };
        return $.extend(obj1, param);
    },

    init: function() {
        store.query('start', {}, function(data) {
            store.category = data.allcategory;
            store.update_category(data.category);

            var id = $('.category_list').find('li').first().find('a').attr('data-id');
            $('[name=parent]').val(id);
            store.update_list(store.category[id]);
        });

        store.types = eval('(' + $('[name="types"]').val() + ')');

        $('a.item-reinstall').live('click', function() {
            if (confirm($(this).attr('data-text'))) store.install(this);
            return false;
        });

        $('a.item-install').live('click', function() {
            store.install(this);
            return false;
        });

        $('.category_list a').live('click', function() {
            $('[name=parent]').val($(this).attr('data-id'));
            store.update_list(store.category[$(this).attr('data-id')], $(this).attr('data-tpl'));
            return false;
        });

        var file;
        $('#install_file').on('change', function() {
            file = this.files[0];
        });

        $('#install_file_btn').on('click', function() {
            if ($.isEmptyObject(file)) return;
            $('#install_file_resp').html('');
            $('#install_file_prg').fadeIn();
            $('#install_file_btn').fadeOut();

            $.ajax({
                url: link() + '&method=fast',
                type: 'POST',
                data: new FormData($('#install_file_form')[0]),
                cache: false,
                contentType: false,
                processData: false,
                xhr: function() {
                    var myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) {
                        myXhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                $('progress').attr({ value: e.loaded, max: e.total });
                            }
                        }, false);
                    }
                    return myXhr;
                }
            }).done(function(resp) {
                $('#install_file_resp').html(file.name + '<br>' + resp);
                $('#install_file_prg').fadeOut();
                $('#install_file_btn').fadeIn();
                $('progress').attr({ value: 0 });
                $('#install_file_form')[0].reset();
                $('#install_file').removeAttr('disabled');
            }).fail(function(resp) {
                $('#install_file_resp').html(file.name + '<br>' + resp);
                $('#install_file_prg').fadeOut();
                $('#install_file_btn').fadeIn();
                $('#install_file').removeAttr('disabled');
            });
            $('#install_file').attr({ disabled: 'disabled' });
        });
    },

    install: function(elm) {
        var el = $(elm).closest('.catalog_item').find('.informer');
        var file = $(elm).closest('.catalog_item').find('[name="link"]').val();

        $('.item_list .catalog_item').addClass('blocked');
        $(elm).closest('.catalog_item').find('.loader').show();

        $.ajax({
            url: link() + '&method=fast&action=install&cid=' + $(elm).attr('data-id') + '&name=' + $(elm).attr('data-name'),
            type: 'POST',
            data: { method: 'fast', file: file },
            success: function() {
                el.closest('.catalog_item').find('.loader').hide();
                el.css('display', 'block').animate({ opacity: 1 }, 500, function() {
                    el.delay(2000).animate({ opacity: 0 }, 3000, function() { el.css('display', 'none'); });
                });
                el.closest('.catalog_item')
                    .removeClass('item-install')
                    .addClass('item-reinstall');
                el.closest('.catalog_item').find('.curr').hide();
                $('.item_list .catalog_item').removeClass('blocked');
            }
        });
    },

    query: function(action, param, callback) {
        param = store.extend(param);
        $.ajax({
            url: window.STORE_API + '?get=' + action,
            cache: false,
            data: param,
            dataType: 'json',
            type: 'post',
            success: function(data) {
                callback(data);
            }
        });
    },

    update_category: function(data) {
        $('.category_list').html('<ul>' + store.parse_list1(data, $('.tpl #tpl_category').html()) + '</ul>');
    },

    update_list: function(data, tpl) {
        tpl = tpl || 'list';
        $('.item_list').html(store.parse_list(data, $('.tpl #tpl_' + tpl).html(), tpl));
    },

    parse_list: function(data, tpl, template) {
        var out = '';
        if (data) {
            $.each(data, function(key, value) {
                try {
                    out = out + store.parse_list_item(tpl, value, template);
                } catch (e) {
                    console.log(e.name);
                }
            });
        }
        return out;
    },

    parse_list1: function(data, tpl) {
        var out = '';
        $.each(data, function(key, value) {
            try {
                out = out + store.parse(tpl, value);
            } catch (e) {
                console.log(e.name);
            }
        });
        return out;
    },

    parse_list_item: function(str, array, tpl) {
        tpl = tpl || 'list';
        array.cls = 'pack_install';
        array.version = array.version || '';
        array.date = array.date || '';

        if ($.isPlainObject(array.url)) {
            var options = [];
            var versions = array.url.fieldValue;
            $.each(versions, function(key, value) {
                options.push('<option value="' + value.file + '">' + value.version + '</option>');
                if (!array.version) array.version = value.version;
                if (!array.date) array.date = value.date;
            });
            var $str = $(str);
            $str.find('[name=link]').append(options.join(''));
            $str.find('option').first().prop('selected', true);
            if (versions.length == 1) {
                $str.find('[name=link]').hide();
            }
            str = $str.wrapAll('<div></div>').parent().html();
        }

        if (array.type) {
            var type = array.type;
            type = type == 'snippet' ? 'snippets' : type;
            type = type == 'module' ? 'modules' : type;
            type = type == 'plugin' ? 'plugins' : type;

            if (store.types[type] && typeof store.types[type][array.name_in_modx] !== 'undefined') {
                array.cls = 'pack_reinstall';
            }
        }

        return str.replace(/%\w+%/g, function(placeholder) {
            return array[placeholder.split('%').join('')] || '';
        });
    },

    parse: function(str, array) {
        return str.replace(/%\w+%/g, function(placeholder) {
            return array[placeholder.split('%').join('')] || '';
        });
    },

    is_array: function(inputArray) {
        return inputArray && !(inputArray.propertyIsEnumerable('length')) && typeof inputArray === 'object' && typeof inputArray.length === 'number';
    }
};

$(function() {
    store.init();
});