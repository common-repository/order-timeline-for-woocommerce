jQuery(document).ready(function()
{
    'use strict';
    
    jQuery('.jomps-icons').each(function()
    {
        var thisEl = jQuery(this);
        if ( thisEl.find('input').val() !== '' )
            thisEl.find('.jomps-icons-selector').find('li').find( '.' + thisEl.find('input').val() ).parent('li').addClass('active');
    });
    
    jQuery('.jomps-icons-selector').find('li').on('click', function()
    {
        var thisLi   = jQuery(this),
            liMain   = thisLi.parents('.jomps-icons'),
            liParent = liMain.find('.jomps-icons-selector'),
            icon     = thisLi.find('span').attr('class');

        liParent.find('li').removeClass('active');
        thisLi.addClass('active');

        liMain.find('input').val( icon );
    });
});
