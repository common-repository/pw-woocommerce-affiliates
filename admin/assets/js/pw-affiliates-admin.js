jQuery(function() {
    jQuery('.pwwa-date').datepicker({
        defaultDate: '',
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: true
    });

    jQuery('#pwwa-affiliates-report-form').on('submit', function(e) {
        pwwaAdminAffiliatesReport();

        e.preventDefault();
        return false;
    });

    jQuery('#pwwa-create-affiliate-form').on('submit', function(e) {
        pwwaAdminCreateAffiliate();

        e.preventDefault();
        return false;
    });

    jQuery('#pwwa-save-commissions-form').on('submit', function(e) {
        pwwaAdminSaveCommissions();

        e.preventDefault();
        return false;
    });

    jQuery('#pwwa-edit-affiliate-form').on('submit', function(e) {
        pwwaAdminEditAffiliate();

        e.preventDefault();
        return false;
    });

    jQuery('#pwwa-save-settings-form').on('submit', function(e) {
        pwwaAdminSaveSettings();

        e.preventDefault();
        return false;
    });

    jQuery('.pwwa-section-item').click(function(e) {
        jQuery('.pwwa-section-item').removeClass('pwwa-section-item-selected');
        jQuery(this).addClass('pwwa-section-item-selected');
        var section = jQuery(this).attr('data-section');
        jQuery('.pwwa-section').hide();
        jQuery('#pwwa-section-' + section).show();
    });

    jQuery('#pwwa-create-code-automatically').on('change', function() {
        jQuery('#pwwa-create-manual-code-container').toggle(jQuery(this).checked);
    });

    jQuery('.pwwa-edit-affiliate-cancel').off('click.pimwick').on('click.pimwick', function(e) {
        pwwaHideEditAffiliateDialog();

        e.preventDefault();
        return false;
    });

    jQuery('.pwwa-edit-affiliate-delete').off('click.pimwick').on('click.pimwick', function(e) {
        pwwaDeleteAffiliate();

        e.preventDefault();
        return false;
    });

    pwwaAdminAffiliatesReport();

    jQuery('.pwwa-alphanumeric').bind('keyup blur',function() {
        var node = jQuery(this);
        node.val(node.val().replace(/[^a-zA-Z0-9\-_]/g, ''));
    });
});

function pwwaAdminSaveSettings() {
    var messageContainer = jQuery('#pwwa-save-settings-message');
    var saveButton = jQuery('#pwwa-save-settings-button');
    var form = jQuery('#pwwa-save-settings-form').serialize();

    saveButton.prop('disabled', true);
    messageContainer.html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'pwwa-save-settings', 'form': form, 'security': pwwa.nonces.saveSettings }, function(result) {
        pwwaAdminAffiliatesReport();
        saveButton.prop('disabled', false);
        messageContainer.html(result.data.html);
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.prop('disabled', false);
        if (errorThrown) {
            messageContainer.html(errorThrown);
        } else {
            messageContainer.text('Unknown ajax error');
        }
    });
}

function pwwaAdminAffiliatesReport() {
    var form = jQuery('#pwwa-affiliates-report-form');
    var reports = jQuery('#pwwa-affiliates-report-results');
    reports.html('<div class="pwwa-admin-top-text">' + pwwa.i18n.loading + '</div><i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'pwwa-affiliates-report', 'form': form.serialize(), 'security': pwwa.nonces.affiliatesReport}, function(result) {
        reports.html(result.data.html);

        pwwaAdminBindSortableColumns(form, '#pwwa-affiliates-table');
        pwwaAdminBindExportButton();

        jQuery('.pwwa-view-orders').off('click.pimwick').on('click.pimwick', function(e) {
            var affiliate = jQuery(this).closest('tr').attr('data-code');
            var ordersUrl = pwwa.ordersUrl + '&pw_affiliate=' + affiliate;

            var win = window.open(ordersUrl, '_blank');
            win.focus();

            e.preventDefault();
            return false;
        });

        jQuery('.pwwa-copy-url').off('hover.pimwick').on('hover.pimwick', function(e) {
            var icon = jQuery(this).closest('td').find('.fa-copy');
            if (icon.css('visibility') == 'visible') {
                icon.css('visibility', 'hidden');
            } else {
                icon.css('visibility', 'visible');
            }
        });

        jQuery('.pwwa-copy-url').off('click.pimwick').on('click.pimwick', function(e) {
            var temp = jQuery('<input>');
            jQuery('body').append(temp);
            temp.val(jQuery(this).attr('href')).select();
            document.execCommand('copy');
            temp.remove();

            var message = jQuery('<div style="color: blue;">' + pwwa.i18n.linkCopied + '</div>');
            jQuery(this).closest('td').append(message);
            message.fadeOut(2000);

            e.preventDefault();
            return false;
        });

        jQuery('.pwwa-edit-affiliate').off('click.pimwick').on('click.pimwick', function(e) {
            var id = jQuery(this).closest('tr').attr('data-id');
            var code = jQuery(this).closest('tr').attr('data-code');
            var name = jQuery(this).closest('tr').attr('data-name');
            var userId = jQuery(this).closest('tr').attr('data-user_id');
            var commission = jQuery(this).closest('tr').attr('data-commission');

            pwwaAdminShowEditAffiliateDialog(id, code, name, userId, commission);

            e.preventDefault();
            return false;
        });

    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            reports.text(errorThrown);
        } else {
            reports.text('Unknown error');
        }
    });
}

