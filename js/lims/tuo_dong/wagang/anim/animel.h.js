(function() {
	var NodeW = QW.NodeW;
	if(!NodeW) return;

	var mix = QW.ObjectH.mix, 
		Dom = QW.Dom,
		HelperH = QW.HelperH, 
		applyTo = HelperH.applyTo, 
		methodizeTo = HelperH.methodizeTo;

	var AnimElH = (function(){
		var newAnim = function(el, opt, callback, dur, type) {
			var ElAnim = QW.ElAnim;
			switch(type) {
				case "color" :
					ElAnim = QW.ColorAnim;
					break;
				case "scroll" :
					ElAnim = QW.ScrollAnim;
					break;
			}
			var anim = new ElAnim(el, opt, dur||800);
			if(callback) {
				anim.on("suspend", function() {
					callback();
				});
			}
			anim.play();
			return anim;
		};

		return {
			fadeIn : function(el, dur, callback) {
				return newAnim(el, {
					"opacity" : {
						to   : 1
					}
				}, callback, dur);
			},
			fadeOut : function(el, dur, callback) {
				return newAnim(el, {
					"opacity" : {
						to   : 0
					}
				}, callback, dur);
			},
			slideUp : function(el, dur, callback) {
				return newAnim(el, {
					"height" : {
						to  : 0
					}
				}, callback, dur);
			},
			slideDown : function(el, dur, callback) {
				el = W.g(el);
				el.setStyle("height","");
				var height = parseInt(el.getCurrentStyle("height"));
				el.setStyle("height","0");
				return newAnim(el, {
					"height" : {
						from : 0,
						to : height
					}
				}, callback, dur);
			},
			shine4Error : function(el, dur, callback) {
				return newAnim(el, {
					"backgroundColor" : {
						from : "#f33",
						to	 : "#fff",
						end	 : ""
					}
				}, callback, dur, "color");
			}
		};
	})();

	NodeW.pluginHelper(AnimElH,'operator');
})();