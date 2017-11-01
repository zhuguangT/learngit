/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	version: $version$ $release$ released
	author: quguangyu
*/

/**
 *@class Anim 动画
 *@namespace QW
 */

(function(){
	var CustEvent=QW.CustEvent,
		mix=QW.ObjectH.mix,
		contains=QW.ArrayH.contains;

	/**
	* @class Anim 动画
	* @namespace QW
	* @constructor
	* @param {function} animFun - 管理动画效果的闭包
	* @param {int} dur - 动画效果持续的时间 
	* @param {json|object|else} opts - 其它参数， 
		---目前只支持以下参数：
		{boolean} byStep: 是否按帧动画（即"不跳帧"）。如果为true，表示每一帧都走到，帧数为dur/28
	* @return {Anim} anim - 动画对象
	
	*/
	var Anim=function(animFun,dur,opts){
		mix(this,{
			animFun:animFun, //animFun，动画函数，
			dur:dur, //动画时长
			status:0, //0－未播放，1－播放中，2－播放结束，4－被暂停，8－被终止
			startDate:0,
			costDur:0,
			byStep:!!(opts && opts.byStep)
		});
		CustEvent.createEvents(this,Anim.EVENTS);
	};

	Anim.EVENTS = 'beforeplay,play,step,pause,resume,stop,suspend,reset'.split(',');
	Anim.STEP_TIME = 28;

	/*
	* 动画播放器
	*/
	var animInterval=0;
	/*
	* 正在播放的动画集合
	*/
	var playingAnims=[];
	/*
	*按步播放所有正在播放的动画 
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
		 *play: 开始播放 
		 *@return {boolean}: 是否开始顺利开始。（因为onbeforeplay有可能阻止了play） 
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
		 *判断是否正在播放
		 */
		isPlaying: function(){
			return this.status==1;
		},
		/**
		 *播放一帧 
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
		 *停止播放 
		 */
		stop: function(){
			this.startDate=0;
			this.costDur=0;
			this.status=8;
			this.fire('stop');
		},

		/**
		 *播放到最后 
		 */
		suspend: function(){
			this.animFun(1);
			this.status=2;
			this.fire('suspend');
		},

		/**
		 *暂停播放 
		 */
		pause: function(){
			this.costDur=new Date()-this.startDate;
			this.status=4;
			this.fire('pause');
		},

		/**
		 *继续播放 
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
		 *播放到最开始 
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

