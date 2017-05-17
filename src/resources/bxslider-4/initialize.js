jQuery(document).ready(function(){

    jQuery('.enupal-slider').each(function(){
        jQuery(this).bxSlider({
            mode: jQuery(this).data('enupalslider-mode'),
            speed: jQuery(this).data('enupalslider-speed'),
            slideMargin: jQuery(this).data('enupalslider-slide-margin'),
            startSlide: jQuery(this).data('enupalslider-start-slide'),
            randomStart: jQuery(this).data('enupalslider-random-start'),
            slideSelector: jQuery(this).data('enupalslider-slide-selector'),
            infiniteLoop: jQuery(this).data('enupalslider-infinite-loop'),
            hideControlOnEnd: jQuery(this).data('enupalslider-hide-control-on-end'),
            captions: jQuery(this).data('enupalslider-captions'),
            ticker: jQuery(this).data('enupalslider-ticker'),
            tickerHover: jQuery(this).data('enupalslider-ticker-hover'),
            adaptiveHeight: jQuery(this).data('enupalslider-adaptive-height'),
            adaptiveHeightSpeed: jQuery(this).data('enupalslider-adaptive-height-speed'),
            video: jQuery(this).data('enupalslider-video'),
            responsive: jQuery(this).data('enupalslider-responsive'),
            useCSS: jQuery(this).data('enupalslider-use-css'),
            easing: jQuery(this).data('enupalslider-easing'),
            preloadImages: jQuery(this).data('enupalslider-preload-images'),
            touchEnabled: jQuery(this).data('enupalslider-touch-enabled'),
            swipeThreshold: jQuery(this).data('enupalslider-swipe-threshold'),
            oneToOneTouch: jQuery(this).data('enupalslider-one-to-one-touch'),
            preventDefaultSwipeX: jQuery(this).data('enupalslider-prevent-default-swipe-x'),
            preventDefaultSwipeY: jQuery(this).data('enupalslider-prevent-default-swipe-y')
        });
    });
});