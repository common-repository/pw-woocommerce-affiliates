jQuery(function() {
    jQuery('.pwwa-date').datepicker({
        defaultDate: '',
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: true
    });

    jQuery('.pwwa-copy-url').off('click.pimwick').on('click.pimwick', function(e) {
        var temp = jQuery('<input>');
        jQuery('body').append(temp);
        temp.val(jQuery(this).attr('href')).select();
        document.execCommand('copy');
        temp.remove();

        var message = jQuery('<div style="color: blue;">' + pwwa.i18n.linkCopied + '</div>');
        jQuery(this).append(message);
        message.fadeOut(2000);

        e.preventDefault();
        return false;
    });
});