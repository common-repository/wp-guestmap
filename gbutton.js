//
/*-----------------------------------*/
/*             类GButton             */
/*-----------------------------------*/
//本类目前尚不完善，仅添加了必要的功能
var GButton = function(b){
	this.listeners = [];
	//监听事件
	if(!!b.callback) this.listeners.push(GEvent.addListener(this,"click",b.callback));
	
	this.div=document.createElement("div");
	this.caption = b.caption;
    with(this.div){
		unselectable="on";
		onselectstart=function(){
			return false;
    	};
		innerHTML=this.caption;
		
		with (style){
			if(!!b.style){
				style.cssText = b.style;
			}else{
				cursor=b.cursor||"pointer";
				position=b.position||"relative";
				fontSize=b.fontSize||"12px";
				fontFamily=b.fontFamily||"Arial, sans serif";
				border=b.border||"solid 1px black";//border-style:solid;border-width:1px;border-color:black;
				textAlign=b.textAlign||"center";
				padding=b.padding||"3px";
				margin=b.margin||"0px";
				color=b.color||"black";
				background=b.background||"white";
				width=b.width||"60px";
				b.height?(height=b.height):null;
			}
			MozUserSelect="none";
		}
	};
}

GButton.prototype=new GControl();

GButton.prototype.initialize=function(a){
    a.getContainer().appendChild(this.div);
    GEvent.bindDom(this.div,"click",this,this.onClick);
    return this.div;
}

GButton.prototype.onClick=function(){
    GEvent.trigger(this,"click");
}

GButton.prototype.addCallback=function(action){
    this.listeners.push(GEvent.addListener(this,"click",action));
}
	
GButton.prototype.remove=function(a){
    a.removeControl(this);
}

GButton.prototype.show=function(){
	this.div.style.display = "block";
}

GButton.prototype.hide=function(){
	this.div.style.display = "none";
}

GButton.prototype.setCaption=function(caption){
	this.caption = caption;
	this.div.innerHTML = this.caption;
}

GButton.prototype.getDefaultPosition=function(){
	return new GControlPosition(G_ANCHOR_TOP_RIGHT,new GSize(6,6))
}
