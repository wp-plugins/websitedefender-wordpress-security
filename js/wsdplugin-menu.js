
//!# update menu item
(function($){
    var e = $('#adminmenu');
    if(e.length>0){
        var li = $('li.toplevel_page_wsdplugin_dashboard ul.wp-submenu li:nth-child(3)', e);
        if(li.length>0){li.find('a').html('Sign Up');}
    }
})(jQuery);
