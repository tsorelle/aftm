(function(global, $) {
    var originalNav = $('.ccm-responsive-navigation');
    /*
    if(!($('#aftm-mobile-menu').length)) {
        $('body').append('<div class="ccm-responsive-overlay"></div>');
    }
    */
    var clonedNavigation = originalNav.clone();
    $(clonedNavigation).removeClass('original');
    $('#aftm-mobile-menu').append(clonedNavigation);
    $('.ccm-responsive-menu-launch').click(function(){
        $('.ccm-responsive-menu-launch').toggleClass('responsive-button-close');   // slide out mobile nav
        $('#aftm-mobile-menu').toggle();
    });
    $('#aftm-mobile-menu ul li').children('ul').hide();
    $('#aftm-mobile-menu li').each(function(index) {
        if($(this).children('ul').size() > 0) {
            $(this).addClass('parent-ul');
        } else {
            $(this).addClass('last-li');
        }
    });
    $('#aftm-mobile-menu .parent-ul a').click(function(event) {
        if(!($(this).parent('li').hasClass('last-li'))) {
            $(this).parent('li').siblings().children('ul').hide();
            if($(this).parent('li').children('ul').is(':visible')) {
            } else {
                $(this).next('ul').show();
                event.preventDefault();
            }
        }
    });
})(window, $);