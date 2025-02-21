jQuery(document).ready(function ($) {
    var wpftcf_wrap = $('.wpftcf-wrap')

    window.addEventListener('blur', function () {
        $('.wpftcf-structure .wpftcf-save-structure, .wpftcf-fields-wrap .wpftcf-save-fields', wpftcf_wrap).trigger('click')
    }, false)

    window.onbeforeunload = function () {
        $('.wpftcf-structure .wpftcf-save-structure, .wpftcf-fields-wrap .wpftcf-save-fields', wpftcf_wrap).trigger('click')
    }

    document.addEventListener('visibilitychange', function (ev) {
        if (document.visibilityState === 'hidden') {
            $('.wpftcf-structure .wpftcf-save-structure, .wpftcf-fields-wrap .wpftcf-save-fields', wpftcf_wrap).trigger('click')
        }
    })

    function activate_save_button () {
        var button = $('.wpftcf-structure .wpftcf-save-structure, .wpftcf-fields-wrap .wpftcf-save-fields', wpftcf_wrap)
        $('.wpftcf-save-button-text', button).text('Save')
        button.removeAttr('disabled')
    }

    function deactivate_save_button () {
        var button = $('.wpftcf-structure .wpftcf-save-structure, .wpftcf-fields-wrap .wpftcf-save-fields', wpftcf_wrap)
        $('.wpftcf-save-button-text', button).text('Saved')
        button.attr('disabled', true)
    }

    function init_sortables (element) {
        if (!element) element = wpftcf_wrap
        $('.wpftcf-sortable.wpftcf-sortable-rows', element).sortable({
            connectWith: '.wpftcf-sortable-rows',
            placeholder: 'wpftcf-state-highlight wpftcf-state-highlight-row',
            update: function () {
                activate_save_button()
            },
            start: function (event, ui) {
                if (ui.item && ui.item.length) {
                    var h = $(ui.item[0]).outerHeight()
                    if (h) $('.wpftcf-state-highlight').css('height', h + h * 0.1)
                }
            }
        }).disableSelection()
        $('.wpftcf-sortable.wpftcf-sortable-cols', element).sortable({
            connectWith: '.wpftcf-row:not(.wpftcf-cols-has-full) .wpftcf-sortable-cols',
            placeholder: 'wpftcf-state-highlight wpftcf-state-highlight-col',
            cancel: '.wpftcf-row[data-cols=\'1\'] .wpftcf-col',
            update: function (event, ui) {
                if (ui.sender && ui.sender.length) {
                    hide_or_show_row_actions(ui.sender[0])
                    if (ui.item && ui.item.length) hide_or_show_row_actions(ui.item[0])
                    init_sortables()
                }
                activate_save_button()
            },
            start: function (event, ui) {
                if (ui.item && ui.item.length) {
                    var h = $(ui.item[0]).outerHeight()
                    if (h) $('.wpftcf-state-highlight').css('height', h + h * 0.1)
                }
            }
        }).disableSelection()
        $('.wpftcf-sortable.wpftcf-sortable-fields', element).sortable({
            connectWith: '.wpftcf-sortable-fields',
            placeholder: 'wpftcf-state-highlight',
            cancel: '.wpftcf-fields-wrap .wpftcf-cant-be-inactive, .wpftcf-fields-wrap .wpftcf-cant-be-removed',
            update: function () {
                activate_save_button()
            },
            start: function (event, ui) {
                if (ui.item && ui.item.length) {
                    var h = $(ui.item[0]).outerHeight()
                    if (h) $('.wpftcf-state-highlight').css('height', h + h * 0.5)
                }
            },
            stop: function (event, ui) {
                if (ui.item && ui.item.length) {
                    if (ui.item.hasClass('wpftcf-cant-be-inactive') && ui.item.closest('.wpftcf-inactive').length) {
                        alert('This item cant be inactive.')
                        event.preventDefault()
                    } else if (ui.item.hasClass('wpftcf-cant-be-removed') && ui.item.closest('.wpftcf-trash').length) {
                        alert('This item cant be Trashed.')
                        event.preventDefault()
                    }
                }
            }
        }).disableSelection()
    }

    function hide_or_show_row_actions (element) {
        var row_wrap = $(element).closest('.wpftcf-row')
        var cols = $('.wpftcf-col', row_wrap)
        if (cols.length >= 2) {
            row_wrap.addClass('wpftcf-cols-has-full')
        } else {
            row_wrap.removeClass('wpftcf-cols-has-full')
        }
        row_wrap.attr('data-cols', cols.length)
    }

    init_sortables(wpftcf_wrap)

    wpftcf_wrap.on('click', '.wpftcf-add-new-row', function () {
        var wrap = $(this).closest('.wpftcf-wrap')
        var rows_wrap = $('.wpftcf-structure > .wpftcf-sortable-rows', wrap)
        var blank_row = $('#wpftcf-blank-row .wpftcf-row').clone()
        blank_row.appendTo(rows_wrap)
        init_sortables(blank_row)
        activate_save_button()
    })

    wpftcf_wrap.on('click', '.wpftcf-row .wpftcf-del-row', function () {
        var wrap = $(this).closest('.wpftcf-row')
        var fields = $('.wpftcf-field', wrap)
        var has_update = false
        if (fields.length) {
            if ($('.wpftcf-cant-be-inactive', wrap).length) {
                alert('Sorry: this row contains field or fields that cannot be inactive')
            } else if (confirm('all fields in in this row will be moved to inactive place')) {
                fields.appendTo($('.wpftcf-inactive', wpftcf_wrap))
                wrap.remove()
                has_update = true
            }
        } else {
            wrap.remove()
            has_update = true
        }
        if (has_update) {
            activate_save_button()
        }
    })

    wpftcf_wrap.on('click', '.wpftcf-sortable-rows .wpftcf-row-body .wpftcf-add-new-col', function () {
        var wrap = $(this).closest('.wpftcf-row-body')
        var cols_wrap = $('> .wpftcf-sortable-cols', wrap)
        var blank_col = $('#wpftcf-blank-row .wpftcf-col').clone()
        blank_col.appendTo(cols_wrap)
        hide_or_show_row_actions(wrap)
        init_sortables()
        activate_save_button()
    })

    wpftcf_wrap.on('click', '.wpftcf-col .wpftcf-del-col', function () {
        var cols_wrap = $(this).closest('.wpftcf-sortable-cols')
        var wrap = $(this).closest('.wpftcf-col')
        var fields = $('.wpftcf-field', wrap)
        var has_update = false
        if (fields.length) {
            if ($('.wpftcf-cant-be-inactive', wrap).length) {
                alert('Sorry: this column contains field or fields that cannot be inactive')
            } else if (confirm('all fields in in this column will be moved to inactive place')) {
                fields.appendTo($('.wpftcf-inactive', wpftcf_wrap))
                wrap.remove()
                has_update = true
            }
        } else {
            wrap.remove()
            has_update = true
        }
        if (has_update) {
            hide_or_show_row_actions(cols_wrap)
            init_sortables()
            activate_save_button()
        }
    })

    wpftcf_wrap.on('click', '.wpftcf-structure .wpftcf-save-structure:not([disabled])', function () {
        var ajax = false
        var structure = {}
        var structure_wrap = $(this).closest('.wpftcf-structure')
        var fieldKey
        $('.wpftcf-row', structure_wrap).each(function (rk, row) {
            structure[rk] = {}
            $('.wpftcf-col', row).each(function (ck, col) {
                structure[rk][ck] = {}
                $('.wpftcf-field', col).each(function (fk, field) {
                    fieldKey = $(field).attr('id')
                    if (fieldKey) {
                        structure[rk][ck][fieldKey] = fieldKey
                        ajax = true
                    }
                })
            })
        })

        if (ajax && $.active === 0) {
            var ico = $('.dashicons', $(this))
            var txt = $('.wpftcf-save-button-text', $(this))
            ico.toggleClass('wpftcf-loading-ico dashicons-update dashicons-editor-table')
            txt.text('Saving')

            $.ajax({
                type: 'POST',
                url: window.wpforotcf.ajax_url,
                data: {
                    nonce: window.wpftcf_current_form['ajax_nonce'],
                    formid: parseInt(window.wpftcf_current_form['formid']),
                    structure: JSON.stringify(structure),
                    action: 'wpforotcf_save_structure'
                }
            }).done(function () {
                ico.toggleClass('wpftcf-loading-ico dashicons-update dashicons-editor-table')
                deactivate_save_button()
            })
        }

    })

    // fields
    function init_fa_iconpicker () {
        var fa_wrap = $('.wpftcf-fa-iconpicker')
        var fa = $('[name=faIcon]', fa_wrap)
        fa.iconpicker({
            placement: 'top',
            selectedCustomClass: 'wpftcf-bg-primary',
            component: '.wpftcf-fa-ico-preview',
            collision: true
        })
    }

    function init_validations () {
        $('[name]').on('input propertychange', function () {
            var wrap = $(this).closest('.wpftcf-form-field-wrap')
            var val = $(this).val()
            $(this).removeClass('wpftcf-field-invalid')
            $('.wpftcf-field-form-notice', wrap).remove()

            if ($(this).attr('name') === 'fieldKey') {
                var r = new RegExp('[^0-9a-z_]')
                var oldval = $(this).data('oldval')
                if (oldval && val === oldval) return
                if (window.wpftcf && typeof window.wpftcf === 'object' && Object.keys(window.wpftcf).length) {
                    if (!val) {
                        $(this).addClass('wpftcf-field-invalid')
                        wrap.append('<span class="wpftcf-field-form-notice">fieldKey is required and cannot be empty</span>')
                    } else if (r.test(val)) {
                        $(this).addClass('wpftcf-field-invalid')
                        wrap.append('<span class="wpftcf-field-form-notice">Use only latin lowercase letters, numbers and underscore symbol</span>')
                    } else if (window.wpftcf[val] !== undefined) {
                        $(this).addClass('wpftcf-field-invalid')
                        wrap.append('<span class="wpftcf-field-form-notice">This key is already in use please write another value</span>')
                    }
                }
            } else {
                if (!val && $(this).attr('required')) {
                    $(this).addClass('wpftcf-field-invalid')
                    wrap.append('<span class="wpftcf-field-form-notice">This Field is required and cannot be empty</span>')
                }
            }
        })
    }

    function show_dialog (content, caption, w, h) {
        var s = tb_getPageSize()

        if (!caption) caption = 'Setup Field'

        if (!w) {
            w = Math.ceil(s[0] * (s[0] > 1000 ? 0.5 : 0.8))
        } else if (parseInt(w) > parseInt(s[0])) {
            w = Math.ceil(parseInt(s[0]) * 0.85)
            h = Math.ceil(parseInt(s[1]) * 0.7)
        }

        if (!h) {
            h = Math.ceil(s[1] * 0.83)
        } else if (parseInt(h) > parseInt(s[1])) {
            h = Math.ceil(parseInt(s[1]) * 0.9)
        }

        tb_show(caption, '#TB_inline?width=' + w + '&height=' + h)
        var TB_ajaxContent = $('#TB_ajaxContent')
        if (content) TB_ajaxContent.html(content)
        $('[name]:visible:first', TB_ajaxContent).trigger('focus')

        TB_ajaxContent.on('click', '.wpftcf-field-form-save:not([disabled])', function () {
            var has_invalids = false
            var invalids = $('[name][required],[name].wpftcf-field-invalid', TB_ajaxContent)
            if (invalids.length) {
                invalids.each(function (k, el) {
                    if ($(el).hasClass('wpftcf-field-invalid')) {
                        $(el).trigger('focus')
                        has_invalids = true
                    } else if (!$(el).val()) {
                        var wrap = $(el).closest('.wpftcf-form-field-wrap')
                        $('.wpftcf-field-form-notice', wrap).remove()
                        $(el).addClass('wpftcf-field-invalid')
                        wrap.append('<span class="wpftcf-field-form-notice">This Field is required and cannot be empty</span>')
                        $(el).trigger('focus')
                        has_invalids = true
                    }
                })
            }
            if (has_invalids) return
            $(this).attr('disabled', true)

            var field = {}
            $('[name]:not([type=radio]):not([type=checkbox])', TB_ajaxContent).each(function (k, el) {
                el = $(el)
                field[el.attr('name')] = el.val()
            })
            $('[name][type=radio]:checked', TB_ajaxContent).each(function (k, el) {
                el = $(el)
                field[el.attr('name')] = el.val()
            })
            $('[name][type=checkbox]', TB_ajaxContent).each(function (k, el) {
                el = $(el)
                var name = el.attr('name');
                var replacedName = name.replace(/\[]$/, '');
                var isArray = (name !== replacedName);
                if( field[replacedName] === undefined ) field[replacedName] = ( isArray ? [] : '' );
                if( el.is(':checked') ){
                    if( isArray ){
                        field[replacedName].push( el.val() );
                    }else{
                        field[replacedName] = el.val();
                    }
                }
            })

            if (Object.keys(field).length) {
                if (!field['fieldKey']) field['fieldKey'] = form.uniqid()

                var has_changes = true
                if (window.wpftcf[field['fieldKey']] !== undefined) {
                    has_changes = false
                    $.each(field, function (k, v) {
                        if (window.wpftcf[field['fieldKey']][k] !== undefined && ! areEquals( window.wpftcf[field['fieldKey']][k], v ) ) {
                            has_changes = true
                        }
                    })
                }

                if (has_changes) {
                    window.wpftcf[field['fieldKey']] = field
                    activate_save_button()
                }

                var fdom = $('#' + field['fieldKey'], $('.wpftcf-sortable', wpftcf_wrap))
                var f = (fdom.length ? fdom : $('#wpftcf-blank-field #blank').clone())
                f.attr('id', field['fieldKey'])
                if (field['faIcon'] !== undefined) $('.wpftcf-field-ico', f).html('<i class="' + field['faIcon'] + '"></i>')
                var name = (field['label'] !== undefined && field['label'] ? field['label'] : (field['title'] !== undefined && field['title'] ? field['title'] : field['fieldKey']))
                if (field['isOnlyForGuests'] !== undefined && parseInt(field['isOnlyForGuests'])) name += '&nbsp;|&nbsp;<span class="wpftcf-guests-only" title="This field will be shown only for guests.">Guests Only</span>'
                if (field['isRequired'] !== undefined && parseInt(field['isRequired'])) name += '&nbsp;<span class="wpftcf-required-asterisk" title="This field is required">*</span>'
                $('.wpftcf-field-label > span', f).html(name)
                if (!fdom.length) f.appendTo($('.wpftcf-fields.wpftcf-sortable'))

                tb_remove()
            }
        })

        TB_ajaxContent.on('keydown', '[type=text]', function (e) {
            if (e.keyCode === 13) {
                $('.wpftcf-field-form-save', TB_ajaxContent).trigger('click')
            }
        })

        TB_ajaxContent.on('click', '.wpftcf-add-new-buttons .wpftcf-new-field', function () {
            var type = $(this).data('type')
            var ico = $(this).data('ico')
            show_dialog(form.print({ type: type, faIcon: ico }), 'Add New Field: ' + type)
        })

        init_fa_iconpicker()
        init_validations()
    }

    wpftcf_wrap.on('click', '.wpftcf-add-new-field', function () {
        var content = ''
        var types = {
            'email': { 'ico': 'far fa-envelope', 'name': 'Email' },
            'text': { 'ico': 'fas fa-align-justify', 'name': 'Text' },
            'textarea': { 'ico': 'fas fa-align-justify', 'name': 'Textarea' },
            'tinymce': { 'ico': 'fas fa-align-justify', 'name': 'WYSIWYG' },
            'tel': { 'ico': 'fas fa-phone', 'name': 'Phone' },
            'number': { 'ico': 'fas fa-sort-numeric-down', 'name': 'Number' },
            'select': { 'ico': 'fas fa-caret-down', 'name': 'Drop Down' },
            'url': { 'ico': 'fas fa-globe', 'name': 'URL' },
            'date': { 'ico': 'fas fa-calendar-alt', 'name': 'Date' },
            'radio': { 'ico': 'far fa-dot-circle', 'name': 'Radio' },
            'checkbox': { 'ico': 'far fa-check-square', 'name': 'Checkbox' },
            'html': { 'ico': 'fas fa-code', 'name': 'HTML' },
            'file': { 'ico': 'fas fa-upload', 'name': 'File Upload' },
            'autocomplete': { 'ico': 'fa-regular fa-rectangle-list', 'name': 'Autocomplete' },
        }
        $.each(form.types, function (k) {
            var ico = (types[k] === undefined ? '' : types[k]['ico'])
            var name = (types[k] === undefined ? k : types[k]['name'])
            content += '<div class="wpftcf-new-field button button-secondary" data-type="' + k + '" data-ico="' + ico + '">' + (ico ? '<i class="' + ico + '"></i>&nbsp;' : '') + name + '</div>'
        })
        if (content) content = '<div class="wpftcf-add-new-buttons">' + content + '</div>'
        show_dialog(content, 'Add New Field', 740, 180)
    })

    wpftcf_wrap.on('click', '.wpftcf-field .wpftcf-action-edit', function () {
        var wrap = $(this).closest('.wpftcf-field')
        var fieldKey = wrap.attr('id')
        if (window.wpftcf && typeof window.wpftcf === 'object' && Object.keys(window.wpftcf).length) {
            fieldKey = fieldKey.toLowerCase()
            if (window.wpftcf[fieldKey] !== undefined) {
                show_dialog(form.print(window.wpftcf[fieldKey]), 'Edit Field: ' + window.wpftcf[fieldKey]['type'])
            }
        }
    })

    wpftcf_wrap.on('click', '.wpftcf-trash .wpftcf-action-delete', function () {
        var wrap = $(this).closest('.wpftcf-field')
        var fieldKey = wrap.attr('id')
        if (fieldKey && !wrap.hasClass('wpftcf-cant-be-removed') && confirm('You really want to permanently delete this field from the system.')) {
            if (window.wpftcf && typeof window.wpftcf === 'object' && Object.keys(window.wpftcf).length) {
                fieldKey = fieldKey.toLowerCase()
                if (window.wpftcf[fieldKey] !== undefined) {
                    delete window.wpftcf[fieldKey]
                }
            }
            if (window.wpftcf_trash && typeof window.wpftcf_trash === 'object' && Object.keys(window.wpftcf_trash).length) {
                if (window.wpftcf_trash[fieldKey] !== undefined) {
                    delete window.wpftcf_trash[fieldKey]
                }
            }
            wrap.remove()
            activate_save_button()
        }
    })

    wpftcf_wrap.on('click', '.wpftcf-fields-wrap .wpftcf-save-fields:not([disabled])', function () {
        if ($.active === 0) {
            var ico = $('.dashicons', $(this))
            var txt = $('.wpftcf-save-button-text', $(this))
            ico.toggleClass('wpftcf-loading-ico dashicons-update dashicons-editor-table')
            txt.text('Saving')

            window.wpftcf_trash = {}
            var trashed_fields = $('.wpftcf-sortable-fields.wpftcf-trash .wpftcf-field')
            if (trashed_fields.length) {
                trashed_fields.each(function (k, el) {
                    var fieldKey = $(el).attr('id')
                    if (fieldKey) {
                        if (window.wpftcf_trash[fieldKey] !== undefined) {
                            delete window.wpftcf[fieldKey]
                        } else if (window.wpftcf[fieldKey] !== undefined) {
                            window.wpftcf_trash[fieldKey] = window.wpftcf[fieldKey]
                            delete window.wpftcf[fieldKey]
                        }
                    }
                })
            }

            $.ajax({
                type: 'POST',
                url: window.wpforotcf.ajax_url,
                data: {
                    nonce: window.wpftcf_current_form['ajax_nonce'],
                    formid: parseInt(window.wpftcf_current_form['formid']),
                    fields: JSON.stringify(window.wpftcf),
                    fields_trash: JSON.stringify(window.wpftcf_trash),
                    action: 'wpforotcf_save_fields'
                }
            }).done(function () {
                ico.toggleClass('wpftcf-loading-ico dashicons-update dashicons-editor-table')
                deactivate_save_button()
            })
        }
    })

    wpftcf_wrap.on('change', 'form#wpftcf-form input[name="form[is_default]"]', function () {
        var wrap = $(this).closest('form#wpftcf-form')
        if (!!parseInt($(this).val())) {
            $('.wpftcf-form-forumids select', wrap).prop('required', false)
            $('.wpftcf-form-forumids', wrap).hide()
            $('.wpftcf-form-groupids', wrap).hide()
        } else {
            $('.wpftcf-form-forumids select', wrap).prop('required', true)
            $('.wpftcf-form-forumids', wrap).show()
            $('.wpftcf-form-groupids', wrap).show()
        }
    })

    function areEquals( var1, var2 ){
        if( Array.isArray(var1) && Array.isArray(var2) && var1.length === var2.length && var1.every( (v, k) => v == var2[k] ) ) return true;
        return var1 == var2;
    }

    var form = {
        col1: [],
        col2: [],
        reset: function () {
            this.col1 = []
            this.col2 = []
        },
        uniqid: function () {
            return 'field_' + Math.floor(Math.random() * Date.now())
        },
        fix_field: function (field) {
            var f = JSON.parse(JSON.stringify(window.wpftcf_default_field))
            if (!field) return f
            $.each(field, function (k, v) {
                f[k] = v
            })
            return f
        },
        default: function (field) {
            this.reset()
            field = form.fix_field(field)

            this.col1.push(this.fields.label(field['label']))
            this.col1.push(this.fields.placeholder(field['placeholder']))
            this.col1.push(this.fields.title(field['title']))
            this.col1.push(this.fields.description(field['description']))
            this.col1.push(this.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
            // this.col1.push( this.fields.minmax(field['minLength'], field['maxLength']) );
            this.col1.push(this.fields.faIcon(field['faIcon']))
            if (field['fieldKey'] !== 'title' && field['fieldKey'] !== 'body') {
                this.col2.push(this.fields.isRequired(field['isRequired']))
                this.col2.push(this.fields.isOnlyForGuests(field['isOnlyForGuests']))
                if (field['fieldKey'] !== 'name' && field['fieldKey'] !== 'email') this.col2.push(this.fields.isSearchable(field['isSearchable']))
            }
            if(!+field['isDefault']) this.col2.push(this.fields.printTheValue(field['printTheValue']))
            var html = ''
            html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + this.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
            if (this.col2.length) html += '<div class="wpftcf-form-col2"><div class="wpftcf-form-field-wrap">' + this.col2.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
            return html
        },
        fields: {
            fieldKey: function (value, is_readonly) {
                if (!value) value = ''
                if (!is_readonly) is_readonly = false
                return '<p class="wpftcf-field-form-input-info">Field Unique Key Name <span style="color: red;">*</span></p>' +
                    '<input type="text" name="fieldKey" placeholder="Field Unique Key Name" required ' + (is_readonly ? 'readonly' : '') + ' data-oldval="' + value + '" value="' + value + '">'
            },
            title: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">title attribute value on mouse hover</p>' +
                    '<input type="text" name="title" placeholder="Title" value="' + value + '">'
            },
            label: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">Label</p>' +
                    '<input type="text" name="label" placeholder="Label" value="' + value + '">'
            },
            placeholder: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">Placeholder</p>' +
                    '<input type="text" name="placeholder" placeholder="Placeholder" value="' + value + '">'
            },
            description: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">Description</p>' +
                    '<textarea name="description" placeholder="Description">' + value + '</textarea>'
            },
            html: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">Html</p>' +
                    '<textarea name="html" placeholder="Html" style="min-height: 370px">' + value + '</textarea>'
            },
            values: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">Options List (each line is a one option)</p>' +
                    '<textarea name="values" placeholder="Values" style="min-height: 135px" required>' + value + '</textarea>'
            },
            minLength: function (value) {
                if (!value) value = 0
                return '<p class="wpftcf-field-form-input-info">Minimum Length / Value</p>' +
                    '<input type="number" name="minLength" placeholder="Minimum Length / Value" min="0" value="' + value + '">'
            },
            maxLength: function (value) {
                if (!value) value = 0
                return '<p class="wpftcf-field-form-input-info">Maximum Length / Value</p>' +
                    '<input type="number" name="maxLength" placeholder="Maximum Length / Value" min="0" value="' + value + '">'
            },
            minmax: function (min, max) {
                if (!min) min = 0
                if (!max) max = 0
                return '<p class="wpftcf-field-form-input-info">Content Length / Value</p>' +
                    '<input style="width: 40%; padding-right: 50px;" type="number" name="minLength" placeholder="Min" min="0" value="' + min + '"><span style="margin-left: -40px; opacity: 0.6;">Min</span>&nbsp;&nbsp;&nbsp;&nbsp;' +
                    '<input style="width: 40%; padding-right: 50px; margin-left: 5%;" type="number" name="maxLength" placeholder="Max" min="0" value="' + max + '"><span style="margin-left: -40px; opacity: 0.6;">Max</span>'
            },
            fileSize: function (value) {
                if (!value) value = 1
                return '<p class="wpftcf-field-form-input-info">Upload file max size in MB</p>' +
                    '<input type="number" name="fileSize" required placeholder="Upload file max size in MB" min="1" value="' + value + '">'
            },
            fileExtensions: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">Comma separated file extensions</p>' +
                    '<input type="text" name="fileExtensions" placeholder="Comma separated file extensions" value="' + value + '">'
            },
            faIcon: function (value) {
                if (!value) value = ''
                return '<p class="wpftcf-field-form-input-info">Font Awesome Icon</p>' +
                    '<div class="wpftcf-fa-iconpicker"><input type="text" name="faIcon" placeholder="Pick an icon" autocomplete="off" value="' + value + '"><span class="wpftcf-fa-ico-preview"><i class="' + value + '"></i></span></div>'
            },
            isRequired: function (value) {
                if (!value) value = 0
                value = parseInt(value)
                return '<p class="wpftcf-field-form-input-info">Is Required?</p>' +
                    '<div class="wpf-switch-field">' +
                    '<input type="radio" value="1" name="isRequired" id="isRequired_1" ' + (value === 1 ? 'checked' : '') + '>' +
                    '<label for="isRequired_1">Yes</label>' +
                    '<input type="radio" value="0" name="isRequired" id="isRequired_0" ' + (value === 0 ? 'checked' : '') + '>' +
                    '<label for="isRequired_0">No</label>' +
                    '</div>'
            },
            isLabelFirst: function (value) {
                if (!value) value = 0
                value = parseInt(value)
                return '<p class="wpftcf-field-form-input-info">Put a label first, a input last</p>' +
                    '<div class="wpf-switch-field">' +
                    '<input type="radio" value="1" name="isLabelFirst" id="isLabelFirst_1" ' + (value === 1 ? 'checked' : '') + '>' +
                    '<label for="isLabelFirst_1">Yes</label>' +
                    '<input type="radio" value="0" name="isLabelFirst" id="isLabelFirst_0" ' + (value === 0 ? 'checked' : '') + '>' +
                    '<label for="isLabelFirst_0">No</label>' +
                    '</div>'
            },
            isMultiChoice: function (value) {
                if (!value) value = 0
                value = parseInt(value)
                return '<p class="wpftcf-field-form-input-info">Is Multichoice?</p>' +
                    '<div class="wpf-switch-field">' +
                    '<input type="radio" value="1" name="isMultiChoice" id="isMultiChoice_1" ' + (value === 1 ? 'checked' : '') + '>' +
                    '<label for="isMultiChoice_1">Yes</label>' +
                    '<input type="radio" value="0" name="isMultiChoice" id="isMultiChoice_0" ' + (value === 0 ? 'checked' : '') + '>' +
                    '<label for="isMultiChoice_0">No</label>' +
                    '</div>'
            },
            isAllowedCustomValues: function (value) {
                if (!value) value = 0
                value = parseInt(value)
                return '<p class="wpftcf-field-form-input-info">Is Allowed Custom Values?</p>' +
                    '<div class="wpf-switch-field">' +
                    '<input type="radio" value="1" name="isAllowedCustomValues" id="isAllowedCustomValues_1" ' + (value === 1 ? 'checked' : '') + '>' +
                    '<label for="isAllowedCustomValues_1">Yes</label>' +
                    '<input type="radio" value="0" name="isAllowedCustomValues" id="isAllowedCustomValues_0" ' + (value === 0 ? 'checked' : '') + '>' +
                    '<label for="isAllowedCustomValues_0">No</label>' +
                    '</div>'
            },
            isOnlyForGuests: function (value) {
                if (!value) value = 0
                value = parseInt(value)
                return '<p class="wpftcf-field-form-input-info">Is Only for Guest?</p>' +
                    '<div class="wpf-switch-field">' +
                    '<input type="radio" value="1" name="isOnlyForGuests" id="isOnlyForGuests_1" ' + (value === 1 ? 'checked' : '') + '>' +
                    '<label for="isOnlyForGuests_1">Yes</label>' +
                    '<input type="radio" value="0" name="isOnlyForGuests" id="isOnlyForGuests_0" ' + (value === 0 ? 'checked' : '') + '>' +
                    '<label for="isOnlyForGuests_0">No</label>' +
                    '</div>'
            },
            isSearchable: function (value) {
                if (!value) value = 1
                value = parseInt(value)
                return '<p class="wpftcf-field-form-input-info">Is Searchable?</p>' +
                    '<div class="wpf-switch-field">' +
                    '<input type="radio" value="1" name="isSearchable" id="isSearchable_1" ' + (value === 1 ? 'checked' : '') + '>' +
                    '<label for="isSearchable_1">Yes</label>' +
                    '<input type="radio" value="0" name="isSearchable" id="isSearchable_0" ' + (value === 0 ? 'checked' : '') + '>' +
                    '<label for="isSearchable_0">No</label>' +
                    '</div>'
            },
            UGroupIdsFrontAddNewDefaultValues: function (value) {
                if (!value) value = []
                value = [...value].map( v => +v );
                return `<p class="wpftcf-field-form-input-info">Allow usergroups to add new pre-defined values from the topic form.</p><hr>
                    ${ wpforotcf.usergroups.reduce( ( d, i ) => {
                        d += `<div style="text-align: right;"><label for="usergroupid_${i.value}">${i.label}</label><input style="width: auto; margin-left: 2px;" id="usergroupid_${i.value}" type="checkbox" name="UGroupIdsFrontAddNewDefaultValues[]" value="${i.value}" ${ ( value.includes(i.value) ? 'checked' : '' ) } /></div>`;
                        return d 
                    }, '' ) }
                `
            },
            printTheValue: function (value) {
                if (!value) value = 'after'
                return '<p class="wpftcf-field-form-input-info">Print the Value (Before or After) the Content</p>' +
                    '<div class="wpf-switch-field">' +
                    '<input type="radio" value="before" name="printTheValue" id="printTheValue_before" ' + (value === 'before' ? 'checked' : '') + '>' +
                    '<label for="printTheValue_before">Before</label>' +
                    '<input type="radio" value="after" name="printTheValue" id="printTheValue_after" ' + (value === 'after' ? 'checked' : '') + '>' +
                    '<label for="printTheValue_after">After</label>' +
                    '</div>'
            },
        },
        types: {
            text: function (field) {
                var html = '<input type="hidden" name="type" value="text">'
                html += form.default(field)
                return html
            },
            email: function (field) {
                var html = '<input type="hidden" name="type" value="email">'
                html += form.default(field)
                return html
            },
            textarea: function (field) {
                var html = '<input type="hidden" name="type" value="textarea">'
                html += form.default(field)
                return html
            },
            tinymce: function (field) {
                var html = '<input type="hidden" name="type" value="tinymce">'
                html += form.default(field)
                return html
            },
            tel: function (field) {
                var html = '<input type="hidden" name="type" value="tel">'
                html += form.default(field)
                return html
            },
            number: function (field) {
                var html = '<input type="hidden" name="type" value="number">'
                html += form.default(field)
                return html
            },
            url: function (field) {
                var html = '<input type="hidden" name="type" value="url">'
                html += form.default(field)
                return html
            },
            html: function (field) {
                var html = '<input type="hidden" name="type" value="html">'
                form.reset()
                field = form.fix_field(field)

                form.col1.push(form.fields.html(field['html']))
                form.col1.push(form.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
                form.col1.push(form.fields.isOnlyForGuests(field['isOnlyForGuests']))
                if(!+field['isDefault']) form.col1.push(form.fields.printTheValue(field['printTheValue']))
                html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + form.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                return html
            },
            file: function (field) {
                var html = '<input type="hidden" name="type" value="file">'
                form.reset()
                field = form.fix_field(field)

                form.col1.push(form.fields.label(field['label']))
                form.col1.push(form.fields.title(field['title']))
                form.col1.push(form.fields.description(field['description']))
                form.col1.push(form.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
                form.col1.push(form.fields.fileSize(field['fileSize']))
                form.col1.push(form.fields.fileExtensions(field['fileExtensions']))
                form.col2.push(form.fields.isRequired(field['isRequired']))
                form.col2.push(form.fields.isOnlyForGuests(field['isOnlyForGuests']))
                if(!+field['isDefault']) form.col2.push(form.fields.printTheValue(field['printTheValue']))
                html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + form.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                html += '<div class="wpftcf-form-col2"><div class="wpftcf-form-field-wrap">' + form.col2.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                return html
            },
            date: function (field) {
                var html = '<input type="hidden" name="type" value="date">'
                form.reset()
                field = form.fix_field(field)

                form.col1.push(form.fields.label(field['label']))
                form.col1.push(form.fields.placeholder(field['placeholder']))
                form.col1.push(form.fields.title(field['title']))
                form.col1.push(form.fields.description(field['description']))
                form.col1.push(form.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
                form.col1.push(form.fields.faIcon(field['faIcon']))
                form.col2.push(form.fields.isRequired(field['isRequired']))
                form.col2.push(form.fields.isOnlyForGuests(field['isOnlyForGuests']))
                form.col2.push(form.fields.isSearchable(field['isSearchable']))
                if(!+field['isDefault']) form.col2.push(form.fields.printTheValue(field['printTheValue']))
                html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + form.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                html += '<div class="wpftcf-form-col2"><div class="wpftcf-form-field-wrap">' + form.col2.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                return html
            },
            select: function (field) {
                var html = '<input type="hidden" name="type" value="select">'
                form.reset()
                field = form.fix_field(field)

                form.col1.push(form.fields.label(field['label']))
                form.col1.push(form.fields.title(field['title']))
                form.col1.push(form.fields.description(field['description']))
                form.col1.push(form.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
                form.col1.push(form.fields.values(field['values']))
                form.col1.push(form.fields.faIcon(field['faIcon']))
                form.col2.push(form.fields.isRequired(field['isRequired']))
                form.col2.push(form.fields.isOnlyForGuests(field['isOnlyForGuests']))
                form.col2.push(form.fields.isMultiChoice(field['isMultiChoice']))
                form.col2.push(form.fields.isSearchable(field['isSearchable']))
                if(!+field['isDefault']) form.col2.push(form.fields.printTheValue(field['printTheValue']))
                html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + form.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                html += '<div class="wpftcf-form-col2"><div class="wpftcf-form-field-wrap">' + form.col2.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                return html
            },
            radio: function (field) {
                var html = '<input type="hidden" name="type" value="radio">'
                form.reset()
                field = form.fix_field(field)

                form.col1.push(form.fields.label(field['label']))
                form.col1.push(form.fields.title(field['title']))
                form.col1.push(form.fields.description(field['description']))
                form.col1.push(form.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
                form.col1.push(form.fields.values(field['values']))
                form.col1.push(form.fields.faIcon(field['faIcon']))
                form.col2.push(form.fields.isRequired(field['isRequired']))
                form.col2.push(form.fields.isOnlyForGuests(field['isOnlyForGuests']))
                form.col2.push(form.fields.isLabelFirst(field['isLabelFirst']))
                form.col2.push(form.fields.isSearchable(field['isSearchable']))
                if(!+field['isDefault']) form.col2.push(form.fields.printTheValue(field['printTheValue']))
                html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + form.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                html += '<div class="wpftcf-form-col2"><div class="wpftcf-form-field-wrap">' + form.col2.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                return html
            },
            checkbox: function (field) {
                var html = '<input type="hidden" name="type" value="checkbox">'
                form.reset()
                field = form.fix_field(field)

                form.col1.push(form.fields.label(field['label']))
                form.col1.push(form.fields.title(field['title']))
                form.col1.push(form.fields.description(field['description']))
                form.col1.push(form.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
                form.col1.push(form.fields.values(field['values']))
                form.col1.push(form.fields.faIcon(field['faIcon']))
                form.col2.push(form.fields.isRequired(field['isRequired']))
                form.col2.push(form.fields.isOnlyForGuests(field['isOnlyForGuests']))
                form.col2.push(form.fields.isLabelFirst(field['isLabelFirst']))
                form.col2.push(form.fields.isSearchable(field['isSearchable']))
                if(!+field['isDefault']) form.col2.push(form.fields.printTheValue(field['printTheValue']))
                html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + form.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                html += '<div class="wpftcf-form-col2"><div class="wpftcf-form-field-wrap">' + form.col2.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                return html
            },
            autocomplete: function (field) {
                var html = '<input type="hidden" name="type" value="autocomplete">'
                form.reset()
                field = form.fix_field(field)

                form.col1.push(form.fields.label(field['label']))
                form.col1.push(form.fields.title(field['title']))
                form.col1.push(form.fields.description(field['description']))
                form.col1.push(form.fields.fieldKey(field['fieldKey'], parseInt(field['isDefault'])))
                form.col1.push(form.fields.values(field['values']))
                form.col1.push(form.fields.faIcon(field['faIcon']))
                form.col2.push(form.fields.isRequired(field['isRequired']))
                form.col2.push(form.fields.isOnlyForGuests(field['isOnlyForGuests']))
                form.col2.push(form.fields.isMultiChoice(field['isMultiChoice']))
                form.col2.push(form.fields.isAllowedCustomValues(field['isAllowedCustomValues']))
                form.col2.push(form.fields.isSearchable(field['isSearchable']))
                if(!+field['isDefault']) form.col2.push(form.fields.printTheValue(field['printTheValue']))
                form.col2.push(form.fields.UGroupIdsFrontAddNewDefaultValues(field['UGroupIdsFrontAddNewDefaultValues']))
                html += '<div class="wpftcf-form-col1"><div class="wpftcf-form-field-wrap">' + form.col1.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                html += '<div class="wpftcf-form-col2"><div class="wpftcf-form-field-wrap">' + form.col2.join('</div><div class="wpftcf-form-field-wrap">') + '</div></div>'
                return html
            },
        },
        print: function (field) {
            var form = ''
            if (field && typeof field === 'object' && Object.keys(field).length) {
                if (this.types[field['type']] !== undefined) {
                    form = '<div class="wpftcf-field-form">' + this.types[field['type']](field) + '</div>'
                } else {
                    form = '<div class="wpftcf-field-form">' + this.types.text(field) + '</div>'
                }
            }

            if (form) {
                form += '<input type="hidden" name="isDefault" value="' + (field['isDefault'] ? parseInt(field['isDefault']) : 0) + '">'
                // form += '<input type="hidden" name="isSearchable" value="'+ (field['isSearchable'] ? parseInt(field['isSearchable']) : 1) +'">';
                form += '<div class="wpftcf-field-form-actions"><div class="wpftcf-field-form-save button button-primary" title="Save Changes">Save</div></div>'
            }
            return form
        }
    }

})
