/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	version: $version$ $release$ released
	author: quguangyu
*/

/**
 *@class Anim ����
 *@namespace QW
 */

(function(){
	var CustEvent=QW.CustEvent,
		mix=QW.ObjectH.mix,
		contains=QW.ArrayH.contains;

	/**
	* @class Anim ����
	* @namespace QW
	* @constructor
	* @param {function} animFun - ������Ч���ıհ�
	* @param {int} dur - ����Ч��������ʱ�� 
	* @param {json|object|else} opts - ���������� 
		---Ŀǰֻ֧�����²�����
		{boolean} byStep: �Ƿ�֡��������"����֡"�������Ϊtrue����ʾÿһ֡���ߵ���֡��Ϊdur/28
	* @return {Anim} anim - ��������
	
	*/
	var Anim=function(animFun,dur,opts){
		mix(this,{
			animFun:animFun, //animFun������������
			dur:dur, //����ʱ��
			status:0, //0��δ���ţ�1�������У�2�����Ž�����4������ͣ��8������ֹ
			startDate:0,
			costDur:0,
			byStep:!!(opts && opts.byStep)
		});
		CustEvent.createEvents(this,Anim.EVENTS);
	};

	Anim.EVENTS = 'beforeplay,play,step,pause,resume,stop,suspend,reset'.split(',');
	Anim.STEP_TIME = 28;

	/*
	* ����������
	*/
	var animInterval=0;
	/*
	* ���ڲ��ŵĶ�������
	*/
	var playingAnims=[];
	/*
	*���������������ڲ��ŵĶ��� 
	*/
	var stepAll=function(){
		for(var i=0;i<playingAnims.length;i++){
			var ef=playingAnims[i];
			if(ef.status!=1){
				playingAnims.splice(i,1);
				i--;
				continue;
			}
			ef.step();
		}
		if(!playingAnims.length){
			window.clearInterval(animInterval);
			animInterval=0;
		}
	};

	var turnOn=function(ef){
		if(ef) {
			ef.step();
			if(!contains(playingAnims,ef)) playingAnims.push(ef);
		}
		if(!animInterval) {
			animInterval=window.setInterval(stepAll,Anim.STEP_TIME);
		}
	};

	mix(Anim.prototype,{
		/**
		 *play: ��ʼ���� 
		 *@return {boolean}: �Ƿ�ʼ˳����ʼ������Ϊonbeforeplay�п�����ֹ��play�� 
		 */
		play: function(){
			var me=this;
			if(contains(playingAnims,me)) me.stop();
			if(!me.fire('beforeplay')) return false;
			me.startDate=new Date();
			if(me.byStep){
				me.currentStep=0;
				me.totalStep=me.dur/Anim.STEP_TIME;
			}
			me.status=1;
			turnOn(me);
			me.fire('play');
			return true;
		},
		/**
		 *�ж��Ƿ����ڲ���
		 */
		isPlaying: function(){
			return this.status==1;
		},
		/**
		 *����һ֡ 
		 */
		step: function(){
			var me=this;
			if(me.byStep){
				var per=me.currentStep++/me.totalStep;
			}
			else{
				per=(new Date()-me.startDate)/me.dur;
			}
			if(per>=1) {
				this.suspend();
			}
			else {
				me.animFun(per);
				me.fire('step');
			}
		},

		/**
		 *ֹͣ���� 
		 */
		stop: function(){
			this.startDate=0;
			this.costDur=0;
			this.status=8;
			this.fire('stop');
		},

		/**
		 *���ŵ���� 
		 */
		suspend: function(){
			this.animFun(1);
			this.status=2;
			this.fire('suspend');
		},

		/**
		 *��ͣ���� 
		 */
		pause: function(){
			this.costDur=new Date()-this.startDate;
			this.status=4;
			this.fire('pause');
		},

		/**
		 *�������� 
		 */
		resume: function(){
			if(this.status==4) {
				this.startDate=new Date()-this.costDur;
				this.status=1;
				this.fire('resume');
				turnOn(this);
			}
		},
		/**
		 *���ŵ��ʼ 
		 */
		reset: function(){
			this.startDate=new Date();
			this.costDur=0;
			this.currentStep=0;
			this.animFun(0);
			this.fire('reset');
		}
	});
	QW.provide('Anim',Anim);
})();

