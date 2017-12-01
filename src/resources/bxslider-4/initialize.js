jQuery(document).ready(function(){
	jQuery('.enupal-slider').each(function(){
		var pagerCustom = {};
		var pager = jQuery(this).data('enupalslider-pager-custom');

		if (pager)
		{
			pagerCustom = {pagerCustom: '#'+pager}
		}

		var options = {
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
			preventDefaultSwipeY: jQuery(this).data('enupalslider-prevent-default-swipe-y'),
			//
			pager: jQuery(this).data('enupalslider-pager'),
			pagerType: jQuery(this).data('enupalslider-pager-type'),
			pagerShortSeparator: jQuery(this).data('enupalslider-pager-short-separator'),
			pagerSelector: jQuery(this).data('enupalslider-pager-selector'),
			//
			controls: jQuery(this).data('enupalslider-controls'),
			nextText: jQuery(this).data('enupalslider-next-text'),
			prevText: jQuery(this).data('enupalslider-prev-text'),
			nextSelector: jQuery(this).data('enupalslider-next-selector'),
			prevSelector: jQuery(this).data('enupalslider-prev-selector'),
			autoControls: jQuery(this).data('enupalslider-auto-controls'),
			startText: jQuery(this).data('enupalslider-start-text'),
			stopText: jQuery(this).data('enupalslider-stop-text'),
			autoControlsCombine: jQuery(this).data('enupalslider-auto-controls-combine'),
			autoControlsSelector: jQuery(this).data('enupalslider-auto-controls-selector'),
			keyboardEnabled: jQuery(this).data('enupalslider-keyboard-enabled'),
			//Auto
			auto: jQuery(this).data('enupalslider-auto'),
			stopAutoOnClick: jQuery(this).data('enupalslider-stop-auto-on-click'),
			pause: jQuery(this).data('enupalslider-pause'),
			autoStart: jQuery(this).data('enupalslider-auto-start'),
			autoDirection: jQuery(this).data('enupalslider-auto-direction'),
			autoHover: jQuery(this).data('enupalslider-auto-hover'),
			autoDelay: jQuery(this).data('enupalslider-auto-delay'),
			//Carousel
			minSlides: jQuery(this).data('enupalslider-min-slides'),
			maxSlides: jQuery(this).data('enupalslider-max-slides'),
			moveSlides: jQuery(this).data('enupalslider-move-slides'),
			slideWidth: jQuery(this).data('enupalslider-slide-width'),
			shrinkItems: jQuery(this).data('enupalslider-shrink-items'),
			wrapperClass: jQuery(this).data('enupalslider-wrapper-class')
		};
		jQuery(this).bxSlider(jQuery.extend(pagerCustom, options));
	});
});