/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	version: $version$ $release$ released
	author: yingjiakuan@baidu.com
*/


/**
 * @class Selector Css Selector��صļ�������
 * @singleton
 * @namespace QW
 */
(function(){
var trim=QW.StringH.trim,
	encode4Js=QW.StringH.encode4Js;

var Selector={
	/**
	 * @property {int} queryStamp ���һ�β�ѯ��ʱ�������չα��ʱ���ܻ��õ���������
	 */
	queryStamp:0,
	/**
	 * @property {Json} _operators selector���������
	 */
	_operators:{	//���±��ʽ��aa��ʾattrֵ��vv��ʾ�Ƚϵ�ֵ
		'': 'aa',//isTrue|hasValue
		'=': 'aa=="vv"',//equal
		'!=': 'aa!="vv"', //unequal
		'~=': 'aa&&(" "+aa+" ").indexOf(" vv ")>-1',//onePart
		'|=': 'aa&&(aa+"-").indexOf("vv-")==0', //firstPart
		'^=': 'aa&&aa.indexOf("vv")==0', // beginWith
		'$=': 'aa&&aa.lastIndexOf("vv")==aa.length-"vv".length', // endWith
		'*=': 'aa&&aa.indexOf("vv")>-1' //contains
	},
	/**
	 * @property {Json} _pseudos α���߼�
	 */
	_pseudos:{
		"first-child":function(a){return !(a=a.previousSibling) || !a.tagName && !a.previousSibling;},
		"last-child":function(a){return !(a=a.nextSibling) || !a.tagName && !a.nextSibling;},
		"only-child":function(a){return !a.previousSibling && !a.nextSibling;},
		"nth-child":function(a,nth){return checkNth(a,nth); },
		"nth-last-child":function(a,nth){return checkNth(a,nth,true); },
		"first-of-type":function(a){ var tag=a.tagName; var el=a; while(el=el.previousSlibling){if(el.tagName==tag) return false;} return true;},
		"last-of-type":function(a){ var tag=a.tagName; var el=a; while(el=el.nextSibling){if(el.tagName==tag) return false;} return true; },
		"only-of-type":function(a){var els=a.parentNode.childNodes; for(var i=els.length-1;i>-1;i--){if(els[i].tagName==a.tagName && els[i]!=a) return false;} return true;},
		"nth-of-type":function(a,nth){var idx=1;var el=a;while(el=el.previousSibling) {if(el.tagName==a.tagName) idx++;} return checkNth(idx,nth); },//JK������Ϊ������α���������Ż�
		"nth-last-of-type":function(a,nth){var idx=1;var el=a;while(el=el.nextSibling) {if(el.tagName==a.tagName) idx++;} return checkNth(idx,nth); },//JK������Ϊ������α���������Ż�
		"empty":function(a){ return !a.firstChild; },
		"parent":function(a){ return !!a.firstChild; },
		"not":function(a,sSelector){ return !s2f(sSelector)(a); },
		"enabled":function(a){ return !a.disabled; },
		"disabled":function(a){ return a.disabled; },
		"checked":function(a){ return a.checked; },
		"contains":function(a,s){return (a.textContent || a.innerText || "").indexOf(s) >= 0;}
	},
	/**
	 * @property {Json} _attrGetters ���õ�Element����
	 */
	_attrGetters:function(){ 
		var o={'class': 'el.className',
			'for': 'el.htmlFor',
			'href':'el.getAttribute("href",2)'};
		var attrs='name,id,className,value,selected,checked,disabled,type,tagName,readOnly,offsetWidth,offsetHeight'.split(',');
		for(var i=0,a;a=attrs[i];i++) o[a]="el."+a;
		return o;
	}(),
	/**
	 * @property {Json} _relations selector��ϵ�����
	 */
	_relations:{
		//Ѱ��
		"":function(el,filter,topEl){
			while((el=el.parentNode) && el!=topEl){
				if(filter(el)) return el;
			}
			return null;
		},
		//Ѱ��
		">":function(el,filter,topEl){
			el=el.parentNode;
			return el!=topEl&&filter(el) ? el:null;
		},
		//Ѱ��С�ĸ��
		"+":function(el,filter,topEl){
			while(el=el.previousSibling){
				if(el.tagName){
					return filter(el) && el;
				}
			}
			return null;
		},
		//Ѱ���еĸ��
		"~":function(el,filter,topEl){
			while(el=el.previousSibling){
				if(el.tagName && filter(el)){
					return el;
				}
			}
			return null;
		}
	},
	/** 
	 * ��һ��selector�ַ���ת����һ�����˺���.
	 * @method selector2Filter
	 * @static
	 * @param {string} sSelector ����selector�����selector��û�й�ϵ�������", >+~"��
	 * @returns {function} : ���ع��˺�����
	 * @example: 
		var fun=selector2Filter("input.aaa");alert(fun);
	 */
	selector2Filter:function(sSelector){
		return s2f(sSelector);
	},
	/** 
	 * �ж�һ��Ԫ���Ƿ����ĳselector.
	 * @method test 
	 * @static
	 * @param {HTMLElement} el: ���������
	 * @param {string} sSelector: ����selector�����selector��û�й�ϵ�������", >+~"��
	 * @returns {function} : ���ع��˺�����
	 */
	test:function(el,sSelector){
		return s2f(sSelector)(el);
	},
	/** 
	 * ��һ��css selector������һ������.
	 * @method filter 
	 * @static
	 * @param {Array|Collection} els: Ԫ������
	 * @param {string} sSelector: ����selector�����selector��û�й�ϵ�������", >+~"��
	 * @param {Element} pEl: ���ڵ㡣Ĭ����document.documentElement
	 * @returns {Array} : �����������������Ԫ����ɵ����顣
	 */
	filter:function(els,sSelector,pEl){
		var sltors=splitSelector(sSelector);
		return filterByRelation(pEl||document.documentElement,els,sltors);
	},
	/** 
	 * ��refElΪ�ο����õ����Ϲ���������HTML Elements. refEl������element������document
	 * @method query
	 * @static
	 * @param {HTMLElement} refEl: �ο�����
	 * @param {string} sSelector: ����selector,
	 * @returns {array} : ����elements���顣
	 * @example: 
		var els=query(document,"li input.aaa");
		for(var i=0;i<els.length;i++ )els[i].style.backgroundColor='red';
	 */
	query:function(refEl,sSelector){
		Selector.queryStamp = queryStamp++;
		refEl=refEl||document.documentElement;
		var els=nativeQuery(refEl,sSelector);
		if(els) return els;//����ʹ��ԭ����
		var groups=trim(sSelector).split(",");
		els=querySimple(refEl,groups[0]);
		for(var i=1,gI;gI=groups[i];i++){
			var els2=querySimple(refEl,gI);
			els=els.concat(els2);
			//els=union(els,els2);//���ػ�̫���������˹���
		}
		return els;
	},
	/** 
	 * ��refElΪ�ο����õ����Ϲ���������һ��Ԫ��. refEl������element������document
	 * @method one
	 * @static
	 * @param {HTMLElement} refEl: �ο�����
	 * @param {string} sSelector: ����selector,
	 * @returns {HTMLElement} : ����element�������ȡ�������򷴻�null��
	 * @example: 
		var els=query(document,"li input.aaa");
		for(var i=0;i<els.length;i++ )els[i].style.backgroundColor='red';
	 */
	one:function(refEl,sSelector){
		var els=Selector.query(refEl,sSelector);
		return els[0];
	}


};

window.__SltPsds=Selector._pseudos;//JK 2010-11-11��Ϊ���Ч��
/*
	retTrue һ������Ϊtrue�ĺ���
*/
function retTrue(){
	return true;
}

/*
	arrFilter(arr,callback) : ��arr���Ԫ�ؽ��й���
*/
function arrFilter(arr,callback){
	var rlt=[],i=0;
	if(callback==retTrue){
		if(arr instanceof Array) return arr.slice(0);
		else{
			for(var len=arr.length;i<len;i++) {
				rlt[i]=arr[i];
			}
		}
	}
	else{
		for(var oI;oI=arr[i++];) {
			callback(oI) && rlt.push(oI);
		}
	}
	return rlt;
};

var elContains,//�����������֧��contains()������FF
	getChildren=function(pEl){ //��Ҫ�޳�textNode�롰<!--xx-->���ڵ�
		var els=pEl.childNodes,len=els.length,ret=[],i=0;for(;i<len;i++) if(els[i].nodeType==1) ret.push(els[i]); return ret;
	},
	hasNativeQuery,//�����������֧��ԭ��querySelectorAll()������IE8-
	findId=function(id) {return document.getElementById(id);};

(function(){
	var div=document.createElement('div');
	div.innerHTML='<div class="aaa"></div>';
	hasNativeQuery=(div.querySelectorAll && div.querySelectorAll('.aaa').length==1);
	elContains=div.contains?
		function(pEl,el){ return pEl!=el && pEl.contains(el);}:
		function(pEl,el){ return (pEl.compareDocumentPosition(el) & 16);};
})();


function checkNth(el,nth,reverse){
	if(nth=='n') return true;
	if(typeof el =='number') var idx=el;
	else{
		var pEl=el.parentNode;
		if(pEl.__queryStamp!=queryStamp){
			var nEl={nextSibling:pEl.firstChild},
				n=1;
			while(nEl=nEl.nextSibling){
				if(nEl.nodeType==1) nEl.__siblingIdx=n++;
			}
			pEl.__queryStamp=queryStamp;
			pEl.__childrenNum=n-1;
		}
		if(reverse) idx=pEl.__childrenNum-el.__siblingIdx+1;
		else idx=el.__siblingIdx;
	}
	switch (nth)
	{
		case 'even':
		case '2n':
			return idx%2==0;
		case 'odd':
		case '2n+1':
			return idx%2==1;
		default:
			if(!(/n/.test(nth))) return idx==nth;
			var arr=nth.replace(/(^|\D+)n/g,"$11n").split("n"),
				k=arr[0]|0,
				kn=idx-arr[1]|0;
			return k*kn>=0 && kn%k==0;
	}
}
/*
 * s2f(sSelector): ��һ��selector�õ�һ�����˺���filter�����selector��û�й�ϵ�������", >+~"��
 */
var filterCache={};
function s2f(sSelector,isForArray){
	if(!isForArray && filterCache[sSelector]) return filterCache[sSelector];
	var pseudos=[],//α������,ÿһ��Ԫ�ض������飬����Ϊ��α������α��ֵ
		s=trim(sSelector),
		reg=/\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/g, //����ѡ����ʽ����,thanks JQuery
		sFun=[];
	s=s.replace(/\:([\w\-]+)(\(([^)]+)\))?/g,function(a,b,c,d,e){pseudos.push([b,d]);return "";})	//α��
		.replace(/^\*/g,function(a){//����tagName����д��
			sFun.push('el.nodeType==1');return ''
		})	
		.replace(/^([\w\-]+)/g,function(a){//tagName����д��
			sFun.push('el.tagName=="'+a.toUpperCase()+'"');return ''
		})	
		.replace(/([\[(].*)|#([\w\-]+)|\.([\w\-]+)/g, function (a, b, c, d) {	//id����д��//className����д��
			return b || c&&'[id="'+c+'"]' || d&&'[className~="'+d+'"]';
		})
		.replace(reg,function(a,b,c,d,e){//��ͨд��[foo][foo=""][foo~=""]��
			var attrGetter=Selector._attrGetters[b] || 'el.getAttribute("'+b+'")';
			sFun.push(Selector._operators[c||''].replace(/aa/g,attrGetter).replace(/vv/g,e||''));
			return '';
		});
	if(!(/^\s*$/).test(s)) {
		throw "Unsupported Selector:\n"+sSelector+"\n-"+s; 
	}
	for(var i=0,pI;pI=pseudos[i];i++) {//α�����
		if(!Selector._pseudos[pI[0]]) throw "Unsupported Selector:\n"+pI[0]+"\n"+s;
		if(/^(nth-|not|contains)/.test(pI[0])){
			sFun.push('__SltPsds["'+pI[0]+'"](el,"'+encode4Js(pI[1])+'")');
		}
		else{
			sFun.push('__SltPsds["'+pI[0]+'"](el)');
		}
	}
	if (sFun.length)
	{
		if(isForArray){
			return new Function('els','var els2=[];for(var i=0,el;el=els[i++];){if('+sFun.join('&&')+') els2.push(el);} return els2;');
		}
		else{
			return filterCache[sSelector]=new Function('el','return '+sFun.join('&&')+';');
		}
	}
	else {
		if(isForArray){
			return function(els){return arrFilter(els,retTrue);}
		}
		else{
			return filterCache[sSelector]=retTrue;
		}
		
	}
};

/* 
	* {int} xxxStamp: ȫ�ֱ�����ѯ���
 */
var queryStamp=0,
	relationStamp=0,
	querySimpleStamp=0;

/*
* nativeQuery(refEl,sSelector): �����ԭ����querySelectorAll������ֻ�Ǽ򵥲�ѯ�������ԭ����query�����򷵻�null. 
* @param {Element} refEl �ο�Ԫ��
* @param {string} sSelector selector�ַ���
* @returns 
*/
function nativeQuery(refEl,sSelector){
		if(hasNativeQuery && /^((^|,)\s*[.\w-][.\w\s\->+~]*)+$/.test(sSelector)) {
			//���������Դ���querySelectorAll�����ұ���query���Ǽ�selector����ֱ�ӵ���selector�Լ���
			//�����������֧����">~+"��ʼ�Ĺ�ϵ�����
			var arr=[],els=refEl.querySelectorAll(sSelector);
			for(var i=0,elI;elI=els[i++];) arr.push(elI);
			return arr;
		}
		return null;
};

/* 
* querySimple(pEl,sSelector): �õ�pEl�µķ��Ϲ���������HTML Elements. 
* sSelector��û��","�����
* pEl��Ĭ����document.body 
* @see: query��
*/
function querySimple(pEl,sSelector){
	querySimpleStamp++;
	/*
		Ϊ����߲�ѯ�ٶȣ�����������ԭ��
		�����ȣ�ԭ����ѯ
		�����ȣ���' '��'>'��ϵ������ǰ���������򣨴��浽���ѯ
		�����ȣ�id��ѯ
		�����ȣ�ֻ��һ����ϵ������ֱ�Ӳ�ѯ
		��ԭʼ���ԣ����ù�ϵ�жϣ���������ײ������ϲ����ߣ������óɹ�������������
	*/

	//�����ȣ�ԭ����ѯ
	var els=nativeQuery(pEl,sSelector);
	if(els) return els;//����ʹ��ԭ����


	var sltors=splitSelector(sSelector),
		pEls=[pEl],
		i,
		elI,
		pElI;

	var sltor0;
	//�����ȣ���' '��'>'��ϵ������ǰ���������򣨴��ϵ��£���ѯ
	while(sltor0=sltors[0]){
		if(!pEls.length) return [];
		var relation=sltor0[0];
		els=[];
		if(relation=='+'){//��һ���ܵ�
			filter=s2f(sltor0[1]);
			for(i=0;elI=pEls[i++];){
				while(elI=elI.nextSibling){
					if(elI.tagName){
						if(filter(elI)) els.push(elI);
						break;
					}
				}
			}
			pEls=els;
			sltors.splice(0,1);
		}
		else if(relation=='~'){//���еĵܵ�
			filter=s2f(sltor0[1]);
			for(i=0;elI=pEls[i++];){
				if(i>1 && elI.parentNode==pEls[i-2].parentNode) continue;//���أ�����Ѿ�query���ֳ����򲻱�query�ܵ�
				while(elI=elI.nextSibling){
					if(elI.tagName){
						if(filter(elI)) els.push(elI);
					}
				}
			}
			pEls=els;
			sltors.splice(0,1);
		}
		else{
			break;
		}
	}
	var sltorsLen=sltors.length;
	if(!sltorsLen || !pEls.length) return pEls;
	
	//�����ȣ�idIdx��ѯ
	for(var idIdx=0,id;sltor=sltors[idIdx];idIdx++){
		if((/^[.\w-]*#([\w-]+)/i).test(sltor[1])){
			id=RegExp.$1;
			sltor[1]=sltor[1].replace('#'+id,'');
			break;
		}
	}
	if(idIdx<sltorsLen){//����id
		var idEl=findId(id);
		if(!idEl) return [];
		for(i=0,pElI;pElI=pEls[i++];){
			if(elContains(pElI,idEl)) {
				els=filterByRelation(pEl,[idEl],sltors.slice(0,idIdx+1));
				if(!els.length || idIdx==sltorsLen-1) return els;
				return querySimple(idEl,sltors.slice(idIdx+1).join(',').replace(/,/g,' '));
			}
		}
		return [];
	}

	//---------------
	var getChildrenFun=function(pEl){return pEl.getElementsByTagName(tagName);},
		tagName='*',
		className='';
	sSelector=sltors[sltorsLen-1][1];
	sSelector=sSelector.replace(/^[\w\-]+/,function(a){tagName=a;return ""});
	if(hasNativeQuery){
		sSelector=sSelector.replace(/^[\w\*]*\.([\w\-]+)/,function(a,b){className=b;return ""});
	}
	if(className){
		getChildrenFun=function(pEl){return pEl.querySelectorAll(tagName+'.'+className);};
	}

	//�����ȣ�ֻʣһ��'>'��' '��ϵ��(���ǰ��Ĵ��룬��ʱ�����ܳ��ֻ�ֻʣ'+'��'~'��ϵ��)
	if(sltorsLen==1){
		if(sltors[0][0]=='>') {
			getChildrenFun=getChildren;
			var filter=s2f(sltors[0][1],true);
		}
		else{
			filter=s2f(sSelector,true);
		}
		els=[];
		for(i=0;pElI=pEls[i++];){
			els=els.concat(filter(getChildrenFun(pElI)));
		}
		return els;
	}


	//�ߵ�һ����ϵ����'>'��' '�����ܷ���
	sltors[sltors.length-1][1] = sSelector;
	els=[];
	for(i=0;pElI=pEls[i++];){
		els=els.concat(filterByRelation(pElI,getChildrenFun(pElI),sltors));
	}
	return els;
};


function splitSelector(sSelector){
	var sltors=[];
	var reg=/(^|\s*[>+~ ]\s*)(([\w\-\:.#*]+|\([^\)]*\)|\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\6|)\s*\])+)(?=($|\s*[>+~ ]\s*))/g;
	var s=trim(sSelector).replace(reg,function(a,b,c,d){sltors.push([trim(b),c]);return "";});
	if(!(/^\s*$/).test(s)) {
		throw "Unsupported Selector:\n"+sSelector+"\n--"+s; 
	}
	return sltors;
}

/*
�ж�һ������������ڵ��Ƿ������ϵҪ��----�ر�˵��������ĵ�һ����ϵֻ���Ǹ��ӹ�ϵ���������ϵ;
*/

function filterByRelation(pEl,els,sltors){
	relationStamp++;
	var sltor=sltors[0],
		len=sltors.length,
		needNotTopJudge=!sltor[0],
		filters=[],
		relations=[],
		needNext=[],
		relationsStr='';
		
	for(var i=0;i<len;i++){
		sltor=sltors[i];
		filters[i]=s2f(sltor[1],i==len-1);//����
		relations[i]=Selector._relations[sltor[0]];//Ѱ�׺���
		if(sltor[0]=='' || sltor[0]=='~') needNext[i]=true;//�Ƿ�ݹ�Ѱ��
		relationsStr+=sltor[0]||' ';
	}
	els=filters[len-1](els);//�������
	if(relationsStr==' ') return els;
	if(/[+>~] |[+]~/.test(relationsStr)){//��Ҫ����
		//alert(1); //�õ������֧�Ŀ����Ժ�С������Ч�ʵ�׷��
		function chkRelation(el){//��ϵ�˹���
			var parties=[],//�м��ϵ��
				j=len-1,
				party=parties[j]=el;
			for(;j>-1;j--){
				if(j>0){//�����һ�������
					party=relations[j](party,filters[j-1],pEl);
				}
				else if(needNotTopJudge || party.parentNode==pEl){//���һ��ͨ���ж�
					return true;
				}
				else {//���һ��δͨ���ж�
					party=null;
				}
				while(!party){//����
					if(++j==len) { //cache��ͨ��
						return false;
					}
					if(needNext[j]) {
						party=parties[j-1];
						j++;
					}
				}
				parties[j-1]=party;
			}
		};
		return arrFilter(els,chkRelation);
	}
	else{//�������
		var els2=[];
		for(var i=0,el,elI; el=elI=els[i++] ; ) {
			for(var j=len-1;j>0;j--){
				if(!(el=relations[j](el,filters[j-1],pEl))) {
					break;
				}
			}
			if(el && (needNotTopJudge || el.parentNode==pEl)) els2.push(elI);
		}
		return els2;
	}

}

QW.Selector=Selector;
})();