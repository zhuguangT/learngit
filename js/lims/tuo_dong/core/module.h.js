/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	Download by http://www.codefans.net
	version: $version$ $release$ released
	author: yingjiakuan@baidu.com
*/

/**
 * @class ModuleH ģ�����Helper
 * @singleton 
 * @namespace QW
 * @helper
 */
(function(){

var modules={},
	mix = function(des, src, override){
		for(var i in src){
			if(override || !(i in des)){
				des[i] = src[i];
			}
		}
		return des;
	},
	isPlainObject = function(obj){return !!obj && obj.constructor == Object;},
	loadJs = QW.loadJs,
	loadingModules=[],
	isLoading=false;
function loadsJsInOrder(){
	//��������ܱ�֤��̬��ӵ�ScriptElement�ᰴ˳��ִ�У�������Ϊ����֤һ��
	//�μ���http://www.stevesouders.com/blog/2009/04/27/loading-scripts-without-blocking/
	//���԰�����http://1.cuzillion.com/bin/resource.cgi?type=js&sleep=3&jsdelay=0&n=1&t=1294649352
	//todo: Ŀǰû�г�����ò���������Ĳ������ع��ܣ����ԸĽ���
	//todo: �������������combo������޸�������������Ӧ��
	var moduleI=loadingModules[0];
	if (!isLoading && moduleI)	{
		//alert(moduleI.url);
		isLoading=true;
		loadingModules.splice(0,1);
		function loadedDone(){
			moduleI.loadStatus=2;
			var cbs=moduleI.__callbacks;
			for(var i=0;i<cbs.length;i++) cbs[i]();
			isLoading=false;
			loadsJsInOrder();
		};
		var checker=moduleI.loadedChecker;
		if(checker && checker()){ //�����loaderChecker������loaderChecker�ж�һ���Ƿ��Ѿ����ع�
			loadedDone();
		}
		else loadJs(moduleI.url.replace(/^\/\//,QW.PATH), loadedDone);
	}
};


var ModuleH = {
	/**
	 * @property {Array} provideDomains provide������Ե������ռ�
	 */
	provideDomains:[QW],
	/**
	 * ��QW��������ռ��������
	 * @method provide
	 * @static
	 * @param {string|Json} moduleName �������Ϊstring����Ϊkey������ΪJson����ʾ����Json���ֵdump��QW�����ռ�
	 * @param {any} value (Optional) ֵ
	 * @return {void} 
	 */		
	provide: function(moduleName, value){
		if(typeof moduleName =='string'){
			var domains=ModuleH.provideDomains;
			for(var i=0;i<domains.length;i++){
				if(!domains[i][moduleName]) domains[i][moduleName]=value;
			}
		}
		else if(isPlainObject(moduleName)) {
			for(i in moduleName){
				ModuleH.provide(i,moduleName[i]);
			}
		}
	},
	
	/** 
	* ���ģ�����á�
	* @method addConfig
	* @static
	* @param {string} moduleName ģ�����������Ϊjson������moduleName/details �ļ�ֵ��json��
	* @param {json} details ģ����������ã�Ŀǰ֧�����£�
		url: string��js·�����������"//"��ͷ����ָ�����QW.PATH��
		requires: string����ģ������������ģ�顣���ģ���á�,���ָ�
		use: ��ģ�����غ���Ҫ���ż��ص�ģ�顣���ģ���á�,���ָ�
		loadedChecker: ģ���Ƿ��Ѿ�Ԥ���ص��жϺ������������������true����ʾ�Ѿ����ع���
	* @example 
		addConfig('Editor',{url:'wed/editor/Editor.js',requires:'Dom',use:'Panel,Drap'});//����һ��ģ��
		addConfig({'Editor':{url:'wed/editor/Editor.js',requires:'Dom',use:'Panel,Drap'}});//���ö��ģ��
	*/
	addConfig : function(moduleName,details){
		if(typeof moduleName =='string'){
			var json=mix({},details);
			json.moduleName=moduleName;
			json.__callbacks=[];
			modules[moduleName]=json;
		}
		else if(isPlainObject(moduleName)) {
			for(var i in moduleName){
				ModuleH.addConfig(i,moduleName[i]);
			}
		}
	},

	/** 
	* �������ģ�����js���������ִ��callback��
	* @method use
	* @static
	* @param {string} moduleName ��Ҫ���ż��ص�ģ���������ģ���á�,���ָ�
	* @param {Function} callback ��Ҫִ�еĺ���.
	* @return {void} 
	* @remark 
		��Ҫ���ǵ������
		use��moduleδ����/������/�Ѽ��ء�����required��use���ļ��Ѽ���/������/δ����
	*/
	use: function(moduleName,callback){
		var modulesJson={},//��Ҫ���ص�ģ��Json����jsonЧ�ʿ죩
			modulesArray=[],//��Ҫ���ص�ģ��Array����array������			
			names=moduleName.split(','),
			i,
			j,
			k,
			len,
			moduleI;

		while (names.length){//�ռ���Ҫ�Ŷӵ�ģ�鵽modulesJson
			var names2={};
			for(i=0;i<names.length;i++){
				var nameI=names[i];
				if(!nameI || QW[nameI]) continue; //����ѱ�Ԥ���أ�Ҳ�����
				if (!modulesJson[nameI]){	//��û�����ռ�
					if(!modules[nameI]){	//��û����config
						throw 'Unknown module: '+nameI;
					}
					if(!modules[nameI].loadStatus!=2) {//��û�����ع�  loadStatus:1:�����С�2:�Ѽ���
						var checker=modules[nameI].loadedChecker;
						if(checker && checker()){ //�����loaderChecker������loaderChecker�ж�һ���Ƿ��Ѿ����ع�
							continue;
						}
						modulesJson[nameI]=modules[nameI];//������С�
					}
					var refs=['requires','use'];
					for(j=0;j<refs.length;j++){ //�ռ�������Ҫ���ص�ģ��
						var sRef= modules[nameI][refs[j]];
						if(sRef){
							var refNames=sRef.split(',');
							for(k=0;k<refNames.length;k++) names2[refNames[k]]=0;
						}
					}					
				}
			}
			names=[];
			for(i in names2){
				names.push(i);
			}
		}
		for(i in modulesJson){//ת���ɼ�������
			modulesArray.push(modulesJson[i]);
		}

		for(i=0,len=modulesArray.length;i<len;i++) {//���� �����򷨽�Լ���룬����������
			if(!modulesArray[i].requires) continue;
			for(j=i+1;j<len;j++){
				if(new RegExp('(^|,)'+modulesArray[j].moduleName+'(,|$)').test(modulesArray[i].requires)) {
					//�������ǰ���ģ��requires�����ģ�飬�򽫱�required��ģ���Ƶ�ǰ�����������²�������λ���Ƿ����
					var moduleJ=modulesArray[j];
					modulesArray.splice(j,1);
					modulesArray.splice(i,0,moduleJ);
					i--;
					break;
				}
			}
		}

		var loadIdx=-1,//��Ҫ���ز���δ���ص����һ��ģ���index
			loadingIdx=-1;//��Ҫ���ز������ڼ��ص����һ��ģ���index
		for(i=0;i<modulesArray.length;i++){
			moduleI=modulesArray[i];
			if(!moduleI.loadStatus && (new RegExp('(^|,)'+moduleI.moduleName+'(,|$)').test(moduleName)) ) loadIdx=i;
			if(moduleI.loadStatus == 1 && (new RegExp('(^|,)'+moduleI.moduleName+'(,|$)').test(moduleName)) ) loadingIdx=i;
		}
		if(loadIdx != -1) {//����δ��ʼ���ص�
			modulesArray[loadIdx].__callbacks.push(callback);
		}
		else if(loadingIdx!=-1) {//�������ڼ��ص�
			modulesArray[loadingIdx].__callbacks.push(callback);
		}
		else{
			callback();
			return;
		}
		
		for(i=0;i<modulesArray.length;i++){
			moduleI=modulesArray[i];
			if(!moduleI.loadStatus) {//��Ҫload��js��todo: ģ��combo����
				moduleI.loadStatus=1;
				loadingModules.push(moduleI);
			}
		}
		loadsJsInOrder();
	}
};

QW.ModuleH=ModuleH;
QW.use=ModuleH.use;
QW.provide=ModuleH.provide;

})();