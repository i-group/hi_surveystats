/**
 * jPagination - jQuery plugin to navigate through a list of elements  
 * @requires jQuery v1.1.3.1 or above
 *
 * http://www.rueprich.de/jquery/jpagination/
 *
 * Copyright (c) 2007 Holger Rüprich (www.rueprich.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Version: 1.0
 */

/**
 * Creates a carousel-style navigation widget for images/any-content from a simple HTML markup.
 *
 * The HTML markup that is used to build the pagination-list can be as simple as...
 *
 *  <ul class="pagination">
 *      <li>1</li>
 *      <li>2</li>
 *      <li>3</li>
 *  </ul>
 *
 * To navigate the elements of the pagination-list, you can use two buttons ("previous" and "next").
 * If you like to display on which elements are currently shown you can use the labelField.
 *
 * $(".pagination").after(
 *     '<div class="pagination_nav">' +
 *         ' <span class="prev"><< previous</span> ' + 
 *         ' <span class="label"></span> ' +
 *         ' <span class="next">next >></span> ' +
 *     '</div>'
 * );
 *
 * $(".pagination").jPagination({
 *      btnNext: ".next",
 *      btnPrev: ".prev",
 *      labelField: ".label"
 * });
 *
 * That's it.
 *
 * There are quite a few other options that you can use to customize it though.
 * Each will be explained with an example below.
 *
 * @param an options object - You can specify all the options shown below as an options object param.
 *
 * @option btnPrev, btnNext : string 	- default is null
 * @desc Path to the previous and next buttons. Clicking either one navigates through the elements.
 *
 * @option labelField 		: string 	- default is null
 * @desc The labelField is used to show which elmenents of how many are shown
 *
 * @option counter 			: int 		- default is 1
 * @desc Defines the starting element
 *
 * @option visibleElements 	: int 		- default is 5
 * @desc How many elements should be visible at once
 *
 * @option listElements 	: string 	- default is 'li'
 * @desc Type of the listed elements
 *
 * @option textOf 			: string 	- default is 'von'
 * @desc Text value for 'of' in "Results 1-10 of 100"
 *
 * @option textResults 		: string 	- default is 'Ergebnisse'
 * @desc Text value for 'Results' in "Results 1-10 of 100"
 *
 *
 * @cat Plugins/UserInterface Pagination
 * @author Holger Rüprich/holger@rueprich.de
 */

(function($) {  // Compliant with jquery.noConflict()
	jQuery.fn.jPagination = function(options)
	{
		options 				= options || {};
		options.counter 		= options.counter || 1;
		options.visibleElements = options.visibleElements || 5;
		options.listElements	= options.listElements || 'li';
		options.btnNext			= options.btnNext || null;
		options.btnPrev			= options.btnPrev || null;
		options.labelField		= options.labelField || null;
		options.textOf			= options.textOf || 'von';
		options.textResults		= options.textResults || 'Ergebnisse';
		
		// Returns the element collection. Chainable.
		return this.each(function() {
			var input = this;
			new jQuery.jPagination(input, options);
		});
	}
	
	jQuery.jPagination = function(input, options)
	{
		var self 		= $(input);
		var current 	= options.counter;
		var btnNext 	= (options.btnNext) ? $(options.btnNext) : null;
		var btnPrev 	= (options.btnPrev) ? $(options.btnPrev) : null;
		var labelField 	= (options.labelField) ? $(options.labelField) : null;
		
		if (btnNext) {			
			btnNext.click(function() { return go(current + options.visibleElements); });
		}
		
		if (btnPrev) {
			btnPrev.click(function() { return go(current - options.visibleElements); });
		}
		
		function go(start)
		{	
			current 	= start;
			childCount 	= self.children(options.listElements).size();
			lastElement = current+options.visibleElements-1;
			
			if (lastElement >= childCount) {
				lastElement = childCount;
				if (btnNext) {
					btnNext.hide();
				}
			} else if (btnNext) {
				btnNext.show();
			}
			
			if (btnPrev && (current-options.visibleElements) < 1) {
				btnPrev.hide();
			} else {
				btnPrev.show();
			}
					
			i = 1;
			self.children(options.listElements).each(function() {
				if (i < current || i > lastElement) {
					$(this).hide();
				} else {
					$(this).show();
				}
				i++;
			});
			
			
			if (labelField) {
				labelField.html(options.textResults + ' <strong>' + current + ' - ' + lastElement + '</strong> ' + options.textOf + ' <strong>' + childCount + '</strong>');
			}
		}
		
		go(current);
	}
})(jQuery);