function pwwaAdminBindExportButton() {
    jQuery('.pwwa-export-button').off('click.pimwick').on('click.pimwick', function(e) {
        var exportButton = jQuery(this);
        var waitMessage = '<div class="pwwa-admin-exporting-message"><i class="fas fa-cog fa-spin"></i> ' + pwwa.i18n.exporting + '</div>';

        exportButton.after(waitMessage);
        exportButton.prop('disabled', true);

        var formId = '#pwwa-affiliates-report-form';
        if (exportButton.attr('id') == 'pwwa-products-report-export-button') {
            formId = '#pwwa-products-report-form';
        }
        var form = jQuery(formId).serialize();

        jQuery.post(ajaxurl, {'action': 'pwwa-export-report', 'form': form, 'security': pwwa.nonces.exportReport}, function(result) {
            var url = pwwa.exportUrl + '&action=pwwa_export&report_type=' + result.data.report_type + '&filename=' + result.data.output_filename;
            window.open( url, '_self');

            exportButton.prop('disabled', false);
            jQuery('.pwwa-admin-exporting-message').remove();

        }).fail(function(xhr, textStatus, errorThrown) {
            exportButton.prop('disabled', false);
            jQuery('.pwwa-admin-exporting-message').remove();

            if (errorThrown) {
                alert(errorThrown);
            } else {
                alert('Unknown error');
            }
        });

        e.preventDefault();
        return false;
    });
}

function pwwaAdminBindSortableColumns(form, table) {
    jQuery(table).find('.pwwa-admin-table-sortable-column').off('click.pimwick').on('click.pimwick', function(e) {
        var column = jQuery(this).attr('data-column');
        var order = 'asc';

        if (jQuery(this).find('.pwwa-sort').hasClass('fa-sort-down')) {
            order = 'desc';
        }

        jQuery(form).find('[name="sort"]').val(column);
        jQuery(form).find('[name="sort_order"]').val(order);

        jQuery(form).submit();
    });

    jQuery('.pwwa-admin-table-sortable-column').off('hover.pimwick').on('hover.pimwick', function(e) {
        var sortIcon = jQuery(this).find('.pwwa-sort');

        if (sortIcon.hasClass('pwwa-invisible')) {
            if ( sortIcon.css('visibility') == 'hidden' ) {
                sortIcon.css('visibility', 'visible');
            } else {
                sortIcon.css('visibility', 'hidden');
            }
        }

        if (sortIcon.hasClass('fa-sort-down')) {
            sortIcon.removeClass('fa-sort-down').addClass('fa-sort-up');
        } else {
            sortIcon.removeClass('fa-sort-up').addClass('fa-sort-down');
        }
    });
}

function pwwaAdminCreateAffiliate() {
    var form = jQuery('#pwwa-create-affiliate-form');
    var messageContainer = form.find('.pwwa-admin-message');
    var saveButton = jQuery('#pwwa-create-affiliate-save-button');

    saveButton.hide();
    messageContainer.removeClass('pwwa-admin-error').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'pwwa-create-affiliate', 'form': form.serialize(), 'security': pwwa.nonces.createAffiliate}, function(result) {
        if (result.success) {
            messageContainer.text(result.data.message);
            setTimeout(function() {
                messageContainer.text('');
            }, 5000);
            form[0].reset();
            jQuery('#pwwa-create-manual-code-container').hide();
            jQuery('#pwwa-affiliates-report-form').submit();
            saveButton.show();
        } else {
            messageContainer.addClass('pwwa-admin-error').text(result.data.message);
            saveButton.show();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.show();
        if (errorThrown) {
            messageContainer.addClass('pwwa-admin-error').text(errorThrown);
        } else {
            messageContainer.addClass('pwwa-admin-error').text('Unknown Error');
        }
    });
}

