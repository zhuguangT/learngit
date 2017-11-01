/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	version: $version$ $release$ released
	author: wuliang@baidu.com
*/


/**
 * @singleton 
 * @class QW QW��QWrap��Ĭ�������еĺ���Class��Ӧ������QW������
 */
(function(){
var _previousQW=window.QW;

var QW = {
	/**
	 * @property {string} VERSION �ű���İ汾��
	 * @default $version$
	 */
	VERSION: "$version$",
	/**
	 * @property {string} RELEASE �ű���ķ����ţ�С�汾��
	 * @default $release$
	 */
	RELEASE: "$release$",
	/**
	 * @property {string} PATH �ű��������·��
	 * @type string
	 */
	PATH: (function(){
		var sTags=document.getElementsByTagName("script");
		return  sTags[sTags.length-1].src.replace(/\/[^\/]+\/[^\/]+$/,"/");
	})(),
	/**
	 * ���һ�������ռ�
	 * @method namespace
	 * @static
	 * @param { String } sSpace �����ռ����������������ռ䲻���ڣ����Զ�������
	 * @param { Object } root (Optional) �����ռ����㡣��û��rootʱ�����sSpace�ԡ�.����ͷ������Ĭ��ΪQWΪ��������Ĭ��Ϊwindow��
	 * @return {any} ���������ռ��Ӧ�Ķ��� 
	 */		
	namespace: function(sSpace,root) {
		var arr=sSpace.split('.'),
			i=0;
		if(sSpace.indexOf('.')==0){
			i=1;
			root=root||QW;
		}
		root=root||window;
		for(;i<arr.length;i++){
			root=root[arr[i]] || (root[arr[i]]={});
		}
		return root;
	},
	
	/**
	 * QW�޳�ͻ������ԭ���ܱ����õ�window.QW����
	 * @method noConflict
	 * @static
	 * @return {json} ����QW�������ռ� 
	 */		
	noConflict: function() {
		window.QW=_previousQW;
		return QW;
	},
	/**
	 * �첽���ؽű�
	 * @method loadJs
	 * @static
	 * @param { String } url Javascript�ļ�·��
	 * @param { Function } onsuccess (Optional) Javascript���غ�Ļص�����
	 * @param { Option } options (Optional) ����ѡ�����charset
	 */
	loadJs: function(url,onsuccess,options){
		options = options || {};
		var head = document.getElementsByTagName('head')[0] || document.documentElement,
			script = document.createElement('script'),
			done = false;
		script.src = url;
		if( options.charset )
			script.charset = options.charset;
		script.onerror = script.onload = script.onreadystatechange = function(){
			if ( !done && (!this.readyState ||
					this.readyState == "loaded" || this.readyState == "complete") ) {
				done = true;
				onsuccess && onsuccess();
				script.onerror = script.onload = script.onreadystatechange = null;
				head.removeChild( script );
			}
		};
		head.insertBefore( script, head.firstChild );
	},
	/**
	 * �׳��쳣
	 * @method error
	 * @static
	 * @param { obj } �쳣����
	 * @param { type } Error (Optional) �������ͣ�Ĭ��ΪError
	 */
	error: function(obj, type){
		type = type || Error;
		throw new type(obj);
	}
};

/**
* @class Wrap Wrap��װ�����ڶ���������һ����Ƥ
* @namespace QW
* @param {any} core ����װ����  
* @return {Wrap} 
*/
/*
QW.Wrap=function(core) {
	this.core=core;
};
*/

window.QW = QW;
})();