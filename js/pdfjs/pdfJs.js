if (typeof jQuery === "undefined") { throw new Error("Bootstrap requires jQuery") }
"use strict";
$.fn.extend({
	pdfView: function(url){
		var pdfDoc = null,
			that = this,
			scale = 2,
			canvas = null,
			ctx = null,
			iUrl = url;
		function renderPage(num,_canvas){
			pdfDoc.getPage(num).then(function(page){
				if( typeof _canvas != "undefined"){
					canvas = _canvas;
					ctx = canvas.getContext('2d');
				}
				var viewport = page.getViewport(scale);
				canvas.width = viewport.width;
				canvas.height = viewport.height;
				if( canvas.width > canvas.height ){
					$(that).find(".canvas-" + num ).css({
						width: "1088px",
						height: "760px"
					});
					$(that).find(".page").addClass("A4_Horizontal");
				}else{
					$(that).find(".canvas-" + num ).css({
						width: "760px",
						height: "1088px"
					});
					$(that).find(".page").addClass("A4_Vertical");
				}
				page.render({
					canvasContext: ctx,
					viewport: viewport
				});
			});
		}
		PDFJS.getDocument(iUrl).then(function (pdfDoc_) {
			pdfDoc = pdfDoc_;
			var index = $(that).find(".pdfView").length;
			$(that).append('<div class="pdfView-'+index+' pdfView"></div>');
			pdfDoc.pdfView = $(that).find(".pdfView-"+index);
			for (var pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
				pdfDoc.pdfView.append('<div class="page"></div>');
				var _canvas = document.createElement('canvas');
				_canvas.setAttribute("class","canvas-" + pageNum + " the-canvas");
				pdfDoc.pdfView.find(".page:eq("+(pageNum-1)+")").append(_canvas).append('<div class="shadow"></div>');
				renderPage(pageNum,_canvas);
			};
		});
		return{
			destroy:function(){
				$(that).empty();
				pdfDoc.destroy();
			}
		}
	}
});