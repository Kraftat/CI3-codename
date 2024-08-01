<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
class Popupbox extends MyController
{
    public $load;

    public function __construct()
    {
    }

    public function index()
    {
    }

    public function show($page = '', $param2 = '', $param3 = '')
    {
        if (is_loggedin()) {
            $this->data['param2'] = $param2;
            $this->data['param3'] = $param3;
            echo view(get_loggedin_user_type() . '/' . $page . '.php', $this->data);
            ?>

<script type="text/javascript">
    $(document).ready(function() {
		if ( $.isFunction($.fn[ 'datepicker' ]) ) {
			$(function() {
				$('[data-plugin-datepicker]').each(function() {
					var $this = $( this ),
						opts = {};

					var pluginOptions = $this.data('plugin-options');
					if (pluginOptions)
						opts = pluginOptions;

					$this.themePluginDatePicker(opts);
				});
			});
		}

		if ( $.isFunction( $.fn[ 'multiselect' ] ) ) {
			$(function() {
				$( '[data-plugin-multiselect]' ).each(function() {

					var $this = $( this ),
						opts = {};

					var pluginOptions = $this.data('plugin-options');
					if (pluginOptions)
						opts = pluginOptions;

					$this.themePluginMultiSelect(opts);

				});
			});
		}

		if ( $.isFunction($.fn[ 'select2' ]) ) {
			$(function() {
				$('[data-plugin-selectTwo]').each(function() {
					var $this = $( this ),
						opts = {};

					var pluginOptions = $this.data('plugin-options');
					if (pluginOptions)
						opts = pluginOptions;

					$this.themePluginSelect2(opts);
				});
			});
		}

		if ( $.isFunction($.fn[ 'timepicker' ]) ) {
			$(function() {
				$('[data-plugin-timepicker]').each(function() {
					var $this = $( this ),
						opts = {};

					var pluginOptions = $this.data('plugin-options');
					if (pluginOptions)
						opts = pluginOptions;

					$this.themePluginTimePicker(opts);
				});
			});
		}

		if($.isFunction($.fn.validate)) {
			$("form.validate").each(function(i, el)
			{
				var $this = $(el),
				opts = {
					highlight: function( label ) {
						$(label).closest('.form-group').removeClass('has-success').addClass('has-error');
					},
					success: function( label ) {
						$(label).closest('.form-group').removeClass('has-error');
						label.remove();
					},
					errorPlacement: function( error, element ) {
						var placement = element.closest('.input-group');
						if (!placement.get(0)) {
							placement = element;
						}
						if (error.text() !== '') {
							if(element.parent('.checkbox, .radio').length || element.parent('.input-group').length) {
								placement.after(error);
							} else {
								var placement = element.closest('div');
								placement.append(error);
								wrapper: "li"
							}
						}
					}
				};
				$this.validate(opts);
			});
		}
    });
</script>
<?php 
        } else {
            return redirect()->to(base_url());
        }

        return null;
    }
}
