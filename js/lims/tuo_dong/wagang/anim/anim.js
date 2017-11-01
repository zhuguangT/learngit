(function() {
	var els=document.getElementsByTagName('script'), srcPath = '';
	for (var i = 0; i < els.length; i++) {
		var src = els[i].src.split(/wagang[\\\/]/g);
		if (src[1]) {
			srcPath = src[0];
			break;
		}
	}

	document.write(
		  '<script type="text/javascript" src="'+srcPath+'wagang/anim/anim_base.js"></script>'
		, '<script type="text/javascript" src="'+srcPath+'wagang/anim/elanim.js"></script>'
		, '<script type="text/javascript" src="'+srcPath+'wagang/anim/easing.js"></script>'
		, '<script type="text/javascript" src="'+srcPath+'wagang/anim/animel.h.js"></script>'
	);
})();