function pwwaAdminEditAffiliate() {
    var form = jQuery('#pwwa-edit-affiliate-form');
    var messageContainer = form.find('.pwwa-admin-message');
    var saveButton = jQuery('#pwwa-edit-affiliate-save-button');

    saveButton.hide();
    messageContainer.removeClass('pwwa-admin-error').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'pwwa-edit-affiliate', 'form': form.serialize(), 'security': pwwa.nonces.editAffiliate}, function(result) {
        if (result.success) {
            messageContainer.text(result.data.message);
            jQuery('#pwwa-affiliates-report-form').submit();
            saveButton.show();
            pwwaHideEditAffiliateDialog();
        } else {
            messageContainer.addClass('pwwa-admin-error').text(result.data.message);
            saveButton.show();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.show();
        if (errorThrown) {
            messageContainer.addClass('pwwa-admin-error').text(errorThrown);
        } else {
            messageContainer.addClass('pwwa-admin-error').text('Unknown Error');
        }
    });
}

function pwwaDeleteAffiliate() {
    if (confirm(pwwa.i18n.confirmDelete)) {
        var affiliateId = jQuery('#pwwa-edit-affiliate-id').val();

        jQuery.post(ajaxurl, {'action': 'pwwa-delete-affiliate', 'affiliate_id': affiliateId, 'security': pwwa.nonces.deleteAffiliate}, function(result) {
            if (result.success) {
                jQuery('#pwwa-affiliates-report-form').submit();
                pwwaHideEditAffiliateDialog();
            } else {
                alert(result.data.message);
            }
        }).fail(function(xhr, textStatus, errorThrown) {
            if (errorThrown) {
                alert(errorThrown);
            } else {
                alert('Unknown Error');
            }
        });
    }
}

function pwwaAdminShowEditAffiliateDialog(id, code, name, userId, commission) {
    var form = jQuery('#pwwa-edit-affiliate-form');
    var messageContainer = form.find('.pwwa-admin-message');
    var section = jQuery('#pwwa-section-affiliates-report');

    messageContainer.text('');

    var overlay = jQuery('<div id="pwwa-overlay">');
    jQuery('body').append(overlay);
    overlay.css('top', jQuery('#wpadminbar').height() + 'px');
    overlay.css('left', jQuery('#adminmenuwrap').width() + 'px');

    var dialog = jQuery('#pwwa-edit-affiliate-container');
    dialog.css('position', 'fixed');
    dialog.css('display', 'inline');
    dialog.css('height', '');
    dialog.css('width', '');
    dialog.css('left', 400); // dialog.css('left', section.position().left + (section.width() / 2) - dialog.width());
    dialog.css('top', 200); // dialog.css('top', section.position().top);

    if (!userId) {
        userId = -1;
    }

    jQuery('#pwwa-edit-affiliate-id').val(id);
    jQuery('#pwwa-edit-code').val(code);
    jQuery('#pwwa-edit-name').val(name);
    jQuery('#pwwa-edit-user_id').val(userId);
    jQuery('#pwwa-edit-commission').val(commission);

    overlay.css('display', 'block');
    dialog.css('display', 'block');
}

function pwwaHideEditAffiliateDialog() {
    var form = jQuery('#pwwa-edit-affiliate-form');
    form[0].reset();

    jQuery('#pwwa-edit-affiliate-container').hide();
    jQuery('#pwwa-overlay').remove();
}

function pwwaAdminSaveCommissions() {
    var form = jQuery('#pwwa-save-commissions-form');
    var messageContainer = form.find('#pwwa-save-commissions-message');
    var saveButton = jQuery('#pwwa-save-commissions-button');

    saveButton.hide();
    messageContainer.removeClass('pwwa-admin-error').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'pwwa-save-commissions', 'form': form.serialize(), 'security': pwwa.nonces.saveCommissions}, function(result) {
        if (result.success) {
            messageContainer.text(result.data.message);
            setTimeout(function() {
                messageContainer.text('');
            }, 5000);
            form[0].reset();
            saveButton.show();
        } else {
            messageContainer.addClass('pwwa-admin-error').text(result.data.message);
            saveButton.show();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.show();
        if (errorThrown) {
            messageContainer.addClass('pwwa-admin-error').text(errorThrown);
        } else {
            messageContainer.addClass('pwwa-admin-error').text('Unknown Error');
        }
    });
}
