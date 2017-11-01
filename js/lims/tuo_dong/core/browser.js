/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	version: $version$ $release$ released
	author: yingjiakuan@baidu.com
*/


/**
 * @class Browser js�����л�����������Լ��汾��Ϣ����Browser������userAgent������̽�����ڲ��Ͻ���ȱ�ݡ���
 * @singleton 
 * @namespace QW 
 */
QW.Browser=function(){
	var na = window.navigator,
		ua = na.userAgent.toLowerCase(),
		browserTester = /(msie|webkit|gecko|presto|opera|safari|firefox|chrome|maxthon)[ \/]([\d.]+)/ig,
		Browser = {platform: na.platform};
	ua.replace(browserTester,function(a,b,c){
		var bLower=b.toLowerCase();
		Browser[bLower]=c;
	});
	if(Browser.opera) {//Opera9.8��汾��λ�ñ仯
		ua.replace(/opera.*version\/([\d.]+)/, function(a,b){Browser.opera=b;});
	}
	if(Browser.msie){
		Browser.ie = Browser.msie;
		var v = parseInt(Browser.msie);
		Browser.ie6 = v==6;
		Browser.ie7 = v==7;
		Browser.ie8 = v==8;
		Browser.ie9 = v==9;
	}
	return Browser;
}();
if(QW.Browser.ie){try{document.execCommand("BackgroundImageCache",false,true);}catch(e){}}
