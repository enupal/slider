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
			var that = this;
			var pagerCustom = {};
			var pager = $(this).data('enupalslider-pager-custom');

			if (pager)
			{
				pagerCustom = {pagerCustom: '#'+pager}
			}

			//this.slider = $("#livepreview-slider").bxSlider($.extend(pagerCustom, options));

			$('#enupalslider-preview-button').on('click', function(e) {
				e.preventDefault();

				var datastring = $("#container").serialize();
				var data = datastring;

				if (!that.previewModal)
				{
					$('#enupalslider-preview').removeClass('hidden');
					Craft.postActionRequest('enupalslider/sliders/live-preview', data, $.proxy(function(response)
					{
						if (response.success == true)
						{
							//REMOVE ALL THIS CODE AND ADD it to a separate tab net to slides :D
							console.log(response.slides.length);
							for (var i = response.slides.length - 1; i >= 0; i--)
							{
								$(".bxslider").append('<li><img src="/enupalslider/slider1/Copia-de-Week-32-6.png" title="Copia-De-Week-32-6"></li>');
							}
							that.slider.reloadSlider();
						}
					}, this));
					/*$(".bxslider").append('<li>HEEEEE2</li>');
					$(".bxslider").append('<li>HEEEEE2</li>');
					$(".bxslider").append('<li>HEEEEE2</li>');*/
					//that.slider.reloadSlider();
					that.previewModal = new Garnish.Modal($('#enupalslider-preview'), {
						resizable: true
					});
				}
				else
				{
					that.previewModal.show();
				}

			});
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