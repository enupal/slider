(function($)
{
	/**
	 * EnupalSlider class
	 */
	var EnupalSlider = Garnish.Base.extend({

		cssEasingOptions: null,
		easingOptions: null,
		previewModal: null,
		/**
		 * The constructor.
		 * @param - The easing options
		 */
		init: function(cssEasingOptions, easingOptions)
		{
			this.easingOptions = easingOptions;
			this.cssEasingOptions = cssEasingOptions;
			this.addListener($("#useCss"), 'activate', 'changeOptions');
			var that = this;
			$('#enupalslider-preview-button').on('click', function(e) {
				e.preventDefault();

				var datastring = $("#container").serialize();
				var data = datastring;

				Craft.postActionRequest('enupalslider/sliders/live-preview', data, $.proxy(function(response)
				{
					if (response.success == true)
					{
						$('#enupalslider-previewbody').html(response.html);
					}
				}, this));

				if (!that.previewModal)
				{
					$('#enupalslider-preview').removeClass('hidden');
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