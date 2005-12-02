/* Copyright (c) 2004-2005 The Dojo Foundation, Licensed under the Academic Free License version 2.1 or above */var dj_global=this;
function dj_undef(_1,_2){
if(!_2){
_2=dj_global;
}
return (typeof _2[_1]=="undefined");
}
function dj_eval_object_path(_3,_4){
if(typeof _3!="string"){
return dj_global;
}
if(_3.indexOf(".")==-1){
return dj_undef(_3)?undefined:dj_global[_3];
}
var _5=_3.split(/\./);
var _6=dj_global;
for(var i=0;i<_5.length;++i){
if(!_4){
_6=_6[_5[i]];
if((typeof _6=="undefined")||(!_6)){
return _6;
}
}else{
if(dj_undef(_5[i],_6)){
_6[_5[i]]={};
}
_6=_6[_5[i]];
}
}
return _6;
}
if(dj_undef("djConfig")){
var djConfig={};
}
var dojo;
if(dj_undef("dojo")){
dojo={};
}
dojo.version={major:0,minor:1,patch:0,revision:Number("$Rev: 1321 $".match(/[0-9]+/)[0]),toString:function(){
var v=dojo.version;
return v.major+"."+v.minor+"."+v.patch+" ("+v.revision+")";
}};
function dj_error_to_string(_9){
return ((!dj_undef("message",_9))?_9.message:(dj_undef("description",_9)?_9:_9.description));
}
function dj_debug(){
var _10=arguments;
if(dj_undef("println",dojo.hostenv)){
dj_throw("dj_debug not available (yet?)");
}
if(!dojo.hostenv.is_debug_){
return;
}
var _11=dj_global["jum"];
var s=_11?"":"DEBUG: ";
for(var i=0;i<_10.length;++i){
if(!false&&_10[i] instanceof Error){
var msg="["+_10[i].name+": "+dj_error_to_string(_10[i])+(_10[i].fileName?", file: "+_10[i].fileName:"")+(_10[i].lineNumber?", line: "+_10[i].lineNumber:"")+"]";
}else{
var msg=_10[i];
}
s+=msg+" ";
}
if(_11){
jum.debug(s);
}else{
dojo.hostenv.println(s);
}
}
function dj_throw(_14){
var he=dojo.hostenv;
if(dj_undef("hostenv",dojo)&&dj_undef("println",dojo)){
dojo.hostenv.println("FATAL: "+_14);
}
throw Error(_14);
}
function dj_rethrow(_16,_17){
var _18=dj_error_to_string(_17);
dj_throw(_16+": "+_18);
}
function dj_eval(s){
return dj_global.eval?dj_global.eval(s):eval(s);
}
function dj_unimplemented(_19,_20){
var _21="'"+_19+"' not implemented";
if((typeof _20!="undefined")&&(_20)){
_21+=" "+_20;
}
dj_throw(_21);
}
function dj_deprecated(_22,_23){
var _24="DEPRECATED: "+_22;
if((typeof _23!="undefined")&&(_23)){
_24+=" "+_23;
}
dj_debug(_24);
}
function dj_inherits(_25,_26){
if(typeof _26!="function"){
dj_throw("superclass: "+_26+" borken");
}
_25.prototype=new _26();
_25.prototype.constructor=_25;
_25.superclass=_26.prototype;
_25["super"]=_26.prototype;
}
dojo.render={name:"",ver:dojo.version,os:{win:false,linux:false,osx:false},html:{capable:false,support:{builtin:false,plugin:false},ie:false,opera:false,khtml:false,safari:false,moz:false,prefixes:["html"]},svg:{capable:false,support:{builtin:false,plugin:false},corel:false,adobe:false,batik:false,prefixes:["svg"]},swf:{capable:false,support:{builtin:false,plugin:false},mm:false,prefixes:["Swf","Flash","Mm"]},swt:{capable:false,support:{builtin:false,plugin:false},ibm:false,prefixes:["Swt"]}};
dojo.hostenv=(function(){
var djc=djConfig;
function _def(obj,_29,def){
return (dj_undef(_29,obj)?def:obj[_29]);
}
return {is_debug_:_def(djc,"isDebug",false),base_script_uri_:_def(djc,"baseScriptUri",undefined),base_relative_path_:_def(djc,"baseRelativePath",""),library_script_uri_:_def(djc,"libraryScriptUri",""),auto_build_widgets_:_def(djc,"parseWidgets",true),ie_prevent_clobber_:_def(djc,"iePreventClobber",false),ie_clobber_minimal_:_def(djc,"ieClobberMinimal",false),name_:"(unset)",version_:"(unset)",pkgFileName:"__package__",loading_modules_:{},loaded_modules_:{},addedToLoadingCount:[],removedFromLoadingCount:[],inFlightCount:0,modulePrefixes_:{dojo:{name:"dojo",value:"src"}},setModulePrefix:function(_31,_32){
this.modulePrefixes_[_31]={name:_31,value:_32};
},getModulePrefix:function(_33){
var mp=this.modulePrefixes_;
if((mp[_33])&&(mp[_33]["name"])){
return mp[_33].value;
}
return _33;
},getTextStack:[],loadUriStack:[],loadedUris:[],modules_:{},modulesLoadedFired:false,modulesLoadedListeners:[],getName:function(){
return this.name_;
},getVersion:function(){
return this.version_;
},getText:function(uri){
dj_unimplemented("getText","uri="+uri);
},getLibraryScriptUri:function(){
dj_unimplemented("getLibraryScriptUri","");
}};
})();
dojo.hostenv.getBaseScriptUri=function(){
if(!dj_undef("base_script_uri_",this)){
return this.base_script_uri_;
}
var uri=this.library_script_uri_;
if(!uri){
uri=this.library_script_uri_=this.getLibraryScriptUri();
if(!uri){
dj_throw("Nothing returned by getLibraryScriptUri(): "+uri);
}
}
var _36=uri.lastIndexOf("/");
this.base_script_uri_=this.base_relative_path_;
return this.base_script_uri_;
};
dojo.hostenv.setBaseScriptUri=function(uri){
this.base_script_uri_=uri;
};
dojo.hostenv.loadPath=function(_37,_38,cb){
if(!_37){
dj_throw("Missing relpath argument");
}
if((_37.charAt(0)=="/")||(_37.match(/^\w+:/))){
dj_throw("relpath '"+_37+"'; must be relative");
}
var uri=this.getBaseScriptUri()+_37;
try{
return ((!_38)?this.loadUri(uri):this.loadUriAndCheck(uri,_38));
}
catch(e){
if(dojo.hostenv.is_debug_){
dj_debug(e);
}
return false;
}
};
dojo.hostenv.loadUri=function(uri,cb){
if(dojo.hostenv.loadedUris[uri]){
return;
}
var _40=this.getText(uri,null,true);
if(_40==null){
return 0;
}
var _41=dj_eval(_40);
return 1;
};
dojo.hostenv.getDepsForEval=function(_42){
if(!_42){
_42="";
}
var _43=[];
var tmp=_42.match(/dojo.hostenv.loadModule\(.*?\)/mg);
if(tmp){
for(var x=0;x<tmp.length;x++){
_43.push(tmp[x]);
}
}
tmp=_42.match(/dojo.hostenv.require\(.*?\)/mg);
if(tmp){
for(var x=0;x<tmp.length;x++){
_43.push(tmp[x]);
}
}
tmp=_42.match(/dojo.require\(.*?\)/mg);
if(tmp){
for(var x=0;x<tmp.length;x++){
_43.push(tmp[x]);
}
}
tmp=_42.match(/dojo.hostenv.conditionalLoadModule\([\w\W]*?\)/gm);
if(tmp){
for(var x=0;x<tmp.length;x++){
_43.push(tmp[x]);
}
}
return _43;
};
dojo.hostenv.loadUriAndCheck=function(uri,_46,cb){
var ok=true;
try{
ok=this.loadUri(uri,cb);
}
catch(e){
dj_debug("failed loading ",uri," with error: ",e);
}
return ((ok)&&(this.findModule(_46,false)))?true:false;
};
dojo.loaded=function(){
};
dojo.hostenv.loaded=function(){
this.modulesLoadedFired=true;
var mll=this.modulesLoadedListeners;
for(var x=0;x<mll.length;x++){
mll[x]();
}
dojo.loaded();
};
dojo.addOnLoad=function(obj,_49){
if(arguments.length==1){
dojo.hostenv.modulesLoadedListeners.push(obj);
}else{
if(arguments.length>1){
dojo.hostenv.modulesLoadedListeners.push(function(){
obj[_49]();
});
}
}
};
dojo.hostenv.modulesLoaded=function(){
if(this.modulesLoadedFired){
return;
}
if((this.loadUriStack.length==0)&&(this.getTextStack.length==0)){
if(this.inFlightCount>0){
dj_debug("couldn't initialize, there are files still in flight");
return;
}
this.loaded();
}
};
dojo.hostenv.moduleLoaded=function(_50){
var _51=dj_eval_object_path((_50.split(".").slice(0,-1)).join("."));
this.loaded_modules_[(new String(_50)).toLowerCase()]=_51;
};
dojo.hostenv.loadModule=function(_52,_53,_54){
var _55=this.findModule(_52,false);
if(_55){
return _55;
}
if(dj_undef(_52,this.loading_modules_)){
this.addedToLoadingCount.push(_52);
}
this.loading_modules_[_52]=1;
var _56=_52.replace(/\./g,"/")+".js";
var _57=_52.split(".");
var _58=_52.split(".");
for(var i=_57.length-1;i>0;i--){
var _59=_57.slice(0,i).join(".");
var _60=this.getModulePrefix(_59);
if(_60!=_59){
_57.splice(0,i,_60);
break;
}
}
var _61=_57[_57.length-1];
if(_61=="*"){
_52=(_58.slice(0,-1)).join(".");
while(_57.length){
_57.pop();
_57.push(this.pkgFileName);
_56=_57.join("/")+".js";
if(_56.charAt(0)=="/"){
_56=_56.slice(1);
}
ok=this.loadPath(_56,((!_54)?_52:null));
if(ok){
break;
}
_57.pop();
}
}else{
_56=_57.join("/")+".js";
_52=_58.join(".");
var ok=this.loadPath(_56,((!_54)?_52:null));
if((!ok)&&(!_53)){
_57.pop();
while(_57.length){
_56=_57.join("/")+".js";
ok=this.loadPath(_56,((!_54)?_52:null));
if(ok){
break;
}
_57.pop();
_56=_57.join("/")+"/"+this.pkgFileName+".js";
if(_56.charAt(0)=="/"){
_56=_56.slice(1);
}
ok=this.loadPath(_56,((!_54)?_52:null));
if(ok){
break;
}
}
}
if((!ok)&&(!_54)){
dj_throw("Could not load '"+_52+"'; last tried '"+_56+"'");
}
}
if(!_54){
_55=this.findModule(_52,false);
if(!_55){
dj_throw("symbol '"+_52+"' is not defined after loading '"+_56+"'");
}
}
return _55;
};
function dj_load(_62,_63){
return dojo.hostenv.loadModule(_62,_63);
}
dojo.hostenv.startPackage=function(_64){
var _65=_64.split(/\./);
if(_65[_65.length-1]=="*"){
_65.pop();
}
return dj_eval_object_path(_65.join("."),true);
};
dojo.hostenv.findModule=function(_66,_67){
if(!dj_undef(_66,this.modules_)){
return this.modules_[_66];
}
if(this.loaded_modules_[(new String(_66)).toLowerCase()]){
return this.loaded_modules_[_66];
}
var _68=dj_eval_object_path(_66);
if((typeof _68!=="undefined")&&(_68)){
return this.modules_[_66]=_68;
}
if(_67){
dj_throw("no loaded module named '"+_66+"'");
}
return null;
};
dj_addNodeEvtHdlr=function(_69,_70,fp,_72){
if(_69.attachEvent){
_69.attachEvent("on"+_70,fp);
}else{
if(_69.addEventListener){
_69.addEventListener(_70,fp,_72);
}else{
var _73=_69["on"+_70];
if(typeof _73!="undefined"){
_69["on"+_70]=function(){
fp.apply(_69,arguments);
_73.apply(_69,arguments);
};
}else{
_69["on"+_70]=fp;
}
}
}
return true;
};
if(typeof window=="undefined"){
dj_throw("no window object");
}
(function(){
if((dojo.hostenv["base_script_uri_"]==""||dojo.hostenv["base_relative_path_"]=="")&&document&&document.getElementsByTagName){
var _74=document.getElementsByTagName("script");
var _75=/(__package__|dojo)\.js$/i;
for(var i=0;i<_74.length;i++){
var src=_74[i].getAttribute("src");
if(_75.test(src)){
var _77=src.replace(_75,"");
if(dojo.hostenv["base_script_uri_"]==""){
dojo.hostenv["base_script_uri_"]=_77;
}
if(dojo.hostenv["base_relative_path_"]==""){
dojo.hostenv["base_relative_path_"]=_77;
}
break;
}
}
}
})();
with(dojo.render){
html.UA=navigator.userAgent;
html.AV=navigator.appVersion;
html.capable=true;
html.support.builtin=true;
ver=parseFloat(html.AV);
os.mac=html.AV.indexOf("Macintosh")==-1?false:true;
os.win=html.AV.indexOf("Windows")==-1?false:true;
html.opera=html.UA.indexOf("Opera")==-1?false:true;
html.khtml=((html.AV.indexOf("Konqueror")>=0)||(html.AV.indexOf("Safari")>=0))?true:false;
html.safari=(html.AV.indexOf("Safari")>=0)?true:false;
html.mozilla=html.moz=((html.UA.indexOf("Gecko")>=0)&&(!html.khtml))?true:false;
html.ie=((document.all)&&(!html.opera))?true:false;
html.ie50=html.ie&&html.AV.indexOf("MSIE 5.0")>=0;
html.ie55=html.ie&&html.AV.indexOf("MSIE 5.5")>=0;
html.ie60=html.ie&&html.AV.indexOf("MSIE 6.0")>=0;
}
dojo.hostenv.startPackage("dojo.hostenv");
dojo.hostenv.name_="browser";
dojo.hostenv.searchIds=[];
var DJ_XMLHTTP_PROGIDS=["Msxml2.XMLHTTP","Microsoft.XMLHTTP","Msxml2.XMLHTTP.4.0"];
dojo.hostenv.getXmlhttpObject=function(){
var _78=null;
var _79=null;
try{
_78=new XMLHttpRequest();
}
catch(e){
}
if(!_78){
for(var i=0;i<3;++i){
var _80=DJ_XMLHTTP_PROGIDS[i];
try{
_78=new ActiveXObject(_80);
}
catch(e){
_79=e;
}
if(_78){
DJ_XMLHTTP_PROGIDS=[_80];
break;
}
}
}
if((_79)&&(!_78)){
dj_rethrow("Could not create a new ActiveXObject using any of the progids "+DJ_XMLHTTP_PROGIDS.join(", "),_79);
}else{
if(!_78){
return dj_throw("No XMLHTTP implementation available, for uri "+uri);
}
}
return _78;
};
dojo.hostenv.getText=function(uri,_81,_82){
var _83=this.getXmlhttpObject();
if(_81){
_83.onreadystatechange=function(){
if((4==_83.readyState)&&(_83["status"])){
if(_83.status==200){
dj_debug("LOADED URI: "+uri);
_81(_83.responseText);
}
}
};
}
_83.open("GET",uri,_81?true:false);
_83.send(null);
if(_81){
return null;
}
return _83.responseText;
};
function dj_last_script_src(){
var _84=window.document.getElementsByTagName("script");
if(_84.length<1){
dj_throw("No script elements in window.document, so can't figure out my script src");
}
var _85=_84[_84.length-1];
var src=_85.src;
if(!src){
dj_throw("Last script element (out of "+_84.length+") has no src");
}
return src;
}
if(!dojo.hostenv["library_script_uri_"]){
dojo.hostenv.library_script_uri_=dj_last_script_src();
}
dojo.hostenv.println=function(s){
var ti=null;
var dis="<div>"+s+"</div>";
try{
ti=document.createElement("div");
document.body.appendChild(ti);
ti.innerHTML=s;
}
catch(e){
try{
document.write(dis);
}
catch(e2){
window.status=s;
}
}
delete ti;
delete dis;
delete s;
};
dj_addNodeEvtHdlr(window,"load",function(){
if(dojo.render.html.ie){
dojo.hostenv.makeWidgets();
}
dojo.hostenv.modulesLoaded();
});
dojo.hostenv.makeWidgets=function(){
if((dojo.hostenv.auto_build_widgets_)||(dojo.hostenv.searchIds.length>0)){
if(dj_eval_object_path("dojo.widget.Parse")){
try{
var _88=new dojo.xml.Parse();
var _89=dojo.hostenv.searchIds;
if(_89.length>0){
for(var x=0;x<_89.length;x++){
if(!document.getElementById(_89[x])){
continue;
}
var _90=_88.parseElement(document.getElementById(_89[x]),null,true);
dojo.widget.getParser().createComponents(_90);
}
}else{
if(dojo.hostenv.auto_build_widgets_){
var _90=_88.parseElement(document.body,null,true);
dojo.widget.getParser().createComponents(_90);
}
}
}
catch(e){
dj_debug("auto-build-widgets error:",e);
}
}
}
};
dojo.hostenv.modulesLoadedListeners.push(function(){
if(!dojo.render.html.ie){
dojo.hostenv.makeWidgets();
}
});
if((!window["djConfig"])||(!window["djConfig"]["preventBackButtonFix"])){
document.write("<iframe style='border: 0px; width: 1px; height: 1px; position: absolute; bottom: 0px; right: 0px; visibility: visible;' name='djhistory' id='djhistory' src='"+(dojo.hostenv.getBaseScriptUri()+"iframe_history.html")+"'></iframe>");
}
dojo.hostenv.writeIncludes=function(){
};
dojo.hostenv.conditionalLoadModule=function(_91){
var _92=_91["common"]||[];
var _93=(_91[dojo.hostenv.name_])?_92.concat(_91[dojo.hostenv.name_]||[]):_92.concat(_91["default"]||[]);
for(var x=0;x<_93.length;x++){
var _94=_93[x];
if(_94.constructor==Array){
dojo.hostenv.loadModule.apply(dojo.hostenv,_94);
}else{
dojo.hostenv.loadModule(_94);
}
}
};
dojo.hostenv.require=dojo.hostenv.loadModule;
dojo.require=function(){
dojo.hostenv.loadModule.apply(dojo.hostenv,arguments);
};
dojo.requireIf=function(){
if((arguments[0]=="common")||(dojo.render[arguments[0]].capable)){
dojo.require(arguments[1],arguments[2],arguments[3]);
}
};
dojo.conditionalRequire=dojo.requireIf;
dojo.kwCompoundRequire=function(){
dojo.hostenv.conditionalLoadModule.apply(dojo.hostenv,arguments);
};
dojo.hostenv.provide=dojo.hostenv.startPackage;
dojo.provide=function(){
dojo.hostenv.startPackage.apply(dojo.hostenv,arguments);
};
dojo.provide("dojo.alg.Alg");
dojo.alg.find=function(arr,val){
for(var i=0;i<arr.length;++i){
if(arr[i]==val){
return i;
}
}
return -1;
};
dojo.alg.inArray=function(arr,val){
if((!arr||arr.constructor!=Array)&&(val&&val.constructor==Array)){
var a=arr;
arr=val;
val=a;
}
return dojo.alg.find(arr,val)>-1;
};
dojo.alg.inArr=dojo.alg.inArray;
dojo.alg.getNameInObj=function(ns,_99){
if(!ns){
ns=dj_global;
}
for(var x in ns){
if(ns[x]===_99){
return new String(x);
}
}
return null;
};
dojo.alg.has=function(obj,name){
return (typeof obj[name]!=="undefined");
};
dojo.alg.forEach=function(arr,_101,_102){
var il=arr.length;
for(var i=0;i<((_102)?il:arr.length);i++){
if(_101(arr[i])=="break"){
break;
}
}
};
dojo.alg.for_each=dojo.alg.forEach;
dojo.alg.map=function(arr,obj,_104){
for(var i=0;i<arr.length;++i){
_104.call(obj,arr[i]);
}
};
dojo.alg.tryThese=function(){
for(var x=0;x<arguments.length;x++){
try{
if(typeof arguments[x]=="function"){
var ret=(arguments[x]());
if(ret){
return ret;
}
}
}
catch(e){
dj_debug(e);
}
}
};
dojo.alg.delayThese=function(farr,cb,_107,_108){
if(!farr.length){
if(typeof _108=="function"){
_108();
}
return;
}
if((typeof _107=="undefined")&&(typeof cb=="number")){
_107=cb;
cb=function(){
};
}else{
if(!cb){
cb=function(){
};
}
}
setTimeout(function(){
(farr.shift())();
cb();
dojo.alg.delayThese(farr,cb,_107,_108);
},_107);
};
dojo.alg.for_each_call=dojo.alg.map;
dojo.require("dojo.alg.Alg",false,true);
dojo.hostenv.moduleLoaded("dojo.alg.*");
dojo.provide("dojo.lang.Lang");
dojo.lang.mixin=function(obj,_109){
var tobj={};
for(var x in _109){
if(typeof tobj[x]=="undefined"){
obj[x]=_109[x];
}
}
return obj;
};
dojo.lang.extend=function(ctor,_112){
this.mixin(ctor.prototype,_112);
};
dojo.lang.extendPrototype=function(obj,_113){
this.extend(obj.constructor,_113);
};
dojo.lang.setTimeout=function(func,_115){
var _116=window,argsStart=2;
if(typeof _115=="function"){
_116=func;
func=_115;
_115=arguments[2];
argsStart++;
}
var args=[];
for(var i=argsStart;i<arguments.length;i++){
args.push(arguments[i]);
}
return setTimeout(function(){
func.apply(_116,args);
},_115);
};
dojo.lang.mixin(dojo.lang,{isObject:function(wh){
return typeof wh=="object"||dojo.lang.isArray(wh)||dojo.lang.isFunction(wh);
},isArray:function(wh){
return (wh instanceof Array||typeof wh=="array");
},isFunction:function(wh){
return (wh instanceof Function||typeof wh=="function");
},isString:function(wh){
return (wh instanceof String||typeof wh=="string");
},isNumber:function(wh){
return (wh instanceof Number||typeof wh=="number");
},isBoolean:function(wh){
return (wh instanceof Boolean||typeof wh=="boolean");
},isUndefined:function(wh){
return wh==undefined;
}});
dojo.hostenv.conditionalLoadModule({common:["dojo.lang.Lang"]});
dojo.hostenv.moduleLoaded("dojo.lang.*");
dojo.require("dojo.alg.*");
dojo.provide("dojo.event.Event");
dojo.event=new function(){
var _119=0;
this.anon={};
this.nameAnonFunc=function(_120,_121){
var nso=(_121||this.anon);
if((dj_global["djConfig"])&&(djConfig["slowAnonFuncLookups"]==true)){
for(var x in nso){
if(nso[x]===_120){
dj_debug(x);
return x;
}
}
}
var ret="_"+_119++;
while(typeof nso[ret]!="undefined"){
ret="_"+_119++;
}
nso[ret]=_120;
return ret;
};
this.createFunctionPair=function(obj,cb){
var ret=[];
if(typeof obj=="function"){
ret[1]=dojo.event.nameAnonFunc(obj,dj_global);
ret[0]=dj_global;
return ret;
}else{
if((typeof obj=="object")&&(typeof cb=="string")){
return [obj,cb];
}else{
if((typeof obj=="object")&&(typeof cb=="function")){
ret[1]=dojo.event.nameAnonFunc(cb,obj);
ret[0]=obj;
return ret;
}
}
}
return null;
};
this.matchSignature=function(args,_123){
var end=Math.min(args.length,_123.length);
for(var x=0;x<end;x++){
if(compareTypes){
if((typeof args[x]).toLowerCase()!=(typeof _123[x])){
return false;
}
}else{
if((typeof args[x]).toLowerCase()!=_123[x].toLowerCase()){
return false;
}
}
}
return true;
};
this.matchSignatureSets=function(args){
for(var x=1;x<arguments.length;x++){
if(this.matchSignature(args,arguments[x])){
return true;
}
}
return false;
};
function interpolateArgs(args){
var ao={srcObj:dj_global,srcFunc:null,adviceObj:dj_global,adviceFunc:null,aroundObj:null,aroundFunc:null,adviceType:(args.length>2)?args[0]:"after",precedence:"last",once:false,delay:null};
switch(args.length){
case 0:
return;
case 1:
return;
case 2:
ao.srcFunc=args[0];
ao.adviceFunc=args[1];
break;
case 3:
if((typeof args[0]=="object")&&(typeof args[1]=="string")&&(typeof args[2]=="string")){
ao.adviceType="after";
ao.srcObj=args[0];
ao.srcFunc=args[1];
ao.adviceFunc=args[2];
}else{
if((typeof args[1]=="string")&&(typeof args[2]=="string")){
ao.srcFunc=args[1];
ao.adviceFunc=args[2];
}else{
if((typeof args[0]=="object")&&(typeof args[1]=="string")&&(typeof args[2]=="function")){
ao.adviceType="after";
ao.srcObj=args[0];
ao.srcFunc=args[1];
var _126=dojo.event.nameAnonFunc(args[2],ao.adviceObj);
ao.adviceObj[_126]=args[2];
ao.adviceFunc=_126;
}else{
if((typeof args[0]=="function")&&(typeof args[1]=="object")&&(typeof args[2]=="string")){
ao.adviceType="after";
ao.srcObj=dj_global;
var _126=dojo.event.nameAnonFunc(args[0],ao.srcObj);
ao.srcObj[_126]=args[0];
ao.srcFunc=_126;
ao.adviceObj=args[1];
ao.adviceFunc=args[2];
}
}
}
}
break;
case 4:
if((typeof args[0]=="object")&&(typeof args[2]=="object")){
ao.adviceType="after";
ao.srcObj=args[0];
ao.srcFunc=args[1];
ao.adviceObj=args[2];
ao.adviceFunc=args[3];
}else{
if((typeof args[1]).toLowerCase()=="object"){
ao.srcObj=args[1];
ao.srcFunc=args[2];
ao.adviceObj=dj_global;
ao.adviceFunc=args[3];
}else{
if((typeof args[2]).toLowerCase()=="object"){
ao.srcObj=dj_global;
ao.srcFunc=args[1];
ao.adviceObj=args[2];
ao.adviceFunc=args[3];
}else{
ao.srcObj=ao.adviceObj=ao.aroundObj=dj_global;
ao.srcFunc=args[1];
ao.adviceFunc=args[2];
ao.aroundFunc=args[3];
}
}
}
break;
case 6:
ao.srcObj=args[1];
ao.srcFunc=args[2];
ao.adviceObj=args[3];
ao.adviceFunc=args[4];
ao.aroundFunc=args[5];
ao.aroundObj=dj_global;
break;
default:
ao.srcObj=args[1];
ao.srcFunc=args[2];
ao.adviceObj=args[3];
ao.adviceFunc=args[4];
ao.aroundObj=args[5];
ao.aroundFunc=args[6];
ao.once=args[7];
ao.delay=args[8];
break;
}
if((typeof ao.srcFunc).toLowerCase()!="string"){
ao.srcFunc=dojo.alg.getNameInObj(ao.srcObj,ao.srcFunc);
}
if((typeof ao.adviceFunc).toLowerCase()!="string"){
ao.adviceFunc=dojo.alg.getNameInObj(ao.adviceObj,ao.adviceFunc);
}
if((ao.aroundObj)&&((typeof ao.aroundFunc).toLowerCase()!="string")){
ao.aroundFunc=dojo.alg.getNameInObj(ao.aroundObj,ao.aroundFunc);
}
if(!ao.srcObj){
dj_throw("bad srcObj for srcFunc: "+ao.srcFunc);
}
if(!ao.adviceObj){
dj_throw("bad srcObj for srcFunc: "+ao.adviceFunc);
}
return ao;
}
this.connect=function(){
var ao=interpolateArgs(arguments);
var mjp=dojo.event.MethodJoinPoint.getForMethod(ao.srcObj,ao.srcFunc);
if(ao.adviceFunc){
var mjp2=dojo.event.MethodJoinPoint.getForMethod(ao.adviceObj,ao.adviceFunc);
}
mjp.kwAddAdvice(ao);
return mjp;
};
this.connectBefore=function(){
var args=["before"];
for(var i=0;i<arguments.length;i++){
args.push(arguments[i]);
}
return this.connect.apply(this,args);
};
this.connectAround=function(){
var args=["around"];
for(var i=0;i<arguments.length;i++){
args.push(arguments[i]);
}
return this.connect.apply(this,args);
};
this.kwConnectImpl_=function(_129,_130){
var fn=(_130)?"disconnect":"connect";
if(typeof _129["srcFunc"]=="function"){
_129.srcObj=_129["srcObj"]||dj_global;
var _132=dojo.event.nameAnonFunc(_129.srcFunc,_129.srcObj);
_129.srcFunc=_132;
}
if(typeof _129["adviceFunc"]=="function"){
_129.adviceObj=_129["adviceObj"]||dj_global;
var _132=dojo.event.nameAnonFunc(_129.adviceFunc,_129.adviceObj);
_129.adviceFunc=_132;
}
return dojo.event[fn]((_129["type"]||_129["adviceType"]||"after"),_129["srcObj"],_129["srcFunc"],_129["adviceObj"]||_129["targetObj"],_129["adviceFunc"]||_129["targetFunc"],_129["aroundObj"],_129["aroundFunc"],_129["once"],_129["delay"]);
};
this.kwConnect=function(_133){
return this.kwConnectImpl_(_133,false);
};
this.disconnect=function(){
var ao=interpolateArgs(arguments);
if(!ao.adviceFunc){
return;
}
var mjp=dojo.event.MethodJoinPoint.getForMethod(ao.srcObj,ao.srcFunc);
return mjp.removeAdvice(ao.adviceObj,ao.adviceFunc,ao.adviceType,ao.once);
};
this.kwDisconnect=function(_134){
return this.kwConnectImpl_(_134,true);
};
};
dojo.event.MethodInvocation=function(_135,obj,args){
this.jp_=_135;
this.object=obj;
this.args=[];
for(var x=0;x<args.length;x++){
this.args[x]=args[x];
}
this.around_index=-1;
};
dojo.event.MethodInvocation.prototype.proceed=function(){
this.around_index++;
if(this.around_index>=this.jp_.around.length){
return this.jp_.object[this.jp_.methodname].apply(this.jp_.object,this.args);
}else{
var ti=this.jp_.around[this.around_index];
var mobj=ti[0]||dj_global;
var meth=ti[1];
return mobj[meth].call(mobj,this);
}
};
dojo.event.MethodJoinPoint=function(obj,_138){
this.object=obj||dj_global;
this.methodname=_138;
this.methodfunc=this.object[_138];
this.before=[];
this.after=[];
this.around=[];
};
dojo.event.MethodJoinPoint.getForMethod=function(obj,_139){
if(!obj){
obj=dj_global;
}
if(!obj[_139]){
obj[_139]=function(){
};
}else{
if(typeof obj[_139]!="function"){
return null;
}
}
var _140=_139+"$joinpoint";
var _141=_139+"$joinpoint$method";
var _142=obj[_140];
if(!_142){
var _143=false;
if(dojo.event["browser"]){
if((obj["attachEvent"])||(obj["nodeType"])||(obj["addEventListener"])){
_143=true;
dojo.event.browser.addClobberAttrs(_140,_141,_139);
dojo.event.browser.addClobberNode(obj);
}
}
obj[_141]=obj[_139];
_142=obj[_140]=new dojo.event.MethodJoinPoint(obj,_141);
obj[_139]=function(){
var args=[];
if((_143)&&(!arguments.length)&&(window.event)){
args.push(dojo.event.browser.fixEvent(window.event));
}else{
for(var x=0;x<arguments.length;x++){
if((x==0)&&(_143)&&(typeof Event!="undefined")&&(arguments[x] instanceof Event)){
args.push(dojo.event.browser.fixEvent(arguments[x]));
}else{
args.push(arguments[x]);
}
}
}
return _142.run.apply(_142,args);
};
}
return _142;
};
dojo.event.MethodJoinPoint.prototype.unintercept=function(){
this.object[this.methodname]=this.methodfunc;
};
dojo.event.MethodJoinPoint.prototype.run=function(){
var obj=this.object||dj_global;
var args=arguments;
var _144=[];
for(var x=0;x<args.length;x++){
_144[x]=args[x];
}
var _145=function(marr){
var _147=marr[0]||dj_global;
var _148=marr[1];
if(!_147[_148]){
throw new Error("function \""+_148+"\" does not exist on \""+_147+"\"");
}
var _149=marr[2]||dj_global;
var _150=marr[3];
var _151;
var _152=parseInt(marr[4]);
var _153=((!isNaN(_152))&&(marr[4]!==null)&&(typeof marr[4]!="undefined"));
var to={args:[],jp_:this,object:obj,proceed:function(){
return _147[_148].apply(_147,to.args);
}};
to.args=_144;
if(_150){
_149[_150].call(_149,to);
}else{
if((_153)&&((dojo.render.html)||(dojo.render.svg))){
dj_global["setTimeout"](function(){
_147[_148].apply(_147,args);
},_152);
}else{
_147[_148].apply(_147,args);
}
}
};
if(this.before.length>0){
dojo.alg.forEach(this.before,_145,true);
}
var _155;
if(this.around.length>0){
var mi=new dojo.event.MethodInvocation(this,obj,args);
_155=mi.proceed();
}else{
if(this.methodfunc){
_155=this.object[this.methodname].apply(this.object,args);
}
}
if(this.after.length>0){
dojo.alg.forEach(this.after,_145,true);
}
return (this.methodfunc)?_155:null;
};
dojo.event.MethodJoinPoint.prototype.getArr=function(kind){
var arr=this.after;
if((typeof kind=="string")&&(kind.indexOf("before")!=-1)){
arr=this.before;
}else{
if(kind=="around"){
arr=this.around;
}
}
return arr;
};
dojo.event.MethodJoinPoint.prototype.kwAddAdvice=function(args){
this.addAdvice(args["adviceObj"],args["adviceFunc"],args["aroundObj"],args["aroundFunc"],args["adviceType"],args["precedence"],args["once"],args["delay"]);
};
dojo.event.MethodJoinPoint.prototype.addAdvice=function(_158,_159,_160,_161,_162,_163,once,_165){
var arr=this.getArr(_162);
if(!arr){
dj_throw("bad this: "+this);
}
var ao=[_158,_159,_160,_161,_165];
if(once){
if(this.hasAdvice(_158,_159,_162,arr)>=0){
return;
}
}
if(_163=="first"){
arr.unshift(ao);
}else{
arr.push(ao);
}
};
dojo.event.MethodJoinPoint.prototype.hasAdvice=function(_166,_167,_168,arr){
if(!arr){
arr=this.getArr(_168);
}
var ind=-1;
for(var x=0;x<arr.length;x++){
if((arr[x][0]==_166)&&(arr[x][1]==_167)){
ind=x;
}
}
return ind;
};
dojo.event.MethodJoinPoint.prototype.removeAdvice=function(_170,_171,_172,once){
var arr=this.getArr(_172);
var ind=this.hasAdvice(_170,_171,_172,arr);
if(ind==-1){
return false;
}
while(ind!=-1){
arr.splice(ind,1);
if(once){
break;
}
ind=this.hasAdvice(_170,_171,_172,arr);
}
return true;
};
dojo.provide("dojo.event.Event");
dojo.provide("dojo.event.Topic");
dojo.require("dojo.event.Event");
dojo.event.Topic={};
dojo.event.topic=new function(){
this.topics={};
this.getTopic=function(_173){
if(!this.topics[_173]){
this.topics[_173]=new this.TopicImpl(_173);
}
return this.topics[_173];
};
this.registerPublisher=function(_174,obj,_175){
var _174=this.getTopic(_174);
_174.registerPublisher(obj,_175);
};
this.subscribe=function(_176,obj,_177){
var _176=this.getTopic(_176);
_176.subscribe(obj,_177);
};
this.unsubscribe=function(_178,obj,_179){
var _178=this.getTopic(_178);
_178.subscribe(obj,_179);
};
this.publish=function(_180,_181){
var _180=this.getTopic(_180);
var args=[];
if((arguments.length==2)&&(_181.length)&&(typeof _181!="string")){
args=_181;
}else{
var args=[];
for(var x=1;x<arguments.length;x++){
args.push(arguments[x]);
}
}
_180.sendMessage.apply(_180,args);
};
};
dojo.event.topic.TopicImpl=function(_182){
this.topicName=_182;
var self=this;
self.subscribe=function(_184,_185){
dojo.event.connect("before",self,"sendMessage",_184,_185);
};
self.unsubscribe=function(_186,_187){
dojo.event.disconnect("before",self,"sendMessage",_186,_187);
};
self.registerPublisher=function(_188,_189){
dojo.event.connect(_188,_189,self,"sendMessage");
};
self.sendMessage=function(_190){
};
};
dojo.provide("dojo.event.BrowserEvent");
dojo.event.browser={};
dojo.require("dojo.event.Event");
dojo_ie_clobber=new function(){
this.clobberArr=["data","onload","onmousedown","onmouseup","onmouseover","onmouseout","onmousemove","onclick","ondblclick","onfocus","onblur","onkeypress","onkeydown","onkeyup","onsubmit","onreset","onselect","onchange","onselectstart","ondragstart","oncontextmenu"];
this.exclusions=[];
this.clobberList={};
this.clobberNodes=[];
this.addClobberAttr=function(type){
if(dojo.render.html.ie){
if(this.clobberList[type]!="set"){
this.clobberArr.push(type);
this.clobberList[type]="set";
}
}
};
this.addExclusionID=function(id){
this.exclusions.push(id);
};
if(dojo.render.html.ie){
for(var x=0;x<this.clobberArr.length;x++){
this.clobberList[this.clobberArr[x]]="set";
}
}
this.clobber=function(_193){
for(var x=0;x<this.exclusions.length;x++){
try{
var tn=document.getElementById(this.exclusions[x]);
tn.parentNode.removeChild(tn);
}
catch(e){
}
}
var na;
if(_193){
var tna=_193.getElementsByTagName("*");
na=[_193];
for(var x=0;x<tna.length;x++){
na.push(tna[x]);
}
}else{
na=(this.clobberNodes.length)?this.clobberNodes:document.all;
}
for(var i=na.length-1;i>=0;i=i-1){
var el=na[i];
for(var p=this.clobberArr.length-1;p>=0;p=p-1){
var ta=this.clobberArr[p];
try{
el[ta]=null;
el.removeAttribute(ta);
delete el[ta];
}
catch(e){
}
}
}
};
};
if((dojo.render.html.ie)&&((!dojo.hostenv.ie_prevent_clobber_)||(dojo.hostenv.ie_clobber_minimal_))){
window.onunload=function(){
dojo_ie_clobber.clobber();
if((dojo["widget"])&&(dojo.widget["manager"])){
dojo.widget.manager.destroyAll();
}
CollectGarbage();
};
}
dojo.event.browser=new function(){
this.clean=function(node){
if(dojo.render.html.ie){
dojo_ie_clobber.clobber(node);
}
};
this.addClobberAttr=function(type){
dojo_ie_clobber.addClobberAttr(type);
};
this.addClobberAttrs=function(){
for(var x=0;x<arguments.length;x++){
this.addClobberAttr(arguments[x]);
}
};
this.addClobberNode=function(node){
if(dojo.hostenv.ie_clobber_minimal_){
if(!node.__doClobber__){
dojo_ie_clobber.clobberNodes.push(node);
node.__doClobber__=true;
}
}
};
this.addListener=function(node,_201,fp,_202){
if(!_202){
var _202=false;
}
_201=_201.toLowerCase();
if(_201.substr(0,2)=="on"){
_201=_201.substr(2);
}
if(!node){
return;
}
var _203=function(evt){
if(!evt){
evt=window.event;
}
var ret=fp(dojo.event.browser.fixEvent(evt));
if(_202){
dojo.event.browser.stopEvent(evt);
}
return ret;
};
var _205="on"+_201;
if(node.addEventListener){
node.addEventListener(_201,_203,_202);
return true;
}else{
if(typeof node[_205]=="function"){
var _206=node[_205];
node[_205]=function(e){
_206(e);
_203(e);
};
}else{
node[_205]=_203;
}
if(dojo.render.html.ie){
this.addClobberAttr(_205);
this.addClobberNode(node);
}
return true;
}
};
this.fixEvent=function(evt){
if(evt.type&&evt.type.indexOf("key")==0){
var keys={KEY_BACKSPACE:8,KEY_TAB:9,KEY_ENTER:13,KEY_SHIFT:16,KEY_CTRL:17,KEY_ALT:18,KEY_PAUSE:19,KEY_CAPS_LOCK:20,KEY_ESCAPE:27,KEY_PAGE_UP:33,KEY_PAGE_DOWN:34,KEY_END:35,KEY_HOME:36,KEY_LEFT_ARROW:37,KEY_UP_ARROW:38,KEY_RIGHT_ARROW:39,KEY_DOWN_ARROW:40,KEY_INSERT:45,KEY_DELETE:46,KEY_LEFT_WINDOW:91,KEY_RIGHT_WINDOW:92,KEY_SELECT:93,KEY_F1:112,KEY_F2:113,KEY_F3:114,KEY_F4:115,KEY_F5:116,KEY_F6:117,KEY_F7:118,KEY_F8:119,KEY_F9:120,KEY_F10:121,KEY_F11:122,KEY_F12:123,KEY_NUM_LOCK:144,KEY_SCROLL_LOCK:145};
evt.keys=[];
for(var key in keys){
evt[key]=keys[key];
evt.keys[keys[key]]=key;
}
if(dojo.render.html.ie&&evt.type=="keypress"){
evt.charCode=evt.keyCode;
}
}
if(dojo.render.html.ie){
if(!evt.target){
evt.target=evt.srcElement;
}
if(!evt.currentTarget){
evt.currentTarget=evt.srcElement;
}
if(!evt.layerX){
evt.layerX=evt.offsetX;
}
if(!evt.layerY){
evt.layerY=evt.offsetY;
}
if(evt.fromElement){
evt.relatedTarget=evt.fromElement;
}
if(evt.toElement){
evt.relatedTarget=evt.toElement;
}
evt.callListener=function(_210,_211){
if(typeof _210!="function"){
dj_throw("listener not a function: "+_210);
}
evt.currentTarget=_211;
var ret=_210.call(_211,evt);
return ret;
};
evt.stopPropagation=function(){
evt.cancelBubble=true;
};
evt.preventDefault=function(){
evt.returnValue=false;
};
}
return evt;
};
this.stopEvent=function(ev){
if(window.event){
ev.returnValue=false;
ev.cancelBubble=true;
}else{
ev.preventDefault();
ev.stopPropagation();
}
};
};
dojo.hostenv.conditionalLoadModule({common:["dojo.event.Event","dojo.event.Topic"],browser:["dojo.event.BrowserEvent"]});
dojo.hostenv.moduleLoaded("dojo.event.*");
dojo.provide("dojo.logging.Logger");
dojo.provide("dojo.log");
dojo.require("dojo.lang.*");
dojo.logging.Record=function(lvl,msg){
this.level=lvl;
this.message=msg;
this.time=new Date();
};
dojo.logging.LogFilter=function(_214){
this.passChain=_214||"";
this.filter=function(_215){
return true;
};
};
dojo.logging.Logger=function(){
this.cutOffLevel=0;
this.propagate=true;
this.parent=null;
this.data=[];
this.filters=[];
this.handlers=[];
};
dojo.lang.extend(dojo.logging.Logger,{argsToArr:function(args){
var ret=[];
for(var x=0;x<args.length;x++){
ret.push(args[x]);
}
return ret;
},setLevel:function(lvl){
this.cutOffLevel=parseInt(lvl);
},isEnabledFor:function(lvl){
return parseInt(lvl)>=this.cutOffLevel;
},getEffectiveLevel:function(){
if((this.cutOffLevel==0)&&(this.parent)){
return this.parent.getEffectiveLevel();
}
return this.cutOffLevel;
},addFilter:function(flt){
this.filters.push(flt);
return this.filters.length-1;
},removeFilterByIndex:function(_217){
if(this.filters[_217]){
delete this.filters[_217];
return true;
}
return false;
},removeFilter:function(_218){
for(var x=0;x<this.filters.length;x++){
if(this.filters[x]===_218){
delete this.filters[x];
return true;
}
}
return false;
},removeAllFilters:function(){
this.filters=[];
},filter:function(rec){
for(var x=0;x<this.filters.length;x++){
if((this.filters[x]["filter"])&&(!this.filters[x].filter(rec))||(rec.level<this.cutOffLevel)){
return false;
}
}
return true;
},addHandler:function(hdlr){
this.handlers.push(hdlr);
return this.handlers.length-1;
},handle:function(rec){
if((!this.filter(rec))||(rec.level<this.cutOffLevel)){
return false;
}
for(var x=0;x<this.handlers.length;x++){
if(this.handlers[x]["handle"]){
this.handlers[x].handle(rec);
}
}
return true;
},log:function(lvl,msg){
if((this.propagate)&&(this.parent)&&(this.parent.rec.level>=this.cutOffLevel)){
this.parent.log(lvl,msg);
return false;
}
this.handle(new dojo.logging.Record(lvl,msg));
return true;
},debug:function(msg){
return this.logType("DEBUG",this.argsToArr(arguments));
},info:function(msg){
return this.logType("INFO",this.argsToArr(arguments));
},warning:function(msg){
return this.logType("WARNING",this.argsToArr(arguments));
},error:function(msg){
return this.logType("ERROR",this.argsToArr(arguments));
},critical:function(msg){
return this.logType("CRITICAL",this.argsToArr(arguments));
},exception:function(msg,e,_221){
if(e){
var _222=[e.name,(e.description||e.message)];
if(e.fileName){
_222.push(e.fileName);
_222.push("line "+e.lineNumber);
}
msg+=" "+_222.join(" : ");
}
this.logType("ERROR",msg);
if(!_221){
throw e;
}
},logType:function(type,args){
var na=[dojo.logging.log.getLevel(type)];
if(typeof args=="array"){
na=na.concat(args);
}else{
if((typeof args=="object")&&(args["length"])){
na=na.concat(this.argsToArr(args));
}else{
na=na.concat(this.argsToArr(arguments).slice(1));
}
}
return this.log.apply(this,na);
}});
void (function(){
var _223=dojo.logging.Logger.prototype;
_223.warn=_223.warning;
_223.err=_223.error;
_223.crit=_223.critical;
})();
dojo.logging.LogHandler=function(_224){
this.cutOffLevel=(_224)?_224:0;
this.formatter=null;
this.data=[];
this.filters=[];
};
dojo.logging.LogHandler.prototype.setFormatter=function(fmtr){
dj_unimplemented("setFormatter");
};
dojo.logging.LogHandler.prototype.flush=function(){
dj_unimplemented("flush");
};
dojo.logging.LogHandler.prototype.close=function(){
dj_unimplemented("close");
};
dojo.logging.LogHandler.prototype.handleError=function(){
dj_unimplemented("handleError");
};
dojo.logging.LogHandler.prototype.handle=function(_226){
if((this.filter(_226))&&(_226.level>=this.cutOffLevel)){
this.emit(_226);
}
};
dojo.logging.LogHandler.prototype.emit=function(_227){
dj_unimplemented("emit");
};
void (function(){
var _228=["setLevel","addFilter","removeFilterByIndex","removeFilter","removeAllFilters","filter"];
var tgt=dojo.logging.LogHandler.prototype;
var src=dojo.logging.Logger.prototype;
for(var x=0;x<_228.length;x++){
tgt[_228[x]]=src[_228[x]];
}
})();
dojo.logging.log=new dojo.logging.Logger();
dojo.logging.log.levels=[{"name":"DEBUG","level":1},{"name":"INFO","level":2},{"name":"WARNING","level":3},{"name":"ERROR","level":4},{"name":"CRITICAL","level":5}];
dojo.logging.log.loggers={};
dojo.logging.log.getLogger=function(name){
if(!this.loggers[name]){
this.loggers[name]=new dojo.logging.Logger();
this.loggers[name].parent=this;
}
return this.loggers[name];
};
dojo.logging.log.getLevelName=function(lvl){
for(var x=0;x<this.levels.length;x++){
if(this.levels[x].level==lvl){
return this.levels[x].name;
}
}
return null;
};
dojo.logging.log.addLevelName=function(name,lvl){
if(this.getLevelName(name)){
this.err("could not add log level "+name+" because a level with that name already exists");
return false;
}
this.levels.append({"name":name,"level":parseInt(lvl)});
return true;
};
dojo.logging.log.getLevel=function(name){
for(var x=0;x<this.levels.length;x++){
if(this.levels[x].name.toUpperCase()==name.toUpperCase()){
return this.levels[x].level;
}
}
return null;
};
dojo.logging.MemoryLogHandler=function(_230,_231,_232,_233){
dojo.logging.LogHandler.call(this,_230);
this.numRecords=(typeof djConfig["loggingNumRecords"]!="undefined")?djConfig["loggingNumRecords"]:(_231||-1);
this.postType=(typeof djConfig["loggingPostType"]!="undefined")?djConfig["loggingPostType"]:(_232||-1);
this.postInterval=(typeof djConfig["loggingPostInterval"]!="undefined")?djConfig["loggingPostInterval"]:(_232||-1);
};
dojo.logging.MemoryLogHandler.prototype=new dojo.logging.LogHandler();
dojo.logging.MemoryLogHandler.prototype.emit=function(_234){
this.data.push(_234);
if(this.numRecords!=-1){
while(this.data.length>this.numRecords){
this.data.pop();
}
}
};
dojo.logging.logQueueHandler=new dojo.logging.MemoryLogHandler(0,50,0,10000);
dojo.logging.logQueueHandler.emit=function(_235){
var _236=String(dojo.log.getLevelName(_235.level)+": "+_235.time.toLocaleTimeString())+": "+_235.message;
if(typeof dj_global["print"]=="function"){
print(_236);
}else{
if(dj_global["dj_debug"]){
dj_debug(_236);
}
}
};
dojo.logging.log.addHandler(dojo.logging.logQueueHandler);
dojo.log=dojo.logging.log;
dojo.hostenv.conditionalLoadModule({common:["dojo.logging.Logger",false,false],rhino:["dojo.logging.RhinoLogger"]});
dojo.hostenv.moduleLoaded("dojo.logging.*");
dojo.provide("dojo.io.IO");
dojo.io.transports=[];
dojo.io.hdlrFuncNames=["load","error"];
dojo.io.Request=function(url,mt,_239,curl){
this.url=url;
this.mimetype=mt;
this.transport=_239;
this.changeUrl=curl;
this.formNode=null;
this.events_={};
var _241=this;
this.error=function(type,_242){
switch(type){
case "io":
var _243=dojo.io.IOEvent.IO_ERROR;
var _244="IOError: error during IO";
break;
case "parse":
var _243=dojo.io.IOEvent.PARSE_ERROR;
var _244="IOError: error during parsing";
default:
var _243=dojo.io.IOEvent.UNKOWN_ERROR;
var _244="IOError: cause unkown";
}
var _245=new dojo.io.IOEvent("error",null,_241,_244,this.url,_243);
_241.dispatchEvent(_245);
if(_241.onerror){
_241.onerror(_244,_241.url,_245);
}
};
this.load=function(type,data,evt){
var _247=new dojo.io.IOEvent("load",data,_241,null,null,null);
_241.dispatchEvent(_247);
if(_241.onload){
_241.onload(_247);
}
};
this.backButton=function(){
var _248=new dojo.io.IOEvent("backbutton",null,_241,null,null,null);
_241.dispatchEvent(_248);
if(_241.onbackbutton){
_241.onbackbutton(_248);
}
};
this.forwardButton=function(){
var _249=new dojo.io.IOEvent("forwardbutton",null,_241,null,null,null);
_241.dispatchEvent(_249);
if(_241.onforwardbutton){
_241.onforwardbutton(_249);
}
};
};
dojo.io.Request.prototype.addEventListener=function(type,func){
if(!this.events_[type]){
this.events_[type]=[];
}
for(var i=0;i<this.events_[type].length;i++){
if(this.events_[type][i]==func){
return;
}
}
this.events_[type].push(func);
};
dojo.io.Request.prototype.removeEventListener=function(type,func){
if(!this.events_[type]){
return;
}
for(var i=0;i<this.events_[type].length;i++){
if(this.events_[type][i]==func){
this.events_[type].splice(i,1);
}
}
};
dojo.io.Request.prototype.dispatchEvent=function(evt){
if(!this.events_[evt.type]){
return;
}
for(var i=0;i<this.events_[evt.type].length;i++){
this.events_[evt.type][i](evt);
}
return false;
};
dojo.io.IOEvent=function(type,data,_250,_251,_252,_253){
this.type=type;
this.data=data;
this.request=_250;
this.errorMessage=_251;
this.errorUrl=_252;
this.errorCode=_253;
};
dojo.io.IOEvent.UNKOWN_ERROR=0;
dojo.io.IOEvent.IO_ERROR=1;
dojo.io.IOEvent.PARSE_ERROR=2;
dojo.io.Error=function(msg,type,num){
this.message=msg;
this.type=type||"unknown";
this.number=num||0;
};
dojo.io.transports.addTransport=function(name){
this.push(name);
this[name]=dojo.io[name];
};
dojo.io.bind=function(_255){
if(!_255["url"]){
_255.url="";
}else{
_255.url=_255.url.toString();
}
if(!_255["mimetype"]){
_255.mimetype="text/plain";
}
if(!_255["method"]&&!_255["formNode"]){
_255.method="get";
}else{
if(_255["formNode"]){
_255.method=_255["method"]||_255["formNode"].method||"get";
}
}
if(_255["handler"]){
_255.handle=_255.handler;
}
if(!_255["handle"]){
_255.handle=function(){
};
}
if(_255["loaded"]){
_255.load=_255.loaded;
}
if(_255["changeUrl"]){
_255.changeURL=_255.changeUrl;
}
for(var x=0;x<this.hdlrFuncNames.length;x++){
var fn=this.hdlrFuncNames[x];
if(typeof _255[fn]=="function"){
continue;
}
if(typeof _255.handler=="object"){
if(typeof _255.handler[fn]=="function"){
_255[fn]=_255.handler[fn]||_255.handler["handle"]||function(){
};
}
}else{
if(typeof _255["handler"]=="function"){
_255[fn]=_255.handler;
}else{
if(typeof _255["handle"]=="function"){
_255[fn]=_255.handle;
}
}
}
}
var _256="";
if(_255["transport"]){
_256=_255["transport"];
if(!this[_256]){
return false;
}
}else{
for(var x=0;x<dojo.io.transports.length;x++){
var tmp=dojo.io.transports[x];
if((this[tmp])&&(this[tmp].canHandle(_255))){
_256=tmp;
}
}
if(_256==""){
return false;
}
}
this[_256].bind(_255);
return true;
};
dojo.io.argsFromMap=function(map){
var _258=new Object();
var _259="";
for(var x in map){
if(!_258[x]){
_259+=encodeURIComponent(x)+"="+encodeURIComponent(map[x])+"&";
}
}
return _259;
};
dojo.provide("dojo.io.BrowserIO");
dojo.require("dojo.io.IO");
dojo.require("dojo.alg.*");
dojo.io.checkChildrenForFile=function(node){
var _260=false;
var _261=node.getElementsByTagName("input");
dojo.alg.forEach(_261,function(_262){
if(_260){
return;
}
if(_262.getAttribute("type")=="file"){
_260=true;
}
});
return _260;
};
dojo.io.formHasFile=function(_263){
return dojo.io.checkChildrenForFile(_263);
};
dojo.io.encodeForm=function(_264){
if((!_264)||(!_264.tagName)||(!_264.tagName.toLowerCase()=="form")){
dj_throw("Attempted to encode a non-form element.");
}
var ec=encodeURIComponent;
var _266=[];
for(var i=0;i<_264.elements.length;i++){
var elm=_264.elements[i];
if(elm.disabled){
continue;
}
var name=ec(elm.name);
var type=elm.type.toLowerCase();
if((type=="select")&&(elm.multiple)){
for(var j=0;j<elm.options.length;j++){
_266.push(name+"="+ec(elm.options[j].value));
}
}else{
if(dojo.alg.inArray(type,["radio","checked"])){
if(elm.checked){
_266.push(name+"="+ec(elm.value));
}
}else{
if(!dojo.alg.inArray(type,["file","submit","reset","button"])){
_266.push(name+"="+ec(elm.value));
}
}
}
}
return _266.join("&");
};
dojo.io.setIFrameSrc=function(_269,src,_270){
try{
var r=dojo.render.html;
if(!_270){
if(r.safari){
_269.location=src;
}else{
frames[_269.name].location=src;
}
}else{
var idoc=(r.moz)?_269.contentWindow:_269;
idoc.location.replace(src);
dj_debug(_269.contentWindow.location);
}
}
catch(e){
dj_debug("setIFrameSrc: "+e);
}
};
dojo.io.createIFrame=function(_273){
if(window[_273]){
return window[_273];
}
if(window.frames[_273]){
return window.frames[_273];
}
var r=dojo.render.html;
var _274=null;
_274=document.createElement((((r.ie)&&(r.win))?"<iframe name="+_273+">":"iframe"));
with(_274){
name=_273;
setAttribute("name",_273);
id=_273;
}
window[_273]=_274;
document.body.appendChild(_274);
with(_274.style){
position="absolute";
left=top="0px";
height=width="1px";
visibility="hidden";
if(dojo.hostenv.is_debug_){
position="relative";
height="100px";
width="300px";
visibility="visible";
}
}
dojo.io.setIFrameSrc(_274,dojo.hostenv.getBaseScriptUri()+"iframe_history.html",true);
return _274;
};
dojo.io.cancelDOMEvent=function(evt){
if(!evt){
return false;
}
if(evt.preventDefault){
evt.stopPropagation();
evt.preventDefault();
}else{
if(window.event){
window.event.cancelBubble=true;
window.event.returnValue=false;
}
}
return false;
};
dojo.io.XMLHTTPTransport=new function(){
var _275=this;
this.initialHref=window.location.href;
this.initialHash=window.location.hash;
this.moveForward=false;
var _276={};
this.useCache=false;
this.historyStack=[];
this.forwardStack=[];
this.historyIframe=null;
this.bookmarkAnchor=null;
this.locationTimer=null;
function getCacheKey(url,_277,_278){
return url+"|"+_277+"|"+_278.toLowerCase();
}
function addToCache(url,_279,_280,http){
_276[getCacheKey(url,_279,_280)]=http;
}
function getFromCache(url,_282,_283){
return _276[getCacheKey(url,_282,_283)];
}
this.clearCache=function(){
_276={};
};
function doLoad(_284,http,url,_285,_286){
if(http.status==200||(location.protocol=="file:"&&http.status==0)){
var ret;
if(_284.method.toLowerCase()=="head"){
var _287=http.getAllResponseHeaders();
ret={};
ret.toString=function(){
return _287;
};
var _288=_287.split(/[\r\n]+/g);
for(var i=0;i<_288.length;i++){
var pair=_288[i].match(/^([^:]+)\s*:\s*(.+)$/i);
if(pair){
ret[pair[1]]=pair[2];
}
}
}else{
if(_284.mimetype=="text/javascript"){
ret=dj_eval(http.responseText);
}else{
if(_284.mimetype=="text/xml"){
ret=http.responseXML;
if(!ret||typeof ret=="string"){
ret=dojo.xml.domUtil.createDocumentFromText(http.responseText);
}
}else{
ret=http.responseText;
}
}
}
if(_286){
addToCache(url,_285,_284.method,http);
}
if(typeof _284.load=="function"){
_284.load("load",ret,http);
}
}else{
var _290=new dojo.io.Error("XMLHttpTransport Error: "+http.status+" "+http.statusText);
if(typeof _284.error=="function"){
_284.error("error",_290,http);
}
}
}
function setHeaders(http,_291){
if(_291["headers"]){
for(var _292 in _291["headers"]){
if(_292.toLowerCase()=="content-type"&&!_291["contentType"]){
_291["contentType"]=_291["headers"][_292];
}else{
http.setRequestHeader(_292,_291["headers"][_292]);
}
}
}
}
this.addToHistory=function(args){
var _293=args["back"]||args["backButton"]||args["handle"];
var hash=null;
if(!this.historyIframe){
this.historyIframe=window.frames["djhistory"];
}
if(!this.bookmarkAnchor){
this.bookmarkAnchor=document.createElement("a");
document.body.appendChild(this.bookmarkAnchor);
this.bookmarkAnchor.style.display="none";
}
if((!args["changeURL"])||(dojo.render.html.ie)){
var url=dojo.hostenv.getBaseScriptUri()+"iframe_history.html?"+(new Date()).getTime();
this.moveForward=true;
dojo.io.setIFrameSrc(this.historyIframe,url,false);
}
if(args["changeURL"]){
hash="#"+((args["changeURL"]!==true)?args["changeURL"]:(new Date()).getTime());
setTimeout("window.location.href = '"+hash+"';",1);
this.bookmarkAnchor.href=hash;
if(dojo.render.html.ie){
var _295=_293;
var lh=null;
var hsl=this.historyStack.length-1;
if(hsl>=0){
while(!this.historyStack[hsl]["urlHash"]){
hsl--;
}
lh=this.historyStack[hsl]["urlHash"];
}
if(lh){
_293=function(){
if(window.location.hash!=""){
setTimeout("window.location.href = '"+lh+"';",1);
}
_295();
};
}
this.forwardStack=[];
var _298=args["forward"]||args["forwardButton"];
var tfw=function(){
if(window.location.hash!=""){
window.location.href=hash;
}
if(_298){
_298();
}
};
if(args["forward"]){
args.forward=tfw;
}else{
if(args["forwardButton"]){
args.forwardButton=tfw;
}
}
}else{
if(dojo.render.html.moz){
if(!this.locationTimer){
this.locationTimer=setInterval("dojo.io.XMLHTTPTransport.checkLocation();",200);
}
}
}
}
this.historyStack.push({"url":url,"callback":_293,"kwArgs":args,"urlHash":hash});
};
this.checkLocation=function(){
var hsl=this.historyStack.length;
if((window.location.hash==this.initialHash)||(window.location.href==this.initialHref)&&(hsl==1)){
this.handleBackButton();
return;
}
if(this.forwardStack.length>0){
if(this.forwardStack[this.forwardStack.length-1].urlHash==window.location.hash){
this.handleForwardButton();
return;
}
}
if((hsl>=2)&&(this.historyStack[hsl-2])){
if(this.historyStack[hsl-2].urlHash==window.location.hash){
this.handleBackButton();
return;
}
}
};
this.iframeLoaded=function(evt,_300){
var isp=_300.href.split("?");
if(isp.length<2){
if(this.historyStack.length==1){
this.handleBackButton();
}
return;
}
var _302=isp[1];
if(this.moveForward){
this.moveForward=false;
return;
}
var last=this.historyStack.pop();
if(!last){
if(this.forwardStack.length>0){
var next=this.forwardStack[this.forwardStack.length-1];
if(_302==next.url.split("?")[1]){
this.handleForwardButton();
}
}
return;
}
this.historyStack.push(last);
if(this.historyStack.length>=2){
if(isp[1]==this.historyStack[this.historyStack.length-2].url.split("?")[1]){
this.handleBackButton();
}
}else{
this.handleBackButton();
}
};
this.handleBackButton=function(){
var last=this.historyStack.pop();
if(!last){
return;
}
if(last["callback"]){
last.callback();
}else{
if(last.kwArgs["backButton"]){
last.kwArgs["backButton"]();
}else{
if(last.kwArgs["back"]){
last.kwArgs["back"]();
}else{
if(last.kwArgs["handle"]){
last.kwArgs.handle("back");
}
}
}
}
this.forwardStack.push(last);
};
this.handleForwardButton=function(){
var last=this.forwardStack.pop();
if(!last){
return;
}
if(last.kwArgs["forward"]){
last.kwArgs.forward();
}else{
if(last.kwArgs["forwardButton"]){
last.kwArgs.forwardButton();
}else{
if(last.kwArgs["handle"]){
last.kwArgs.handle("forward");
}
}
}
this.historyStack.push(last);
};
var _305=dojo.hostenv.getXmlhttpObject()?true:false;
this.canHandle=function(_306){
return _305&&dojo.alg.inArray(_306["mimetype"],["text/plain","text/html","text/xml","text/javascript"])&&dojo.alg.inArray(_306["method"].toLowerCase(),["post","get","head"])&&!(_306["formNode"]&&dojo.io.formHasFile(_306["formNode"]));
};
this.bind=function(_307){
if(!_307["url"]){
if(!_307["formNode"]&&(_307["backButton"]||_307["back"]||_307["changeURL"]||_307["watchForURL"])&&(!window["djConfig"]&&!window["djConfig"]["preventBackButtonFix"])){
this.addToHistory(_307);
return true;
}
}
var url=_307.url;
var _308="";
if(_307["formNode"]){
var ta=_307.formNode.getAttribute("action");
if((ta)&&(!_307["url"])){
url=ta;
}
var tp=_307.formNode.getAttribute("method");
if((tp)&&(!_307["method"])){
_307.method=tp;
}
_308+=dojo.io.encodeForm(_307.formNode);
}
if(!_307["method"]){
_307.method="get";
}
if(_307["content"]){
_308+=dojo.io.argsFromMap(_307.content);
}
if(_307["postContent"]&&_307.method.toLowerCase()=="post"){
_308=_307.postContent;
}
if(_307["backButton"]||_307["back"]||_307["changeURL"]){
this.addToHistory(_307);
}
var _310=_307["sync"]?false:true;
var _311=_307["useCache"]==true||(this.useCache==true&&_307["useCache"]!=false);
if(_311){
var _312=getFromCache(url,_308,_307.method);
if(_312){
doLoad(_307,_312,url,_308,false);
return;
}
}
var http=dojo.hostenv.getXmlhttpObject();
var _313=false;
if(_310){
http.onreadystatechange=function(){
if(4==http.readyState){
if(_313){
return;
}
_313=true;
doLoad(_307,http,url,_308,_311);
}
};
}
if(_307.method.toLowerCase()=="post"){
http.open("POST",url,_310);
setHeaders(http,_307);
http.setRequestHeader("Content-Type",_307["contentType"]||"application/x-www-form-urlencoded");
http.send(_308);
}else{
var _314=url;
if(_308!=""){
_314+=(url.indexOf("?")>-1?"&":"?")+_308;
}
http.open(_307.method.toUpperCase(),_314,_310);
setHeaders(http,_307);
http.send(null);
}
if(!_310){
doLoad(_307,http,url,_308,_311);
}
return;
};
dojo.io.transports.addTransport("XMLHTTPTransport");
};
dojo.provide("dojo.io.Cookies");
dojo.io.cookies=new function(){
this.setCookie=function(name,_315,days,path){
var _318=-1;
if(typeof days=="number"&&days>=0){
var d=new Date();
d.setTime(d.getTime()+(days*24*60*60*1000));
_318=d.toGMTString();
}
_315=escape(_315);
document.cookie=name+"="+_315+";"+(_318!=-1?" expires="+_318+";":"")+"path="+(path||"/");
};
this.getCookie=function(name){
var idx=document.cookie.indexOf(name+"=");
if(idx==-1){
return null;
}
value=document.cookie.substring(idx+name.length+1);
var end=value.indexOf(";");
if(end==-1){
end=value.length;
}
value=value.substring(0,end);
value=unescape(value);
return value;
};
this.deleteCookie=function(name){
this.setCookie(name,"-",0);
};
this.setObjectCookie=function(name,obj,days,path,_321){
var _322=[],cookie,value="";
if(!_321){
cookie=this.getObjectCookie(name);
}
if(days>=0){
if(!cookie){
cookie={};
}
for(var prop in obj){
if(prop==null){
delete cookie[prop];
}else{
if(typeof obj[prop]=="string"||typeof obj[prop]=="number"){
cookie[prop]=obj[prop];
}
}
}
prop=null;
for(var prop in cookie){
_322.push(escape(prop)+"="+escape(cookie[prop]));
}
value=_322.join("&");
}
this.setCookie(name,value,days,path);
};
this.getObjectCookie=function(name){
var _324=null,cookie=this.getCookie(name);
if(cookie){
_324={};
var _325=cookie.split("&");
for(var i=0;i<_325.length;i++){
var pair=_325[i].split("=");
var _326=pair[1];
if(isNaN(_326)){
_326=unescape(pair[1]);
}
_324[unescape(pair[0])]=_326;
}
}
return _324;
};
this.isSupported=function(){
if(typeof navigator.cookieEnabled!="boolean"){
this.setCookie("__TestingYourBrowserForCookieSupport__","CookiesAllowed",90,null);
var _327=this.getCookie("__TestingYourBrowserForCookieSupport__");
navigator.cookieEnabled=(_327=="CookiesAllowed");
if(navigator.cookieEnabled){
this.deleteCookie("__TestingYourBrowserForCookieSupport__");
}
}
return navigator.cookieEnabled;
};
};
dojo.hostenv.conditionalLoadModule({common:["dojo.io.IO",false,false],rhino:["dojo.io.RhinoIO",false,false],browser:[["dojo.io.BrowserIO",false,false],["dojo.io.Cookies",false,false]]});
dojo.hostenv.moduleLoaded("dojo.io.*");
dojo.provide("dojo.text.String");
dojo.text={trim:function(_328){
if(arguments.length==0){
_328=this;
}
if(typeof _328!="string"){
return _328;
}
if(!_328.length){
return _328;
}
return _328.replace(/^\s*/,"").replace(/\s*$/,"");
},paramString:function(str,_330,_331){
if(typeof str!="string"){
_330=str;
_331=_330;
str=this;
}
for(var name in _330){
var re=new RegExp("\\%\\{"+name+"\\}","g");
str=str.replace(re,_330[name]);
}
if(_331){
str=str.replace(/%\{([^\}\s]+)\}/g,"");
}
return str;
},capitalize:function(str){
if(typeof str!="string"||str==null){
return "";
}
if(arguments.length==0){
str=this;
}
var _333=str.split(" ");
var _334="";
var len=_333.length;
for(var i=0;i<len;i++){
var word=_333[i];
word=word.charAt(0).toUpperCase()+word.substring(1,word.length);
_334+=word;
if(i<len-1){
_334+=" ";
}
}
return new String(_334);
},isBlank:function(str){
if(typeof str!="string"||str==null){
return true;
}
return (dojo.text.trim(str).length==0);
}};
dojo.text.String={};
dojo.provide("dojo.text.Builder");
dojo.require("dojo.text");
dojo.text.Builder=function(str){
var a=[];
var b=str||"";
var _338=this.length=b.length;
if(!dojo.text.isBlank(b)){
a.push(b);
}
b="";
this.toString=this.valueOf=function(){
return a.join("");
};
this.append=function(s){
a.push(s);
_338+=s.length;
this.length=_338;
};
this.clear=function(){
a=[];
_338=this.length=0;
};
this.remove=function(f,l){
var s="";
b=a.join("");
a=[];
if(f>0){
s=b.substring(0,(f-1));
}
b=s+b.substring(f+l);
a.push(b);
_338=this.length=b.length;
b="";
};
this.replace=function(o,n){
b=a.join("");
a=[];
b.replace(o,n);
a.push(b);
_338=this.length=b.length;
b="";
};
this.insert=function(idx,s){
b=a.join("");
a=[];
if(idx==0){
b=s+b;
}else{
var _343=b.substring(0,idx-1);
var end=b.substring(idx);
b=_343+s+end;
}
_338=this.length=b.length;
a.push(b);
b="";
};
};
dojo.hostenv.conditionalLoadModule({common:["dojo.text.String","dojo.text.Builder"]});
dojo.hostenv.moduleLoaded("dojo.text.*");
if(!this["dojo"]){
alert("\"dojo/__package__.js\" is now located at \"dojo/dojo.js\". Please update your includes accordingly");
}
dojo.provide("dojo.graphics.color");
dojo.graphics.color=new function(){
this.blend=function(a,b,_344){
if(typeof a=="string"){
return this.blendHex(a,b,_344);
}
if(!_344){
_344=0;
}else{
if(_344>1){
_344=1;
}else{
if(_344<-1){
_344=-1;
}
}
}
var c=new Array(3);
for(var i=0;i<3;i++){
var half=Math.abs(a[i]-b[i])/2;
c[i]=Math.floor(Math.min(a[i],b[i])+half+(half*_344));
}
return c;
};
this.blendHex=function(a,b,_347){
return this.rgb2hex(this.blend(this.hex2rgb(a),this.hex2rgb(b),_347));
};
this.extractRGB=function(_348){
var hex="0123456789abcdef";
_348=_348.toLowerCase();
if(_348.indexOf("rgb")==0){
var _350=_348.match(/rgba*\((\d+), *(\d+), *(\d+)/i);
var ret=_350.splice(1,3);
return ret;
}else{
if(_348.indexOf("#")==0){
var _351=[];
_348=_348.substring(1);
if(_348.length==3){
_351[0]=_348.charAt(0)+_348.charAt(0);
_351[1]=_348.charAt(1)+_348.charAt(1);
_351[2]=_348.charAt(2)+_348.charAt(2);
}else{
_351[0]=_348.substring(0,2);
_351[1]=_348.substring(2,4);
_351[2]=_348.substring(4,6);
}
for(var i=0;i<_351.length;i++){
var c=_351[i];
_351[i]=hex.indexOf(c.charAt(0))*16+hex.indexOf(c.charAt(1));
}
return _351;
}else{
switch(_348){
case "white":
return [255,255,255];
case "black":
return [0,0,0];
case "red":
return [255,0,0];
case "green":
return [0,255,0];
case "blue":
return [0,0,255];
case "navy":
return [0,0,128];
case "gray":
return [128,128,128];
case "silver":
return [192,192,192];
}
}
}
return [255,255,255];
};
this.hex2rgb=function(hex){
var _352="0123456789ABCDEF";
var rgb=new Array(3);
if(hex.indexOf("#")==0){
hex=hex.substring(1);
}
hex=hex.toUpperCase();
if(hex.length==3){
rgb[0]=hex.charAt(0)+hex.charAt(0);
rgb[1]=hex.charAt(1)+hex.charAt(1);
rgb[2]=hex.charAt(2)+hex.charAt(2);
}else{
rgb[0]=hex.substring(0,2);
rgb[1]=hex.substring(2,4);
rgb[2]=hex.substring(4);
}
for(var i=0;i<rgb.length;i++){
rgb[i]=_352.indexOf(rgb[i].charAt(0))*16+_352.indexOf(rgb[i].charAt(1));
}
return rgb;
};
this.rgb2hex=function(r,g,b){
if(r.constructor==Array){
g=r[1]||0;
b=r[2]||0;
r=r[0]||0;
}
return ["#",r.toString(16),g.toString(16),b.toString(16)].join("");
};
};
dojo.provide("dojo.xml.domUtil");
dojo.require("dojo.graphics.color");
dojo.require("dojo.text.String");
dojo.xml.domUtil=new function(){
this.nodeTypes={ELEMENT_NODE:1,ATTRIBUTE_NODE:2,TEXT_NODE:3,CDATA_SECTION_NODE:4,ENTITY_REFERENCE_NODE:5,ENTITY_NODE:6,PROCESSING_INSTRUCTION_NODE:7,COMMENT_NODE:8,DOCUMENT_NODE:9,DOCUMENT_TYPE_NODE:10,DOCUMENT_FRAGMENT_NODE:11,NOTATION_NODE:12};
this.dojoml="http://www.dojotoolkit.org/2004/dojoml";
this.idIncrement=0;
this.getTagName=function(node){
var _355=node.tagName;
if(_355.substr(0,5).toLowerCase()!="dojo:"){
if(_355.substr(0,4).toLowerCase()=="dojo"){
return "dojo:"+_355.substring(4).toLowerCase();
}
var djt=node.getAttribute("dojoType")||node.getAttribute("dojotype");
if(djt){
return "dojo:"+djt.toLowerCase();
}
if((node.getAttributeNS)&&(node.getAttributeNS(this.dojoml,"type"))){
return "dojo:"+node.getAttributeNS(this.dojoml,"type").toLowerCase();
}
try{
djt=node.getAttribute("dojo:type");
}
catch(e){
}
if(djt){
return "dojo:"+djt.toLowerCase();
}
if((!dj_global["djConfig"])||(!djConfig["ignoreClassNames"])){
var _357=node.className||node.getAttribute("class");
if((_357)&&(_357.indexOf("dojo-")!=-1)){
var _358=_357.split(" ");
for(var x=0;x<_358.length;x++){
if((_358[x].length>5)&&(_358[x].indexOf("dojo-")>=0)){
return "dojo:"+_358[x].substr(5);
}
}
}
}
}
return _355.toLowerCase();
};
this.getUniqueId=function(){
var base="dj_unique_";
this.idIncrement++;
while(document.getElementById(base+this.idIncrement)){
this.idIncrement++;
}
return base+this.idIncrement;
};
this.getFirstChildTag=function(_360){
var node=_360.firstChild;
while(node&&node.nodeType!=1){
node=node.nextSibling;
}
return node;
};
this.getLastChildTag=function(_361){
if(!node){
return null;
}
var node=_361.lastChild;
while(node&&node.nodeType!=1){
node=node.previousSibling;
}
return node;
};
this.getNextSiblingTag=function(node){
if(!node){
return null;
}
do{
node=node.nextSibling;
}while(node&&node.nodeType!=1);
return node;
};
this.getPreviousSiblingTag=function(node){
if(!node){
return null;
}
do{
node=node.previousSibling;
}while(node&&node.nodeType!=1);
return node;
};
this.forEachChildTag=function(node,_362){
var _363=this.getFirstChildTag(node);
while(_363){
if(_362(_363)=="break"){
break;
}
_363=this.getNextSiblingTag(_363);
}
};
this.moveChildren=function(_364,_365,trim){
var _367=0;
if(trim){
while(_364.hasChildNodes()&&_364.firstChild.nodeType==3){
_364.removeChild(_364.firstChild);
}
while(_364.hasChildNodes()&&_364.lastChild.nodeType==3){
_364.removeChild(_364.lastChild);
}
}
while(_364.hasChildNodes()){
_365.appendChild(_364.firstChild);
_367++;
}
return _367;
};
this.copyChildren=function(_368,_369,trim){
var cp=_368.cloneNode(true);
return this.moveChildren(cp,_369,trim);
};
this.clearChildren=function(node){
var _371=0;
while(node.hasChildNodes()){
node.removeChild(node.firstChild);
_371++;
}
return _371;
};
this.replaceChildren=function(node,_372){
this.clearChildren(node);
node.appendChild(_372);
};
this.getStyle=function(_373,_374){
var _375=undefined,camelCased=dojo.xml.domUtil.toCamelCase(_374);
_375=_373.style[camelCased];
if(!_375){
if(document.defaultView){
_375=document.defaultView.getComputedStyle(_373,"").getPropertyValue(_374);
}else{
if(_373.currentStyle){
_375=_373.currentStyle[camelCased];
}else{
if(_373.style.getPropertyValue){
_375=_373.style.getPropertyValue(_374);
}
}
}
}
return _375;
};
this.toCamelCase=function(_376){
var arr=_376.split("-"),cc=arr[0];
for(var i=1;i<arr.length;i++){
cc+=arr[i].charAt(0).toUpperCase()+arr[i].substring(1);
}
return cc;
};
this.toSelectorCase=function(_377){
return _377.replace(/([A-Z])/g,"-$1").toLowerCase();
};
this.getAncestors=function(node){
var _378=[];
while(node){
_378.push(node);
node=node.parentNode;
}
return _378;
};
this.isChildOf=function(node,_379,_380){
if(_380&&node){
node=node.parentNode;
}
while(node){
if(node==_379){
return true;
}
node=node.parentNode;
}
return false;
};
this.createDocumentFromText=function(str,_381){
if(!_381){
_381="text/xml";
}
if(typeof DOMParser!="undefined"){
var _382=new DOMParser();
return _382.parseFromString(str,_381);
}else{
if(typeof ActiveXObject!="undefined"){
var _383=new ActiveXObject("Microsoft.XMLDOM");
if(_383){
_383.async=false;
_383.loadXML(str);
return _383;
}else{
dj_debug("toXml didn't work?");
}
}else{
if(document.createElement){
var tmp=document.createElement("xml");
tmp.innerHTML=str;
if(document.implementation&&document.implementation.createDocument){
var _384=document.implementation.createDocument("foo","",null);
for(var i=0;i<tmp.childNodes.length;i++){
_384.importNode(tmp.childNodes.item(i),true);
}
return _384;
}
return tmp.document&&tmp.document.firstChild?tmp.document.firstChild:tmp;
}
}
}
return null;
};
if(dojo.render.html.capable){
this.createNodesFromText=function(txt,wrap){
var tn=document.createElement("div");
tn.style.visibility="hidden";
document.body.appendChild(tn);
tn.innerHTML=txt;
tn.normalize();
if(wrap){
var ret=[];
var fc=tn.firstChild;
ret[0]=((fc.nodeValue==" ")||(fc.nodeValue=="\t"))?fc.nextSibling:fc;
document.body.removeChild(tn);
return ret;
}
var _388=[];
for(var x=0;x<tn.childNodes.length;x++){
_388.push(tn.childNodes[x].cloneNode(true));
}
tn.style.display="none";
document.body.removeChild(tn);
return _388;
};
}else{
if(dojo.render.svg.capable){
this.createNodesFromText=function(txt,wrap){
var _389=parseXML(txt,window.document);
_389.normalize();
if(wrap){
var ret=[_389.firstChild.cloneNode(true)];
return ret;
}
var _390=[];
for(var x=0;x<_389.childNodes.length;x++){
_390.push(_389.childNodes.item(x).cloneNode(true));
}
return _390;
};
}
}
this.extractRGB=function(){
return dojo.graphics.color.extractRGB.call(dojo.graphics.color,arguments);
};
this.hex2rgb=function(){
return dojo.graphics.color.hex2rgb.call(dojo.graphics.color,arguments);
};
this.rgb2hex=function(){
return dojo.graphics.color.rgb2hex.call(dojo.graphics.color,arguments);
};
this.insertBefore=function(node,ref){
var pn=ref.parentNode;
pn.insertBefore(node,ref);
};
this.before=this.insertBefore;
this.insertAfter=function(node,ref){
var pn=ref.parentNode;
if(ref==pn.lastChild){
pn.appendChild(node);
}else{
pn.insertBefore(node,ref.nextSibling);
}
};
this.after=this.insertAfter;
this.insert=function(node,ref,_393){
switch(_393.toLowerCase()){
case "before":
this.before(node,ref);
break;
case "after":
this.after(node,ref);
break;
case "first":
if(ref.firstChild){
this.before(node,ref.firstChild);
}else{
ref.appendChild(node);
}
break;
default:
ref.appendChild(node);
break;
}
};
this.insertAtIndex=function(node,ref,_394){
var pn=ref.parentNode;
var _395=pn.childNodes;
var _396=false;
for(var i=0;i<_395.length;i++){
if((_395.item(i)["getAttribute"])&&(parseInt(_395.item(i).getAttribute("dojoinsertionindex"))>_394)){
this.before(node,_395.item(i));
_396=true;
break;
}
}
if(!_396){
this.before(node,ref);
}
};
this.textContent=function(node,text){
if(text){
this.replaceChildren(node,document.createTextNode(text));
return text;
}else{
var _398="";
if(node==null){
return _398;
}
for(var i=0;i<node.childNodes.length;i++){
switch(node.childNodes[i].nodeType){
case 1:
case 5:
_398+=dojo.xml.domUtil.textContent(node.childNodes[i]);
break;
case 3:
case 2:
case 4:
_398+=node.childNodes[i].nodeValue;
break;
default:
break;
}
}
return _398;
}
};
this.renderedTextContent=function(node){
var _399="";
if(node==null){
return _399;
}
for(var i=0;i<node.childNodes.length;i++){
switch(node.childNodes[i].nodeType){
case 1:
case 5:
switch(dojo.xml.domUtil.getStyle(node.childNodes[i],"display")){
case "block":
case "list-item":
case "run-in":
case "table":
case "table-row-group":
case "table-header-group":
case "table-footer-group":
case "table-row":
case "table-column-group":
case "table-column":
case "table-cell":
case "table-caption":
_399+="\n";
_399+=dojo.xml.domUtil.renderedTextContent(node.childNodes[i]);
_399+="\n";
break;
case "none":
break;
default:
_399+=dojo.xml.domUtil.renderedTextContent(node.childNodes[i]);
break;
}
break;
case 3:
case 2:
case 4:
var text=node.childNodes[i].nodeValue;
switch(dojo.xml.domUtil.getStyle(node,"text-transform")){
case "capitalize":
text=dojo.text.capitalize(text);
break;
case "uppercase":
text=text.toUpperCase();
break;
case "lowercase":
text=text.toLowerCase();
break;
default:
break;
}
switch(dojo.xml.domUtil.getStyle(node,"text-transform")){
case "nowrap":
break;
case "pre-wrap":
break;
case "pre-line":
break;
case "pre":
break;
default:
text=text.replace(/\s+/," ");
if(/\s$/.test(_399)){
text.replace(/^\s/,"");
}
break;
}
_399+=text;
break;
default:
break;
}
}
return _399;
};
this.remove=function(node){
if(node&&node.parentNode){
node.parentNode.removeChild(node);
}
};
};
dojo.provide("dojo.xml.Parse");
dojo.require("dojo.xml.domUtil");
dojo.xml.Parse=function(){
this.parseFragment=function(_400){
var _401={};
var _402=dojo.xml.domUtil.getTagName(_400);
_401[_402]=new Array(_400.tagName);
var _403=this.parseAttributes(_400);
for(var attr in _403){
if(!_401[attr]){
_401[attr]=[];
}
_401[attr][_401[attr].length]=_403[attr];
}
var _405=_400.childNodes;
for(var _406 in _405){
switch(_405[_406].nodeType){
case dojo.xml.domUtil.nodeTypes.ELEMENT_NODE:
_401[_402].push(this.parseElement(_405[_406]));
break;
case dojo.xml.domUtil.nodeTypes.TEXT_NODE:
if(_405.length==1){
if(!_401[_400.tagName]){
_401[_402]=[];
}
_401[_402].push({value:_405[0].nodeValue});
}
break;
}
}
return _401;
};
this.parseElement=function(node,_407,_408,_409){
var _410={};
var _411=dojo.xml.domUtil.getTagName(node);
_410[_411]=[];
if((!_408)||(_411.substr(0,4).toLowerCase()=="dojo")){
var _412=this.parseAttributes(node);
for(var attr in _412){
if(!_410[_411][attr]){
_410[_411][attr]=[];
}
_410[_411][attr].push(_412[attr]);
}
}
_410[_411].nodeRef=node;
_410.tagName=_411;
_410.index=_409||0;
var _413=dojo.xml.domUtil.nodeTypes;
var _414=0;
for(var i=0;i<node.childNodes.length;i++){
var tcn=node.childNodes.item(i);
switch(tcn.nodeType){
case _413.ELEMENT_NODE:
_414++;
var ctn=dojo.xml.domUtil.getTagName(tcn);
if(!_410[ctn]){
_410[ctn]=[];
}
_410[ctn].push(this.parseElement(tcn,true,_408,_414));
if((tcn.childNodes.length==1)&&(tcn.childNodes.item(0).nodeType==_413.TEXT_NODE)){
_410[ctn][_410[ctn].length-1].value=tcn.childNodes.item(0).nodeValue;
}
break;
case _413.TEXT_NODE:
if(node.childNodes.length==1){
_410[_411].push({value:node.childNodes.item(0).nodeValue});
}
break;
default:
break;
}
}
return _410;
};
this.parseAttributes=function(node){
var _417={};
var atts=node.attributes;
for(var i=0;i<atts.length;i++){
var _419=atts.item(i);
if((dojo.render.html.capable)&&(dojo.render.html.ie)){
if(!_419){
continue;
}
if((typeof _419=="object")&&(typeof _419.nodeValue=="undefined")||(_419.nodeValue==null)||(_419.nodeValue=="")){
continue;
}
}
_417[_419.nodeName]={value:_419.nodeValue};
}
return _417;
};
};
dojo.provide("dojo.uri.Uri");
dojo.uri=new function(){
this.joinPath=function(){
var arr=[];
for(var i=0;i<arguments.length;i++){
arr.push(arguments[i]);
}
return arr.join("/").replace(/\/{2,}/g,"/").replace(/((https*|ftps*):)/i,"$1/");
};
this.dojoUri=function(uri){
return new dojo.uri.Uri(dojo.hostenv.getBaseScriptUri(),uri);
};
this.Uri=function(){
var uri=arguments[0];
for(var i=1;i<arguments.length;i++){
if(!arguments[i]){
continue;
}
var _420=new dojo.uri.Uri(arguments[i].toString());
var _421=new dojo.uri.Uri(uri.toString());
if(_420.path==""&&_420.scheme==null&&_420.authority==null&&_420.query==null){
if(_420.fragment!=null){
_421.fragment=_420.fragment;
}
_420=_421;
}else{
if(_420.scheme==null){
_420.scheme=_421.scheme;
if(_420.authority==null){
_420.authority=_421.authority;
if(_420.path.charAt(0)!="/"){
var path=_421.path.substring(0,_421.path.lastIndexOf("/")+1)+_420.path;
var segs=path.split("/");
for(var j=0;j<segs.length;j++){
if(segs[j]=="."){
if(j==segs.length-1){
segs[j]="";
}else{
segs.splice(j,1);
j--;
}
}else{
if(j>0&&!(j==1&&segs[0]=="")&&segs[j]==".."&&segs[j-1]!=".."){
if(j==segs.length-1){
segs.splice(j,1);
segs[j-1]="";
}else{
segs.splice(j-1,2);
j-=2;
}
}
}
}
_420.path=segs.join("/");
}
}
}
}
uri="";
if(_420.scheme!=null){
uri+=_420.scheme+":";
}
if(_420.authority!=null){
uri+="//"+_420.authority;
}
uri+=_420.path;
if(_420.query!=null){
uri+="?"+_420.query;
}
if(_420.fragment!=null){
uri+="#"+_420.fragment;
}
}
this.uri=uri.toString();
var _423="^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\\?([^#]*))?(#(.*))?$";
var r=this.uri.match(new RegExp(_423));
this.scheme=r[2]||(r[1]?"":null);
this.authority=r[4]||(r[3]?"":null);
this.path=r[5];
this.query=r[7]||(r[6]?"":null);
this.fragment=r[9]||(r[8]?"":null);
if(this.authority!=null){
_423="^((([^:]+:)?([^@]+))@)?([^:]*)(:([0-9]+))?$";
r=this.authority.match(new RegExp(_423));
this.user=r[3]||null;
this.password=r[4]||null;
this.host=r[5];
this.port=r[7]||null;
}
this.toString=function(){
return this.uri;
};
};
};
dojo.provide("dojo.xml.htmlUtil");
dojo.require("dojo.xml.domUtil");
dojo.require("dojo.text.String");
dojo.require("dojo.event.*");
dojo.require("dojo.uri.Uri");
dojo.xml.htmlUtil=new function(){
var _424=this;
var _425=false;
this.styleSheet=null;
this._clobberSelection=function(){
try{
if(window.getSelection){
var _426=window.getSelection();
_426.collapseToEnd();
}else{
if(document.selection){
document.selection.clear();
}
}
}
catch(e){
}
};
this.disableSelect=function(){
if(!_425){
_425=true;
var db=document.body;
if(dojo.render.html.moz){
db.style.MozUserSelect="none";
}else{
dojo.event.connect(db,"onselectstart",dojo.event.browser,"stopEvent");
dojo.event.connect(db,"ondragstart",dojo.event.browser,"stopEvent");
dojo.event.connect(db,"onmousemove",this,"_clobberSelection");
}
}
};
this.enableSelect=function(){
if(_425){
_425=false;
var db=document.body;
if(dojo.render.html.moz){
db.style.MozUserSelect="";
}else{
dojo.event.disconnect(db,"onselectstart",dojo.event.browser,"stopEvent");
dojo.event.disconnect(db,"ondragstart",dojo.event.browser,"stopEvent");
dojo.event.disconnect(db,"onmousemove",this,"_clobberSelection");
}
}
};
var cm=document["compatMode"];
var _429=((cm)&&((cm=="BackCompat")||(cm=="QuirksMode")))?true:false;
this.getInnerWidth=function(node){
return node.offsetWidth;
};
this.getOuterWidth=function(node){
dj_unimplemented("dojo.xml.htmlUtil.getOuterWidth");
};
this.getInnerHeight=function(node){
return node.offsetHeight;
};
this.getOuterHeight=function(node){
dj_unimplemented("dojo.xml.htmlUtil.getOuterHeight");
};
this.getTotalOffset=function(node,type){
var _430=(type=="top")?"offsetTop":"offsetLeft";
var alt=(type=="top")?"y":"x";
var ret=0;
if(node["offsetParent"]){
do{
ret+=node[_430];
node=node.offsetParent;
}while(node!=document.body.parentNode&&node!=null);
}else{
if(node[alt]){
ret+=node[alt];
}
}
return ret;
};
this.totalOffsetLeft=function(node){
return this.getTotalOffset(node,"left");
};
this.getAbsoluteX=this.totalOffsetLeft;
this.totalOffsetTop=function(node){
return this.getTotalOffset(node,"top");
};
this.getAbsoluteY=this.totalOffsetTop;
this.getEventTarget=function(evt){
if((window["event"])&&(event["srcElement"])){
return event.srcElement;
}else{
if((evt)&&(evt.target)){
return evt.target;
}
}
};
this.getScrollTop=function(){
return document.documentElement.scrollTop||document.body.scrollTop||0;
};
this.getScrollLeft=function(){
return document.documentElement.scrollLeft||document.body.scrollLeft||0;
};
this.evtTgt=this.getEventTarget;
this.getParentOfType=function(node,type){
var _432=node;
type=type.toLowerCase();
while(_432.nodeName.toLowerCase()!=type){
if((!_432)||(_432==(document["body"]||document["documentElement"]))){
return null;
}
_432=_432.parentNode;
}
return _432;
};
this.getAttribute=function(node,attr){
if((!node)||(!node.getAttribute)){
return null;
}
var ta=typeof attr=="string"?attr:new String(attr);
var v=node.getAttribute(ta.toUpperCase());
if((v)&&(typeof v=="string")&&(v!="")){
return v;
}
if(v&&typeof v=="object"&&v.value){
return v.value;
}
if((node.getAttributeNode)&&(node.getAttributeNode(ta))){
return (node.getAttributeNode(ta)).value;
}else{
if(node.getAttribute(ta)){
return node.getAttribute(ta);
}else{
if(node.getAttribute(ta.toLowerCase())){
return node.getAttribute(ta.toLowerCase());
}
}
}
return null;
};
this.getAttr=function(node,attr){
dj_deprecated("dojo.xml.htmlUtil.getAttr is deprecated, use dojo.xml.htmlUtil.getAttribute instead");
dojo.xml.htmlUtil.getAttribute(node,attr);
};
this.hasAttribute=function(node,attr){
var v=this.getAttribute(node,attr);
return v?true:false;
};
this.hasAttr=function(node,attr){
dj_deprecated("dojo.xml.htmlUtil.hasAttr is deprecated, use dojo.xml.htmlUtil.hasAttribute instead");
dojo.xml.htmlUtil.hasAttribute(node,attr);
};
this.getClass=function(node){
if(node.className){
return node.className;
}else{
if(this.hasAttribute(node,"class")){
return this.getAttribute(node,"class");
}
}
return "";
};
this.hasClass=function(node,_433){
var _434=this.getClass(node).split(/\s+/g);
for(var x=0;x<_434.length;x++){
if(_433==_434[x]){
return true;
}
}
return false;
};
this.prependClass=function(node,_435){
if(!node){
return null;
}
if(this.hasAttribute(node,"class")||node.className){
_435+=" "+(node.className||this.getAttribute(node,"class"));
}
return this.setClass(node,_435);
};
this.addClass=function(node,_436){
if(!node){
return null;
}
if(this.hasAttribute(node,"class")||node.className){
_436=(node.className||this.getAttribute(node,"class"))+" "+_436;
}
return this.setClass(node,_436);
};
this.setClass=function(node,_437){
if(!node){
return false;
}
var cs=new String(_437);
try{
if(typeof node.className=="string"){
node.className=cs;
}else{
if(node.setAttribute){
node.setAttribute("class",_437);
node.className=cs;
}else{
return false;
}
}
}
catch(e){
dj_debug("__util__.setClass() failed",e);
}
return true;
};
this.removeClass=function(node,_439){
if(!node){
return false;
}
var _439=dojo.text.trim(new String(_439));
try{
var cs=String(node.className).split(" ");
var nca=[];
for(var i=0;i<cs.length;i++){
if(cs[i]!=_439){
nca.push(cs[i]);
}
}
node.className=nca.join(" ");
}
catch(e){
dj_debug("__util__.removeClass() failed",e);
}
return true;
};
this.classMatchType={ContainsAll:0,ContainsAny:1,IsOnly:2};
this.getElementsByClass=function(_441,_442,_443,_444){
if(!_442){
_442=document;
}
var _445=_441.split(/\s+/g);
var _446=[];
if(_444!=1&&_444!=2){
_444=0;
}
if(false&&document.evaluate){
var _447="//"+(_443||"*")+"[contains(";
if(_444!=_424.classMatchType.ContainsAny){
_447+="concat(' ',@class,' '), ' "+_445.join(" ') and contains(concat(' ',@class,' '), ' ")+" ')]";
}else{
_447+="concat(' ',@class,' '), ' "+_445.join(" ')) or contains(concat(' ',@class,' '), ' ")+" ')]";
}
var _448=document.evaluate(_447,_442,null,XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE,null);
outer:
for(var node=null,i=0;node=_448.snapshotItem(i);i++){
if(_444!=_424.classMatchType.IsOnly){
_446.push(node);
}else{
if(!_424.getClass(node)){
continue outer;
}
var _449=_424.getClass(node).split(/\s+/g);
var _450=new RegExp("(\\s|^)("+_445.join(")|(")+")(\\s|$)");
for(var j=0;j<_449.length;j++){
if(!_449[j].match(_450)){
continue outer;
}
}
_446.push(node);
}
}
}else{
if(!_443){
_443="*";
}
var _451=_442.getElementsByTagName(_443);
outer:
for(var i=0;i<_451.length;i++){
var node=_451[i];
if(!_424.getClass(node)){
continue outer;
}
var _449=_424.getClass(node).split(/\s+/g);
var _450=new RegExp("(\\s|^)(("+_445.join(")|(")+"))(\\s|$)");
var _452=0;
for(var j=0;j<_449.length;j++){
if(_450.test(_449[j])){
if(_444==_424.classMatchType.ContainsAny){
_446.push(node);
continue outer;
}else{
_452++;
}
}else{
if(_444==_424.classMatchType.IsOnly){
continue outer;
}
}
}
if(_452==_445.length){
if(_444==_424.classMatchType.IsOnly&&_452==_449.length){
_446.push(node);
}else{
if(_444==_424.classMatchType.ContainsAll){
_446.push(node);
}
}
}
}
}
return _446;
};
this.getElementsByClassName=this.getElementsByClass;
this.setOpacity=function(node,_453,_454){
var h=dojo.render.html;
if(!_454){
if(_453>=1){
if(h.ie){
this.clearOpacity(node);
return;
}else{
_453=0.999999;
}
}else{
if(_453<0){
_453=0;
}
}
}
if(h.ie){
if(node.nodeName.toLowerCase()=="tr"){
var tds=node.getElementsByTagName("td");
for(var x=0;x<tds.length;x++){
tds[x].style.filter="Alpha(Opacity="+_453*100+")";
}
}
node.style.filter="Alpha(Opacity="+_453*100+")";
}else{
if(h.moz){
node.style.opacity=_453;
node.style.MozOpacity=_453;
}else{
if(h.safari){
node.style.opacity=_453;
node.style.KhtmlOpacity=_453;
}else{
node.style.opacity=_453;
}
}
}
};
this.getOpacity=function(node){
if(dojo.render.html.ie){
var opac=(node.filters&&node.filters.alpha&&typeof node.filters.alpha.opacity=="number"?node.filters.alpha.opacity:100)/100;
}else{
var opac=node.style.opacity||node.style.MozOpacity||node.style.KhtmlOpacity||1;
}
return opac>=0.999999?1:Number(opac);
};
this.clearOpacity=function(node){
var h=dojo.render.html;
if(h.ie){
if(node.filters&&node.filters.alpha){
node.style.filter="";
}
}else{
if(h.moz){
node.style.opacity=1;
node.style.MozOpacity=1;
}else{
if(h.safari){
node.style.opacity=1;
node.style.KhtmlOpacity=1;
}else{
node.style.opacity=1;
}
}
}
};
this.gravity=function(node,e){
var _458=e.pageX||e.clientX+document.body.scrollLeft;
var _459=e.pageY||e.clientY+document.body.scrollTop;
with(dojo.xml.htmlUtil){
var _460=getAbsoluteX(node)+(getInnerWidth(node)/2);
var _461=getAbsoluteY(node)+(getInnerHeight(node)/2);
}
with(arguments.callee){
return ((_458<_460?WEST:EAST)|(_459<_461?NORTH:SOUTH));
}
};
this.gravity.NORTH=1;
this.gravity.SOUTH=1<<1;
this.gravity.EAST=1<<2;
this.gravity.WEST=1<<3;
this.overElement=function(_462,e){
var _463=e.pageX||e.clientX+document.body.scrollLeft;
var _464=e.pageY||e.clientY+document.body.scrollTop;
with(dojo.xml.htmlUtil){
var top=getAbsoluteY(_462);
var _466=top+getInnerHeight(_462);
var left=getAbsoluteX(_462);
var _468=left+getInnerWidth(_462);
}
return (_463>=left&&_463<=_468&&_464>=top&&_464<=_466);
};
this.insertCssRule=function(_469,_470,_471){
if(dojo.render.html.ie){
if(!this.styleSheet){
}
if(!_471){
_471=this.styleSheet.rules.length;
}
return this.styleSheet.addRule(_469,_470,_471);
}else{
if(document.styleSheets[0]&&document.styleSheets[0].insertRule){
if(!this.styleSheet){
}
if(!_471){
_471=this.styleSheet.cssRules.length;
}
var rule=_469+"{"+_470+"}";
return this.styleSheet.insertRule(rule,_471);
}
}
};
this.insertCSSRule=function(_473,_474,_475){
dj_deprecated("dojo.xml.htmlUtil.insertCSSRule is deprecated, use dojo.xml.htmlUtil.insertCssRule instead");
dojo.xml.htmlUtil.insertCssRule(_473,_474,_475);
};
this.removeCssRule=function(_476){
if(!this.styleSheet){
dj_debug("no stylesheet defined for removing rules");
return false;
}
if(dojo.render.html.ie){
if(!_476){
_476=this.styleSheet.rules.length;
this.styleSheet.removeRule(_476);
}
}else{
if(document.styleSheets[0]){
if(!_476){
_476=this.styleSheet.cssRules.length;
}
this.styleSheet.deleteRule(_476);
}
}
return true;
};
this.removeCSSRule=function(_477){
dj_deprecated("dojo.xml.htmlUtil.removeCSSRule is deprecated, use dojo.xml.htmlUtil.removeCssRule instead");
dojo.xml.htmlUtil.removeCssRule(_477);
};
this.insertCssFile=function(URI,doc,_480){
if(!URI){
return;
}
if(!doc){
doc=document;
}
if(doc.baseURI){
URI=new dojo.uri.Uri(doc.baseURI,URI);
}
if(_480&&doc.styleSheets){
var loc=location.href.split("#")[0].substring(0,location.href.indexOf(location.pathname));
for(var i=0;i<doc.styleSheets.length;i++){
if(doc.styleSheets[i].href&&URI==new dojo.uri.Uri(doc.styleSheets[i].href)){
return;
}
}
}
var file=doc.createElement("link");
file.setAttribute("type","text/css");
file.setAttribute("rel","stylesheet");
file.setAttribute("href",URI);
var head=doc.getElementsByTagName("head")[0];
head.appendChild(file);
};
this.insertCSSFile=function(URI,doc,_484){
dj_deprecated("dojo.xml.htmlUtil.insertCSSFile is deprecated, use dojo.xml.htmlUtil.insertCssFile instead");
dojo.xml.htmlUtil.insertCssFile(URI,doc,_484);
};
this.getBackgroundColor=function(node){
var _485;
do{
_485=dojo.xml.domUtil.getStyle(node,"background-color");
if(_485.toLowerCase()=="rgba(0, 0, 0, 0)"){
_485="transparent";
}
if(node==document.body){
node=null;
break;
}
node=node.parentNode;
}while(node&&_485=="transparent");
if(_485=="transparent"){
_485=[255,255,255,0];
}else{
_485=dojo.xml.domUtil.extractRGB(_485);
}
return _485;
};
this.getUniqueId=function(){
return dojo.xml.domUtil.getUniqueId();
};
this.getStyle=function(el,css){
dojo.xml.domUtil.getStyle(el,css);
};
};
dojo.require("dojo.xml.Parse");
dojo.hostenv.conditionalLoadModule({common:["dojo.xml.domUtil"],browser:["dojo.xml.htmlUtil"],svg:["dojo.xml.svgUtil"]});
dojo.hostenv.moduleLoaded("dojo.xml.*");
dojo.provide("dojo.math");
dojo.math=new function(){
this.degToRad=function(x){
return (x*Math.PI)/180;
};
this.radToDeg=function(x){
return (x*180)/Math.PI;
};
this.factorial=function(n){
if(n<1){
return 0;
}
var _487=1;
for(var i=1;i<=n;i++){
_487*=i;
}
return _487;
};
this.permutations=function(n,k){
if(n==0||k==0){
return 1;
}
return (this.factorial(n)/this.factorial(n-k));
};
this.combinations=function(n,r){
if(n==0||r==0){
return 1;
}
return (this.factorial(n)/(this.factorial(n-r)*this.factorial(r)));
};
this.bernstein=function(t,n,i){
return (this.combinations(n,i)*Math.pow(t,i)*Math.pow(1-t,n-i));
};
};
dojo.provide("dojo.math.Math");
dojo.provide("dojo.math.curves");
dojo.require("dojo.math.Math");
dojo.math.curves={Line:function(_490,end){
this.start=_490;
this.end=end;
this.dimensions=_490.length;
for(var i=0;i<_490.length;i++){
_490[i]=Number(_490[i]);
}
for(var i=0;i<end.length;i++){
end[i]=Number(end[i]);
}
this.getValue=function(n){
var _491=new Array(this.dimensions);
for(var i=0;i<this.dimensions;i++){
_491[i]=((this.end[i]-this.start[i])*n)+this.start[i];
}
return _491;
};
return this;
},Bezier:function(pnts){
this.getValue=function(step){
if(step>=1){
step=0.99999999;
}
var _494=new Array(this.p[0].length);
for(var k=0;j<this.p[0].length;k++){
_494[k]=0;
}
for(var j=0;j<this.p[0].length;j++){
var C=0;
var D=0;
for(var i=0;i<this.p.length;i++){
C+=this.p[i][j]*this.p[this.p.length-1][0]*dojo.math.bernstein(step,this.p.length,i);
}
for(var l=0;l<this.p.length;l++){
D+=this.p[this.p.length-1][0]*dojo.math.bernstein(step,this.p.length,l);
}
_494[j]=C/D;
}
return _494;
};
this.p=pnts;
return this;
},CatmullRom:function(pnts,c){
this.getValue=function(step){
var _497=step*(this.p.length-1);
var node=Math.floor(_497);
var _498=_497-node;
var i0=node-1;
if(i0<0){
i0=0;
}
var i=node;
var i1=node+1;
if(i1>=this.p.length){
i1=this.p.length-1;
}
var i2=node+2;
if(i2>=this.p.length){
i2=this.p.length-1;
}
var u=_498;
var u2=_498*_498;
var u3=_498*_498*_498;
var _505=new Array(this.p[0].length);
for(var k=0;k<this.p[0].length;k++){
var x1=(-this.c*this.p[i0][k])+((2-this.c)*this.p[i][k])+((this.c-2)*this.p[i1][k])+(this.c*this.p[i2][k]);
var x2=(2*this.c*this.p[i0][k])+((this.c-3)*this.p[i][k])+((3-2*this.c)*this.p[i1][k])+(-this.c*this.p[i2][k]);
var x3=(-this.c*this.p[i0][k])+(this.c*this.p[i1][k]);
var x4=this.p[i][k];
_505[k]=x1*u3+x2*u2+x3*u+x4;
}
return _505;
};
if(!c){
this.c=0.7;
}else{
this.c=c;
}
this.p=pnts;
return this;
},Arc:function(_510,end,ccw){
var _512=dojo.math.points.midpoint(_510,end);
var _513=dojo.math.points.translate(dojo.math.points.invert(_512),_510);
var rad=Math.sqrt(Math.pow(_513[0],2)+Math.pow(_513[1],2));
var _515=dojo.math.radToDeg(Math.atan(_513[1]/_513[0]));
if(_513[0]<0){
_515-=90;
}else{
_515+=90;
}
dojo.math.curves.CenteredArc.call(this,_512,rad,_515,_515+(ccw?-180:180));
},CenteredArc:function(_516,_517,_518,end){
this.center=_516;
this.radius=_517;
this.start=_518||0;
this.end=end;
this.getValue=function(n){
var _519=new Array(2);
var _520=dojo.math.degToRad(this.start+((this.end-this.start)*n));
_519[0]=this.center[0]+this.radius*Math.sin(_520);
_519[1]=this.center[1]-this.radius*Math.cos(_520);
return _519;
};
return this;
},Circle:function(_521,_522){
dojo.math.curves.CenteredArc.call(this,_521,_522,0,360);
return this;
},Path:function(){
var _523=[];
var _524=[];
var _525=[];
var _526=0;
this.add=function(_527,_528){
if(_528<0){
dj_throw("dojo.math.curves.Path.add: weight cannot be less than 0");
}
_523.push(_527);
_524.push(_528);
_526+=_528;
computeRanges();
};
this.remove=function(_529){
for(var i=0;i<_523.length;i++){
if(_523[i]==_529){
_523.splice(i,1);
_526-=_524.splice(i,1)[0];
break;
}
}
computeRanges();
};
this.removeAll=function(){
_523=[];
_524=[];
_526=0;
};
this.getValue=function(n){
var _530=false,value=0;
for(var i=0;i<_525.length;i++){
var r=_525[i];
if(n>=r[0]&&n<r[1]){
var subN=(n-r[0])/r[2];
value=_523[i].getValue(subN);
_530=true;
break;
}
}
if(!_530){
value=_523[_523.length-1].getValue(1);
}
for(j=0;j<i;j++){
value=dojo.math.points.translate(value,_523[j].getValue(1));
}
return value;
};
function computeRanges(){
var _532=0;
for(var i=0;i<_524.length;i++){
var end=_532+_524[i]/_526;
var len=end-_532;
_525[i]=[_532,end,len];
_532=end;
}
}
return this;
}};
dojo.provide("dojo.animation");
dojo.provide("dojo.animation.Animation");
dojo.require("dojo.math.Math");
dojo.require("dojo.math.curves");
dojo.animation={};
dojo.animation.Animation=function(_533,_534,_535,_536){
var _537=this;
this.curve=_533;
this.duration=_534;
this.accel=_535;
this.repeatCount=_536||0;
this.animSequence_=null;
this.onBegin=null;
this.onAnimate=null;
this.onEnd=null;
this.onPlay=null;
this.onPause=null;
this.onStop=null;
this.handler=null;
var _538=null,endTime=null,lastFrame=null,timer=null,percent=0,active=false,paused=false;
this.play=function(_539){
if(_539){
clearTimeout(timer);
active=false;
paused=false;
percent=0;
}else{
if(active&&!paused){
return;
}
}
_538=new Date().valueOf();
if(paused){
_538-=(_537.duration*percent/100);
}
endTime=_538+_537.duration;
lastFrame=_538;
var e=new dojo.animation.AnimationEvent(_537,null,_537.curve.getValue(percent),_538,_538,endTime,_537.duration,percent,0);
active=true;
paused=false;
if(percent==0){
e.type="begin";
if(typeof _537.handler=="function"){
_537.handler(e);
}
if(typeof _537.onBegin=="function"){
_537.onBegin(e);
}
}
e.type="play";
if(typeof _537.handler=="function"){
_537.handler(e);
}
if(typeof _537.onPlay=="function"){
_537.onPlay(e);
}
if(this.animSequence_){
this.animSequence_.setCurrent(this);
}
cycle();
};
this.pause=function(){
clearTimeout(timer);
if(!active){
return;
}
paused=true;
var e=new dojo.animation.AnimationEvent(_537,"pause",_537.curve.getValue(percent),_538,new Date().valueOf(),endTime,_537.duration,percent,0);
if(typeof _537.handler=="function"){
_537.handler(e);
}
if(typeof _537.onPause=="function"){
_537.onPause(e);
}
};
this.playPause=function(){
if(!active||paused){
_537.play();
}else{
_537.pause();
}
};
this.gotoPercent=function(pct,_541){
clearTimeout(timer);
active=true;
paused=true;
percent=pct;
if(_541){
this.play();
}
};
this.stop=function(_542){
clearTimeout(timer);
var step=percent/100;
if(_542){
step=1;
}
var e=new dojo.animation.AnimationEvent(_537,"stop",_537.curve.getValue(step),_538,new Date().valueOf(),endTime,_537.duration,percent,Math.round(fps));
if(typeof _537.handler=="function"){
_537.handler(e);
}
if(typeof _537.onStop=="function"){
_537.onStop(e);
}
active=false;
paused=false;
};
this.status=function(){
if(active){
return paused?"paused":"playing";
}else{
return "stopped";
}
};
function cycle(){
clearTimeout(timer);
if(active){
var curr=new Date().valueOf();
var step=(curr-_538)/(endTime-_538);
fps=1000/(curr-lastFrame);
lastFrame=curr;
if(step>=1){
step=1;
percent=100;
}else{
percent=step*100;
}
var e=new dojo.animation.AnimationEvent(_537,"animate",_537.curve.getValue(step),_538,curr,endTime,_537.duration,percent,Math.round(fps));
if(typeof _537.handler=="function"){
_537.handler(e);
}
if(typeof _537.onAnimate=="function"){
_537.onAnimate(e);
}
if(step<1){
timer=setTimeout(cycle,10);
}else{
e.type="end";
active=false;
if(typeof _537.handler=="function"){
_537.handler(e);
}
if(typeof _537.onEnd=="function"){
_537.onEnd(e);
}
if(_537.repeatCount>0){
_537.repeatCount--;
_537.play(true);
}else{
if(_537.repeatCount==-1){
_537.play(true);
}else{
if(_537.animSequence_){
_537.animSequence_.playNext();
}
}
}
}
}
}
};
dojo.animation.AnimationEvent=function(anim,type,_545,_546,_547,_548,dur,pct,fps){
this.type=type;
this.animation=anim;
this.coords=_545;
this.x=_545[0];
this.y=_545[1];
this.z=_545[2];
this.startTime=_546;
this.currentTime=_547;
this.endTime=_548;
this.duration=dur;
this.percent=pct;
this.fps=fps;
this.coordsAsInts=function(){
var _551=new Array(this.coords.length);
for(var i=0;i<this.coords.length;i++){
_551[i]=Math.round(this.coords[i]);
}
return _551;
};
return this;
};
dojo.animation.AnimationSequence=function(_552){
var _553=[];
var _554=-1;
this.repeatCount=_552||0;
this.onBegin=null;
this.onEnd=null;
this.onNext=null;
this.handler=null;
this.add=function(){
for(var i=0;i<arguments.length;i++){
_553.push(arguments[i]);
arguments[i].animSequence_=this;
}
};
this.remove=function(anim){
for(var i=0;i<_553.length;i++){
if(_553[i]==anim){
_553[i].animSequence_=null;
_553.splice(i,1);
break;
}
}
};
this.removeAll=function(){
for(var i=0;i<_553.length;i++){
_553[i].animSequence_=null;
}
_553=[];
_554=-1;
};
this.play=function(_555){
if(_553.length==0){
return;
}
if(_555||!_553[_554]){
_554=0;
}
if(_553[_554]){
if(_554==0){
var e={type:"begin",animation:_553[_554]};
if(typeof this.handler=="function"){
this.handler(e);
}
if(typeof this.onBegin=="function"){
this.onBegin(e);
}
}
_553[_554].play(_555);
}
};
this.pause=function(){
if(_553[_554]){
_553[_554].pause();
}
};
this.playPause=function(){
if(_553.length==0){
return;
}
if(_554==-1){
_554=0;
}
if(_553[_554]){
_553[_554].playPause();
}
};
this.stop=function(){
if(_553[_554]){
_553[_554].stop();
}
};
this.status=function(){
if(_553[_554]){
return _553[_554].status();
}else{
return "stopped";
}
};
this.setCurrent=function(anim){
for(var i=0;i<_553.length;i++){
if(_553[i]==anim){
_554=i;
break;
}
}
};
this.playNext=function(){
if(_554==-1||_553.length==0){
return;
}
_554++;
if(_553[_554]){
var e={type:"next",animation:_553[_554]};
if(typeof this.handler=="function"){
this.handler(e);
}
if(typeof this.onNext=="function"){
this.onNext(e);
}
_553[_554].play(true);
}else{
var e={type:"end",animation:_553[_553.length-1]};
if(typeof this.handler=="function"){
this.handler(e);
}
if(typeof this.onEnd=="function"){
this.onEnd(e);
}
if(this.repeatCount>0){
_554=0;
this.repeatCount--;
_553[_554].play(true);
}else{
if(this.repeatCount==-1){
_554=0;
_553[_554].play(true);
}else{
_554=-1;
}
}
}
};
};
dojo.hostenv.conditionalLoadModule({common:["dojo.animation.Animation",false,false]});
dojo.hostenv.moduleLoaded("dojo.animation.*");
dojo.provide("dojo.graphics.htmlEffects");
dojo.require("dojo.animation.*");
dojo.require("dojo.xml.domUtil");
dojo.require("dojo.xml.htmlUtil");
dojo.require("dojo.event.*");
dojo.require("dojo.alg.*");
dojo.graphics.htmlEffects=new function(){
this.fadeOut=function(node,_556,_557){
return this.fade(node,_556,dojo.xml.htmlUtil.getOpacity(node),0,_557);
};
this.fadeIn=function(node,_558,_559){
return this.fade(node,_558,dojo.xml.htmlUtil.getOpacity(node),1,_559);
};
this.fadeHide=function(node,_560,_561){
if(!_560){
_560=150;
}
return this.fadeOut(node,_560,function(node){
node.style.display="none";
if(typeof _561=="function"){
_561(node);
}
});
};
this.fadeShow=function(node,_562,_563){
if(!_562){
_562=150;
}
node.style.display="block";
return this.fade(node,_562,0,1,_563);
};
this.fade=function(node,_564,_565,_566,_567){
var anim=new dojo.animation.Animation(new dojo.math.curves.Line([_565],[_566]),_564,0);
dojo.event.connect(anim,"onAnimate",function(e){
dojo.xml.htmlUtil.setOpacity(node,e.x);
});
if(_567){
dojo.event.connect(anim,"onEnd",function(e){
_567(node,anim);
});
}
anim.play(true);
return anim;
};
this.slideTo=function(node,_568,_569,_570){
return this.slide(node,[node.offsetLeft,node.offsetTop],_568,_569,_570);
};
this.slideBy=function(node,_571,_572,_573){
return this.slideTo(node,[node.offsetLeft+_571[0],node.offsetTop+_571[1]],_572,_573);
};
this.slide=function(node,_574,_575,_576,_577){
var anim=new dojo.animation.Animation(new dojo.math.curves.Line(_574,_575),_576,0);
dojo.event.connect(anim,"onAnimate",function(e){
with(node.style){
left=e.x+"px";
top=e.y+"px";
}
});
if(_577){
dojo.event.connect(anim,"onEnd",function(e){
_577(node,anim);
});
}
anim.play(true);
return anim;
};
this.colorFadeIn=function(node,_578,_579,_580,_581){
var _582=dojo.xml.htmlUtil.getBackgroundColor(node);
var bg=dojo.xml.domUtil.getStyle(node,"background-color").toLowerCase();
var _584=bg=="transparent"||bg=="rgba(0, 0, 0, 0)";
while(_582.length>3){
_582.pop();
}
while(_578.length>3){
_578.pop();
}
var anim=this.colorFade(node,_578,_582,_579,_581,true);
dojo.event.connect(anim,"onEnd",function(e){
if(_584){
node.style.backgroundColor="transparent";
}
});
if(_580>0){
node.style.backgroundColor="rgb("+_578.join(",")+")";
setTimeout(function(){
anim.play(true);
},_580);
}else{
anim.play(true);
}
return anim;
};
this.highlight=this.colorFadeIn;
this.colorFadeFrom=this.colorFadeIn;
this.colorFadeOut=function(node,_585,_586,_587,_588){
var _589=dojo.xml.htmlUtil.getBackgroundColor(node);
while(_589.length>3){
_589.pop();
}
while(_585.length>3){
_585.pop();
}
var anim=this.colorFade(node,_589,_585,_586,_588,_587>0);
if(_587>0){
node.style.backgroundColor="rgb("+_589.join(",")+")";
setTimeout(function(){
anim.play(true);
},_587);
}
return anim;
};
this.unhighlight=this.colorFadeOut;
this.colorFadeTo=this.colorFadeOut;
this.colorFade=function(node,_590,_591,_592,_593,_594){
while(_590.length>3){
_590.pop();
}
while(_591.length>3){
_591.pop();
}
var anim=new dojo.animation.Animation(new dojo.math.curves.Line(_590,_591),_592,0);
dojo.event.connect(anim,"onAnimate",function(e){
node.style.backgroundColor="rgb("+e.coordsAsInts().join(",")+")";
});
if(_593){
dojo.event.connect(anim,"onEnd",function(e){
_593(node,anim);
});
}
if(!_594){
anim.play(true);
}
return anim;
};
this.wipeIn=function(node,_595,_596,_597){
var _598=dojo.xml.htmlUtil.getStyle(node,"overflow");
var _599=dojo.xml.htmlUtil.getStyle(node,"height");
node.style.display=dojo.alg.inArray(node.tagName.toLowerCase(),["tr","td","th"])?"":"block";
var _600=node.offsetHeight;
if(_598=="visible"){
node.style.overflow="hidden";
}
node.style.height=0;
var anim=new dojo.animation.Animation(new dojo.math.curves.Line([0],[_600]),_595,0);
dojo.event.connect(anim,"onAnimate",function(e){
node.style.height=Math.round(e.x)+"px";
});
dojo.event.connect(anim,"onEnd",function(e){
if(_598!="visible"){
node.style.overflow=_598;
}
node.style.height=_599;
if(_596){
_596(node,anim);
}
});
if(!_597){
anim.play(true);
}
return anim;
};
this.wipeOut=function(node,_601,_602,_603){
var _604=dojo.xml.htmlUtil.getStyle(node,"overflow");
var _605=dojo.xml.htmlUtil.getStyle(node,"height");
var _606=node.offsetHeight;
node.style.overflow="hidden";
var anim=new dojo.animation.Animation(new dojo.math.curves.Line([_606],[0]),_601,0);
dojo.event.connect(anim,"onAnimate",function(e){
node.style.height=Math.round(e.x)+"px";
});
dojo.event.connect(anim,"onEnd",function(e){
node.style.display="none";
node.style.overflow=_604;
node.style.height=_605;
if(_602){
_602(node,anim);
}
});
if(!_603){
anim.play(true);
}
return anim;
};
this.explode=function(_607,_608,_609,_610){
var _611=[dojo.xml.htmlUtil.getAbsoluteX(_607),dojo.xml.htmlUtil.getAbsoluteY(_607),dojo.xml.htmlUtil.getInnerWidth(_607),dojo.xml.htmlUtil.getInnerHeight(_607)];
return this.explodeFromBox(_611,_608,_609,_610);
};
this.explodeFromBox=function(_612,_613,_614,_615){
var _616=document.createElement("div");
with(_616.style){
position="absolute";
border="1px solid black";
display="none";
}
document.body.appendChild(_616);
with(_613.style){
visibility="hidden";
display="block";
}
var _617=[dojo.xml.htmlUtil.getAbsoluteX(_613),dojo.xml.htmlUtil.getAbsoluteY(_613),dojo.xml.htmlUtil.getInnerWidth(_613),dojo.xml.htmlUtil.getInnerHeight(_613)];
with(_613.style){
display="none";
visibility="visible";
}
var anim=new dojo.animation.Animation(new dojo.math.curves.Line(_612,_617),_614,0);
dojo.event.connect(anim,"onBegin",function(e){
_616.style.display="block";
});
dojo.event.connect(anim,"onAnimate",function(e){
with(_616.style){
left=e.x+"px";
top=e.y+"px";
width=e.coords[2]+"px";
height=e.coords[3]+"px";
}
});
dojo.event.connect(anim,"onEnd",function(){
_613.style.display="block";
_616.parentNode.removeChild(_616);
if(_615){
_615(_613,anim);
}
});
anim.play();
return anim;
};
this.implode=function(_618,_619,_620,_621){
var _622=[dojo.xml.htmlUtil.getAbsoluteX(_619),dojo.xml.htmlUtil.getAbsoluteY(_619),dojo.xml.htmlUtil.getInnerWidth(_619),dojo.xml.htmlUtil.getInnerHeight(_619)];
return this.implodeToBox(_618,_622,_620,_621);
};
this.implodeToBox=function(_623,_624,_625,_626){
var _627=document.createElement("div");
with(_627.style){
position="absolute";
border="1px solid black";
display="none";
}
document.body.appendChild(_627);
var anim=new dojo.animation.Animation(new dojo.math.curves.Line([dojo.xml.htmlUtil.getAbsoluteX(_623),dojo.xml.htmlUtil.getAbsoluteY(_623),dojo.xml.htmlUtil.getInnerWidth(_623),dojo.xml.htmlUtil.getInnerHeight(_623)],_624),_625,0);
dojo.event.connect(anim,"onBegin",function(e){
_623.style.display="none";
_627.style.display="block";
});
dojo.event.connect(anim,"onAnimate",function(e){
with(_627.style){
left=e.x+"px";
top=e.y+"px";
width=e.coords[2]+"px";
height=e.coords[3]+"px";
}
});
dojo.event.connect(anim,"onEnd",function(){
_627.parentNode.removeChild(_627);
if(_626){
_626(_623,anim);
}
});
anim.play();
return anim;
};
};
dojo.graphics.htmlEffects.Exploder=function(_628,_629){
var _630=this;
this.waitToHide=500;
this.timeToShow=100;
this.waitToShow=200;
this.timeToHide=70;
this.autoShow=false;
this.autoHide=false;
var _631=null;
var _632=null;
var _633=null;
var _634=null;
var _635=null;
var _636=null;
this.showing=false;
this.onBeforeExplode=null;
this.onAfterExplode=null;
this.onBeforeImplode=null;
this.onAfterImplode=null;
this.onExploding=null;
this.onImploding=null;
this.timeShow=function(){
clearTimeout(_633);
_633=setTimeout(_630.show,_630.waitToShow);
};
this.show=function(){
clearTimeout(_633);
clearTimeout(_634);
if((_632&&_632.status()=="playing")||(_631&&_631.status()=="playing")||_630.showing){
return;
}
if(typeof _630.onBeforeExplode=="function"){
_630.onBeforeExplode(_628,_629);
}
_631=dojo.graphics.htmlEffects.explode(_628,_629,_630.timeToShow,function(e){
_630.showing=true;
if(typeof _630.onAfterExplode=="function"){
_630.onAfterExplode(_628,_629);
}
});
if(typeof _630.onExploding=="function"){
dojo.event.connect(_631,"onAnimate",this,"onExploding");
}
};
this.timeHide=function(){
clearTimeout(_633);
clearTimeout(_634);
if(_630.showing){
_634=setTimeout(_630.hide,_630.waitToHide);
}
};
this.hide=function(){
clearTimeout(_633);
clearTimeout(_634);
if(_631&&_631.status()=="playing"){
return;
}
_630.showing=false;
if(typeof _630.onBeforeImplode=="function"){
_630.onBeforeImplode(_628,_629);
}
_632=dojo.graphics.htmlEffects.implode(_629,_628,_630.timeToHide,function(e){
if(typeof _630.onAfterImplode=="function"){
_630.onAfterImplode(_628,_629);
}
});
if(typeof _630.onImploding=="function"){
dojo.event.connect(_632,"onAnimate",this,"onImploding");
}
};
dojo.event.connect(_628,"onclick",function(e){
if(_630.showing){
_630.hide();
}else{
_630.show();
}
});
dojo.event.connect(_628,"onmouseover",function(e){
if(_630.autoShow){
_630.timeShow();
}
});
dojo.event.connect(_628,"onmouseout",function(e){
if(_630.autoHide){
_630.timeHide();
}
});
dojo.event.connect(_629,"onmouseover",function(e){
clearTimeout(_634);
});
dojo.event.connect(_629,"onmouseout",function(e){
if(_630.autoHide){
_630.timeHide();
}
});
dojo.event.connect(document.documentElement||document.body,"onclick",function(e){
if(_630.autoHide&&_630.showing&&!dojo.xml.domUtil.isChildOf(e.target,_629)&&!dojo.xml.domUtil.isChildOf(e.target,_628)){
_630.hide();
}
});
return this;
};
dojo.hostenv.conditionalLoadModule({browser:["dojo.graphics.htmlEffects"]});
dojo.hostenv.moduleLoaded("dojo.graphics.*");
dojo.require("dojo.lang.*");
dojo.provide("dojo.dnd.DragSource");
dojo.provide("dojo.dnd.DropTarget");
dojo.provide("dojo.dnd.DragObject");
dojo.provide("dojo.dnd.DragManager");
dojo.provide("dojo.dnd.DragAndDrop");
dojo.dnd.DragSource=function(){
dojo.dnd.dragManager.registerDragSource(this);
};
dojo.lang.extend(dojo.dnd.DragSource,{type:"",onDragEnd:function(){
},onDragStart:function(){
}});
dojo.dnd.DragObject=function(){
dojo.dnd.dragManager.registerDragObject(this);
};
dojo.lang.extend(dojo.dnd.DragObject,{type:"",onDragStart:function(){
},onDragMove:function(){
},onDragOver:function(){
},onDragOut:function(){
},onDragEnd:function(){
},onDragLeave:this.onDragOut,onDragEnter:this.onDragOver,ondragout:this.onDragOut,ondragover:this.onDragOver});
dojo.dnd.DropTarget=function(){
dojo.dnd.dragManager.registerDropTarget(this);
};
dojo.lang.extend(dojo.dnd.DropTarget,{acceptedTypes:[],onDragOver:function(){
},onDragOut:function(){
},onDragMove:function(){
},onDrop:function(){
}});
dojo.dnd.DragEvent=function(){
this.dragSource=null;
this.dragObject=null;
this.target=null;
this.eventSatus="success";
};
dojo.dnd.DragManager=function(){
};
dojo.lang.extend(dojo.dnd.DragManager,{selectedSources:[],dragObjects:[],dragSources:[],registerDragSource:function(){
},dropTargets:[],registerDropTarget:function(){
},lastDragTarget:null,currentDragTarget:null,onKeyDown:function(){
},onMouseOut:function(){
},onMouseMove:function(){
},onMouseUp:function(){
}});
dojo.dnd.dragManager=null;
dojo.provide("dojo.dnd.HtmlDragManager");
dojo.require("dojo.event.*");
dojo.require("dojo.alg.*");
dojo.require("dojo.xml.htmlUtil");
dojo.dnd.HtmlDragManager=function(){
};
dj_inherits(dojo.dnd.HtmlDragManager,dojo.dnd.DragManager);
dojo.lang.extend(dojo.dnd.HtmlDragManager,{mouseDownTimer:null,dsCounter:0,dsPrefix:"dojoDragSource",dropTargetDimensions:[],currentDropTarget:null,currentDropTargetPoints:null,previousDropTarget:null,currentX:null,currentY:null,lastX:null,lastY:null,mouseDownX:null,mouseDownY:null,dropAcceptable:false,registerDragSource:function(ds){
var dp=this.dsPrefix;
var _639=dp+"Idx_"+(this.dsCounter++);
ds.dragSourceId=_639;
this.dragSources[_639]=ds;
ds.domNode.setAttribute(dp,_639);
},registerDropTarget:function(dt){
this.dropTargets.push(dt);
},getDragSource:function(e){
var tn=e.target;
if(tn===document.body){
return;
}
var ta=dojo.xml.htmlUtil.getAttribute(tn,this.dsPrefix);
while((!ta)&&(tn)){
tn=tn.parentNode;
if((!tn)||(tn===document.body)){
return;
}
ta=dojo.xml.htmlUtil.getAttribute(tn,this.dsPrefix);
}
return this.dragSources[ta];
},onKeyDown:function(e){
},onMouseDown:function(e){
var ds=this.getDragSource(e);
if(!ds){
return;
}
if(!dojo.alg.inArray(this.selectedSources,ds)){
this.selectedSources.push(ds);
}
dojo.event.connect(document,"onmousemove",this,"onMouseMove");
},onMouseUp:function(e){
var _641=this;
if((!e.shiftKey)&&(!e.ctrlKey)){
dojo.alg.forEach(this.dragObjects,function(_642){
var ret=null;
if(!_642){
return;
}
if(_641.currentDropTarget){
e.dragObject=_642;
var ce=_641.currentDropTarget.domNode.childNodes;
if(ce.length>0){
e.dropTarget=ce[0];
while(e.dropTarget==_642.domNode){
e.dropTarget=e.dropTarget.nextSibling;
}
}else{
e.dropTarget=_641.currentDropTarget.domNode;
}
if(_641.dropAcceptable){
ret=_641.currentDropTarget.onDrop(e);
}else{
_641.currentDropTarget.onDragOut(e);
}
}
_642.onDragEnd({dragStatus:(_641.dropAcceptable&&ret)?"dropSuccess":"dropFailure"});
});
this.selectedSources=[];
this.dragObjects=[];
}
dojo.event.disconnect(document,"onmousemove",this,"onMouseMove");
this.currentDropTarget=null;
this.currentDropTargetPoints=null;
},onMouseMove:function(e){
var _644=this;
if((this.selectedSources.length)&&(!this.dragObjects.length)){
dojo.alg.forEach(this.selectedSources,function(_645){
if(!_645){
return;
}
var tdo=_645.onDragStart(e);
if(tdo){
tdo.onDragStart(e);
_644.dragObjects.push(tdo);
}
});
this.dropTargetDimensions=[];
dojo.alg.forEach(this.dropTargets,function(_647){
var hu=dojo.xml.htmlUtil;
var tn=_647.domNode;
var ttx=hu.getAbsoluteX(tn);
var tty=hu.getAbsoluteY(tn);
_644.dropTargetDimensions.push([[ttx,tty],[ttx+hu.getInnerWidth(tn),tty+hu.getInnerHeight(tn)],_647]);
});
}
dojo.alg.forEach(this.dragObjects,function(_651){
if(!_651){
return;
}
_651.onDragMove(e);
});
var dtp=this.currentDropTargetPoints;
if((dtp)&&(_644.isInsideBox(e,dtp))){
this.currentDropTarget.onDragMove(e);
}else{
if(this.currentDropTarget){
this.currentDropTarget.onDragOut(e);
}
this.currentDropTarget=null;
this.currentDropTargetPoints=null;
this.dropAcceptable=false;
dojo.alg.forEach(this.dropTargetDimensions,function(_653){
if((!_644.currentDropTarget)&&(_644.isInsideBox(e,_653))){
_644.currentDropTarget=_653[2];
_644.currentDropTargetPoints=_653;
return "break";
}
});
e.dragObjects=this.dragObjects;
if(this.currentDropTarget){
this.dropAcceptable=this.currentDropTarget.onDragOver(e);
}
}
},isInsideBox:function(e,_654){
if((e.clientX>_654[0][0])&&(e.clientX<_654[1][0])&&(e.clientY>_654[0][1])&&(e.clientY<_654[1][1])){
return true;
}
return false;
},onMouseOver:function(e){
},onMouseOut:function(e){
}});
dojo.dnd.dragManager=new dojo.dnd.HtmlDragManager();
(function(){
var d=document;
var dm=dojo.dnd.dragManager;
dojo.event.connect(d,"onkeydown",dm,"onKeyDown");
dojo.event.connect(d,"onmouseover",dm,"onMouseOver");
dojo.event.connect(d,"onmouseout",dm,"onMouseOut");
dojo.event.connect(d,"onmousedown",dm,"onMouseDown");
dojo.event.connect(d,"onmouseup",dm,"onMouseUp");
})();
dojo.provide("dojo.dnd.HtmlDragAndDrop");
dojo.provide("dojo.dnd.HtmlDragSource");
dojo.provide("dojo.dnd.HtmlDropTarget");
dojo.provide("dojo.dnd.HtmlDragObject");
dojo.require("dojo.dnd.HtmlDragManager");
dojo.require("dojo.animation.*");
dojo.dnd.HtmlDragSource=function(node,type){
this.domNode=node;
dojo.dnd.DragSource.call(this);
this.type=type||this.domNode.nodeName.toLowerCase();
};
dojo.lang.extend(dojo.dnd.HtmlDragSource,{onDragStart:function(){
return new dojo.dnd.HtmlDragObject(this.domNode,this.type);
}});
dojo.dnd.HtmlDragObject=function(node,type){
this.type=type;
this.domNode=node;
};
dojo.lang.extend(dojo.dnd.HtmlDragObject,{onDragStart:function(e){
if(document.selection){
document.selection.clear();
}else{
if(window.getSelection&&window.getSelection().removeAllRanges){
window.getSelection().removeAllRanges();
}
}
this.dragStartPosition={top:dojo.xml.htmlUtil.getAbsoluteY(this.domNode),left:dojo.xml.htmlUtil.getAbsoluteX(this.domNode)};
this.dragOffset={top:this.dragStartPosition.top-e.clientY,left:this.dragStartPosition.left-e.clientX};
this.dragClone=this.domNode.cloneNode(true);
this.domNode.parentNode.replaceChild(this.dragClone,this.domNode);
with(this.domNode.style){
position="absolute";
top=this.dragOffset.top+e.clientY+"px";
left=this.dragOffset.left+e.clientY+"px";
}
dojo.xml.htmlUtil.setOpacity(this.domNode,0.5);
document.body.appendChild(this.domNode);
},onDragMove:function(e){
this.domNode.style.top=this.dragOffset.top+e.clientY+"px";
this.domNode.style.left=this.dragOffset.left+e.clientX+"px";
},onDragEnd:function(e){
switch(e.dragStatus){
case "dropSuccess":
with(this.domNode.style){
position=null;
left=null;
top=null;
}
this.dragClone.parentNode.removeChild(this.dragClone);
this.dragClone=null;
dojo.xml.htmlUtil.setOpacity(this.domNode,1);
break;
case "dropFailure":
with(dojo.xml.htmlUtil){
var _656=[getAbsoluteX(this.domNode),getAbsoluteY(this.domNode)];
}
var _657=[this.dragStartPosition.left+1,this.dragStartPosition.top+1];
var line=new dojo.math.curves.Line(_656,_657);
var anim=new dojo.animation.Animation(line,300,0,0);
var _659=this;
dojo.event.connect(anim,"onAnimate",function(e){
_659.domNode.style.left=e.x+"px";
_659.domNode.style.top=e.y+"px";
});
dojo.event.connect(anim,"onEnd",function(e){
setTimeout(function(){
dojo.xml.htmlUtil.setOpacity(_659.domNode,1);
_659.dragClone.parentNode.replaceChild(_659.domNode,_659.dragClone);
with(_659.domNode.style){
position=null;
left=null;
top=null;
}
},200);
});
anim.play();
break;
}
}});
dojo.dnd.HtmlDropTarget=function(node,_660){
this.domNode=node;
dojo.dnd.DropTarget.call(this);
this.acceptedTypes=_660||[];
};
dojo.lang.extend(dojo.dnd.HtmlDropTarget,{onDragOver:function(e){
var dos=e.dragObjects;
if(!dos){
return false;
}
var _662=false;
var _663=this;
dojo.alg.forEach(dos,function(tdo){
if((_663.acceptedTypes)&&(dojo.alg.inArray(_663.acceptedTypes,tdo.type))){
_662=true;
return "break";
}
});
this.childBoxes=[];
for(var i=0,child;i<this.domNode.childNodes.length;i++){
child=this.domNode.childNodes[i];
if(child.nodeType!=dojo.xml.domUtil.nodeTypes.ELEMENT_NODE){
continue;
}
with(dojo.xml.htmlUtil){
var top=getAbsoluteY(child);
var _664=top+getInnerHeight(child);
var left=getAbsoluteX(child);
var _665=left+getInnerWidth(child);
}
this.childBoxes.push({top:top,bottom:_664,left:left,right:_665,node:child});
}
return _662;
},onDragMove:function(e){
var _666=e.pageX||e.clientX+document.body.scrollLeft;
var _667=e.pageY||e.clientY+document.body.scrollTop;
for(var i=0,child;i<this.childBoxes.length;i++){
with(this.childBoxes[i]){
if(_666>=left&&_666<=right&&_667>=top&&_667<=bottom){
break;
}
}
}
if(i==this.childBoxes.length){
return;
}
if(!this.dropIndicator){
this.dropIndicator=document.createElement("div");
with(this.dropIndicator.style){
position="absolute";
background="black";
height="1px";
width=dojo.xml.htmlUtil.getInnerWidth(this.domNode)+"px";
left=dojo.xml.htmlUtil.getAbsoluteX(this.domNode)+"px";
}
}
with(this.dropIndicator.style){
var _668=0,gravity=dojo.xml.htmlUtil.gravity;
if(gravity(this.childBoxes[i].node,e)&gravity.SOUTH){
if(this.childBoxes[i+1]){
i+=1;
}else{
_668=this.childBoxes[i].bottom-this.childBoxes[i].top;
}
}
top=this.childBoxes[i].top+_668+"px";
}
if(!this.dropIndicator.parentNode){
document.body.appendChild(this.dropIndicator);
}
},onDragOut:function(e){
dojo.xml.domUtil.remove(this.dropIndicator);
},onDrop:function(e){
this.onDragOut(e);
var _669=e.pageX||e.clientX+document.body.scrollLeft;
var _670=e.pageY||e.clientY+document.body.scrollTop;
for(var i=0,child;i<this.childBoxes.length;i++){
with(this.childBoxes[i]){
if(_669>=left&&_669<=right&&_670>=top&&_670<=bottom){
break;
}
}
}
if(i==this.childBoxes.length){
return false;
}
var _671=dojo.xml.htmlUtil.gravity,child=this.childBoxes[i].node;
if(_671(child,e)&_671.SOUTH){
dojo.xml.domUtil.after(e.dragObject.domNode,child);
}else{
dojo.xml.domUtil.before(e.dragObject.domNode,child);
}
return true;
}});
dojo.hostenv.conditionalLoadModule({common:["dojo.dnd.DragAndDrop"],browser:["dojo.dnd.HtmlDragAndDrop"]});
dojo.hostenv.moduleLoaded("dojo.dnd.*");
dojo.provide("dojo.widget.Manager");
dojo.require("dojo.alg.*");
dojo.widget.manager=new function(){
this.widgets=[];
this.widgetIds=[];
this.root=null;
var _672=0;
this.getUniqueId=function(){
return _672++;
};
this.add=function(_673){
this.widgets.push(_673);
if(_673.widgetId==""){
_673.widgetId=_673.widgetType+"_"+this.getUniqueId();
}else{
if(this.widgetIds[_673.widgetId]){
dj_debug("widget ID collision on ID: "+_673.widgetId);
}
}
this.widgetIds[_673.widgetId]=_673;
};
this.destroyAll=function(){
for(var x=this.widgets.length-1;x>=0;x--){
try{
this.widgets[x].destroy(true);
delete this.widgets[x];
}
catch(e){
}
}
};
this.remove=function(_674){
var tw=this.widgets[_674].widgetId;
delete this.widgetIds[tw];
this.widgets.splice(_674,1);
};
this.removeById=function(id){
for(var i=0;i<this.widgets.length;i++){
if(this.widgets[i].widgetId==id){
this.remove(i);
break;
}
}
};
this.getWidgetById=function(id){
return this.widgetIds[id];
};
this.getWidgetsByType=function(type){
var lt=type.toLowerCase();
var ret=[];
dojo.alg.forEach(this.widgets,function(x){
if(x.widgetType.toLowerCase()==lt){
ret.push(x);
}
});
return ret;
};
this.getWidgetsOfType=function(id){
dj_deprecated("getWidgetsOfType is depecrecated, use getWidgetsByType");
return dojo.widget.manager.getWidgetsByType(id);
};
this.getWidgetsByFilter=function(_677){
var ret=[];
dojo.alg.forEach(this.widgets,function(x){
if(_677(x)){
ret.push(x);
}
});
return ret;
};
var _678=[];
var _679=["dojo.widget","dojo.webui.widgets"];
for(var i=0;i<_679.length;i++){
_679[_679[i]]=true;
}
this.registerWidgetPackage=function(_680){
if(!_679[_680]){
_679[_680]=true;
_679.push(_680);
}
};
this.getImplementation=function(_681,_682,_683){
var impl=this.getImplementationName(_681);
if(impl){
var item=new impl(_682);
return item;
}
};
this.getImplementationName=function(_686){
var impl=_678[_686.toLowerCase()];
if(impl){
return impl;
}
for(var i=0;i<_679.length;i++){
var pn=_679[i];
var pkg=dj_eval_object_path(pn);
for(var x in pkg){
var xlc=(new String(x)).toLowerCase();
for(var y in dojo.render){
if((dojo.render[y]["capable"])&&(dojo.render[y].capable===true)){
var ps=dojo.render[y].prefixes;
for(var z=0;z<ps.length;z++){
if((ps[z]+_686).toLowerCase()==xlc){
_678[xlc]=pkg[x];
return pkg[x];
}
}
}
}
}
}
};
this.getWidgetFromPrimitive=function(_692){
dj_unimplemented("dojo.widget.manager.getWidgetFromPrimitive");
};
this.getWidgetFromEvent=function(_693){
dj_unimplemented("dojo.widget.manager.getWidgetFromEvent");
};
};
dojo.provide("dojo.widget.Widget");
dojo.provide("dojo.widget.tags");
dojo.require("dojo.lang.*");
dojo.require("dojo.widget.Manager");
dojo.require("dojo.event.*");
dojo.require("dojo.text.*");
dojo.widget.Widget=function(){
this.children=[];
this.rightClickItems=[];
this.extraArgs={};
};
dojo.lang.extend(dojo.widget.Widget,{parent:null,isTopLevel:false,isModal:false,isEnabled:true,isHidden:false,isContainer:false,widgetId:"",widgetType:"Widget",enable:function(){
this.isEnabled=true;
},disable:function(){
this.isEnabled=false;
},hide:function(){
this.isHidden=true;
},show:function(){
this.isHidden=false;
},create:function(args,_694,_695){
this.satisfyPropertySets(args,_694,_695);
this.mixInProperties(args,_694,_695);
dojo.widget.manager.add(this);
this.buildRendering(args,_694,_695);
this.initialize(args,_694,_695);
this.postInitialize(args,_694,_695);
return this;
},destroy:function(_696){
this.uninitialize();
this.destroyRendering(_696);
dojo.widget.manager.removeById(this.widgetId);
},destroyChildren:function(_697){
_697=(!_697)?function(){
return true;
}:_697;
for(var x=0;x<this.children.length;x++){
var tc=this.children[x];
if((tc)&&(_697(tc))){
tc.destroy();
}
}
},destroyChildrenOfType:function(type){
type=type.toLowerCase();
this.destroyChildren(function(item){
if(item.widgetType.toLowerCase()==type){
return true;
}else{
return false;
}
});
},satisfyPropertySets:function(args){
var _699=[];
var _700=[];
for(var x=0;x<_699.length;x++){
}
for(var x=0;x<_700.length;x++){
}
return args;
},mixInProperties:function(args,frag){
if((args["fastMixIn"])||(frag["fastMixIn"])){
for(var x in args){
this[x]=args[x];
}
return;
}
var _702;
var _703;
if(this.constructor.prototype["lcArgs"]){
_703=this.constructor.prototype.lcArgs;
}else{
_703={};
for(var y in this){
_703[((new String(y)).toLowerCase())]=y;
}
this.constructor.prototype.lcArgs=_703;
}
for(var x in args){
if(!this[x]){
var y=_703[(new String(x)).toLowerCase()];
if(y){
args[y]=args[x];
x=y;
}
}
if((typeof this[x])!=(typeof _702)){
if(typeof args[x]!="string"){
this[x]=args[x];
}else{
if(typeof this[x]=="string"){
this[x]=args[x];
}else{
if(typeof this[x]=="number"){
this[x]=new Number(args[x]);
}else{
if(typeof this[x]=="boolean"){
this[x]=(args[x].toLowerCase()=="false")?false:true;
}else{
if(typeof this[x]=="function"){
var tn=dojo.event.nameAnonFunc(new Function(args[x]),this);
dojo.event.connect(this,x,this,tn);
}else{
if(this[x].constructor==Array){
this[x]=args[x].split(";");
}else{
if(typeof this[x]=="object"){
var _704=args[x].split(";");
for(var y=0;y<_704.length;y++){
var si=_704[y].indexOf(":");
if((si!=-1)&&(_704[y].length>si)){
this[x][dojo.text.trim(_704[y].substr(0,si))]=_704[y].substr(si+1);
}
}
}else{
this[x]=args[x];
}
}
}
}
}
}
}
}else{
this.extraArgs[x]=args[x];
}
}
},initialize:function(args,frag){
return false;
},postInitialize:function(args,frag){
return false;
},uninitialize:function(){
return false;
},buildRendering:function(){
dj_unimplemented("dojo.widget.Widget.buildRendering");
return false;
},destroyRendering:function(){
dj_unimplemented("dojo.widget.Widget.destroyRendering");
return false;
},cleanUp:function(){
dj_unimplemented("dojo.widget.Widget.cleanUp");
return false;
},addedTo:function(_706){
},addChild:function(_707){
dj_unimplemented("dojo.widget.Widget.addChild");
return false;
},addChildAtIndex:function(_708,_709){
dj_unimplemented("dojo.widget.Widget.addChildAtIndex");
return false;
},removeChild:function(_710){
dj_unimplemented("dojo.widget.Widget.removeChild");
return false;
},removeChildAtIndex:function(_711){
dj_unimplemented("dojo.widget.Widget.removeChildAtIndex");
return false;
},resize:function(_712,_713){
this.setWidth(_712);
this.setHeight(_713);
},setWidth:function(_714){
if((typeof _714=="string")&&(_714.substr(-1)=="%")){
this.setPercentageWidth(_714);
}else{
this.setNativeWidth(_714);
}
},setHeight:function(_715){
if((typeof _715=="string")&&(_715.substr(-1)=="%")){
this.setPercentageHeight(_715);
}else{
this.setNativeHeight(_715);
}
},setPercentageHeight:function(_716){
return false;
},setNativeHeight:function(_717){
return false;
},setPercentageWidth:function(_718){
return false;
},setNativeWidth:function(_719){
return false;
}});
dojo.widget.tags={};
dojo.widget.tags.addParseTreeHandler=function(type){
var _720=type.toLowerCase();
this[_720]=function(_721,_722,_723,_724){
return dojo.widget.buildWidgetFromParseTree(_720,_721,_722,_723,_724);
};
};
dojo.widget.tags.addParseTreeHandler("dojo:widget");
dojo.widget.tags["dojo:propertyset"]=function(_725,_726,_727){
var _728=_726.parseProperties(_725["dojo:propertyset"]);
};
dojo.widget.tags["dojo:connect"]=function(_729,_730,_731){
var _732=_730.parseProperties(_729["dojo:connect"]);
};
dojo.widget.buildWidgetFromParseTree=function(type,frag,_733,_734,_735){
var _736=type.split(":");
_736=(_736.length==2)?_736[1]:type;
var _737=_733.getPropertySets(frag);
var _738=_733.parseProperties(frag["dojo:"+_736]);
for(var x=0;x<_737.length;x++){
}
var _739=dojo.widget.manager.getImplementation(_736);
if(!_739){
throw new Error("cannot find \""+_736+"\" widget");
}else{
if(!_739.create){
throw new Error("\""+_736+"\" widget object does not appear to implement *Widget");
}
}
_738["dojoinsertionindex"]=_735;
return _739.create(_738,frag,_734);
};
dojo.provide("dojo.widget.Parse");
dojo.require("dojo.widget.Manager");
dojo.require("dojo.text.*");
dojo.widget.Parse=function(_740){
this.propertySetsList=[];
this.fragment=_740;
this.createComponents=function(_740,_741){
var _742=dojo.widget.tags;
var _743=[];
for(var item in _740){
var _744=false;
try{
if(_740[item]&&(_740[item]["tagName"])&&(_740[item]!=_740["nodeRef"])){
var tn=new String(_740[item]["tagName"]);
var tna=tn.split(";");
for(var x=0;x<tna.length;x++){
var ltn=dojo.text.trim(tna[x]).toLowerCase();
if(_742[ltn]){
_744=true;
_740[item].tagName=ltn;
_743.push(_742[ltn](_740[item],this,_741,_740[item]["index"]));
}else{
if(ltn.substr(0,5)=="dojo:"){
dj_debug("no tag handler registed for type: ",ltn);
}
}
}
}
}
catch(e){
if(dojo.hostenv.is_debug_){
dj_debug(e);
}
}
if((!_744)&&(typeof _740[item]=="object")&&(_740[item]!=_740.nodeRef)&&(_740[item]!=_740["tagName"])){
_743.push(this.createComponents(_740[item],_741));
}
}
return _743;
};
this.parsePropertySets=function(_746){
return [];
var _747=[];
for(var item in _746){
if((_746[item]["tagName"]=="dojo:propertyset")){
_747.push(_746[item]);
}
}
this.propertySetsList.push(_747);
return _747;
};
this.parseProperties=function(_748){
var _749={};
for(var item in _748){
if((_748[item]==_748["tagName"])||(_748[item]==_748.nodeRef)){
}else{
if((_748[item]["tagName"])&&(dojo.widget.tags[_748[item].tagName.toLowerCase()])){
}else{
if((_748[item][0])&&(_748[item][0].value!="")){
try{
if(item.toLowerCase()=="dataprovider"){
var _750=this;
this.getDataProvider(_750,_748[item][0].value);
_749.dataProvider=this.dataProvider;
}
_749[item]=_748[item][0].value;
var _751=this.parseProperties(_748[item]);
for(var _752 in _751){
_749[_752]=_751[_752];
}
}
catch(e){
dj_debug(e);
}
}
}
}
}
return _749;
};
this.getDataProvider=function(_753,_754){
dojo.io.bind({url:_754,load:function(type,_755){
if(type=="load"){
_753.dataProvider=_755;
}
},mimetype:"text/javascript",sync:true});
};
this.getPropertySetById=function(_756){
for(var x=0;x<this.propertySetsList.length;x++){
if(_756==this.propertySetsList[x]["id"][0].value){
return this.propertySetsList[x];
}
}
return "";
};
this.getPropertySetsByType=function(_757){
var _758=[];
for(var x=0;x<this.propertySetsList.length;x++){
var cpl=this.propertySetsList[x];
var cpcc=cpl["componentClass"]||cpl["componentType"]||null;
if((cpcc)&&(propertySetId==cpcc[0].value)){
_758.push(cpl);
}
}
return _758;
};
this.getPropertySets=function(_761){
var ppl="dojo:propertyproviderlist";
var _763=[];
var _764=_761["tagName"];
if(_761[ppl]){
var _765=_761[ppl].value.split(" ");
for(propertySetId in _765){
if((propertySetId.indexOf("..")==-1)&&(propertySetId.indexOf("://")==-1)){
var _766=this.getPropertySetById(propertySetId);
if(_766!=""){
_763.push(_766);
}
}else{
}
}
}
return (this.getPropertySetsByType(_764)).concat(_763);
};
this.createComponentFromScript=function(_767,_768,_769,_770){
var frag={};
var _771="dojo:"+_768.toLowerCase();
frag[_771]={};
var bo={};
for(prop in _769){
if(typeof bo[prop]=="undefined"){
frag[_771][prop.toLowerCase()]=[{value:_769[prop]}];
}
}
frag[_771]["dojotype"]=[{value:_768}];
frag[_771].nodeRef=_767;
frag.tagName=_771;
var _773=[frag];
if(_770){
_773[0].fastMixIn=true;
}
return this.createComponents(_773);
};
};
dojo.widget._parser_collection={"dojo":new dojo.widget.Parse()};
dojo.widget.getParser=function(name){
if(!name){
name="dojo";
}
if(!this._parser_collection[name]){
this._parser_collection[name]=new dojo.widget.Parse();
}
return this._parser_collection[name];
};
dojo.widget.fromScript=function(name,_774,_775,_776){
if((typeof name!="string")&&(typeof _774=="string")){
return dojo.widget._oldFromScript(name,_774,_775);
}
_774=_774||{};
var _777=false;
var tn=null;
var h=dojo.render.html.capable;
if(h){
tn=document.createElement("span");
}
if(!_775){
_777=true;
_775=tn;
if(h){
document.body.appendChild(_775);
}
}else{
if(_776){
dojo.xml.domUtil.insert(tn,_775,_776);
}else{
tn=_775;
}
}
var _778=dojo.widget._oldFromScript(tn,name,_774);
if(!_778[0]||typeof _778[0].widgetType=="undefined"){
throw new Error("Creation of \""+name+"\" widget fromScript failed.");
}
if(_777){
if(_778[0].domNode.parentNode){
_778[0].domNode.parentNode.removeChild(_778[0].domNode);
}
}
return _778[0];
};
dojo.widget._oldFromScript=function(_779,name,_780){
var ln=name.toLowerCase();
var tn="dojo:"+ln;
_780[tn]={dojotype:[{value:ln}],nodeRef:_779,fastMixIn:true};
return dojo.widget.getParser().createComponentFromScript(_779,name,_780);
};
dojo.hostenv.conditionalLoadModule({common:["dojo.uri.Uri",false,false]});
dojo.hostenv.moduleLoaded("dojo.uri.*");
dojo.provide("dojo.widget.DomWidget");
dojo.require("dojo.event.*");
dojo.require("dojo.text.*");
dojo.require("dojo.widget.Widget");
dojo.require("dojo.xml.*");
dojo.require("dojo.math.curves");
dojo.require("dojo.animation.Animation");
dojo.require("dojo.uri.*");
dojo.widget._cssFiles={};
dojo.widget.buildFromTemplate=function(obj,_782,_783,_784){
var _785=_782||obj.templatePath;
var _786=_783||obj.templateCssPath;
if(!_786&&obj.templateCSSPath){
obj.templateCssPath=_786=obj.templateCSSPath;
obj.templateCSSPath=null;
dj_deprecated("templateCSSPath is deprecated, use templateCssPath");
}
if(_785&&!(_785 instanceof dojo.uri.Uri)){
_785=dojo.uri.dojoUri(_785);
dj_deprecated("templatePath should be of type dojo.uri.Uri");
}
if(_786&&!(_786 instanceof dojo.uri.Uri)){
_786=dojo.uri.dojoUri(_786);
dj_deprecated("templateCssPath should be of type dojo.uri.Uri");
}
var _787=dojo.widget.DomWidget.templates;
if(!obj["widgetType"]){
do{
var _788="__dummyTemplate__"+dojo.widget.buildFromTemplate.dummyCount++;
}while(_787[_788]);
obj.widgetType=_788;
}
if((_786)&&(!dojo.widget._cssFiles[_786])){
dojo.xml.htmlUtil.insertCssFile(_786);
obj.templateCssPath=null;
dojo.widget._cssFiles[_786]=true;
}
var ts=_787[obj.widgetType];
if(!ts){
_787[obj.widgetType]={};
ts=_787[obj.widgetType];
}
if(!obj.templateString){
obj.templateString=_784||ts["string"];
}
if(!obj.templateNode){
obj.templateNode=ts["node"];
}
if((!obj.templateNode)&&(!obj.templateString)&&(_785)){
var _790=dojo.hostenv.getText(_785);
if(_790){
var _791=_790.match(/<body[^>]*>\s*([\s\S]+)\s*<\/body>/im);
if(_791){
_790=_791[1];
}
}else{
_790="";
}
obj.templateString=_790;
ts.string=_790;
}
if(!ts["string"]){
ts.string=obj.templateString;
}
};
dojo.widget.buildFromTemplate.dummyCount=0;
dojo.widget.attachProperty="dojoAttachPoint";
dojo.widget.eventAttachProperty="dojoAttachEvent";
dojo.widget.subTemplateProperty="dojoSubTemplate";
dojo.widget.onBuildProperty="dojoOnBuild";
dojo.widget.attachTemplateNodes=function(_792,_793,_794,_795){
var _796=dojo.xml.domUtil.nodeTypes.ELEMENT_NODE;
if(!_792){
_792=_793.domNode;
}
if(_792.nodeType!=_796){
return;
}
var _797=_792.getElementsByTagName("*");
var _798=_793;
for(var x=-1;x<_797.length;x++){
var _799=(x==-1)?_792:_797[x];
var _800=_799.getAttribute(this.attachProperty);
if(_800){
_793[_800]=_799;
}
var _801=_799.getAttribute(this.templateProperty);
if(_801){
_793[_801]=_799;
}
var _801=_799.getAttribute(this.subTemplateProperty);
if(_801){
_794.subTemplates[_801]=_799.parentNode.removeChild(_799);
_794.subTemplates[_801].removeAttribute(this.subTemplateProperty);
}
var _802=_799.getAttribute(this.eventAttachProperty);
if(_802){
var evts=_802.split(";");
for(var y=0;y<evts.length;y++){
if(!evts[y]){
continue;
}
if(!evts[y].length){
continue;
}
var tevt=null;
var _805=null;
tevt=dojo.text.trim(evts[y]);
if(tevt.indexOf(":")>=0){
var _806=tevt.split(":");
tevt=dojo.text.trim(_806[0]);
_805=dojo.text.trim(_806[1]);
}
if(!_805){
_805=tevt;
}
var tf=function(){
var ntf=new String(_805);
return function(evt){
if(_798[ntf]){
_798[ntf](evt);
}
};
}();
dojo.event.browser.addListener(_799,tevt.substr(2),tf);
}
}
for(var y=0;y<_795.length;y++){
var _809=_799.getAttribute(_795[y]);
if((_809)&&(_809.length)){
var _805=null;
var _810=_795[y].substr(4).toLowerCase();
_805=dojo.text.trim(_809);
var tf=function(){
var ntf=new String(_805);
return function(evt){
if(_798[ntf]){
_798[ntf](evt);
}
};
}();
dojo.event.browser.addListener(_799,_810.substr(2),tf);
}
}
var _811=_799.getAttribute(this.onBuildProperty);
if(_811){
eval("var node = baseNode; var widget = targetObj; "+_811);
}
}
};
dojo.widget.getDojoEventsFromStr=function(str){
var re=/(dojoOn([a-z]+)(\s?))=/gi;
var evts=str?str.match(re)||[]:[];
var ret=[];
var lem={};
for(var x=0;x<evts.length;x++){
if(evts[x].legth<1){
continue;
}
var cm=evts[x].replace(/\s/,"");
cm=(cm.slice(0,cm.length-1));
if(!lem[cm]){
lem[cm]=true;
ret.push(cm);
}
}
return ret;
};
dojo.widget.buildAndAttachTemplate=function(obj,_813,_814,_815,_816){
this.buildFromTemplate(obj,_813,_814,_815);
var node=dojo.xml.domUtil.createNodesFromText(obj.templateString,true)[0];
this.attachTemplateNodes(node,_816||obj,obj,dojo.widget.getDojoEventsFromStr(_815));
return node;
};
dojo.widget.DomWidget=function(){
dojo.widget.Widget.call(this);
if((arguments.length>0)&&(typeof arguments[0]=="object")){
this.create(arguments[0]);
}
};
dj_inherits(dojo.widget.DomWidget,dojo.widget.Widget);
dojo.lang.extend(dojo.widget.DomWidget,{templateNode:null,templateString:null,subTemplates:{},domNode:null,containerNode:null,addChild:function(_817,_818,pos,ref,_820){
if(!this.isContainer){
dj_debug("dojo.widget.DomWidget.addChild() attempted on non-container widget");
return false;
}else{
if((!this.containerNode)&&(!_818)){
this.containerNode=this.domNode;
}
var cn=(_818)?_818:this.containerNode;
if(!pos){
pos="after";
}
if(!ref){
ref=cn.lastChild;
}
if(!_820){
_820=0;
}
_817.domNode.setAttribute("dojoinsertionindex",_820);
if(!ref){
cn.appendChild(_817.domNode);
}else{
dojo.xml.domUtil[pos](_817.domNode,ref,_820);
}
this.children.push(_817);
_817.parent=this;
_817.addedTo(this);
}
return _817;
},removeChild:function(_822){
for(var x=0;x<this.children.length;x++){
if(this.children[x]===_822){
this.children.splice(x,1);
break;
}
}
return _822;
},postInitialize:function(args,frag,_823){
if(_823){
_823.addChild(this,"","insertAtIndex","",args["dojoinsertionindex"]);
}else{
if(!frag){
return;
}
var _824=frag["dojo:"+this.widgetType.toLowerCase()]["nodeRef"];
if(!_824){
return;
}
this.parent=dojo.widget.manager.root;
if((this.domNode)&&(this.domNode!==_824)){
var _825=_824.parentNode.replaceChild(this.domNode,_824);
}
}
if(this.isContainer){
var _826=dojo.xml.domUtil.nodeTypes.ELEMENT_NODE;
var _827=dojo.widget.getParser();
_827.createComponents(frag,this);
}
},startResize:function(_828){
dj_unimplemented("dojo.widget.DomWidget.startResize");
},updateResize:function(_829){
dj_unimplemented("dojo.widget.DomWidget.updateResize");
},endResize:function(_830){
dj_unimplemented("dojo.widget.DomWidget.endResize");
},buildRendering:function(args,frag){
var ts=dojo.widget.DomWidget.templates[this.widgetType];
if((this.templatePath)||(this.templateNode)||((this["templateString"])&&(this.templateString.length))||((typeof ts!="undefined")&&((ts["string"])||(ts["node"])))){
this.buildFromTemplate(args,frag);
}else{
this.domNode=frag["dojo:"+this.widgetType.toLowerCase()]["nodeRef"];
}
this.fillInTemplate(args,frag);
},buildFromTemplate:function(args,frag){
var ts=dojo.widget.DomWidget.templates[this.widgetType];
if(ts){
if(!this.templateString.length){
this.templateString=ts["string"];
}
if(!this.templateNode){
this.templateNode=ts["node"];
}
}
var node=null;
if((!this.templateNode)&&(this.templateString)){
this.templateString=this.templateString.replace(/\$\{baseScriptUri\}/mg,dojo.hostenv.getBaseScriptUri());
this.templateString=this.templateString.replace(/\$\{dojoRoot\}/mg,dojo.hostenv.getBaseScriptUri());
this.templateNode=this.createNodesFromText(this.templateString,true)[0];
ts.node=this.templateNode;
}
if(!this.templateNode){
dj_debug("weren't able to create template!");
return false;
}
var node=this.templateNode.cloneNode(true);
if(!node){
return false;
}
this.domNode=node;
this.attachTemplateNodes(this.domNode,this);
},attachTemplateNodes:function(_831,_832){
if(!_832){
_832=this;
}
return dojo.widget.attachTemplateNodes(_831,_832,this,dojo.widget.getDojoEventsFromStr(this.templateString));
},fillInTemplate:function(){
},destroyRendering:function(){
try{
var _833=this.domNode.parentNode.removeChild(this.domNode);
delete _833;
}
catch(e){
}
},cleanUp:function(){
},getContainerHeight:function(){
return dojo.xml.htmlUtil.getInnerHeight(this.domNode.parentNode);
},getContainerWidth:function(){
return dojo.xml.htmlUtil.getInnerWidth(this.domNode.parentNode);
},createNodesFromText:function(){
dj_unimplemented("dojo.widget.DomWidget.createNodesFromText");
}});
dojo.widget.DomWidget.templates={};
dojo.require("dojo.widget.DomWidget");
dojo.provide("dojo.widget.HtmlWidget");
dojo.widget.HtmlWidget=function(args){
dojo.widget.DomWidget.call(this);
};
dj_inherits(dojo.widget.HtmlWidget,dojo.widget.DomWidget);
dojo.lang.extend(dojo.widget.HtmlWidget,{templateCssPath:null,templatePath:null,allowResizeX:true,allowResizeY:true,resizeGhost:null,initialResizeCoords:null,getContainerHeight:function(){
dj_unimplemented("dojo.widget.HtmlWidget.getContainerHeight");
},getContainerWidth:function(){
return this.parent.domNode.offsetWidth;
},setNativeHeight:function(_834){
var ch=this.getContainerHeight();
},startResize:function(_836){
var hu=dojo.xml.htmlUtil;
_836.offsetLeft=hu.totalOffsetLeft(this.domNode);
_836.offsetTop=hu.totalOffsetTop(this.domNode);
_836.innerWidth=hu.getInnerWidth(this.domNode);
_836.innerHeight=hu.getInnerHeight(this.domNode);
if(!this.resizeGhost){
this.resizeGhost=document.createElement("div");
var rg=this.resizeGhost;
rg.style.position="absolute";
rg.style.backgroundColor="white";
rg.style.border="1px solid black";
dojo.xml.htmlUtil.setOpacity(rg,0.3);
document.body.appendChild(rg);
}
with(this.resizeGhost.style){
left=_836.offsetLeft+"px";
top=_836.offsetTop+"px";
}
this.initialResizeCoords=_836;
this.resizeGhost.style.display="";
this.updateResize(_836,true);
},updateResize:function(_838,_839){
var dx=_838.x-this.initialResizeCoords.x;
var dy=_838.y-this.initialResizeCoords.y;
with(this.resizeGhost.style){
if((this.allowResizeX)||(_839)){
width=this.initialResizeCoords.innerWidth+dx+"px";
}
if((this.allowResizeY)||(_839)){
height=this.initialResizeCoords.innerHeight+dy+"px";
}
}
},endResize:function(_842){
var dx=_842.x-this.initialResizeCoords.x;
var dy=_842.y-this.initialResizeCoords.y;
with(this.domNode.style){
if(this.allowResizeX){
width=this.initialResizeCoords.innerWidth+dx+"px";
}
if(this.allowResizeY){
height=this.initialResizeCoords.innerHeight+dy+"px";
}
}
this.resizeGhost.style.display="none";
},createNodesFromText:function(txt,wrap){
return dojo.xml.domUtil.createNodesFromText(txt,wrap);
},_old_buildFromTemplate:dojo.widget.DomWidget.prototype.buildFromTemplate,buildFromTemplate:function(){
dojo.widget.buildFromTemplate(this);
this._old_buildFromTemplate();
},destroyRendering:function(_843){
try{
var _844=this.domNode.parentNode.removeChild(this.domNode);
if(!_843){
dojo.event.browser.clean(_844);
}
delete _844;
}
catch(e){
}
}});
dojo.hostenv.conditionalLoadModule({common:["dojo.xml.Parse","dojo.widget.Widget","dojo.widget.Parse","dojo.widget.Manager"],browser:["dojo.widget.DomWidget","dojo.widget.HtmlWidget"],svg:["dojo.widget.SvgWidget"]});
dojo.hostenv.moduleLoaded("dojo.widget.*");
dojo.provide("dojo.math.points");
dojo.require("dojo.math.Math");
dojo.math.points={translate:function(a,b){
if(a.length!=b.length){
dj_throw("dojo.math.translate: points not same size (a:["+a+"], b:["+b+"])");
}
var c=new Array(a.length);
for(var i=0;i<a.length;i++){
c[i]=a[i]+b[i];
}
return c;
},midpoint:function(a,b){
if(a.length!=b.length){
dj_throw("dojo.math.midpoint: points not same size (a:["+a+"], b:["+b+"])");
}
var c=new Array(a.length);
for(var i=0;i<a.length;i++){
c[i]=(a[i]+b[i])/2;
}
return c;
},invert:function(a){
var b=new Array(a.length);
for(var i=0;i<a.length;i++){
b[i]=-a[i];
}
return b;
},distance:function(a,b){
return Math.sqrt(Math.pow(b[0]-a[0],2)+Math.pow(b[1]-a[1],2));
}};
dojo.hostenv.conditionalLoadModule({common:[["dojo.math.Math",false,false],["dojo.math.curves",false,false],["dojo.math.points",false,false]]});
dojo.hostenv.moduleLoaded("dojo.math.*");

