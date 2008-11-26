/*-------------------------------------------------------------------- 
Scripts for positioning overlaid components: menus, tooltips, overlays, loading indicators, etc.
Version: 1.0, 01.18.2008

By: Maggie Costello Wachs (maggie@filamentgroup.com)
	http://www.filamentgroup.com
		
Copyright (c) 2007 Filament Group
Licensed under GPL (http://www.opensource.org/licenses/gpl-license.php)

Dependencies:
	jQuery library
	utilities.js
	
Requirements:
	- the object is absolutely positioned with CSS
	
Parameters (defaults noted with * where applicable):
	@referrer = the link (or other element) used to show the overlaid object 
	@settings = can override four of the Position defaults:
		- posX/Y: where the top left corner of the object should be positioned in relation to its referrer.
				X: left*, center, right
				Y: top, center, bottom*
		- offsetX/Y: the number of pixels to be offset from the x or y position.  Can be a positive or negative number.
		- directionH/V: where the entire menu should appear in relation to its referrer.
				Horizontal: left*, right
				Vertical: up, down*
		- detectH/V: detect the viewport horizontally / vertically
		- linkToFront: copy the menu link and place it on top of the menu (visual effect)
--------------------------------------------------------------------*/

jQuery.fn.positionObject = function(referrer, settings) { 
	var settings = jQuery.extend({
		posX: 'left', 
		posY: 'bottom',
		offsetX: 0,
		offsetY: 0,
		directionH: 'right',
		directionV: 'down', 
		detectH: true, // do horizontal collision detection  
		detectV: true, // do vertical collision detection
		linkToFront: false
	}, settings);

	var el = $(this);
	var referrer = referrer;
	var dims = {
		elW: el.width(),
		elH: el.height(),
		refX: referrer.offset().left,
		refY: referrer.offset().top,
		refW: referrer.getTotalWidth(),
		refH: referrer.getTotalHeight()
	};	
	var xVal, yVal;
	
	el.insertPositionHelper(dims);
	
	// get X pos
	switch(settings.posX) {
		case 'left': 	xVal = 0; 
			break;				
		case 'center': xVal = dims.refW / 2;
			break;				
		case 'right': xVal = dims.refW;
			break;
	};
	
	// get Y pos
	switch(settings.posY) {
		case 'top': 	yVal = 0;
			break;				
		case 'center': yVal = dims.refH / 2;
			break;				
		case 'bottom': yVal = dims.refH;
			break;
	};
	
	// add the offsets (zero by default)
	xVal += settings.offsetX;
	yVal += settings.offsetY;
	
	// position the object vertically
	if (settings.directionV == 'up') {
		el.css({ top: 'auto', bottom: yVal });
		if (settings.detectV && !fitVertical(el)) {
			el.css({ bottom: 'auto', top: yVal });
		}
	} 
	else {
		el.css({ bottom: 'auto', top: yVal });
		if (settings.detectV && !fitVertical(el)) {
			el.css({ top: 'auto', bottom: yVal });
		}
	};
	
	// and horizontally
	if (settings.directionH == 'left') {
		el.css({ left: 'auto', right: xVal });
		if (settings.detectH && !fitHorizontal(el)) {
			el.css({ right: 'auto', left: xVal });
		}
	} 
	else {
		el.css({ right: 'auto', left: xVal });
		if (settings.detectH && !fitHorizontal(el)) {
			el.css({ left: 'auto', right: xVal });
		}
	};
	
	// if specified, clone the referring element and position it so that it appears on top of the menu
	if (settings.linkToFront) {
		referrer.clone().addClass('linkClone').css({
			position: 'absolute', 
			top: 0, 
			right: 'auto', 
			bottom: 'auto', 
			left: 0, 
			width: referrer.width(), 
			height: referrer.height()
		}).insertAfter(el);
	};

	return $(this);
};

jQuery.fn.insertPositionHelper = function(dims) {
	var el = $(this);
	var dims = dims;
	var helper = $('<div class="positionHelper"></div>');
	helper.css({ left: dims.refX, top: dims.refY, width: dims.refW, height: dims.refH });
	el.wrap(helper);
	return $(this);
};