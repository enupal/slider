(function($)
{
	/**
	 * EnupalSlider class
	 */
	var EnupalSlider = Garnish.Base.extend({

		cssEasingOptions: null,
		easingOptions: null,
		previewModal: null,
		slider: null,
		$loadSpinner: null,
		$container: null,
		/**
		 * The constructor.
		 * @param - The easing options
		 */
		init: function(cssEasingOptions, easingOptions, enupalSlider)
		{
			this.easingOptions = easingOptions;
			this.cssEasingOptions = cssEasingOptions;
			this.addListener($("#useCss"), 'activate', 'changeOptions');
			this.slider = enupalSlider;
			console.log(this.slider);
			var that = this;
			var pagerCustom = {};
			var pager = $(this).data('enupalslider-pager-custom');
			this.$container = $('#enupalslider-preview');
			this.$loadSpinner = $('.spinner');

			if (pager)
			{
				pagerCustom = {pagerCustom: '#'+pager}
			}

			//this.slider = $("#livepreview-slider").bxSlider($.extend(pagerCustom, options));

			$('#enupalslider-preview-button').on('click', function(e) {
				e.preventDefault();

				var datastring = $("#container").serialize();
				var data = datastring;
				if (that.previewModal)
				{
					$(".bxslider").empty();
					$('#enupalslider-previewbody').addClass('enupalslider-content');
					that.slider.destroySlider();
					that.previewModal.destroy();
				}

				that.$container.removeClass('hidden');
				that.previewModal = new Garnish.Modal(that.$container, {
					resizable: true
				});
				that.$loadSpinner.removeClass('hidden');
				Craft.postActionRequest('enupalslider/sliders/live-preview', data, $.proxy(function(response)
				{
					if (response.success == true)
					{
						//REMOVE ALL THIS CODE AND ADD it to a separate tab net to slides :D
						console.log(response.slides.length);
						for (var i = response.slides.length - 1; i >= 0; i--)
						{
							$(".bxslider").append('<li><img src="'+response.slides[i].url+'" title="'+response.slides[i].title+'"></li>');
						}
						setTimeout(function() {that.reload(response.options,that.slider, that.$loadSpinner);}, 2500);
					}
				}, this));
					/*$(".bxslider").append('<li>HEEEEE2</li>');
					$(".bxslider").append('<li>HEEEEE2</li>');
					$(".bxslider").append('<li>HEEEEE2</li>');*/
					//that.slider.reloadSlider();

			});
		},

		reload: function(options, slider, $loadSpinner){
			console.log("reloading...");
			console.log(slider);
			slider.reloadSlider(options);
			$('#enupalslider-previewbody').removeClass('enupalslider-content');
			$loadSpinner.addClass('hidden');
		},

		changeOptions: function(option)
		{
			var option = option.currentTarget;
			var value = $(option).attr('aria-checked');
			var $select = $("#easing");
			$select.empty();

			if (value == 'true')
			{
				$.each(this.cssEasingOptions, function( index, value ) {
					$select.append('<option value="'+index+'">'+value+'</option>');
				});
			}
			else
			{
				$.each(this.easingOptions, function( index, value ) {
					$select.append('<option value="'+index+'">'+value+'</option>');
				});
			}
		},
	});

	window.EnupalSlider = EnupalSlider;

})(jQuery);