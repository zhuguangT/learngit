/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	Download by http://www.codefans.net
	version: $version$ $release$ released
	author: yingjiakuan@baidu.com
*/

/**
 * @class StringH ���Ķ���String����չ
 * @singleton
 * @namespace QW
 * @helper
 */

(function(){

var StringH = {
	/** 
	* ��ȥ�ַ������ߵĿհ��ַ�
	* @method trim
	* @static
	* @param {String} s ��Ҫ������ַ���
	* @return {String}  ��ȥ���˿հ��ַ�����ַ���
	* @remark ����ַ����м��кܶ�����tab,����������Ч������,��Ӧ�����������һ�仰�����.
		return s.replace(/^[\s\xa0\u3000]+/g,"").replace(/([^\u3000\xa0\s])[\u3000\xa0\s]+$/g,"$1");
	*/
	trim:function(s){
		return s.replace(/^[\s\xa0\u3000]+|[\u3000\xa0\s]+$/g, "");
	},
	/** 
	* ��һ���ַ������ж��replace
	* @method mulReplace
	* @static
	* @param {String} s  ��Ҫ������ַ���
	* @param {array} arr  ���飬ÿһ��Ԫ�ض�����replace����������ɵ�����
	* @return {String} ���ش������ַ���
	* @example alert(mulReplace("I like aa and bb. JK likes aa.",[[/aa/g,"ɽ"],[/bb/g,"ˮ"]]));
	*/
	mulReplace:function (s,arr){
		for(var i=0;i<arr.length;i++) s=s.replace(arr[i][0],arr[i][1]);
		return s;
	},
	/** 
	* �ַ�������ģ��
	* @method format
	* @static
	* @param {String} s �ַ���ģ�壬���б�����{0} {1}��ʾ
	* @param {String} arg0 (Optional) �滻�Ĳ���
	* @return {String}  ģ��������滻����ַ���
	* @example alert(format("{0} love {1}.",'I','You'))
	*/
	format:function(s,arg0){
		var args=arguments;
		return s.replace(/\{(\d+)\}/ig,function(a,b){return args[b*1+1]||''});
	},

	/*
	* �ַ�������ģ��
	* @method tmpl
	* @static
	* @param {String} sTmpl �ַ���ģ�壬���б����ԣ�$aaa����ʾ
	* @param {Object} opts ģ�����
	* @return {String}  ģ��������滻����ַ���
	* @example alert(tmpl("{$a} love {$b}.",{a:"I",b:"you"}))
	tmpl:function(sTmpl,opts){
		return sTmpl.replace(/\{\$(\w+)\}/g,function(a,b){return opts[b]});
	},
	*/

	/** 
	* �ַ���ģ��
	* @method tmpl
	* @static
	* @param {String} sTmpl �ַ���ģ�壬���б�����{$aaa}��ʾ��ģ���﷨��
		�ָ���Ϊ{xxx}��"}"֮ǰû�пո��ַ���
		js���ʽ/js������'}', ��ʹ��' }'����ǰ���пո��ַ�
		{strip}...{/strip}�������\r\n��ͷ�Ŀհ׶��ᱻ�����
		{}��ֻ��ʹ�ñ��ʽ������ʹ����䣬����ʹ�����±�ǩ
		{js ...}		��������js���, ���������Ҫ�����ģ�壬��print("aaa");
		{if(...)}		����if��䣬д��Ϊ{if($a>1)},��Ҫ�Դ�����
		{elseif(...)}	����elseif��䣬д��Ϊ{elseif($a>1)},��Ҫ�Դ�����
		{else}			����else��䣬д��Ϊ{else}
		{/if}			����endif��䣬д��Ϊ{/if}
		{for(...)}		����for��䣬д��Ϊ{for(var i=0;i<1;i++)}����Ҫ�Դ�����
		{/for}			����endfor��䣬д��Ϊ{/for}
		{while(...)}	����while���,д��Ϊ{while(i-->0)},��Ҫ�Դ�����
		{/while}		����endwhile���, д��Ϊ{/while}
	* @param {Object} opts (Optional) ģ�����
	* @return {String|Function}  �������ʱ����opts�������򷵻��ַ��������û�����򷵻�һ��function���൱�ڰ�sTmplת����һ��������

	* @example alert(tmpl("{$a} love {$b}.",{a:"I",b:"you"}));
	* @example alert(tmpl("{js print('I')} love {$b}.",{b:"you"}));
	*/
	tmpl:(function(){
		/*
		sArrName ƴ���ַ����ı�������
		*/
		var sArrName="sArrCMX",sLeft=sArrName+'.push("';
		/*
			tag:ģ���ǩ,�����Ժ��壺
			tagG: tagϵ��
			isBgn: �ǿ�ʼ���͵ı�ǩ
			isEnd: �ǽ������͵ı�ǩ
			cond: ��ǩ����
			rlt: ��ǩ���
			sBgn: ��ʼ�ַ���
			sEnd: �����ַ���
		*/
		var tags={
			'js':{tagG:'js',isBgn:1,isEnd:1,sBgn:'");',sEnd:';'+sLeft},	//����js���, ���������Ҫ�����ģ�壬��print("aaa");
			'if':{tagG:'if',isBgn:1,rlt:1,sBgn:'");if',sEnd:'{'+sLeft},	//if��䣬д��Ϊ{if($a>1)},��Ҫ�Դ�����
			'elseif':{tagG:'if',cond:1,rlt:1,sBgn:'");} else if',sEnd:'{'+sLeft},	//if��䣬д��Ϊ{elseif($a>1)},��Ҫ�Դ�����
			'else':{tagG:'if',cond:1,rlt:2,sEnd:'");}else{'+sLeft},	//else��䣬д��Ϊ{else}
			'/if':{tagG:'if',isEnd:1,sEnd:'");}'+sLeft},	//endif��䣬д��Ϊ{/if}
			'for':{tagG:'for',isBgn:1,rlt:1,sBgn:'");for',sEnd:'{'+sLeft},	//for��䣬д��Ϊ{for(var i=0;i<1;i++)},��Ҫ�Դ�����
			'/for':{tagG:'for',isEnd:1,sEnd:'");}'+sLeft},	//endfor��䣬д��Ϊ{/for}
			'while':{tagG:'while',isBgn:1,rlt:1,sBgn:'");while',sEnd:'{'+sLeft},	//while���,д��Ϊ{while(i-->0)},��Ҫ�Դ�����
			'/while':{tagG:'while',isEnd:1,sEnd:'");}'+sLeft}	//endwhile���, д��Ϊ{/while}
		};

		return function (sTmpl,opts){
			var N=-1,NStat=[];//����ջ;
			var ss=[
				[/\{strip\}([\s\S]*?)\{\/strip\}/g, function(a,b){return b.replace(/[\r\n]\s*\}/g," }").replace(/[\r\n]\s*/g,"");}],
				[/\\/g,'\\\\'],[/"/g,'\\"'],[/\r/g,'\\r'],[/\n/g,'\\n'], //Ϊjs��ת��.
				[/\{[\s\S]*?\S\}/g,	//js��ʹ��}ʱ��ǰ��Ҫ�ӿո�
					function(a){
					a=a.substr(1,a.length-2);
					for(var i=0;i<ss2.length;i++) a=a.replace(ss2[i][0],ss2[i][1]);
					var tagName=a;
					if(/^(.\w+)\W/.test(tagName)) tagName=RegExp.$1;
					var tag=tags[tagName];
					if(tag){
						if(tag.isBgn){
							var stat=NStat[++N]={tagG:tag.tagG,rlt:tag.rlt};
						}
						if(tag.isEnd){
							if(N<0) throw new Error("����Ľ������"+a);
							stat=NStat[N--];
							if(stat.tagG!=tag.tagG) throw new Error("��ǲ�ƥ�䣺"+stat.tagG+"--"+tagName);
						}
						else if(!tag.isBgn){
							if(N<0) throw new Error("����ı��"+a);
							stat=NStat[N];
							if(stat.tagG!=tag.tagG) throw new Error("��ǲ�ƥ�䣺"+stat.tagG+"--"+tagName);
							if(tag.cond && !(tag.cond & stat.rlt)) throw new Error("���ʹ��ʱ�����ԣ�"+tagName);
							stat.rlt=tag.rlt;
						}
						return (tag.sBgn||'')+a.substr(tagName.length)+(tag.sEnd||'');
					}
					else{
						return '",('+a+'),"';
					}
				}]
			];
			var ss2=[[/\\n/g,'\n'],[/\\r/g,'\r'],[/\\"/g,'"'],[/\\\\/g,'\\'],[/\$(\w+)/g,'opts["$1"]'],[/print\(/g,sArrName+'.push(']];
			for(var i=0;i<ss.length;i++){
				sTmpl=sTmpl.replace(ss[i][0],ss[i][1]);
			}
			if(N>=0) throw new Error("����δ�����ı�ǣ�"+NStat[N].tagG);
			sTmpl='var '+sArrName+'=[];'+sLeft+sTmpl+'");return '+sArrName+'.join("");';
			//alert('ת�����\n'+sTmpl);
			var fun=new Function('opts',sTmpl);
			if(arguments.length>1) return fun(opts);
			return fun;
		};
	})(),

	/** 
	* �ж�һ���ַ����Ƿ������һ���ַ���
	* @method contains
	* @static
	* @param {String} s �ַ���
	* @param {String} opts ���ַ���
	* @return {String} ģ��������滻����ַ���
	* @example alert(contains("aaabbbccc","ab"))
	*/
	contains:function(s,subStr){
		return s.indexOf(subStr)>-1;
	},

	/** 
	* ȫ���ַ�ת����ַ�
		ȫ�ǿո�Ϊ12288��ת����" "��
		ȫ�Ǿ��Ϊ12290��ת����"."��
		�����ַ����(33-126)��ȫ��(65281-65374)�Ķ�Ӧ��ϵ�ǣ������65248 
	* @method dbc2sbc
	* @static
	* @param {String} s ��Ҫ������ַ���
	* @return {String}  ����ת������ַ���
	* @example 
		var s="��Ʊ���ǣ££ã���������������Ʊ����ǣ���.����Ԫ";
		alert(dbc2sbc(s));
	*/
	dbc2sbc:function(s)
	{
		return StringH.mulReplace(s,[
			[/[\uff01-\uff5e]/g,function(a){return String.fromCharCode(a.charCodeAt(0)-65248);}],
			[/\u3000/g,' '],
			[/\u3002/g,'.']
		]);
	},

	/** 
	* �õ��ֽڳ���
	* @method byteLen
	* @static
	* @param {String} s �ַ���
	* @return {number}  �����ֽڳ���
	*/
	byteLen:function(s)
	{
		return s.replace(/[^\x00-\xff]/g,"--").length;
	},

	/** 
	* �õ�ָ���ֽڳ��ȵ����ַ���
	* @method subByte
	* @static
	* @param {String} s �ַ���
	* @param {number} len �ֽڳ���
	* @optional {string} tail ��β�ַ���
	* @return {string}  ����ָ���ֽڳ��ȵ����ַ���
	*/
	subByte:function(s, len, tail)
	{
		if(StringH.byteLen(s)<=len) return s;
		tail = tail||'';
		len -= StringH.byteLen(tail);
		return s=s.substr(0,len).replace(/([^\x00-\xff])/g,"$1 ")//˫�ֽ��ַ��滻������
			.substr(0,len)//��ȡ����
			.replace(/[^\x00-\xff]$/,"")//ȥ���ٽ�˫�ֽ��ַ�
			.replace(/([^\x00-\xff]) /g,"$1") + tail;//��ԭ
	},

	/** 
	* �շ廯�ַ���������ab-cd��ת��Ϊ��abCd��
	* @method camelize
	* @static
	* @param {String} s �ַ���
	* @return {String}  ����ת������ַ���
	*/
	camelize:function(s) {
		return s.replace(/\-(\w)/ig,function(a,b){return b.toUpperCase();});
	},

	/** 
	* ���շ廯�ַ���������abCd��ת��Ϊ��ab-cd����
	* @method decamelize
	* @static
	* @param {String} s �ַ���
	* @return {String} ����ת������ַ���
	*/
	decamelize:function(s) {
		return s.replace(/[A-Z]/g,function(a){return "-"+a.toLowerCase();});
	},

	/** 
	* �ַ���Ϊjavascriptת��
	* @method encode4Js
	* @static
	* @param {String} s �ַ���
	* @return {String} ����ת������ַ���
	* @example 
		var s="my name is \"JK\",\nnot 'Jack'.";
		window.setTimeout("alert('"+encode4Js(s)+"')",10);
	*/
	encode4Js:function(s){
		return StringH.mulReplace(s,[
			[/\\/g,"\\u005C"],
			[/"/g,"\\u0022"],
			[/'/g,"\\u0027"],
			[/\//g,"\\u002F"],
			[/\r/g,"\\u000A"],
			[/\n/g,"\\u000D"],
			[/\t/g,"\\u0009"]
		]);
	},

	/** 
	* Ϊhttp�Ĳ��ɼ��ַ�������ȫ�ַ��������ַ���ת��
	* @method encode4Http
	* @static
	* @param {String} s �ַ���
	* @return {String} ���ش������ַ���
	*/
	encode4Http:function(s){
		return s.replace(/[\u0000-\u0020\u0080-\u00ff\s"'#\/\|\\%<>\[\]\{\}\^~;\?\:@=&]/,function(a){return encodeURIComponent(a)});
	},

	/** 
	* �ַ���ΪHtmlת��
	* @method encode4Html
	* @static
	* @param {String} s �ַ���
	* @return {String} ���ش������ַ���
	* @example 
		var s="<div>dd";
		alert(encode4Html(s));
	*/
	encode4Html:function(s){
		var el = document.createElement('pre');//����Ҫ��pre����div��ʱ�ᶪʧ���У����磺'a\r\n\r\nb'
		var text = document.createTextNode(s);
		el.appendChild(text);
		return el.innerHTML;
	},

	/** 
	* �ַ���ΪHtml��valueֵת��
	* @method encode4HtmlValue
	* @static
	* @param {String} s �ַ���
	* @return {String} ���ش������ַ���
	* @example:
		var s="<div>\"\'ddd";
		alert("<input value='"+encode4HtmlValue(s)+"'>");
	*/
	encode4HtmlValue:function(s){
		return StringH.encode4Html(s).replace(/"/g,"&quot;").replace(/'/g,"&#039;");
	},

	/** 
	* ��encode4Html�����෴�����з�����
	* @method decode4Html
	* @static
	* @param {String} s �ַ���
	* @return {String} ���ش������ַ���
	*/
	decode4Html:function(s){
		var div = document.createElement('div');
		div.innerHTML = StringH.stripTags(s);
		return div.childNodes[0] ? div.childNodes[0].nodeValue+'' : '';
	},
	/** 
	* ������tag��ǩ��������ȥ��<tag>���Լ�</tag>
	* @method stripTags
	* @static
	* @param {String} s �ַ���
	* @return {String} ���ش������ַ���
	*/
	stripTags:function(s) {
		return s.replace(/<[^>]*>/gi, '');
	},
	/** 
	* evalĳ�ַ����������"eval"����������Ҫ�����ţ����ܲ�Ӱ��YUIѹ�������������ط�����Ҳ�������⣬���Ը���evalJs��
	* @method evalJs
	* @static
	* @param {String} s �ַ���
	* @param {any} opts ����ʱ��Ҫ�Ĳ�����
	* @return {any} �����ַ�������з��ء�
	*/
	evalJs:function(s,opts) { //�����eval����������Ҫ�����ţ����ܲ�Ӱ��YUIѹ�������������ط�����Ҳ�������⣬���Ըĳ�evalJs��
		return new Function("opts",s)(opts);
	},
	/** 
	* evalĳ�ַ���������ַ�����һ��js���ʽ�������ر��ʽ���еĽ��
	* @method evalExp
	* @static
	* @param {String} s �ַ���
	* @param {any} opts evalʱ��Ҫ�Ĳ�����
	* @return {any} �����ַ�������з��ء�
	*/
	evalExp:function(s,opts) {
		return new Function("opts","return "+s+";")(opts);
	}
};

QW.StringH=StringH;

})();