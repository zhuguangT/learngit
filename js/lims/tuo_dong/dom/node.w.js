/*
	Copyright (c) 2010, Baidu Inc.  http://www.youa.com; http://www.QWrap.com
	author: JK
	author: wangchen
*/
/** 
* @class NodeW HTMLElement�����װ��
* @namespace QW
*/
(function () {
	var ObjectH = QW.ObjectH,
		mix = ObjectH.mix,
		isString = ObjectH.isString,
		isArray = ObjectH.isArray,
		push = Array.prototype.push,
		NodeH = QW.NodeH,
		g = NodeH.g,
		query = NodeH.query,
		one = NodeH.one,
		create=QW.DomU.create;


	var NodeW=function(core) {
		if(!core) return null;//�÷���var w=NodeW(null);	����null
		var arg1=arguments[1];
		if(isString(core)){
			if(/^</.test(core)){//�÷���var w=NodeW(html); 
				var list=create(core,true,arg1).childNodes,
					els=[];
				for(var i=0,elI;elI=list[i];i++) {
					els[i]=elI;
				}
				return new NodeW(els);
			}
			else{//�÷���var w=NodeW(sSelector);
				return new NodeW(query(arg1,core));
			}
		}
		else {
			core=g(core,arg1);
			if(this instanceof NodeW){
				this.core=core;
				if(isArray(core)){//�÷���var w=NodeW(elementsArray); 
					this.length=0;
					push.apply( this, core );
				}
				else{//�÷���var w=new NodeW(element)//���Ƽ�; 
					this.length=1;
					this[0]=core;
				}
			}
			else return new NodeW(core);//�÷���var w=NodeW(element); var w2=NodeW(elementsArray); 
		}
	};

	NodeW.one=function(core){
		if(!core) return null;//�÷���var w=NodeW.one(null);	����null
		var arg1=arguments[1];
		if(isString(core)){//�÷���var w=NodeW.one(sSelector); 
			if(/^</.test(core)){//�÷���var w=NodeW.one(html); 
				return new NodeW(create(core,false,arg1));
			}
			else{//�÷���var w=NodeW(sSelector);
				return new NodeW(one(arg1,core)[0]);
			}
		}
		else {
			core=g(core,arg1);
			if(isArray(core)){//�÷���var w=NodeW.one(array); 
				return new NodeW(core[0]);
			}
			else{
				return new NodeW(core);//�÷���var w=NodeW.one(element); 
			}
		}
	}

	/** 
	* ��NodeW��ֲ��һ�����Node��Helper
	* @method	pluginHelper
	* @static
	* @param	{helper} helper ������һ�����Node��Ԫ�أ���Helper	
	* @param	{string|json} wrapConfig	wrap����
	* @param	{json} gsetterConfig	(Optional) gsetter ����
	* @return	{NodeW}	
	*/

	NodeW.pluginHelper =function (helper, wrapConfig, gsetterConfig) {
		var HelperH=QW.HelperH;

		helper=HelperH.mul(helper,wrapConfig);	//֧�ֵ�һ������Ϊarray
		
		var st=HelperH.rwrap(helper,NodeW,wrapConfig);	//�Է���ֵ���а�װ����
		if(gsetterConfig) st = HelperH.gsetter(st,gsetterConfig); //�����gsetter����Ҫ�Ա�̬����gsetter��

		mix(NodeW, st);	//Ӧ����NodeW�ľ�̬����

		var pro=HelperH.methodize(helper,'core');
		pro = HelperH.rwrap(pro,NodeW,wrapConfig);
		if(gsetterConfig) pro = HelperH.gsetter(pro,gsetterConfig);

		mix(NodeW.prototype,pro);
	};

	mix(NodeW.prototype,{
		/** 
		* ����NodeW�ĵ�0��Ԫ�صİ�װ
		* @method	first
		* @return	{NodeW}	
		*/
		first:function(){
			return NodeW(this[0]);
		},
		/** 
		* ����NodeW�����һ��Ԫ�صİ�װ
		* @method	last
		* @return	{NodeW}	
		*/
		last:function(){
			return NodeW(this[this.length-1]);
		},
		/** 
		* ����NodeW�ĵ�i��Ԫ�صİ�װ
		* @method	last
		* @param {int}	i ��i��Ԫ��
		* @return	{NodeW}	
		*/
		item:function(i){
			return NodeW(this[i]);
		}
	});

	QW.NodeW=NodeW;
})();

