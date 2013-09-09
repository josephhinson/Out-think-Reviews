// closure to avoid namespace collision
(function(){
	// creates the plugin
	tinymce.create('tinymce.plugins.otreviews', {
		// creates control instances based on the control's id.
		// our button's id is "otreviews_button"
		createControl : function(id, controlManager) {
			if (id == 'otreviews_button') {
				// creates the button
				var button = controlManager.createButton('otreviews_button', {
					title : 'Out:think Reviews Shortcode', // title of the button
					image : '../wp-content/plugins/outthink-reviews/tinymce/tinymce-icon.png',  // path to the button's image
					onclick : function() {
						// triggers the thickbox
						var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
						W = W - 80;
						H = H - 84;
						tb_show( 'Out:think Reviews Shortcode', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=otreviews-form' );
					}
				});
				return button;
			}
			return null;
		}
	});
	
	// registers the plugin. DON'T MISS THIS STEP!!!
	tinymce.PluginManager.add('otreviews', tinymce.plugins.otreviews);
	
	// executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="otreviews-form"><table id="otreviews-table" class="form-table">\
			<tr>\
				<th><label for="otreviews-number">Number</label></th>\
				<td><input type="text" name="otreviews-number" id="otreviews-number" value="" /><br />\
				<small>Number of reviews to show (Leave blank for all).</small>\
			</tr>\
			<tr>\
				<th><label for="otreviews-orderby">Order By</label></th>\
				<td><select name="orderby" id="otreviews-orderby">\
					<option value="menu_order">Menu Order</option>\
					<option value="rand">Random</option>\
					<option value="modified">Date Modified</option>\
				</select><br />\
				<small>How to order the reviews.</small></td>\
			</tr>\
			<tr>\
				<th><label for="otreviews-source">Source Slug</label></th>\
				<td><input type="text" name="source" id="otreviews-source" value="" /><br />\
					<small>Slug for the source of the review (ex: book, front-page etc)</small></td>\
			</tr>\
		</table>\
		<p class="submit">\
			<input type="button" id="otreviews-submit" class="button-primary" value="Insert Reviews" name="submit" />\
		</p>\
		<p>For more information about the reviews plugin, <a href="http://support.outthinkgroup.com/2012/02/using-the-reviews-or-testimonials-plugin/">visit the post on the Out:think Support website.</a></p>\
		</div>');
		
		var table = form.find('table');
		form.appendTo('body').hide();
		
		// handles the click event of the submit button
		form.find('#otreviews-submit').click(function(){
			// defines the options and their default values
			// again, this is not the most elegant way to do this
			// but well, this gets the job done nonetheless
			var options = { 
				'number'         : '',
				'orderby'    : 'menu_order',
				'source'    : '',

				};
			var shortcode = '[reviews';
			
			for( var index in options) {
				var value = table.find('#otreviews-' + index).val();
				
				// attaches the attribute to the shortcode only if it's different from the default value
				if ( value !== options[index] )
					shortcode += ' ' + index + '="' + value + '"';
			}
			
			shortcode += ']';
			
			// inserts the shortcode into the active editor
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
			
			// closes Thickbox
			tb_remove();
		});
	});
})()