(function (require) {
  define('leave-absences/shared/config',[],function () {
    var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences/shared';

    // This require.config will picked up by the r.js optimizer
    require.config({
      paths: {
        'leave-absences/shared/ui-router': 'leave-absences/shared/vendor/angular-ui-router.min',
      },
      shim: {
        'leave-absences/shared/ui-router': {}
      }
    });

    // This require.config will be used by the "live" RequireJS (with debug ON)
    require.config({
      paths: {
        'leave-absences/shared/ui-router': srcPath + '/vendor/angular-ui-router.min',
      }
    });
  });
})(require);

(function(root) {
define("leave-absences/shared/ui-router", [], function() {
  return (function() {
/**
 * State-based routing for AngularJS
 * @version v0.3.2
 * @link http://angular-ui.github.com/
 * @license MIT License, http://www.opensource.org/licenses/MIT
 */
"undefined"!=typeof module&&"undefined"!=typeof exports&&module.exports===exports&&(module.exports="ui.router"),function(a,b,c){"use strict";function d(a,b){return S(new(S(function(){},{prototype:a})),b)}function e(a){return R(arguments,function(b){b!==a&&R(b,function(b,c){a.hasOwnProperty(c)||(a[c]=b)})}),a}function f(a,b){var c=[];for(var d in a.path){if(a.path[d]!==b.path[d])break;c.push(a.path[d])}return c}function g(a){if(Object.keys)return Object.keys(a);var b=[];return R(a,function(a,c){b.push(c)}),b}function h(a,b){if(Array.prototype.indexOf)return a.indexOf(b,Number(arguments[2])||0);var c=a.length>>>0,d=Number(arguments[2])||0;for(d=d<0?Math.ceil(d):Math.floor(d),d<0&&(d+=c);d<c;d++)if(d in a&&a[d]===b)return d;return-1}function i(a,b,c,d){var e,i=f(c,d),j={},k=[];for(var l in i)if(i[l]&&i[l].params&&(e=g(i[l].params),e.length))for(var m in e)h(k,e[m])>=0||(k.push(e[m]),j[e[m]]=a[e[m]]);return S({},j,b)}function j(a,b,c){if(!c){c=[];for(var d in a)c.push(d)}for(var e=0;e<c.length;e++){var f=c[e];if(a[f]!=b[f])return!1}return!0}function k(a,b){var c={};return R(a,function(a){c[a]=b[a]}),c}function l(a){var b={},c=Array.prototype.concat.apply(Array.prototype,Array.prototype.slice.call(arguments,1));return R(c,function(c){c in a&&(b[c]=a[c])}),b}function m(a){var b={},c=Array.prototype.concat.apply(Array.prototype,Array.prototype.slice.call(arguments,1));for(var d in a)h(c,d)==-1&&(b[d]=a[d]);return b}function n(a,b){var c=Q(a),d=c?[]:{};return R(a,function(a,e){b(a,e)&&(d[c?d.length:e]=a)}),d}function o(a,b){var c=Q(a)?[]:{};return R(a,function(a,d){c[d]=b(a,d)}),c}function p(a){return a.then(c,function(){})&&a}function q(a,b){var d=1,f=2,i={},j=[],k=i,l=S(a.when(i),{$$promises:i,$$values:i});this.study=function(i){function n(a,c){if(s[c]!==f){if(r.push(c),s[c]===d)throw r.splice(0,h(r,c)),new Error("Cyclic dependency: "+r.join(" -> "));if(s[c]=d,O(a))q.push(c,[function(){return b.get(a)}],j);else{var e=b.annotate(a);R(e,function(a){a!==c&&i.hasOwnProperty(a)&&n(i[a],a)}),q.push(c,a,e)}r.pop(),s[c]=f}}function o(a){return P(a)&&a.then&&a.$$promises}if(!P(i))throw new Error("'invocables' must be an object");var p=g(i||{}),q=[],r=[],s={};return R(i,n),i=r=s=null,function(d,f,g){function h(){--u||(v||e(t,f.$$values),r.$$values=t,r.$$promises=r.$$promises||!0,delete r.$$inheritedValues,n.resolve(t))}function i(a){r.$$failure=a,n.reject(a)}function j(c,e,f){function j(a){l.reject(a),i(a)}function k(){if(!M(r.$$failure))try{l.resolve(b.invoke(e,g,t)),l.promise.then(function(a){t[c]=a,h()},j)}catch(a){j(a)}}var l=a.defer(),m=0;R(f,function(a){s.hasOwnProperty(a)&&!d.hasOwnProperty(a)&&(m++,s[a].then(function(b){t[a]=b,--m||k()},j))}),m||k(),s[c]=l.promise}if(o(d)&&g===c&&(g=f,f=d,d=null),d){if(!P(d))throw new Error("'locals' must be an object")}else d=k;if(f){if(!o(f))throw new Error("'parent' must be a promise returned by $resolve.resolve()")}else f=l;var n=a.defer(),r=n.promise,s=r.$$promises={},t=S({},d),u=1+q.length/3,v=!1;if(M(f.$$failure))return i(f.$$failure),r;f.$$inheritedValues&&e(t,m(f.$$inheritedValues,p)),S(s,f.$$promises),f.$$values?(v=e(t,m(f.$$values,p)),r.$$inheritedValues=m(f.$$values,p),h()):(f.$$inheritedValues&&(r.$$inheritedValues=m(f.$$inheritedValues,p)),f.then(h,i));for(var w=0,x=q.length;w<x;w+=3)d.hasOwnProperty(q[w])?h():j(q[w],q[w+1],q[w+2]);return r}},this.resolve=function(a,b,c,d){return this.study(a)(b,c,d)}}function r(a,b,c){this.fromConfig=function(a,b,c){return M(a.template)?this.fromString(a.template,b):M(a.templateUrl)?this.fromUrl(a.templateUrl,b):M(a.templateProvider)?this.fromProvider(a.templateProvider,b,c):null},this.fromString=function(a,b){return N(a)?a(b):a},this.fromUrl=function(c,d){return N(c)&&(c=c(d)),null==c?null:a.get(c,{cache:b,headers:{Accept:"text/html"}}).then(function(a){return a.data})},this.fromProvider=function(a,b,d){return c.invoke(a,null,d||{params:b})}}function s(a,b,e){function f(b,c,d,e){if(q.push(b),o[b])return o[b];if(!/^\w+([-.]+\w+)*(?:\[\])?$/.test(b))throw new Error("Invalid parameter name '"+b+"' in pattern '"+a+"'");if(p[b])throw new Error("Duplicate parameter name '"+b+"' in pattern '"+a+"'");return p[b]=new V.Param(b,c,d,e),p[b]}function g(a,b,c,d){var e=["",""],f=a.replace(/[\\\[\]\^$*+?.()|{}]/g,"\\$&");if(!b)return f;switch(c){case!1:e=["(",")"+(d?"?":"")];break;case!0:f=f.replace(/\/$/,""),e=["(?:/(",")|/)?"];break;default:e=["("+c+"|",")?"]}return f+e[0]+b+e[1]}function h(e,f){var g,h,i,j,k;return g=e[2]||e[3],k=b.params[g],i=a.substring(m,e.index),h=f?e[4]:e[4]||("*"==e[1]?".*":null),h&&(j=V.type(h)||d(V.type("string"),{pattern:new RegExp(h,b.caseInsensitive?"i":c)})),{id:g,regexp:h,segment:i,type:j,cfg:k}}b=S({params:{}},P(b)?b:{});var i,j=/([:*])([\w\[\]]+)|\{([\w\[\]]+)(?:\:\s*((?:[^{}\\]+|\\.|\{(?:[^{}\\]+|\\.)*\})+))?\}/g,k=/([:]?)([\w\[\].-]+)|\{([\w\[\].-]+)(?:\:\s*((?:[^{}\\]+|\\.|\{(?:[^{}\\]+|\\.)*\})+))?\}/g,l="^",m=0,n=this.segments=[],o=e?e.params:{},p=this.params=e?e.params.$$new():new V.ParamSet,q=[];this.source=a;for(var r,s,t;(i=j.exec(a))&&(r=h(i,!1),!(r.segment.indexOf("?")>=0));)s=f(r.id,r.type,r.cfg,"path"),l+=g(r.segment,s.type.pattern.source,s.squash,s.isOptional),n.push(r.segment),m=j.lastIndex;t=a.substring(m);var u=t.indexOf("?");if(u>=0){var v=this.sourceSearch=t.substring(u);if(t=t.substring(0,u),this.sourcePath=a.substring(0,m+u),v.length>0)for(m=0;i=k.exec(v);)r=h(i,!0),s=f(r.id,r.type,r.cfg,"search"),m=j.lastIndex}else this.sourcePath=a,this.sourceSearch="";l+=g(t)+(b.strict===!1?"/?":"")+"$",n.push(t),this.regexp=new RegExp(l,b.caseInsensitive?"i":c),this.prefix=n[0],this.$$paramNames=q}function t(a){S(this,a)}function u(){function a(a){return null!=a?a.toString().replace(/(~|\/)/g,function(a){return{"~":"~~","/":"~2F"}[a]}):a}function e(a){return null!=a?a.toString().replace(/(~~|~2F)/g,function(a){return{"~~":"~","~2F":"/"}[a]}):a}function f(){return{strict:p,caseInsensitive:m}}function i(a){return N(a)||Q(a)&&N(a[a.length-1])}function j(){for(;w.length;){var a=w.shift();if(a.pattern)throw new Error("You cannot override a type's .pattern at runtime.");b.extend(r[a.name],l.invoke(a.def))}}function k(a){S(this,a||{})}V=this;var l,m=!1,p=!0,q=!1,r={},v=!0,w=[],x={string:{encode:a,decode:e,is:function(a){return null==a||!M(a)||"string"==typeof a},pattern:/[^\/]*/},int:{encode:a,decode:function(a){return parseInt(a,10)},is:function(a){return M(a)&&this.decode(a.toString())===a},pattern:/\d+/},bool:{encode:function(a){return a?1:0},decode:function(a){return 0!==parseInt(a,10)},is:function(a){return a===!0||a===!1},pattern:/0|1/},date:{encode:function(a){return this.is(a)?[a.getFullYear(),("0"+(a.getMonth()+1)).slice(-2),("0"+a.getDate()).slice(-2)].join("-"):c},decode:function(a){if(this.is(a))return a;var b=this.capture.exec(a);return b?new Date(b[1],b[2]-1,b[3]):c},is:function(a){return a instanceof Date&&!isNaN(a.valueOf())},equals:function(a,b){return this.is(a)&&this.is(b)&&a.toISOString()===b.toISOString()},pattern:/[0-9]{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[1-2][0-9]|3[0-1])/,capture:/([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/},json:{encode:b.toJson,decode:b.fromJson,is:b.isObject,equals:b.equals,pattern:/[^\/]*/},any:{encode:b.identity,decode:b.identity,equals:b.equals,pattern:/.*/}};u.$$getDefaultValue=function(a){if(!i(a.value))return a.value;if(!l)throw new Error("Injectable functions cannot be called at configuration time");return l.invoke(a.value)},this.caseInsensitive=function(a){return M(a)&&(m=a),m},this.strictMode=function(a){return M(a)&&(p=a),p},this.defaultSquashPolicy=function(a){if(!M(a))return q;if(a!==!0&&a!==!1&&!O(a))throw new Error("Invalid squash policy: "+a+". Valid policies: false, true, arbitrary-string");return q=a,a},this.compile=function(a,b){return new s(a,S(f(),b))},this.isMatcher=function(a){if(!P(a))return!1;var b=!0;return R(s.prototype,function(c,d){N(c)&&(b=b&&M(a[d])&&N(a[d]))}),b},this.type=function(a,b,c){if(!M(b))return r[a];if(r.hasOwnProperty(a))throw new Error("A type named '"+a+"' has already been defined.");return r[a]=new t(S({name:a},b)),c&&(w.push({name:a,def:c}),v||j()),this},R(x,function(a,b){r[b]=new t(S({name:b},a))}),r=d(r,{}),this.$get=["$injector",function(a){return l=a,v=!1,j(),R(x,function(a,b){r[b]||(r[b]=new t(a))}),this}],this.Param=function(a,d,e,f){function j(a){var b=P(a)?g(a):[],c=h(b,"value")===-1&&h(b,"type")===-1&&h(b,"squash")===-1&&h(b,"array")===-1;return c&&(a={value:a}),a.$$fn=i(a.value)?a.value:function(){return a.value},a}function k(c,d,e){if(c.type&&d)throw new Error("Param '"+a+"' has two type configurations.");return d?d:c.type?b.isString(c.type)?r[c.type]:c.type instanceof t?c.type:new t(c.type):"config"===e?r.any:r.string}function m(){var b={array:"search"===f&&"auto"},c=a.match(/\[\]$/)?{array:!0}:{};return S(b,c,e).array}function p(a,b){var c=a.squash;if(!b||c===!1)return!1;if(!M(c)||null==c)return q;if(c===!0||O(c))return c;throw new Error("Invalid squash policy: '"+c+"'. Valid policies: false, true, or arbitrary string")}function s(a,b,d,e){var f,g,i=[{from:"",to:d||b?c:""},{from:null,to:d||b?c:""}];return f=Q(a.replace)?a.replace:[],O(e)&&f.push({from:e,to:c}),g=o(f,function(a){return a.from}),n(i,function(a){return h(g,a.from)===-1}).concat(f)}function u(){if(!l)throw new Error("Injectable functions cannot be called at configuration time");var a=l.invoke(e.$$fn);if(null!==a&&a!==c&&!x.type.is(a))throw new Error("Default value ("+a+") for parameter '"+x.id+"' is not an instance of Type ("+x.type.name+")");return a}function v(a){function b(a){return function(b){return b.from===a}}function c(a){var c=o(n(x.replace,b(a)),function(a){return a.to});return c.length?c[0]:a}return a=c(a),M(a)?x.type.$normalize(a):u()}function w(){return"{Param:"+a+" "+d+" squash: '"+A+"' optional: "+z+"}"}var x=this;e=j(e),d=k(e,d,f);var y=m();d=y?d.$asArray(y,"search"===f):d,"string"!==d.name||y||"path"!==f||e.value!==c||(e.value="");var z=e.value!==c,A=p(e,z),B=s(e,y,z,A);S(this,{id:a,type:d,location:f,array:y,squash:A,replace:B,isOptional:z,value:v,dynamic:c,config:e,toString:w})},k.prototype={$$new:function(){return d(this,S(new k,{$$parent:this}))},$$keys:function(){for(var a=[],b=[],c=this,d=g(k.prototype);c;)b.push(c),c=c.$$parent;return b.reverse(),R(b,function(b){R(g(b),function(b){h(a,b)===-1&&h(d,b)===-1&&a.push(b)})}),a},$$values:function(a){var b={},c=this;return R(c.$$keys(),function(d){b[d]=c[d].value(a&&a[d])}),b},$$equals:function(a,b){var c=!0,d=this;return R(d.$$keys(),function(e){var f=a&&a[e],g=b&&b[e];d[e].type.equals(f,g)||(c=!1)}),c},$$validates:function(a){var d,e,f,g,h,i=this.$$keys();for(d=0;d<i.length&&(e=this[i[d]],f=a[i[d]],f!==c&&null!==f||!e.isOptional);d++){if(g=e.type.$normalize(f),!e.type.is(g))return!1;if(h=e.type.encode(g),b.isString(h)&&!e.type.pattern.exec(h))return!1}return!0},$$parent:c},this.ParamSet=k}function v(a,d){function e(a){var b=/^\^((?:\\[^a-zA-Z0-9]|[^\\\[\]\^$*+?.()|{}]+)*)/.exec(a.source);return null!=b?b[1].replace(/\\(.)/g,"$1"):""}function f(a,b){return a.replace(/\$(\$|\d{1,2})/,function(a,c){return b["$"===c?0:Number(c)]})}function g(a,b,c){if(!c)return!1;var d=a.invoke(b,b,{$match:c});return!M(d)||d}function h(d,e,f,g,h){function m(a,b,c){return"/"===q?a:b?q.slice(0,-1)+a:c?q.slice(1)+a:a}function n(a){function b(a){var b=a(f,d);return!!b&&(O(b)&&d.replace().url(b),!0)}if(!a||!a.defaultPrevented){p&&d.url()===p;p=c;var e,g=j.length;for(e=0;e<g;e++)if(b(j[e]))return;k&&b(k)}}function o(){return i=i||e.$on("$locationChangeSuccess",n)}var p,q=g.baseHref(),r=d.url();return l||o(),{sync:function(){n()},listen:function(){return o()},update:function(a){return a?void(r=d.url()):void(d.url()!==r&&(d.url(r),d.replace()))},push:function(a,b,e){var f=a.format(b||{});null!==f&&b&&b["#"]&&(f+="#"+b["#"]),d.url(f),p=e&&e.$$avoidResync?d.url():c,e&&e.replace&&d.replace()},href:function(c,e,f){if(!c.validates(e))return null;var g=a.html5Mode();b.isObject(g)&&(g=g.enabled),g=g&&h.history;var i=c.format(e);if(f=f||{},g||null===i||(i="#"+a.hashPrefix()+i),null!==i&&e&&e["#"]&&(i+="#"+e["#"]),i=m(i,g,f.absolute),!f.absolute||!i)return i;var j=!g&&i?"/":"",k=d.port();return k=80===k||443===k?"":":"+k,[d.protocol(),"://",d.host(),k,j,i].join("")}}}var i,j=[],k=null,l=!1;this.rule=function(a){if(!N(a))throw new Error("'rule' must be a function");return j.push(a),this},this.otherwise=function(a){if(O(a)){var b=a;a=function(){return b}}else if(!N(a))throw new Error("'rule' must be a function");return k=a,this},this.when=function(a,b){var c,h=O(b);if(O(a)&&(a=d.compile(a)),!h&&!N(b)&&!Q(b))throw new Error("invalid 'handler' in when()");var i={matcher:function(a,b){return h&&(c=d.compile(b),b=["$match",function(a){return c.format(a)}]),S(function(c,d){return g(c,b,a.exec(d.path(),d.search()))},{prefix:O(a.prefix)?a.prefix:""})},regex:function(a,b){if(a.global||a.sticky)throw new Error("when() RegExp must not be global or sticky");return h&&(c=b,b=["$match",function(a){return f(c,a)}]),S(function(c,d){return g(c,b,a.exec(d.path()))},{prefix:e(a)})}},j={matcher:d.isMatcher(a),regex:a instanceof RegExp};for(var k in j)if(j[k])return this.rule(i[k](a,b));throw new Error("invalid 'what' in when()")},this.deferIntercept=function(a){a===c&&(a=!0),l=a},this.$get=h,h.$inject=["$location","$rootScope","$injector","$browser","$sniffer"]}function w(a,e){function f(a){return 0===a.indexOf(".")||0===a.indexOf("^")}function m(a,b){if(!a)return c;var d=O(a),e=d?a:a.name,g=f(e);if(g){if(!b)throw new Error("No reference point given for path '"+e+"'");b=m(b);for(var h=e.split("."),i=0,j=h.length,k=b;i<j;i++)if(""!==h[i]||0!==i){if("^"!==h[i])break;if(!k.parent)throw new Error("Path '"+e+"' not valid for state '"+b.name+"'");k=k.parent}else k=b;h=h.slice(i).join("."),e=k.name+(k.name&&h?".":"")+h}var l=A[e];return!l||!d&&(d||l!==a&&l.self!==a)?c:l}function n(a,b){B[a]||(B[a]=[]),B[a].push(b)}function q(a){for(var b=B[a]||[];b.length;)r(b.shift())}function r(b){b=d(b,{self:b,resolve:b.resolve||{},toString:function(){return this.name}});var c=b.name;if(!O(c)||c.indexOf("@")>=0)throw new Error("State must have a valid name");if(A.hasOwnProperty(c))throw new Error("State '"+c+"' is already defined");var e=c.indexOf(".")!==-1?c.substring(0,c.lastIndexOf(".")):O(b.parent)?b.parent:P(b.parent)&&O(b.parent.name)?b.parent.name:"";if(e&&!A[e])return n(e,b.self);for(var f in D)N(D[f])&&(b[f]=D[f](b,D.$delegates[f]));return A[c]=b,!b[C]&&b.url&&a.when(b.url,["$match","$stateParams",function(a,c){z.$current.navigable==b&&j(a,c)||z.transitionTo(b,a,{inherit:!0,location:!1})}]),q(c),b}function s(a){return a.indexOf("*")>-1}function t(a){for(var b=a.split("."),c=z.$current.name.split("."),d=0,e=b.length;d<e;d++)"*"===b[d]&&(c[d]="*");return"**"===b[0]&&(c=c.slice(h(c,b[1])),c.unshift("**")),"**"===b[b.length-1]&&(c.splice(h(c,b[b.length-2])+1,Number.MAX_VALUE),c.push("**")),b.length==c.length&&c.join("")===b.join("")}function u(a,b){return O(a)&&!M(b)?D[a]:N(b)&&O(a)?(D[a]&&!D.$delegates[a]&&(D.$delegates[a]=D[a]),D[a]=b,this):this}function v(a,b){return P(a)?b=a:b.name=a,r(b),this}function w(a,e,f,h,l,n,q,r,u){function v(b,c,d,f){var g=a.$broadcast("$stateNotFound",b,c,d);if(g.defaultPrevented)return q.update(),F;if(!g.retry)return null;if(f.$retry)return q.update(),G;var h=z.transition=e.when(g.retry);return h.then(function(){return h!==z.transition?(a.$broadcast("$stateChangeCancel",b.to,b.toParams,c,d),D):(b.options.$retry=!0,z.transitionTo(b.to,b.toParams,b.options))},function(){return F}),q.update(),h}function w(a,c,d,g,i,j){function m(){var c=[];return R(a.views,function(d,e){var g=d.resolve&&d.resolve!==a.resolve?d.resolve:{};g.$template=[function(){return f.load(e,{view:d,locals:i.globals,params:n,notify:j.notify})||""}],c.push(l.resolve(g,i.globals,i.resolve,a).then(function(c){if(N(d.controllerProvider)||Q(d.controllerProvider)){var f=b.extend({},g,i.globals);c.$$controller=h.invoke(d.controllerProvider,null,f)}else c.$$controller=d.controller;c.$$state=a,c.$$controllerAs=d.controllerAs,c.$$resolveAs=d.resolveAs,i[e]=c}))}),e.all(c).then(function(){return i.globals})}var n=d?c:k(a.params.$$keys(),c),o={$stateParams:n};i.resolve=l.resolve(a.resolve,o,i.resolve,a);var p=[i.resolve.then(function(a){i.globals=a})];return g&&p.push(g),e.all(p).then(m).then(function(a){return i})}var B=new Error("transition superseded"),D=p(e.reject(B)),E=p(e.reject(new Error("transition prevented"))),F=p(e.reject(new Error("transition aborted"))),G=p(e.reject(new Error("transition failed")));return y.locals={resolve:null,globals:{$stateParams:{}}},z={params:{},current:y.self,$current:y,transition:null},z.reload=function(a){return z.transitionTo(z.current,n,{reload:a||!0,inherit:!1,notify:!0})},z.go=function(a,b,c){return z.transitionTo(a,b,S({inherit:!0,relative:z.$current},c))},z.transitionTo=function(b,c,f){c=c||{},f=S({location:!0,inherit:!1,relative:null,notify:!0,reload:!1,$retry:!1},f||{});var g,j=z.$current,l=z.params,o=j.path,p=m(b,f.relative),r=c["#"];if(!M(p)){var s={to:b,toParams:c,options:f},t=v(s,j.self,l,f);if(t)return t;if(b=s.to,c=s.toParams,f=s.options,p=m(b,f.relative),!M(p)){if(!f.relative)throw new Error("No such state '"+b+"'");throw new Error("Could not resolve '"+b+"' from state '"+f.relative+"'")}}if(p[C])throw new Error("Cannot transition to abstract state '"+b+"'");if(f.inherit&&(c=i(n,c||{},z.$current,p)),!p.params.$$validates(c))return G;c=p.params.$$values(c),b=p;var u=b.path,A=0,F=u[A],H=y.locals,I=[];if(f.reload){if(O(f.reload)||P(f.reload)){if(P(f.reload)&&!f.reload.name)throw new Error("Invalid reload state object");var J=f.reload===!0?o[0]:m(f.reload);if(f.reload&&!J)throw new Error("No such reload state '"+(O(f.reload)?f.reload:f.reload.name)+"'");for(;F&&F===o[A]&&F!==J;)H=I[A]=F.locals,A++,F=u[A]}}else for(;F&&F===o[A]&&F.ownParams.$$equals(c,l);)H=I[A]=F.locals,A++,F=u[A];if(x(b,c,j,l,H,f))return r&&(c["#"]=r),z.params=c,T(z.params,n),T(k(b.params.$$keys(),n),b.locals.globals.$stateParams),f.location&&b.navigable&&b.navigable.url&&(q.push(b.navigable.url,c,{$$avoidResync:!0,replace:"replace"===f.location}),q.update(!0)),z.transition=null,e.when(z.current);if(c=k(b.params.$$keys(),c||{}),r&&(c["#"]=r),f.notify&&a.$broadcast("$stateChangeStart",b.self,c,j.self,l,f).defaultPrevented)return a.$broadcast("$stateChangeCancel",b.self,c,j.self,l),null==z.transition&&q.update(),E;for(var K=e.when(H),L=A;L<u.length;L++,F=u[L])H=I[L]=d(H),K=w(F,c,F===b,K,H,f);var N=z.transition=K.then(function(){var d,e,g;if(z.transition!==N)return a.$broadcast("$stateChangeCancel",b.self,c,j.self,l),D;for(d=o.length-1;d>=A;d--)g=o[d],g.self.onExit&&h.invoke(g.self.onExit,g.self,g.locals.globals),g.locals=null;for(d=A;d<u.length;d++)e=u[d],e.locals=I[d],e.self.onEnter&&h.invoke(e.self.onEnter,e.self,e.locals.globals);return z.transition!==N?(a.$broadcast("$stateChangeCancel",b.self,c,j.self,l),D):(z.$current=b,z.current=b.self,z.params=c,T(z.params,n),z.transition=null,f.location&&b.navigable&&q.push(b.navigable.url,b.navigable.locals.globals.$stateParams,{$$avoidResync:!0,replace:"replace"===f.location}),f.notify&&a.$broadcast("$stateChangeSuccess",b.self,c,j.self,l),q.update(!0),z.current)}).then(null,function(d){return d===B?D:z.transition!==N?(a.$broadcast("$stateChangeCancel",b.self,c,j.self,l),D):(z.transition=null,g=a.$broadcast("$stateChangeError",b.self,c,j.self,l,d),g.defaultPrevented||q.update(),e.reject(d))});return N},z.is=function(a,b,d){d=S({relative:z.$current},d||{});var e=m(a,d.relative);return M(e)?z.$current===e&&(!b||j(e.params.$$values(b),n)):c},z.includes=function(a,b,d){if(d=S({relative:z.$current},d||{}),O(a)&&s(a)){if(!t(a))return!1;a=z.$current.name}var e=m(a,d.relative);if(!M(e))return c;if(!M(z.$current.includes[e.name]))return!1;if(!b)return!0;for(var f=g(b),h=0;h<f.length;h++){var i=f[h],j=e.params[i];if(j&&!j.type.equals(n[i],b[i]))return!1}return!0},z.href=function(a,b,d){d=S({lossy:!0,inherit:!0,absolute:!1,relative:z.$current},d||{});var e=m(a,d.relative);if(!M(e))return null;d.inherit&&(b=i(n,b||{},z.$current,e));var f=e&&d.lossy?e.navigable:e;return f&&f.url!==c&&null!==f.url?q.href(f.url,k(e.params.$$keys().concat("#"),b||{}),{absolute:d.absolute}):null},z.get=function(a,b){if(0===arguments.length)return o(g(A),function(a){return A[a].self});var c=m(a,b||z.$current);return c&&c.self?c.self:null},z}function x(a,b,c,d,e,f){function g(a,b,c){function d(b){return"search"!=a.params[b].location}var e=a.params.$$keys().filter(d),f=l.apply({},[a.params].concat(e)),g=new V.ParamSet(f);return g.$$equals(b,c)}if(!f.reload&&a===c&&(e===c.locals||a.self.reloadOnSearch===!1&&g(c,d,b)))return!0}var y,z,A={},B={},C="abstract",D={parent:function(a){if(M(a.parent)&&a.parent)return m(a.parent);var b=/^(.+)\.[^.]+$/.exec(a.name);return b?m(b[1]):y},data:function(a){return a.parent&&a.parent.data&&(a.data=a.self.data=d(a.parent.data,a.data)),a.data},url:function(a){var b=a.url,c={params:a.params||{}};if(O(b))return"^"==b.charAt(0)?e.compile(b.substring(1),c):(a.parent.navigable||y).url.concat(b,c);if(!b||e.isMatcher(b))return b;throw new Error("Invalid url '"+b+"' in state '"+a+"'")},navigable:function(a){return a.url?a:a.parent?a.parent.navigable:null},ownParams:function(a){var b=a.url&&a.url.params||new V.ParamSet;return R(a.params||{},function(a,c){b[c]||(b[c]=new V.Param(c,null,a,"config"))}),b},params:function(a){var b=l(a.ownParams,a.ownParams.$$keys());return a.parent&&a.parent.params?S(a.parent.params.$$new(),b):new V.ParamSet},views:function(a){var b={};return R(M(a.views)?a.views:{"":a},function(c,d){d.indexOf("@")<0&&(d+="@"+a.parent.name),c.resolveAs=c.resolveAs||a.resolveAs||"$resolve",b[d]=c}),b},path:function(a){return a.parent?a.parent.path.concat(a):[]},includes:function(a){var b=a.parent?S({},a.parent.includes):{};return b[a.name]=!0,b},$delegates:{}};y=r({name:"",url:"^",views:null,abstract:!0}),y.navigable=null,this.decorator=u,this.state=v,this.$get=w,w.$inject=["$rootScope","$q","$view","$injector","$resolve","$stateParams","$urlRouter","$location","$urlMatcherFactory"]}function x(){function a(a,b){return{load:function(a,c){var d,e={template:null,controller:null,view:null,locals:null,notify:!0,async:!0,params:{}};return c=S(e,c),c.view&&(d=b.fromConfig(c.view,c.params,c.locals)),d}}}this.$get=a,a.$inject=["$rootScope","$templateFactory"]}function y(){var a=!1;this.useAnchorScroll=function(){a=!0},this.$get=["$anchorScroll","$timeout",function(b,c){return a?b:function(a){return c(function(){a[0].scrollIntoView()},0,!1)}}]}function z(a,c,d,e,f){function g(){return c.has?function(a){return c.has(a)?c.get(a):null}:function(a){try{return c.get(a)}catch(a){return null}}}function h(a,c){var d=function(){return{enter:function(a,b,c){b.after(a),c()},leave:function(a,b){a.remove(),b()}}};if(k)return{enter:function(a,c,d){b.version.minor>2?k.enter(a,null,c).then(d):k.enter(a,null,c,d)},leave:function(a,c){b.version.minor>2?k.leave(a).then(c):k.leave(a,c)}};if(j){var e=j&&j(c,a);return{enter:function(a,b,c){e.enter(a,null,b),c()},leave:function(a,b){e.leave(a),b()}}}return d()}var i=g(),j=i("$animator"),k=i("$animate"),l={restrict:"ECA",terminal:!0,priority:400,transclude:"element",compile:function(c,g,i){return function(c,g,j){function k(){if(m&&(m.remove(),m=null),o&&(o.$destroy(),o=null),n){var a=n.data("$uiViewAnim");s.leave(n,function(){a.$$animLeave.resolve(),m=null}),m=n,n=null}}function l(h){var l,m=B(c,j,g,e),t=m&&a.$current&&a.$current.locals[m];if(h||t!==p){l=c.$new(),p=a.$current.locals[m],l.$emit("$viewContentLoading",m);var u=i(l,function(a){var e=f.defer(),h=f.defer(),i={$animEnter:e.promise,$animLeave:h.promise,$$animLeave:h};a.data("$uiViewAnim",i),s.enter(a,g,function(){e.resolve(),o&&o.$emit("$viewContentAnimationEnded"),(b.isDefined(r)&&!r||c.$eval(r))&&d(a)}),k()});n=u,o=l,o.$emit("$viewContentLoaded",m),o.$eval(q)}}var m,n,o,p,q=j.onload||"",r=j.autoscroll,s=h(j,c);g.inheritedData("$uiView");c.$on("$stateChangeSuccess",function(){l(!1)}),l(!0)}}};return l}function A(a,c,d,e){return{restrict:"ECA",priority:-400,compile:function(f){var g=f.html();return function(f,h,i){var j=d.$current,k=B(f,i,h,e),l=j&&j.locals[k];if(l){h.data("$uiView",{name:k,state:l.$$state}),h.html(l.$template?l.$template:g);var m=b.extend({},l);f[l.$$resolveAs]=m;var n=a(h.contents());if(l.$$controller){l.$scope=f,l.$element=h;var o=c(l.$$controller,l);l.$$controllerAs&&(f[l.$$controllerAs]=o,f[l.$$controllerAs][l.$$resolveAs]=m),N(o.$onInit)&&o.$onInit(),h.data("$ngControllerController",o),h.children().data("$ngControllerController",o)}n(f)}}}}}function B(a,b,c,d){var e=d(b.uiView||b.name||"")(a),f=c.inheritedData("$uiView");return e.indexOf("@")>=0?e:e+"@"+(f?f.state.name:"")}function C(a,b){var c,d=a.match(/^\s*({[^}]*})\s*$/);if(d&&(a=b+"("+d[1]+")"),c=a.replace(/\n/g," ").match(/^([^(]+?)\s*(\((.*)\))?$/),!c||4!==c.length)throw new Error("Invalid state ref '"+a+"'");return{state:c[1],paramExpr:c[3]||null}}function D(a){var b=a.parent().inheritedData("$uiView");if(b&&b.state&&b.state.name)return b.state}function E(a){var b="[object SVGAnimatedString]"===Object.prototype.toString.call(a.prop("href")),c="FORM"===a[0].nodeName;return{attr:c?"action":b?"xlink:href":"href",isAnchor:"A"===a.prop("tagName").toUpperCase(),clickable:!c}}function F(a,b,c,d,e){return function(f){var g=f.which||f.button,h=e();if(!(g>1||f.ctrlKey||f.metaKey||f.shiftKey||a.attr("target"))){var i=c(function(){b.go(h.state,h.params,h.options)});f.preventDefault();var j=d.isAnchor&&!h.href?1:0;f.preventDefault=function(){j--<=0&&c.cancel(i)}}}}function G(a,b){return{relative:D(a)||b.$current,inherit:!0}}function H(a,c){return{restrict:"A",require:["?^uiSrefActive","?^uiSrefActiveEq"],link:function(d,e,f,g){var h,i=C(f.uiSref,a.current.name),j={state:i.state,href:null,params:null},k=E(e),l=g[1]||g[0],m=null;j.options=S(G(e,a),f.uiSrefOpts?d.$eval(f.uiSrefOpts):{});var n=function(c){c&&(j.params=b.copy(c)),j.href=a.href(i.state,j.params,j.options),m&&m(),l&&(m=l.$$addStateInfo(i.state,j.params)),null!==j.href&&f.$set(k.attr,j.href)};i.paramExpr&&(d.$watch(i.paramExpr,function(a){a!==j.params&&n(a)},!0),j.params=b.copy(d.$eval(i.paramExpr))),n(),k.clickable&&(h=F(e,a,c,k,function(){return j}),e[e.on?"on":"bind"]("click",h),d.$on("$destroy",function(){e[e.off?"off":"unbind"]("click",h)}))}}}function I(a,b){return{restrict:"A",require:["?^uiSrefActive","?^uiSrefActiveEq"],link:function(c,d,e,f){function g(b){m.state=b[0],m.params=b[1],m.options=b[2],m.href=a.href(m.state,m.params,m.options),n&&n(),j&&(n=j.$$addStateInfo(m.state,m.params)),m.href&&e.$set(i.attr,m.href)}var h,i=E(d),j=f[1]||f[0],k=[e.uiState,e.uiStateParams||null,e.uiStateOpts||null],l="["+k.map(function(a){return a||"null"}).join(", ")+"]",m={state:null,params:null,options:null,href:null},n=null;c.$watch(l,g,!0),g(c.$eval(l)),i.clickable&&(h=F(d,a,b,i,function(){return m}),d[d.on?"on":"bind"]("click",h),c.$on("$destroy",function(){d[d.off?"off":"unbind"]("click",h)}))}}}function J(a,b,c){return{restrict:"A",controller:["$scope","$element","$attrs","$timeout",function(b,d,e,f){function g(b,c,e){var f=a.get(b,D(d)),g=h(b,c),i={state:f||{name:b},params:c,hash:g};return p.push(i),q[g]=e,function(){var a=p.indexOf(i);a!==-1&&p.splice(a,1)}}function h(a,c){if(!O(a))throw new Error("state should be a string");return P(c)?a+U(c):(c=b.$eval(c),P(c)?a+U(c):a)}function i(){for(var a=0;a<p.length;a++)l(p[a].state,p[a].params)?j(d,q[p[a].hash]):k(d,q[p[a].hash]),m(p[a].state,p[a].params)?j(d,n):k(d,n)}function j(a,b){f(function(){a.addClass(b)})}function k(a,b){a.removeClass(b)}function l(b,c){return a.includes(b.name,c)}function m(b,c){return a.is(b.name,c)}var n,o,p=[],q={};n=c(e.uiSrefActiveEq||"",!1)(b);try{o=b.$eval(e.uiSrefActive)}catch(a){}o=o||c(e.uiSrefActive||"",!1)(b),P(o)&&R(o,function(c,d){if(O(c)){var e=C(c,a.current.name);g(e.state,b.$eval(e.paramExpr),d)}}),this.$$addStateInfo=function(a,b){if(!(P(o)&&p.length>0)){var c=g(a,b,o);return i(),c}},b.$on("$stateChangeSuccess",i),i()}]}}function K(a){var b=function(b,c){return a.is(b,c)};return b.$stateful=!0,b}function L(a){var b=function(b,c,d){return a.includes(b,c,d)};return b.$stateful=!0,b}var M=b.isDefined,N=b.isFunction,O=b.isString,P=b.isObject,Q=b.isArray,R=b.forEach,S=b.extend,T=b.copy,U=b.toJson;b.module("ui.router.util",["ng"]),b.module("ui.router.router",["ui.router.util"]),b.module("ui.router.state",["ui.router.router","ui.router.util"]),b.module("ui.router",["ui.router.state"]),b.module("ui.router.compat",["ui.router"]),q.$inject=["$q","$injector"],b.module("ui.router.util").service("$resolve",q),r.$inject=["$http","$templateCache","$injector"],b.module("ui.router.util").service("$templateFactory",r);var V;s.prototype.concat=function(a,b){var c={caseInsensitive:V.caseInsensitive(),strict:V.strictMode(),squash:V.defaultSquashPolicy()};return new s(this.sourcePath+a+this.sourceSearch,S(c,b),this)},s.prototype.toString=function(){return this.source},s.prototype.exec=function(a,b){function c(a){function b(a){return a.split("").reverse().join("")}function c(a){return a.replace(/\\-/g,"-")}var d=b(a).split(/-(?!\\)/),e=o(d,b);return o(e,c).reverse()}var d=this.regexp.exec(a);if(!d)return null;b=b||{};var e,f,g,h=this.parameters(),i=h.length,j=this.segments.length-1,k={};if(j!==d.length-1)throw new Error("Unbalanced capture group in route '"+this.source+"'");var l,m;for(e=0;e<j;e++){for(g=h[e],l=this.params[g],m=d[e+1],f=0;f<l.replace.length;f++)l.replace[f].from===m&&(m=l.replace[f].to);m&&l.array===!0&&(m=c(m)),M(m)&&(m=l.type.decode(m)),k[g]=l.value(m)}for(;e<i;e++){for(g=h[e],k[g]=this.params[g].value(b[g]),l=this.params[g],m=b[g],f=0;f<l.replace.length;f++)l.replace[f].from===m&&(m=l.replace[f].to);M(m)&&(m=l.type.decode(m)),k[g]=l.value(m)}return k},s.prototype.parameters=function(a){return M(a)?this.params[a]||null:this.$$paramNames},s.prototype.validates=function(a){return this.params.$$validates(a)},s.prototype.format=function(a){function b(a){return encodeURIComponent(a).replace(/-/g,function(a){return"%5C%"+a.charCodeAt(0).toString(16).toUpperCase()})}a=a||{};var c=this.segments,d=this.parameters(),e=this.params;if(!this.validates(a))return null;var f,g=!1,h=c.length-1,i=d.length,j=c[0];for(f=0;f<i;f++){var k=f<h,l=d[f],m=e[l],n=m.value(a[l]),p=m.isOptional&&m.type.equals(m.value(),n),q=!!p&&m.squash,r=m.type.encode(n);if(k){var s=c[f+1],t=f+1===h;if(q===!1)null!=r&&(j+=Q(r)?o(r,b).join("-"):encodeURIComponent(r)),j+=s;else if(q===!0){var u=j.match(/\/$/)?/\/?(.*)/:/(.*)/;j+=s.match(u)[1]}else O(q)&&(j+=q+s);t&&m.squash===!0&&"/"===j.slice(-1)&&(j=j.slice(0,-1))}else{if(null==r||p&&q!==!1)continue;if(Q(r)||(r=[r]),0===r.length)continue;r=o(r,encodeURIComponent).join("&"+l+"="),j+=(g?"&":"?")+(l+"="+r),g=!0}}return j},t.prototype.is=function(a,b){return!0},t.prototype.encode=function(a,b){return a},t.prototype.decode=function(a,b){return a},t.prototype.equals=function(a,b){return a==b},t.prototype.$subPattern=function(){var a=this.pattern.toString();return a.substr(1,a.length-2)},t.prototype.pattern=/.*/,t.prototype.toString=function(){return"{Type:"+this.name+"}"},t.prototype.$normalize=function(a){return this.is(a)?a:this.decode(a)},t.prototype.$asArray=function(a,b){function d(a,b){function d(a,b){return function(){return a[b].apply(a,arguments)}}function e(a){return Q(a)?a:M(a)?[a]:[]}function f(a){switch(a.length){case 0:return c;case 1:return"auto"===b?a[0]:a;default:return a}}function g(a){return!a}function h(a,b){return function(c){if(Q(c)&&0===c.length)return c;c=e(c);var d=o(c,a);return b===!0?0===n(d,g).length:f(d)}}function i(a){return function(b,c){var d=e(b),f=e(c);if(d.length!==f.length)return!1;for(var g=0;g<d.length;g++)if(!a(d[g],f[g]))return!1;return!0}}this.encode=h(d(a,"encode")),this.decode=h(d(a,"decode")),this.is=h(d(a,"is"),!0),this.equals=i(d(a,"equals")),this.pattern=a.pattern,this.$normalize=h(d(a,"$normalize")),this.name=a.name,this.$arrayMode=b}if(!a)return this;if("auto"===a&&!b)throw new Error("'auto' array mode is for query parameters only");return new d(this,a);
},b.module("ui.router.util").provider("$urlMatcherFactory",u),b.module("ui.router.util").run(["$urlMatcherFactory",function(a){}]),v.$inject=["$locationProvider","$urlMatcherFactoryProvider"],b.module("ui.router.router").provider("$urlRouter",v),w.$inject=["$urlRouterProvider","$urlMatcherFactoryProvider"],b.module("ui.router.state").factory("$stateParams",function(){return{}}).constant("$state.runtime",{autoinject:!0}).provider("$state",w).run(["$injector",function(a){a.get("$state.runtime").autoinject&&a.get("$state")}]),x.$inject=[],b.module("ui.router.state").provider("$view",x),b.module("ui.router.state").provider("$uiViewScroll",y),z.$inject=["$state","$injector","$uiViewScroll","$interpolate","$q"],A.$inject=["$compile","$controller","$state","$interpolate"],b.module("ui.router.state").directive("uiView",z),b.module("ui.router.state").directive("uiView",A),H.$inject=["$state","$timeout"],I.$inject=["$state","$timeout"],J.$inject=["$state","$stateParams","$interpolate"],b.module("ui.router.state").directive("uiSref",H).directive("uiSrefActive",J).directive("uiSrefActiveEq",J).directive("uiState",I),K.$inject=["$state"],L.$inject=["$state"],b.module("ui.router.state").filter("isState",K).filter("includedByState",L)}(window,window.angular);


  }).apply(root, arguments);
});
}(this));

(function (CRM) {
  define('leave-absences/my-leave/modules/settings',[
    'common/angular'
  ], function (angular) {
    return angular.module('my-leave.settings', []).constant('settings', {
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/my-leave/'
    });
  });
})(CRM);

/* eslint-env amd */

(function (CRM) {
  define('leave-absences/my-leave/modules/config',[
    'common/angular',
    'leave-absences/my-leave/modules/settings'
  ], function (angular) {
    return angular.module('my-leave.config', ['my-leave.settings'])
      .config([
        '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider', '$logProvider', 'settings',
        function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider, $logProvider, settings) {
          $logProvider.debugEnabled(settings.debug);

          $resourceProvider.defaults.stripTrailingSlashes = false;
          $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

          $urlRouterProvider.otherwise('/my-leave/report');
          $stateProvider
            .state('my-leave', {
              abstract: true,
              url: '/my-leave',
              template: '<my-leave-container contact-id="$resolve.contactId"></my-leave-container>',
              resolve: {
                contactId: function () {
                  return CRM.vars.leaveAndAbsences.contactId;
                },
                format: ['DateFormat', function (DateFormat) {
                  // stores the data format in HR_setting.DATE_FORMAT
                  return DateFormat.getDateFormat();
                }]
              }
            })
            .state('my-leave.report', {
              url: '/report',
              template: '<staff-leave-report contact-id="myleave.contactId"></staff-leave-report>'
            })
            .state('my-leave.calendar', {
              url: '/calendar',
              template: '<staff-leave-calendar contact-id="myleave.contactId"></staff-leave-calendar>'
            });
        }
      ]);
  });
})(CRM);

define('leave-absences/my-leave/modules/components',[
  'common/angular'
], function (angular) {
  return angular.module('my-leave.components', []);
});

/* eslint-env amd */

define('leave-absences/my-leave/components/my-leave-container',[
  'leave-absences/my-leave/modules/components'
], function (components) {
  components.component('myLeaveContainer', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-container.html';
    }],
    controllerAs: 'myleave',
    controller: ['$log', '$scope', '$uibModal', 'settings', function ($log, $scope, $modal, settings) {
      $log.debug('Component: my-leave-container');

      var vm = {};
      vm.leaveRequest = {
        fromDate: new Date(),
        toDate: new Date(),
        showDatePickerFrom: false,
        showDatePickerTo: false,
        isChangeExpanded: false,
        selectedResponse: '1',
        isAdmin: false
      };

      vm.showModal = function () {
        $modal.open({
          templateUrl: settings.pathTpl + 'components/my-leave-request.html',
          // TODO The controller needs to be moved a separate file when implementing the logic
          controller: ['leaveRequest', '$uibModalInstance', function (leaveRequest, modalInstance) {
            var vm = {};

            vm.leaveRequest = leaveRequest;

            vm.closeModal = function () {
              modalInstance.close();
            };

            return vm;
          }],
          controllerAs: 'modal',
          resolve: {
            leaveRequest: function () {
              return vm.leaveRequest;
            }
          }
        });
      };

      return vm;
    }]
  });
});

/* eslint-env amd */

(function (CRM) {
  define('leave-absences/shared/modules/shared-settings',[
    'common/angular'
  ], function (angular) {
    return angular.module('leave-absences.settings', []).constant('shared-settings', {
      attachmentToken: CRM.vars.leaveAndAbsences.attachmentToken,
      debug: CRM.debug,
      managerPathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/manager-leave/',
      sharedPathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/shared/',
      serverDateFormat: 'YYYY-MM-DD',
      serverDateTimeFormat: 'YYYY-MM-DD HH:mm:ss',
      permissions: {
        admin: {
          access: 'access leave and absences',
          administer: 'administer leave and absences'
        },
        ssp: {
          access: 'access leave and absences in ssp',
          manage: 'manage leave and absences in ssp'
        }
      },
      fileUploader: {
        queueLimit: 10,
        allowedMimeTypes: {
          'txt': 'plain',
          'png': 'png',
          'jpeg': 'jpeg',
          'bmp': 'bmp',
          'gif': 'gif',
          'pdf': 'pdf',
          'doc': 'msword',
          'docx': 'vnd.openxmlformats-officedocument.wordprocessingml.document',
          'xls': 'vnd.ms-excel',
          'xlsx': 'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'ppt': 'vnd.ms-powerpoint',
          'pptx': 'vnd.openxmlformats-officedocument.presentationml.presentation'
        }
      },
      statusNames: {
        approved: 'approved',
        adminApproved: 'admin_approved',
        awaitingApproval: 'awaiting_approval',
        moreInformationRequired: 'more_information_required',
        rejected: 'rejected',
        cancelled: 'cancelled'
      }
    });
  });
})(CRM);

define('leave-absences/shared/modules/apis',[
  'common/angular',
  'common/modules/apis',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.apis', [
    'common.apis',
    'leave-absences.settings'
  ]);
});

define('leave-absences/shared/modules/models-instances',[
  'common/angular',
  'common/models/instances/instance',
  'common/modules/services',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models.instances', [
    'common.models.instances',
    'common.services',
    'leave-absences.settings'
  ]);
});

define('leave-absences/shared/modules/models',[
  'common/angular',
  'common/modules/models',
  'common/modules/services',
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.models', [
    'common.models',
    'common.services',
    'leave-absences.apis',
    'leave-absences.models.instances',
    'leave-absences.settings',
  ]);
});

define('leave-absences/shared/models/instances/absence-period-instance',[
  'leave-absences/shared/modules/models-instances',
  'common/moment',
  'common/models/instances/instance',
  'common/services/hr-settings',
], function (instances, moment) {
  'use strict';

  instances.factory('AbsencePeriodInstance', ['$log', 'ModelInstance', 'HR_settings',
    function ($log, ModelInstance, HR_settings) {
      $log.debug('AbsencePeriodInstance');

      return ModelInstance.extend({
        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            current: false
          };
        },
        /**
         * Sets the current property of this absence period on instantiation.
         *
         * @return {object} updated attributes object
         */
        transformAttributes: function (attributes) {
          var today = moment();
          attributes.current = false;

          if (moment(attributes.start_date).isSameOrBefore(today, 'day') &&
            moment(attributes.end_date).isSameOrAfter(today, 'day')) {
            attributes.current = true;
          }

          return attributes;
        },
        /**
         *  Finds out if given date is in this object's absence period.
         *
         * @param  {Date} whichDate given date either as Date object or its string representation
         * @return true if whichDate is in this instance's period range, else false
         */
        isInPeriod: function (whichDate) {
          var dateFormat = HR_settings.DATE_FORMAT.toUpperCase();
          var checkDate = moment(whichDate, dateFormat);

          return moment(this.start_date).isSameOrBefore(checkDate) &&
            moment(this.end_date).isSameOrAfter(checkDate);
        }
      });
    }
  ]);
});

define('leave-absences/shared/apis/absence-period-api',[
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('AbsencePeriodAPI', ['$log', 'api', function ($log, api) {
    $log.debug('AbsencePeriodAPI');

    return api.extend({
      /**
       * This method returns all the AbsencePeriods.
       *
       * @param  {Object} params  matches the api endpoint params (title, start_date, end_date etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('AbsencePeriodAPI');

        return this.sendGET('AbsencePeriod', 'get', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});

define('leave-absences/shared/models/absence-period-model',[
  'leave-absences/shared/modules/models',
  'common/moment',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/models/instances/absence-period-instance',
  'leave-absences/shared/apis/absence-period-api',
  'common/models/model',
  'common/services/hr-settings',
], function (models, moment) {
  'use strict';

  models.factory('AbsencePeriod', [
    '$log', 'Model', 'AbsencePeriodAPI', 'AbsencePeriodInstance', 'shared-settings',
    function ($log, Model, absencePeriodAPI, instance, sharedSettings) {
      $log.debug('AbsencePeriod');

      return Model.extend({
        /**
         * Calls the all() method of the AbsencePeriod API, and returns an
         * AbsencePeriodInstance for each absencePeriod.
         *
         * @param  {Object} params  matches the api endpoint params (title, start_date, end_date etc)
         * @return {Promise}
         */
        all: function (params) {
          return absencePeriodAPI.all(params)
            .then(function (absencePeriods) {
              return absencePeriods.map(function (absencePeriod) {
                return instance.init(absencePeriod, true);
              });
            });
        },
        /**
         *  Finds out if current date is in any absence period.
         *  If found then return absence period instance of it.
         *
         * @return {Object} Absence period instance or null if not found
         */
        current: function () {
          var today = moment().format(sharedSettings.serverDateFormat);

          var params = {
            "start_date": {
              '<=': today
            },
            "end_date": {
              '>=': today
            }
          };

          return absencePeriodAPI.all(params)
            .then(function (absencePeriods) {

              if (absencePeriods && absencePeriods.length) {
                return instance.init(absencePeriods[0], true);
              }

              return null;
            });
        }
      });
    }
  ]);
});

define('leave-absences/shared/models/instances/absence-type-instance',[
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('AbsenceTypeInstance', ['$log', 'ModelInstance', function ($log, ModelInstance) {
    $log.debug('AbsenceTypeInstance');

    return ModelInstance.extend({});
  }]);
});

define('leave-absences/shared/apis/absence-type-api',[
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (_, moment, apis) {
  'use strict';

  apis.factory('AbsenceTypeAPI', ['$log', 'api', 'shared-settings', function ($log, api, sharedSettings) {
    $log.debug('AbsenceTypeAPI');

    return api.extend({

      /**
       * This method returns all the active AbsenceTypes unless specified in param.
       *
       * @param  {Object} params  matches the api endpoint params (title, weight etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('AbsenceTypeAPI.all');

        return this.sendGET('AbsenceType', 'get', _.defaults(params || {}, { is_active: true }))
          .then(function (data) {
            return data.values;
          });
      },

      /**
       * Calculate Toil Expiry Date
       *
       * @param  {string} absenceTypeID
       * @param  {Object} date
       * @param  {Object} params
       * @return {Promise}
       */
      calculateToilExpiryDate: function (absenceTypeID, date, params) {
        $log.debug('AbsenceTypeAPI.calculateToilExpiryDate');

        params = _.assign({}, params, {
          absence_type_id: absenceTypeID,
          date: moment(date).format(sharedSettings.serverDateFormat)
        });

        return this.sendPOST('AbsenceType', 'calculateToilExpiryDate', params)
          .then(function (data) {
            return data.values.expiry_date;
          });
      }
    });
  }]);
});

define('leave-absences/shared/models/absence-type-model',[
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/absence-type-instance',
  'leave-absences/shared/apis/absence-type-api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('AbsenceType', [
    '$log', 'Model', 'AbsenceTypeAPI', 'AbsenceTypeInstance',
    function ($log, Model, absenceTypeAPI, instance) {
      $log.debug('AbsenceType');

      return Model.extend({
        /**
         * Calls the all() method of the AbsenceType API, and returns an
         * AbsenceTypeInstance for each absenceType.
         *
         * @param  {Object} params  matches the api endpoint params (title, weight etc)
         * @return {Promise}
         */
        all: function (params) {
          return absenceTypeAPI.all(params)
            .then(function (absenceTypes) {
              return absenceTypes.map(function (absenceType) {
                return instance.init(absenceType, true);
              });
            });
        },

        /**
         * Calls the calculateToilExpiryDate() method of the AbsenceType API
         *
         * @param  {string} absenceTypeID
         * @param  {Object} date
         * @param  {Object} params
         * @return {Promise}
         */
        calculateToilExpiryDate: function (absenceTypeID, date, params) {
          return absenceTypeAPI.calculateToilExpiryDate(absenceTypeID, date, params);
        }
      });
    }
  ]);
});

/* eslint-env amd */

define('leave-absences/shared/modules/components',[
  'common/angular'
], function (angular) {
  return angular.module('leave-absences.components', []);
});

/* eslint-env amd */
define('leave-absences/shared/components/staff-leave-report',[
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components'
], function (_, moment, components) {
  components.component('staffLeaveReport', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (settings) {
      return settings.sharedPathTpl + 'components/staff-leave-report.html';
    }],
    controllerAs: 'report',
    controller: [
      '$log', '$q', '$rootScope', 'checkPermissions', 'AbsencePeriod', 'AbsenceType',
      'Entitlement', 'LeaveRequest', 'OptionGroup', 'dialog', 'HR_settings',
      'shared-settings', controller
    ]
  });

  function controller ($log, $q, $rootScope, checkPermissions, AbsencePeriod, AbsenceType, Entitlement, LeaveRequest, OptionGroup, dialog, HRSettings, sharedSettings) {
    $log.debug('Component: staff-leave-report');

    var actionMatrix = {};
    actionMatrix[sharedSettings.statusNames.awaitingApproval] = ['edit', 'cancel', 'delete'];
    actionMatrix[sharedSettings.statusNames.moreInformationRequired] = ['respond', 'cancel', 'delete'];
    actionMatrix[sharedSettings.statusNames.approved] = ['view', 'cancel', 'delete'];
    actionMatrix[sharedSettings.statusNames.cancelled] = ['view', 'delete'];
    actionMatrix[sharedSettings.statusNames.rejected] = ['view', 'delete'];

    var requestSort = 'from_date DESC';
    var role = 'staff';

    var vm = Object.create(this);
    vm.absencePeriods = [];
    vm.absenceTypes = {};
    vm.absenceTypesFiltered = {};
    vm.dateFormat = HRSettings.DATE_FORMAT;
    vm.leaveRequestStatuses = {};
    vm.selectedPeriod = null;
    vm.loading = {
      content: true,
      page: true
    };
    vm.sections = {
      approved: { open: false, data: [], loading: false, loadFn: loadApprovedRequests },
      entitlements: { open: false, data: [], loading: false, loadFn: loadEntitlementsBreakdown },
      expired: { open: false, data: [], loading: false, loadFn: loadExpiredBalanceChanges },
      holidays: { open: false, data: [], loading: false, loadFn: loadPublicHolidaysRequests },
      pending: { open: false, data: [], loading: false, loadFn: loadPendingRequests },
      other: { open: false, data: [], loading: false, loadFn: loadOtherRequests }
    };

    /**
     * Returns the available actions, based on the current status
     * of the given leave request and on additional logic
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @return {Array}
     */
    vm.actionsFor = function (leaveRequest) {
      var statusKey = vm.leaveRequestStatuses[leaveRequest.status_id].name;
      var actions = statusKey ? actionMatrix[statusKey] : [];

      if (!canLeaveRequestBeCancelled(leaveRequest)) {
        actions = _.without(actions, 'cancel');
      }

      // TODO: The logic is not really elegant, but the whole "actions" bit
      // (html + js) should be moved into its own component
      if (role === 'admin') {
        // The staff's "edit" action is "respond" for admin, and viceversa
        if (_.includes(actions, 'edit')) {
          actions = actions.join(',').replace('edit', 'respond').split(',');
        } else if (_.includes(actions, 'respond')) {
          actions = actions.join(',').replace('respond', 'edit').split(',');
        }
      } else {
        // A non-admin user does not have access to the "delete" actions
        actions = _.without(actions, 'delete');
      }

      return actions;
    };

    /**
     * Performs an action on a given leave request
     * NOTE: For now it only supports the similar "cancel" and "delete" actions
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {string} action
     */
    vm.action = function (leaveRequest, action) {
      if (!~['cancel', 'delete'].indexOf(action)) {
        return;
      }

      dialog.open({
        title: 'Confirm ' + (action === 'cancel' ? 'Cancellation' : 'Deletion') + '?',
        copyCancel: 'Cancel',
        copyConfirm: 'Confirm',
        classConfirm: 'btn-danger',
        msg: 'Are you sure you want to ' + action + ' this leave record? This cannot be undone',
        onConfirm: function () {
          return leaveRequest[action]();
        }
      })
      .then(function (response) {
        !!response && removeLeaveRequestFromItsSection(leaveRequest, action === 'cancel');
      });
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {AbsencePeriodInstance} period
     * @return {string}
     */
    vm.labelPeriod = function (period) {
      return period.current ? 'Current Period (' + period.title + ')' : period.title;
    };

    /**
     * Refreshes all data that is dependend on the selected absence period,
     * and clears the cached data of closed sections
     */
    vm.refresh = function () {
      vm.loading.content = true;

      $q.all([
        loadEntitlements(),
        loadBalanceChanges()
      ])
      .then(function () {
        vm.loading.content = false;
      })
      .then(function () {
        return $q.all([
          loadOpenSectionsData(),
          clearClosedSectionsData()
        ]);
      });
    };

    /**
     * Opens/closes the given section. When opening it triggers the
     * load function if no cached data is present
     *
     * @param {string} sectionName
     */
    vm.toggleSection = function (sectionName) {
      var section = vm.sections[sectionName];
      section.open = !section.open;

      if (section.open && !section.data.length) {
        callSectionLoadFn(section);
      }
    };

    // Init block
    (function init () {
      checkPermissions(sharedSettings.permissions.admin.administer)
      .then(function (isAdmin) {
        role = isAdmin ? 'admin' : role;
      })
      .then(function () {
        return $q.all([
          loadStatuses(),
          loadAbsenceTypes(),
          loadAbsencePeriods()
        ]);
      })
      .then(function () {
        vm.loading.page = false;
      })
      .then(function () {
        return $q.all([
          loadEntitlements(),
          loadBalanceChanges()
        ]);
      })
      .then(function () {
        vm.loading.content = false;
      });

      registerEvents();
    })();

    /**
     * Calls the load function of the given data, and puts the section
     * in and out of loading mode
     *
     * @param  {Object} section
     * @return {Promise}
     */
    function callSectionLoadFn (section) {
      section.loading = true;

      return section.loadFn().then(function () {
        section.loading = false;
      });
    }

    /**
     * Checks if the given leave request can be cancelled
     *
     * Based on following constants
     * REQUEST_CANCELATION_NO = 1;
     * REQUEST_CANCELATION_ALWAYS = 2;
     * REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE = 3;
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @return {Boolean}
     */
    function canLeaveRequestBeCancelled (leaveRequest) {
      var allowCancellationValue = vm.absenceTypes[leaveRequest.type_id].allow_request_cancelation;

      if (role === 'admin') {
        return true;
      }

      if (allowCancellationValue === '3') {
        return moment().isBefore(leaveRequest.from_date);
      }

      return allowCancellationValue === '2';
    }

    /**
     * Clears the cached data of all the closed sections
     */
    function clearClosedSectionsData () {
      Object.values(vm.sections)
        .filter(function (section) {
          return !section.open;
        })
        .forEach(function (section) {
          section.data = [];
        });
    }

    /**
     * NOTE: This should be just temporary, see PCHR-1810
     * Loads all the possible statuses of a leave request
     *
     * @return {Promise}
     */
    function loadStatuses () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          vm.leaveRequestStatuses = _.indexBy(statuses, 'value');
        });
    }

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    function loadAbsencePeriods () {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          vm.absencePeriods = _.sortBy(absencePeriods, 'start_date');
          vm.selectedPeriod = _.find(vm.absencePeriods, function (period) {
            return period.current === true;
          });
        });
    }

    /**
     * Loads the absence types
     *
     * @return {Promise}
     */
    function loadAbsenceTypes () {
      return AbsenceType.all()
        .then(function (absenceTypes) {
          vm.absenceTypes = _.indexBy(absenceTypes, 'id');
        });
    }

    /**
     * Loads the approved requests
     *
     * @return {Promise}
     */
    function loadApprovedRequests () {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.selectedPeriod.start_date },
        to_date: { to: vm.selectedPeriod.end_date },
        status_id: valueOfRequestStatus(sharedSettings.statusNames.approved)
      }, null, requestSort)
      .then(function (leaveRequests) {
        vm.sections.approved.data = leaveRequests.list;
      });
    }

    /**
     * Loads the balance changes of the various sections
     * and groups them by absence type
     *
     * @return {Promise}
     */
    function loadBalanceChanges () {
      return $q.all([
        LeaveRequest.balanceChangeByAbsenceType(vm.contactId, vm.selectedPeriod.id, null, true),
        LeaveRequest.balanceChangeByAbsenceType(vm.contactId, vm.selectedPeriod.id, [
          valueOfRequestStatus(sharedSettings.statusNames.approved)
        ]),
        LeaveRequest.balanceChangeByAbsenceType(vm.contactId, vm.selectedPeriod.id, [
          valueOfRequestStatus(sharedSettings.statusNames.awaitingApproval),
          valueOfRequestStatus(sharedSettings.statusNames.moreInformationRequired)
        ])
      ])
      .then(function (results) {
        _.forEach(vm.absenceTypes, function (absenceType) {
          absenceType.balanceChanges = {
            publicHolidays: results[0][absenceType.id],
            approved: results[1][absenceType.id],
            pending: results[2][absenceType.id]
          };
        });
      });
    }

    /**
     * Loads the entitlements, including current and future balance,
     * and groups the entitlements value and remainder by absence type
     * Also Filters the absence types which allows overuse or allows
     * accrual request or has entitlement more than 0
     *
     * @return {Promise}
     */
    function loadEntitlements () {
      return Entitlement.all({
        contact_id: vm.contactId,
        period_id: vm.selectedPeriod.id
      }, true)
      .then(function (entitlements) {
        vm.entitlements = entitlements;
      })
      .then(function () {
        vm.absenceTypesFiltered = _.filter(vm.absenceTypes, function (absenceType) {
          var entitlement = _.find(vm.entitlements, function (entitlement) {
            return entitlement.type_id === absenceType.id;
          });

          // set entitlement to 0 if no entitlement is present
          absenceType.entitlement = entitlement ? entitlement.value : 0;
          absenceType.remainder = entitlement ? entitlement.remainder : { current: 0, future: 0 };

          return !((absenceType.entitlement === 0) &&
          (absenceType.allow_overuse !== '1') &&
          (absenceType.allow_accruals_request !== '1'));
        });
      });
    }

    /**
     * Loads the entitlements breakdown
     *
     * @return {Promise}
     */
    function loadEntitlementsBreakdown () {
      return Entitlement.breakdown({
        contact_id: vm.contactId,
        period_id: vm.selectedPeriod.id
      }, vm.entitlements)
      .then(function () {
        return processBreakdownsList(vm.entitlements);
      })
      .then(function (breakdownListFlatten) {
        vm.sections.entitlements.data = breakdownListFlatten;
      });
    }

    /**
     * Loads the expired balance changes (Brought Forward, TOIL)
     *
     * @return {Promise}
     */
    function loadExpiredBalanceChanges () {
      return $q.all([
        Entitlement.breakdown({
          contact_id: vm.contactId,
          period_id: vm.selectedPeriod.id,
          expired: true
        }),
        LeaveRequest.all({
          contact_id: vm.contactId,
          from_date: {from: vm.selectedPeriod.start_date},
          to_date: {to: vm.selectedPeriod.end_date},
          request_type: 'toil',
          expired: true
        }, null, requestSort)
      ])
        .then(function (results) {
          return $q.all({
            expiredBalanceChangesFlatten: processBreakdownsList(results[0]),
            expiredTOILS: processExpiredTOILS(results[1].list)
          });
        })
        .then(function (results) {
          vm.sections.expired.data = results.expiredBalanceChangesFlatten.concat(results.expiredTOILS);
        });
    }

    /**
     * Loads the data of all the currently opened sections
     *
     * @return {Promise}
     */
    function loadOpenSectionsData () {
      return $q.all(Object.values(vm.sections)
        .filter(function (section) {
          return section.open;
        })
        .map(function (section) {
          return callSectionLoadFn(section);
        }));
    }

    /**
     * Loads the rejected/cancelled leave requests
     *
     * @return {Promise}
     */
    function loadOtherRequests () {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.selectedPeriod.start_date },
        to_date: { to: vm.selectedPeriod.end_date },
        status_id: { in: [
          valueOfRequestStatus(sharedSettings.statusNames.rejected),
          valueOfRequestStatus(sharedSettings.statusNames.cancelled)
        ] }
      }, null, requestSort)
      .then(function (leaveRequests) {
        vm.sections.other.data = leaveRequests.list;
      });
    }

    /**
     * Loads the currently pending leave requests
     *
     * @return {Promise}
     */
    function loadPendingRequests () {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.selectedPeriod.start_date },
        to_date: { to: vm.selectedPeriod.end_date },
        status_id: { in: [
          valueOfRequestStatus(sharedSettings.statusNames.awaitingApproval),
          valueOfRequestStatus(sharedSettings.statusNames.moreInformationRequired)
        ] }
      }, null, requestSort, null, false)
      .then(function (leaveRequests) {
        vm.sections.pending.data = leaveRequests.list;
      });
    }

    /**
     * Loads the leave requests associated to public holidays
     *
     * @return {Promise}
     */
    function loadPublicHolidaysRequests () {
      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { from: vm.selectedPeriod.start_date },
        to_date: { to: vm.selectedPeriod.end_date },
        public_holiday: true
      }, null, requestSort)
      .then(function (leaveRequests) {
        vm.sections.holidays.data = leaveRequests.list;
      });
    }

    /**
     * For each breakdowns, it sets the absence type id to
     * each list entry (based on the entitlement they belong to)
     * and flattens the result in the end to get one single list
     *
     * @param  {Array} list
     *   each breakdown should contain `id` and `breakdown` properties
     * @return {Promise} resolves to the flatten list
     */
    function processBreakdownsList (list) {
      return $q.resolve()
        .then(function () {
          return list.map(function (listEntry) {
            var entitlement = _.find(vm.entitlements, function (entitlement) {
              return entitlement.id === listEntry.id;
            });

            return listEntry.breakdown.map(function (breakdownEntry) {
              return _.assign(_.clone(breakdownEntry), {
                type_id: entitlement.type_id
              });
            });
          });
        })
        .then(function (breakdownList) {
          return Array.prototype.concat.apply([], breakdownList);
        });
    }

    /**
     * Process each expired TOIL requests
     *
     * @param  {Array} list of expired TOIL request
     * @return {Promise} resolves to the flatten list
     */
    function processExpiredTOILS (list) {
      return $q.resolve()
        .then(function () {
          return list.map(function (listEntry) {
            return {
              'expiry_date': listEntry.toil_expiry_date,
              'type': {
                'label': 'Accrued TOIL'
              }
            };
          });
        });
    }

    /**
     * Register events which will be called by other modules
     */
    function registerEvents () {
      $rootScope.$on('LeaveRequest::new', function () {
        vm.refresh();
      });

      $rootScope.$on('LeaveRequest::edit', function () {
        vm.refresh();
      });

      $rootScope.$on('LeaveRequest::deleted', function (event, leaveRequest) {
        removeLeaveRequestFromItsSection(leaveRequest);
      });
    }

    /**
     * Removes the given leave request from the section it currently belongs to
     * (only the "approved", "pending", and "other" sections support request removal)
     *
     * If the leave request belonged to either the "approved" or "pending" section,
     * then the numbers of the section will be recalculated
     *
     * @param  {LeaveRequestInstance} leaveRequest
     * @param  {Boolean} moveToOther If true, it moves the leave request to
     *         the "other" section (if the section has already cached data)
     * @return {Promise}
     */
    function removeLeaveRequestFromItsSection (leaveRequest, moveToOther) {
      var sectionBelonged;

      ['approved', 'pending', 'other'].forEach(function (sectionName) {
        var sections = _.remove(vm.sections[sectionName].data, function (dataEntry) {
          return dataEntry.id === leaveRequest.id;
        });

        sections.length && (sectionBelonged = sectionName);
      });

      if (sectionBelonged !== 'other') {
        updateSectionNumbersWithLeaveRequestBalanceChange(leaveRequest, sectionBelonged);

        if (moveToOther && vm.sections.other.data.length) {
          vm.sections.other.data.push(leaveRequest);
        }
      }
    }

    /**
     * Recalculates the section's balance change and remainder numbers with the
     * given leave request's balance change
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {string} section
     */
    function updateSectionNumbersWithLeaveRequestBalanceChange (leaveRequest, section) {
      var absenceType = vm.absenceTypes[leaveRequest.type_id];
      var remainderType = (section === 'pending') ? 'future' : 'current';

      absenceType.balanceChanges[section] -= leaveRequest.balance_change;
      absenceType.remainder[remainderType] -= leaveRequest.balance_change;
    }

    /**
     * Returns the value of the given leave request status
     *
     * @param  {string} statusName
     * @return {integer}
     */
    function valueOfRequestStatus (statusName) {
      return _.find(vm.leaveRequestStatuses, function (status) {
        return status.name === statusName;
      })['value'];
    }

    return vm;
  }
});

define('leave-absences/shared/modules/controllers',[
  'common/angular',
  'common/angularBootstrap',
  'leave-absences/shared/modules/models',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  return angular.module('leave-absences.controllers', [
    'ui.select',
    'leave-absences.models',
    'leave-absences.models.instances',
    'leave-absences.settings',
  ]);
});

define('leave-absences/shared/models/instances/public-holiday-instance',[
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('PublicHolidayInstance', ['$log', 'ModelInstance', function ($log, ModelInstance) {
    $log.debug('PublicHolidayInstance');

    return ModelInstance.extend({});
  }]);
});

define('leave-absences/shared/apis/public-holiday-api',[
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('PublicHolidayAPI', ['$log', 'api', function ($log, api) {
    $log.debug('PublicHolidayAPI');

    return api.extend({
      /**
       * This method returns all the PublicHolidays.
       *
       * @param  {Object} params  matches the api endpoint params (title, date etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('PublicHolidayAPI');

        return this.sendGET('PublicHoliday', 'get', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});

define('leave-absences/shared/models/public-holiday-model',[
  'leave-absences/shared/modules/models',
  'common/moment',
  'leave-absences/shared/models/instances/public-holiday-instance',
  'leave-absences/shared/apis/public-holiday-api',
  'common/models/model',
  'common/services/hr-settings',
], function (models, moment) {
  'use strict';

  models.factory('PublicHoliday', [
    '$log', 'Model', 'PublicHolidayAPI', 'PublicHolidayInstance', 'shared-settings',
    function ($log, Model, publicHolidayAPI, instance, sharedSettings) {
      $log.debug('PublicHoliday');

      return Model.extend({
        /**
         * Calls the all() method of the PublicHoliday API, and returns an
         * PublicHolidayInstance for each public holiday.
         *
         * @param  {Object} params  matches the api endpoint params (title, date etc)
         * @return {Promise}
         */
        all: function (params) {
          $log.debug('PublicHoliday.all', params);

          return publicHolidayAPI.all(params)
            .then(function (publicHolidays) {
              return publicHolidays.map(function (publicHoliday) {
                return instance.init(publicHoliday, true);
              });
            });
        },
        /**
         *  Finds out if given date is a public holiday.
         *
         * @param  {Date} whichDate given date either as Date object or its string representation
         * @return {Bool} returns true if date is a public holday else false
         */
        isPublicHoliday: function (whichDate) {
          $log.debug('PublicHoliday.isPublicHoliday', whichDate);

          var checkDate = moment(whichDate).format(sharedSettings.serverDateFormat);
          var params = {
            'date': checkDate
          };

          return publicHolidayAPI.all(params)
            .then(function (publicHolidays) {
              return !!publicHolidays.length;
            });
        }
      });
    }
  ]);
});

/* eslint-env amd */
define('leave-absences/shared/controllers/calendar-ctrl',[
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/public-holiday-model'
], function (controllers, _, moment) {
  'use strict';

  controllers.controller('CalendarCtrl', ['$q', '$timeout', 'shared-settings', 'AbsencePeriod', 'AbsenceType',
    'LeaveRequest', 'PublicHoliday', 'OptionGroup', controller]);

  function controller ($q, $timeout, sharedSettings, AbsencePeriod, AbsenceType, LeaveRequest, PublicHoliday, OptionGroup) {
    var dayTypes = [];
    var leaveRequestStatuses = [];
    var publicHolidays = [];

    this.absencePeriods = [];
    this.absenceTypes = [];
    this.leaveRequests = {};
    this.months = [];
    this.monthLabels = moment.monthsShort();
    this.selectedMonths = [];
    this.selectedPeriod = null;
    this.loading = {
      calendar: false,
      page: false
    };

    /**
     * Fetches months from newly selected period and refresh data
     */
    this.changeSelectedPeriod = function () {
      this._fetchMonthsFromPeriod();
      this.refresh();
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {object} absenceType
     * @return {object} style
     */
    this.getAbsenceTypeStyle = function (absenceType) {
      return {
        backgroundColor: absenceType.color,
        borderColor: absenceType.color
      };
    };

    /**
     * Returns day name of the sent date(Monday, Tuesday etc.)
     *
     * @param  {string} date
     * @return {string}
     */
    this.getDayName = function (date) {
      return this._getDateObjectWithFormat(date).format('ddd');
    };

    /**
     * Decides whether sent date is a public holiday
     *
     * @param  {string} date
     * @return {boolean}
     */
    this.isPublicHoliday = function (date) {
      return !!publicHolidays[this._getDateObjectWithFormat(date).valueOf()];
    };

    /**
     * Labels the given period according to whether it's current or not
     *
     * @param  {AbsencePeriodInstance} period
     * @return {string}
     */
    this.labelPeriod = function (period) {
      return period.current ? 'Current Period (' + period.title + ')' : period.title;
    };

    /**
     * Fetch all the months from the current period and
     * save it in vm.months
     */
    this._fetchMonthsFromPeriod = function () {
      var months = [];
      var startDate = moment(this.selectedPeriod.start_date);
      var endDate = moment(this.selectedPeriod.end_date);

      while (startDate.isBefore(endDate)) {
        months.push(this._getMonthSkeleton(startDate));
        startDate.add(1, 'month');
      }

      this.months = months;
    };

    /**
     * Converts given date to moment object with server format
     *
     * @param {Date/String} date from server
     * @return {Date} Moment date
     */
    this._getDateObjectWithFormat = function (date) {
      return moment(date, sharedSettings.serverDateFormat);
    };

    /**
     * Finds the month which matches with the sent date
     * and return the related object
     *
     * @param {object} date
     * @param {array} months
     * @return {object}
     */
    this._getMonthObjectByDate = function (date, months) {
      return _.find(months, function (month) {
        return (month.month === date.month()) && (month.year === date.year());
      });
    };

    /**
     * Returns the styles for a specific leaveRequest
     * which will be used in the view for each date
     *
     * @param  {object} leaveRequest
     * @param  {object} dateObj - Date UI object which handles look of a calendar cell
     * @return {object}
     */
    this._getStyles = function (leaveRequest, dateObj) {
      var absenceType;

      dateObj.leaveRequest = leaveRequest;

      absenceType = _.find(this.absenceTypes, function (absenceType) {
        return absenceType.id === leaveRequest.type_id;
      });

      // If Balance change is positive, mark as Accrued TOIL
      if (leaveRequest.balance_change > 0) {
        dateObj.UI.isAccruedTOIL = true;

        return {
          borderColor: absenceType.color
        };
      }

      return {
        backgroundColor: absenceType.color,
        borderColor: absenceType.color
      };
    };

    /**
     * Initialize the calendar
     *
     * @param {function} intermediateSteps
     */
    this._init = function (intermediateSteps) {
      this.loading.page = true;
      // Select current month as default
      this.selectedMonths = [this.monthLabels[moment().month()]];

      $q.all([
        this._loadAbsencePeriods(),
        this._loadAbsenceTypes(),
        this._loadPublicHolidays(),
        this._loadStatuses(),
        this._loadDayTypes()
      ])
      .then(function () {
        return intermediateSteps ? intermediateSteps() : null;
      })
      .then(function () {
        this.legendCollapsed = false;

        return this._loadLeaveRequestsAndCalendar();
      }.bind(this))
      .finally(function () {
        this.loading.page = false;
      }.bind(this));
    };

    /**
     * Returns whether a date is of a specific type
     * half_day_am or half_day_pm
     *
     * @param  {string} name
     * @param  {object} leaveRequest
     * @param  {string} date
     *
     * @return {boolean}
     */
    this._isDayType = function (name, leaveRequest, date) {
      var dayType = dayTypes[name];

      if (moment(date).isSame(leaveRequest.from_date)) {
        return dayType.value === leaveRequest.from_date_type;
      }

      if (moment(date).isSame(leaveRequest.to_date)) {
        return dayType.value === leaveRequest.to_date_type;
      }
    };

    /**
     * Returns whether a leaveRequest is pending approval
     *
     * @param  {object} leaveRequest
     * @return {boolean}
     */
    this._isPendingApproval = function (leaveRequest) {
      var status = leaveRequestStatuses[leaveRequest.status_id];

      return status.name === sharedSettings.statusNames.awaitingApproval;
    };

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    this._loadAbsencePeriods = function () {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          this.absencePeriods = _.sortBy(absencePeriods, 'start_date');
          this.selectedPeriod = _.find(this.absencePeriods, function (period) {
            return !!period.current;
          });

          this._fetchMonthsFromPeriod();
        }.bind(this));
    };

    /**
     * Loads the active absence types
     *
     * @return {Promise}
     */
    this._loadAbsenceTypes = function () {
      return AbsenceType.all({
        is_active: true
      }).then(function (absenceTypes) {
        this.absenceTypes = absenceTypes;
      }.bind(this));
    };

    /**
     * Loads the leave request day types
     *
     * @return {Promise}
     */
    this._loadDayTypes = function () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (dayTypesData) {
          dayTypes = _.indexBy(dayTypesData, 'name');
        });
    };

    /**
     * Loads the approved, admin_approved and waiting approval leave requests and calls calendar load function
     *
     * @param {string} contactParamName - contact parameter key name
     * @param {boolean} cache
     * @param {function} intermediateSteps
     * @return {Promise}
     */
    this._loadLeaveRequestsAndCalendar = function (contactParamName, cache, intermediateSteps) {
      cache = cache === undefined ? true : cache;

      var params = {
        from_date: {from: this.selectedPeriod.start_date},
        to_date: {to: this.selectedPeriod.end_date},
        status_id: {'IN': [
          getLeaveStatusValuefromName(sharedSettings.statusNames.approved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.adminApproved),
          getLeaveStatusValuefromName(sharedSettings.statusNames.awaitingApproval)
        ]}
      };
      params[contactParamName] = this.contactId;

      return LeaveRequest.all(params, {}, null, null, cache)
        .then(function (leaveRequestsData) {
          this._indexLeaveRequests(leaveRequestsData.list);

          return this._loadCalendar();
        }.bind(this))
        .then(function () {
          intermediateSteps && intermediateSteps();
          this.loading.calendar = false;
          this._showMonthLoader();
        }.bind(this));
    };

    /**
     * Loads all the public holidays
     *
     * @return {Promise}
     */
    this._loadPublicHolidays = function () {
      return PublicHoliday.all()
        .then(function (publicHolidaysData) {
          // convert to an object with time stamp as key
          publicHolidays = _.transform(publicHolidaysData, function (result, publicHoliday) {
            result[this._getDateObjectWithFormat(publicHoliday.date).valueOf()] = publicHoliday;
          }.bind(this), {});
        }.bind(this));
    };

    /**
     * Loads the status option values
     *
     * @return {Promise}
     */
    this._loadStatuses = function () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          leaveRequestStatuses = _.indexBy(statuses, 'value');
        });
    };

    /**
     * Reset the months data for before refresh
     */
    this._resetMonths = function () {
      _.each(this.months, function (month) {
        month.data = [];
      });
    };

    /**
     * Show month loader for all months initially
     * then hide each loader on the interval of an offset value
     */
    this._showMonthLoader = function () {
      var monthLoadDelay = 500;
      var offset = 0;

      this.months.forEach(function (month) {
        // immediately show the current month...
        month.loading = month.label !== this.selectedMonths[0];

        // delay other months
        if (month.loading) {
          $timeout(function () {
            month.loading = false;
          }, offset);

          offset += monthLoadDelay;
        }
      }.bind(this));
    };

    /**
     * Returns leave status value from name
     * @param {String} name - name of the leave status
     * @returns {int/boolean}
     */
    function getLeaveStatusValuefromName (name) {
      var leaveStatus = _.find(leaveRequestStatuses, function (status) {
        return status.name === name;
      });

      return leaveStatus ? leaveStatus.value : false;
    }

    return this;
  }
});

/* eslint-env amd */

define('leave-absences/shared/components/staff-leave-calendar',[
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/controllers/calendar-ctrl'
], function (_, moment, components) {
  components.component('staffLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/staff-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: ['$controller', '$log', '$rootScope', 'Calendar', controller]
  });

  function controller ($controller, $log, $rootScope, Calendar) {
    $log.debug('Component: staff-leave-calendar');

    var parentCtrl = $controller('CalendarCtrl');
    var vm = Object.create(parentCtrl);
    var calendarData;

    /**
     * Returns the calendar information for a specific month
     *
     * @param  {object} monthObj
     * @return {array}
     */
    vm.getMonthData = function (monthObj) {
      var month;

      month = _.find(vm.months, function (month) {
        return (month.month === monthObj.month) && (month.year === monthObj.year);
      });

      return month ? month.data : [];
    };

    /**
     * Refresh all leave request and calendar data
     */
    vm.refresh = function () {
      vm.loading.calendar = true;
      vm._resetMonths();
      vm._loadLeaveRequestsAndCalendar();
    };

    /**
     * Returns skeleton for the month object
     *
     * @param  {Object} startDate
     * @return {Object}
     */
    vm._getMonthSkeleton = function (startDate) {
      return {
        month: startDate.month(),
        year: startDate.year(),
        data: []
      };
    };

    /**
     * Index leave requests by date
     *
     * @param  {Array} leaveRequests - leave requests array from API
     */
    vm._indexLeaveRequests = function (leaveRequests) {
      vm.leaveRequests = {};

      _.each(leaveRequests, function (leaveRequest) {
        _.each(leaveRequest.dates, function (leaveRequestDate) {
          vm.leaveRequests[leaveRequestDate.date] = leaveRequest;
        });
      });
    };

    /**
     * Loads the calendar data
     *
     * @return {Promise}
     */
    vm._loadCalendar = function () {
      return Calendar.get(vm.contactId, vm.selectedPeriod.id)
        .then(function (calendar) {
          calendarData = calendar;
          vm._setCalendarProps(calendarData);
        });
    };

    /**
     * Loads all the leave requests and calls calendar load function
     *
     * @return {Promise}
     */
    vm._loadLeaveRequestsAndCalendar = function () {
      return parentCtrl._loadLeaveRequestsAndCalendar.call(vm, 'contact_id', false);
    };

    /**
     * Sets UI related properties(isWeekend, isNonWorkingDay etc)
     * to the calendar data
     *
     * @param  {object} calendar
     */
    vm._setCalendarProps = function (calendar) {
      var leaveRequest;
      var monthData = _.clone(vm.months);

      _.each(calendar.days, function (dateObj) {
        // fetch leave request, search by date
        leaveRequest = vm.leaveRequests[dateObj.date];

        dateObj.UI = {
          isWeekend: calendar.isWeekend(vm._getDateObjectWithFormat(dateObj.date)),
          isNonWorkingDay: calendar.isNonWorkingDay(vm._getDateObjectWithFormat(dateObj.date)),
          isPublicHoliday: vm.isPublicHoliday(dateObj.date)
        };

        // set below props only if leaveRequest is found
        if (leaveRequest) {
          dateObj.UI.styles = vm._getStyles(leaveRequest, dateObj);
          dateObj.UI.isRequested = vm._isPendingApproval(leaveRequest);
          dateObj.UI.isAM = vm._isDayType('half_day_am', leaveRequest, dateObj.date);
          dateObj.UI.isPM = vm._isDayType('half_day_pm', leaveRequest, dateObj.date);
        }

        vm._getMonthObjectByDate(moment(dateObj.date), monthData).data.push(dateObj);
      });

      vm.months = monthData;
    };

    (function init () {
      vm._init();

      $rootScope.$on('LeaveRequest::new', vm.refresh);
      $rootScope.$on('LeaveRequest::edit', vm.refresh);
      $rootScope.$on('LeaveRequest::deleted', deleteLeaveRequest);
    })();

    /**
     * Event handler for Delete event of Leave Request
     *
     * @param  {object} event
     * @param  {object} leaveRequest
     */
    function deleteLeaveRequest (event, leaveRequest) {
      vm.leaveRequests = _.omit(vm.leaveRequests, function (leaveRequestObj) {
        return leaveRequestObj.id === leaveRequest.id;
      });
      vm._resetMonths();
      vm._setCalendarProps(calendarData);
    }

    return vm;
  }
});

/* eslint-env amd */

define('leave-absences/shared/components/leave-request-create-dropdown',[
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
], function (components) {
  components.component('leaveRequestCreateDropdown', {
    bindings: {
      btnClass: '@',
      contactId: '<',
      selectedContactId: '<',
      isSelfRecord: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-create-dropdown.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: leave-request-create-dropdown');

    var vm = this;

    vm.leaveRequestOptions = [
      { type: 'leave', icon: 'briefcase', label: 'Leave' },
      { type: 'sickness', icon: 'stethoscope', label: 'Sickness' },
      { type: 'toil', icon: 'calendar-plus-o', label: 'Overtime' }
    ];
  }
});

/* eslint-env amd */

define('leave-absences/shared/components/leave-request-popup-comments-tab',[
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
], function (_, moment, components) {
  components.component('leaveRequestPopupCommentsTab', {
    bindings: {
      canManage: '<',
      mode: '<',
      request: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup-comments-tab.html';
    }],
    controllerAs: 'commentsCtrl',
    controller: ['$log', 'HR_settings', 'shared-settings', 'Contact', controller]
  });

  function controller ($log, HRSettings, sharedSettings, Contact) {
    $log.debug('Component: leave-request-popup-comments-tab');

    var vm = Object.create(this);

    vm.comment = {
      text: '',
      contacts: {}
    };

    (function init () {
      loadCommentsAndContactNames();
    }());

    /**
     * Add a comment into comments array, also clears the comments textbox
     */
    vm.addComment = function () {
      vm.request.comments.push({
        contact_id: vm.request.contact_id,
        created_at: moment(new Date()).format(sharedSettings.serverDateTimeFormat),
        leave_request_id: vm.request.id,
        text: vm.comment.text
      });
      vm.comment.text = '';
    };

    /**
     * Format a date-time into user format and returns
     *
     * @return {String}
     */
    vm.formatDateTime = function (dateTime) {
      return moment(dateTime, sharedSettings.serverDateTimeFormat).format(HRSettings.DATE_FORMAT.toUpperCase() + ' HH:mm');
    };

    /**
     * Returns the comments which are not marked for deletion
     *
     * @return {Array}
     */
    vm.getActiveComments = function () {
      return vm.request.comments.filter(function (comment) {
        return !comment.toBeDeleted;
      });
    };

    /**
     * Returns the comment author name
     * @param {String} contactId
     *
     * @return {String}
     */
    vm.getCommentorName = function (contactId) {
      if (contactId === vm.request.contact_id) {
        return 'Me';
      } else if (vm.comment.contacts[contactId]) {
        return vm.comment.contacts[contactId].display_name;
      }
    };

    /**
     * Checks if popup is opened in given mode
     *
     * @param {String} modeParam to open leave request like edit or view or create
     * @return {Boolean}
     */
    vm.isMode = function (modeParam) {
      return vm.mode === modeParam;
    };

    /**
     * Orders comment, used as a angular filter
     * @param {Object} comment
     *
     * @return {Date}
     */
    vm.orderComment = function (comment) {
      return moment(comment.created_at, sharedSettings.serverDateTimeFormat);
    };

    /**
     * Decides visiblity of remove comment button
     * @param {Object} comment - comment object
     *
     * @return {Boolean}
     */
    vm.removeCommentVisibility = function (comment) {
      return !comment.comment_id || vm.canManage;
    };

    /**
     * Loads unique contact names for all the comments
     *
     * @return {Promise}
     */
    function loadContactNames () {
      var contactIDs = [];

      _.each(vm.request.comments, function (comment) {
        // Push only unique contactId's which are not same as logged in user
        if (comment.contact_id !== vm.request.contact_id && contactIDs.indexOf(comment.contact_id) === -1) {
          contactIDs.push(comment.contact_id);
        }
      });

      return Contact.all({
        id: { IN: contactIDs }
      }, { page: 1, size: 0 })
        .then(function (contacts) {
          vm.comment.contacts = _.indexBy(contacts.list, 'contact_id');
        });
    }

    /**
     * Loads the comments for current leave request
     *
     * @return {Promise}
     */
    function loadCommentsAndContactNames () {
      return vm.request.loadComments()
        .then(function () {
          // loadComments sets the comments on request object instead of returning it
          vm.request.comments.length && loadContactNames();
        });
    }

    return vm;
  }
});

define('leave-absences/shared/modules/directives',[
  'common/angular',
  'common/angularBootstrap',
  'common/services/angular-date/date-format',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/modules/controllers',
], function (angular) {
  return angular.module('leave-absences.directives', [
    'ui.bootstrap',
    'common.angularDate',
    'common.mocks',
    'leave-absences.settings',
    'leave-absences.controllers',
  ]);
});

/* eslint-env amd */

define('leave-absences/shared/models/instances/calendar-instance',[
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (_, moment, instances) {
  'use strict';

  instances.factory('CalendarInstance', [
    '$log', 'ModelInstance', 'shared-settings',
    function ($log, ModelInstance, sharedSettings) {
      /**
       * This method checks whether a date matches the send type.
       *
       * @param {Object} date
       * @param {string} Type of day
       *
       * @return {Boolean}
       * @throws error if date is not found in calendarData
       */
      function checkDate (date, dayType) {
        var searchedDate = this.days[getDateObjectWithFormat(date).valueOf()];

        return searchedDate ? searchedDate.type.name === dayType : false;
      }

      /**
       * Converts given date to moment object with server format
       *
       * @param {Date/String} date from server
       * @return {Date} Moment date
       */
      function getDateObjectWithFormat (date) {
        return moment(date, sharedSettings.serverDateFormat).clone();
      }

      return ModelInstance.extend({

        /**
         * Removes the `calendar` property and creates the `day` property
         * which indexes the dates by their timestamp
         *
         * @param  {Object} attributes
         * @return {Object}
         */
        transformAttributes: function (attributes) {
          var datesObj = {};

          // convert array to an object with the timestamp being the key
          attributes.calendar.forEach(function (calendar) {
            datesObj[getDateObjectWithFormat(calendar.date).valueOf()] = calendar;
          });

          return _(attributes)
            .omit('calendar')
            .assign({ days: datesObj })
            .value();
        },

        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            days: []
          };
        },

        /**
         * This method checks whether a date is working day.
         *
         * @param {Object} date
         * @return {Boolean}
         */
        isWorkingDay: function (date) {
          return checkDate.call(this, date, 'working_day');
        },

        /**
         * This method checks whether a date is non working day.
         *
         * @param {Object} date
         * @return {Boolean}
         */
        isNonWorkingDay: function (date) {
          return checkDate.call(this, date, 'non_working_day');
        },

        /**
         * This method checks whether a date is weekend.
         *
         * @param {Object} date
         * @return {Boolean}
         */
        isWeekend: function (date) {
          return checkDate.call(this, date, 'weekend');
        }
      });
    }]);
});

/* eslint-env amd */

define('leave-absences/shared/apis/work-pattern-api',[
  'common/lodash',
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (_, apis) {
  'use strict';

  apis.factory('WorkPatternAPI', ['$log', 'api', function ($log, api) {
    $log.debug('WorkPatternAPI');

    return api.extend({

      /**
       * Assigns a work pattern to a contact
       *
       * @param {string} contactId
       * @param {string} workPatternID
       * @param {string} effectiveDate
       * @param {string} effectiveEndDate
       * @param {string} changeReason
       * @param {object} params - additional parameters
       * @return {Promise}
       */
      assignWorkPattern: function (contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, params) {
        return this.sendPOST('ContactWorkPattern', 'create', _.assign({}, params, {
          contact_id: contactId,
          pattern_id: workPatternID,
          effective_date: effectiveDate,
          effective_end_date: effectiveEndDate,
          change_reason: changeReason
        })).then(function (data) {
          return data.values;
        });
      },

      /**
       * Returns all the work patterns
       *
       * @param {object} params additional parameters
       * @return {Promise} Resolved with {Array} All Work Patterns
       */
      get: function (params) {
        return this.sendGET('WorkPattern', 'get', params || {})
          .then(function (data) {
            return data.values;
          });
      },

      /**
       * Returns the calendar for the given contact(s) and period,
       * as a list of days and their type
       *
       * @param {string/int/Array} contactId can be also an array for multiple contacts
       * @param {string/int} periodId The ID of the Absence Period
       * @param {object} params additional parameters
       * @return {Promise} Resolved with {Array} All calendar records
       */
      getCalendar: function (contactId, periodId, params) {
        $log.debug('WorkPatternAPI.getCalendar', contactId, periodId, params);

        return this.sendGET('WorkPattern', 'getcalendar', _.assign({}, params, {
          contact_id: _.isArray(contactId) ? { 'IN': contactId } : contactId,
          period_id: periodId
        }));
      },

      /**
       * Un assign a work pattern by the given contact work pattern ID
       *
       * @param {string} contactWorkPatternID
       * @return {Promise}
       */
      unassignWorkPattern: function (contactWorkPatternID) {
        return this.sendPOST('ContactWorkPattern', 'delete', {
          id: contactWorkPatternID
        });
      },

      /**
       * Returns all the work patterns of a specific contact
       *
       * @param {string} contactId
       * @param {object} params - additional parameters
       * @param {boolean} cache
       * @return {Promise} Resolved with {Array} All Work Patterns of the contact
       */
      workPatternsOf: function (contactId, params, cache) {
        return this.sendGET('ContactWorkPattern', 'get', _.assign({}, params, {
          contact_id: contactId,
          'api.WorkPattern.get': { 'id': '$value.pattern_id' }
        }), cache).then(function (data) {
          data = data.values;

          return data.map(storeWorkPattern);
        });
      }
    });

    /**
     * ContactWorkPatterns data will have key 'api.WorkPattern.get'
     * which is normalized with a friendlier 'workPatterns' key
     *
     * @param  {Object} workPattern
     * @return {Object}
     */
    function storeWorkPattern (workPattern) {
      var clone = _.clone(workPattern);

      clone['workPattern'] = clone['api.WorkPattern.get']['values'][0];
      delete clone['api.WorkPattern.get'];

      return clone;
    }
  }]);
});

/* eslint-env amd */

define('leave-absences/shared/models/calendar-model',[
  'common/lodash',
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/calendar-instance',
  'leave-absences/shared/apis/work-pattern-api',
  'common/models/model'
], function (_, models) {
  'use strict';

  models.factory('Calendar', ['$log', 'Model', 'WorkPatternAPI', 'CalendarInstance',
    function ($log, Model, workPatternAPI, instance) {
      $log.debug('Calendar');

      return Model.extend({

        /**
         * This method returns the calendar(s) for the given contact(s) and period,
         * as a list of days and their type
         *
         * @param {string/int/Array} contactId can be also an array for multiple contacts
         * @param {string} periodId
         * @param {object} params additional parameters
         * @return {Promise} resolves with CalendarInstance(s)
         */
        get: function (contactId, periodId, params) {
          $log.debug('Calendar.get');

          return workPatternAPI.getCalendar(contactId, periodId, params)
            .then(function (data) {
              var list = data.values.map(function (contactCalendar) {
                return instance.init(contactCalendar);
              });

              return _.isArray(contactId) ? list : list[0];
            });
        }
      });
    }
  ]);
});

define('leave-absences/shared/models/instances/entitlement-instance',[
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('EntitlementInstance', ['$log', 'ModelInstance', 'EntitlementAPI', function ($log, ModelInstance, EntitlementAPI) {
    $log.debug('EntitlementInstance');

    return ModelInstance.extend({
      /**
       * Returns the default custom data (as in, not given by the Entitlement API)
       * with its default values
       *
       * @return {object}
       */
      defaultCustomData: function () {
        return {
          remainder: {
            current: 0,
            future: 0
          },
          breakdown: []
        }
      },

      /**
       * Populates the breakdown of the entitlement, by passing to the api
       * the entitlement id.
       *
       * @return {Promise} with updated entitlement model instance with the side
       * effect of setting this.breakdown property to newly obtained entitlement breakdown
       */
      getBreakdown: function () {
        return EntitlementAPI.breakdown({
            entitlement_id: this.id
          })
          .then(function (breakdown) {
            this.breakdown = breakdown;
          }.bind(this));
      }
    });
  }]);
});

define('leave-absences/shared/apis/entitlement-api',[
  'leave-absences/shared/modules/apis',
  'common/lodash',
  'common/services/api'
], function (apis, _) {
  'use strict';

  apis.factory('EntitlementAPI', ['$log', 'api', function ($log, api) {
    $log.debug('EntitlementAPI');

    /**
     * Entitlements data will have key 'api.LeavePeriodEntitlement.getremainder'
     * which is normalized with a friendlier 'remainder' key
     *
     * @param  {Object} entitlement
     * @return {Object}
     */
    function storeRemainder(entitlement) {
      var clone = _.clone(entitlement);
      var remainderValues = clone['api.LeavePeriodEntitlement.getremainder']['values'];

      if (remainderValues.length) {
        clone['remainder'] = remainderValues[0]['remainder'];
      }

      delete clone['api.LeavePeriodEntitlement.getremainder'];

      return clone;
    }

    /**
     * Entitlements data will have key 'api.LeavePeriodEntitlement.getentitlement'
     * which is normalized with a friendlier 'value' key
     *
     * @param  {Object} entitlement
     * @return {Object}
     */
    function storeValue(entitlement) {
      var clone = _.clone(entitlement);
      var value = clone['api.LeavePeriodEntitlement.getentitlement'].values[0].entitlement;

      clone['value'] = value;
      delete clone['api.LeavePeriodEntitlement.getentitlement'];

      return clone;
    }

    return api.extend({

      /**
       * This method returns all the entitlements.
       *
       * It chains an additional call to the `getentitlement` endpoint to also return
       * the actual value of each entitlement
       *
       * It can also return the remainder (current and future) among the rest of
       * the data when passed withRemainder.
       *
       * @param  {Object} params  matches the api endpoint params (period_id, contact_id, etc)
       * @param  {boolean} withRemainder  can be set to true to return remainder of entitlements
       * @return {Promise}
       */
      all: function (params, withRemainder) {
        $log.debug('EntitlementAPI.all');

        params['api.LeavePeriodEntitlement.getentitlement'] = {
          'entitlement_id': '$value.id'
        };

        if (withRemainder) {
          params['api.LeavePeriodEntitlement.getremainder'] = {
            'entitlement_id': '$value.id',
            'include_future': true
          }
        }

        return this.sendGET('LeavePeriodEntitlement', 'get', params, false)
          .then(function (data) {
            return data.values;
          })
          .then(function (entitlements) {
            entitlements = entitlements.map(storeValue);

            if (withRemainder) {
              entitlements = entitlements.map(storeRemainder);
            }

            return entitlements;
          });
      },
      /**
       * This method returns the breakdown of entitlement from various types of leave balances.
       *
       * @param  {Object} params  matches the api endpoint params (period_id, contact_id, etc)
       * @return {Promise}  will return a promise which when resolved will contain breakdown
       * details along with entitlement id
       */
      breakdown: function (params) {
        $log.debug('EntitlementAPI.breakdown');

        return this.sendGET('LeavePeriodEntitlement', 'getbreakdown', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});

define('leave-absences/shared/models/entitlement-model',[
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/entitlement-instance',
  'leave-absences/shared/apis/entitlement-api',
  'common/models/model'
], function (models) {
  'use strict';

  models.factory('Entitlement', [
    '$log', 'Model', 'EntitlementAPI', 'EntitlementInstance',
    function ($log, Model, entitlementAPI, instance) {
      $log.debug('Entitlement');

      return Model.extend({
        /**
         * Calls the all() method of the Entitlement API, and returns an
         * EntitlementInstance for each entitlement.
         * It can pass the withRemainder property to get remainder for entitlements.
         *
         * @param {Object} params matches the api endpoint params (period_id, contact_id, etc)
         * @param {boolean} withRemainder to return remainder of data
         * @return {Promise}
         */
        all: function (params, withRemainder) {
          return entitlementAPI.all(this.processFilters(params), withRemainder)
            .then(function (entitlements) {
              return entitlements.map(function (entitlement) {
                return instance.init(entitlement, true);
              });
            });
        },
        /**
         * Calls the `breakdown` method of the entitlement API
         *
         * The return value of the promise changes based on whether an array of `EntitlementInstance`s
         * has been passed to the method or not.
         *
         * If it hasn 't, then it returns the entitlements along with breakdown details based on params passed.
         * If it has, then it loads the breakdown data into each correspondent entitlement, which then
         * are returned back.
         *
         * @param {Object} params matches the api endpoint params (period_id, contact_id, etc)
         * @param {Array}  [entitlements] an array of `EntitlementInstance`s
         * @return {Promise}
         */
        breakdown: function (params, entitlements) {
          return entitlementAPI.breakdown(params)
            .then(function (breakdown) {
              if (entitlements) {
                entitlements.map(function (entitlement) {
                  var foundEntitlement = _.find(breakdown, function (element) {
                    return element.id == entitlement.id;
                  });

                  if (foundEntitlement) {
                    entitlement['breakdown'] = foundEntitlement['breakdown'];
                  }

                  return entitlement;
                });

                return entitlements;
              }

              return breakdown.map(function (entitlement) {
                return instance.init(entitlement, true);
              });
            });
        }
      });
    }
  ]);
});

/* eslint-env amd */
define('leave-absences/shared/models/instances/leave-request-instance',[
  'common/lodash',
  'leave-absences/shared/modules/models-instances',
  'common/models/option-group',
  'common/models/instances/instance',
  'common/services/file-upload'
], function (_, instances) {
  'use strict';

  instances.factory('LeaveRequestInstance', ['$q', 'OptionGroup', 'FileUpload',
    'shared-settings', 'ModelInstance', 'LeaveRequestAPI',
    function ($q, OptionGroup, FileUpload, sharedSettings, ModelInstance, LeaveRequestAPI) {
      /**
       * Update status ID
       *
       * @param {string} status - name of the option value
       * @return {Promise} Resolved with {Object} - Error Data in case of error
       */
      function changeLeaveStatus (status) {
        return getOptionIDByName(status)
          .then(function (statusId) {
            this.status_id = statusId.value;
            return this.update();
          }.bind(this));
      }

      /**
       * Checks if a LeaveRequest is of a specific type
       *
       * @param {string} statusName - name of the option value
       * @return {Promise} Resolved with {Boolean}
       */
      function checkLeaveStatus (statusName) {
        return getOptionIDByName(statusName)
          .then(function (statusObj) {
            return this.status_id === statusObj.value;
          }.bind(this));
      }

      /**
       * Deletes the given attachment from server. It iterates through local
       * files array to find which are to be deleted and deletes them.
       *
       * @return {Promise}
       */
      function deleteAttachments () {
        var promises = [];

        _.forEach(this.files, function (file) {
          if (file.toBeDeleted) {
            promises.push(LeaveRequestAPI.deleteAttachment(this.id, file.attachment_id));
          }
        }.bind(this));

        return $q.all(promises);
      }

      /**
       * Get ID of an option value
       *
       * @param {string} name - name of the option value
       * @return {Promise} Resolved with {Object} - Specific leave request
       */
      function getOptionIDByName (name) {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (data) {
            return data.find(function (statusObj) {
              return statusObj.name === name;
            });
          });
      }

      /**
       * Save comments which do not have an ID and delete comments which are marked for deletion
       *
       * @return {Promise}
       */
      function saveAndDeleteComments () {
        var promises = [];
        var self = this;

        // Save comments which dont have an ID
        self.comments.map(function (comment, index) {
          if (!comment.comment_id) {
            // IIFE is created to keep actual value of 'index' when promise is resolved
            (function (index) {
              promises.push(LeaveRequestAPI.saveComment(self.id, comment)
                .then(function (commentData) {
                  self.comments[index] = commentData;
                }));
            })(index);
          } else if (comment.toBeDeleted) {
            promises.push(LeaveRequestAPI.deleteComment(comment.comment_id));
          }
        });

        return $q.all(promises);
      }

      /**
       * Upload attachment in file uploder's queue
       *
       * @return {Promise}
       */
      function uploadAttachments () {
        if (this.fileUploader.queue && this.fileUploader.queue.length > 0) {
          return this.fileUploader.uploadAll({ entityID: this.id });
        } else {
          return $q.resolve([]);
        }
      }

      return ModelInstance.extend({

        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            comments: [],
            files: [],
            request_type: 'leave',
            // FileUpload.uploader has uploader property which was causing circular reference issue
            // hence renamed this uploader to fileUploader
            fileUploader: FileUpload.uploader({
              entityTable: 'civicrm_hrleaveandabsences_leave_request',
              crmAttachmentToken: sharedSettings.attachmentToken,
              queueLimit: sharedSettings.fileUploader.queueLimit,
              allowedMimeTypes: sharedSettings.fileUploader.allowedMimeTypes
            })
          };
        },

        /**
         * Cancel a leave request
         */
        cancel: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.cancelled);
        },

        /**
         * Approve a leave request
         */
        approve: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.approved);
        },

        /**
         * Reject a leave request
         */
        reject: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.rejected);
        },

        /**
         * Sends a leave request back as more information is required
         */
        sendBack: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.moreInformationRequired);
        },

        /**
         * Update a leave request
         *
         * @return {Promise} Resolved with {Object} Updated Leave request
         */
        update: function () {
          return LeaveRequestAPI.update(this.toAPI())
            .then(function () {
              return $q.all([
                saveAndDeleteComments.call(this),
                uploadAttachments.call(this),
                deleteAttachments.call(this)
              ]);
            }.bind(this));
        },

        /**
         * Create a new leave request
         *
         * @return {Promise} Resolved with {Object} Created Leave request with
         *  newly created id for this instance
         */
        create: function () {
          return LeaveRequestAPI.create(this.toAPI())
            .then(function (result) {
              this.id = result.id;

              return $q.all([
                saveAndDeleteComments.call(this),
                uploadAttachments.call(this)
              ]);
            }.bind(this));
        },

        /**
         * Sets the flag to mark file for deletion. The file is not yet deleted
         * from the server.
         *
         * @param {Object} file - Attachment object
         */
        deleteAttachment: function (file) {
          if (!file.toBeDeleted) {
            file.toBeDeleted = true;
          }
        },

        /**
         * Removes a comment from memory
         *
         * @param {Object} commentObj - comment object
         */
        deleteComment: function (commentObj) {
          // If its an already saved comment, mark a toBeDeleted flag
          if (commentObj.comment_id) {
            commentObj.toBeDeleted = true;
            return;
          }

          this.comments = _.reject(this.comments, function (comment) {
            return commentObj.created_at === comment.created_at && commentObj.text === comment.text;
          });
        },

        /**
         * Deletes the leave request
         *
         * @return {Promise}
         */
        delete: function () {
          return LeaveRequestAPI.delete(this.id);
        },

        /**
         * Validate leave request instance attributes.
         *
         * @return {Promise} empty array if no error found otherwise an object
         *  with is_error set and array of errors
         */
        isValid: function () {
          return LeaveRequestAPI.isValid(this.toAPI());
        },

        /**
         * Checks if a LeaveRequest is Approved.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isApproved: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.approved);
        },

        /**
         * Checks if a LeaveRequest is AwaitingApproval.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isAwaitingApproval: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.awaitingApproval);
        },

        /**
         * Checks if a LeaveRequest is cancelled.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isCancelled: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.cancelled);
        },

        /**
         * Checks if a LeaveRequest is Rejected.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isRejected: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.rejected);
        },

        /**
         * Checks if a LeaveRequest is Sent Back.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isSentBack: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.moreInformationRequired);
        },

        /**
         * Loads comments for this leave request.
         *
         * @return {Promise}
         */
        loadComments: function () {
          if (this.id) {
            return LeaveRequestAPI.getComments(this.id)
              .then(function (comments) {
                this.comments = comments;
              }.bind(this));
          }

          return $q.resolve();
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['balance_change', 'dates', 'comments', 'fileUploader', 'files'], key)) {
            result[key] = this[key];
          }
        },

        /**
         * Loads file attachments associated with this leave request
         *
         * @return {Promise} with array of attachments if leave request is already created else empty promise
         */
        loadAttachments: function () {
          if (this.id) {
            return LeaveRequestAPI.getAttachments(this.id)
              .then(function (attachments) {
                this.files = attachments;
              }.bind(this));
          }

          return $q.resolve();
        }
      });
    }
  ]);
});

/* eslint-env amd */
define('leave-absences/shared/apis/leave-request-api',[
  'leave-absences/shared/modules/apis',
  'common/lodash',
  'common/services/api'
], function (apis, _) {
  'use strict';

  apis.factory('LeaveRequestAPI', ['$log', 'api', '$q', 'shared-settings',
    function ($log, api, $q) {
      $log.debug('LeaveRequestAPI');

      return api.extend({

        /**
         * This method returns all the Leave Requests.
         * It supports filters, pagination, sort and extra params
         *
         * @param {object} filters - Values the full list should be filtered by
         * @param {object} pagination
         *   `page` for the current page, `size` for number of items per page
         * @param {string} sort - The field and direction to order by
         * @param  {Object} params
         * @param  {Boolean} cache
         * @return {Promise} Resolved with {Object} All leave requests
         */
        all: function (filters, pagination, sort, params, cache) {
          $log.debug('LeaveRequestAPI.all');
          var defer = $q.defer();

          // if contact_id has an empty array for IN condition, there is no point making the
          // call to the Leave Request API
          // TODO Move to Base API
          if (filters && filters.contact_id && filters.contact_id.IN && filters.contact_id.IN.length === 0) {
            defer.resolve({ list: [], total: 0, allIds: [] });
          } else {
            defer.resolve(this.getAll('LeaveRequest', filters, pagination, sort, params, 'getFull', cache));
          }

          return defer.promise;
        },

        /**
         * This method returns all the total change in balance that is caused by the
         * leave requests of a given absence type, or of all the absence types of a given contact and period.
         *
         * @param {string} contactId The ID of the Contact to get the balance change for
         * @param {string} periodId The ID of the Absence Period to get the balance change for
         * @param {array} [statuses = null] An array of OptionValue values which the list will be filtered by
         * @param {boolean} [isPublicHoliday=false] Based on the value of this param,
         * the calculation will include only the leave requests that aren't/are public holidays
         * @return {Promise} Resolved with {Object} Balance Change data or Error data
         */
        balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
          $log.debug('LeaveRequestAPI.balanceChangeByAbsenceType');
          var deferred = $q.defer();

          if (!contactId || !periodId) {
            deferred.reject('contact_id and period_id are mandatory');
          }

          var params = {
            contact_id: contactId,
            period_id: periodId,
            statuses: statuses ? {'IN': statuses} : null,
            public_holiday: isPublicHoliday || false
          };

          this.sendGET('LeaveRequest', 'getbalancechangebyabsencetype', params, false)
          .then(function (data) {
            deferred.resolve(data.values);
          });

          return deferred.promise;
        },

        /**
         * Gets the overall balance change after a leave request is created. The
         * API will create and return the detailed breakdown of it in days.
         *
         * @param {Object} params matched the API end point params like
         * mandatory values for contact_id, from_date, from_date_type and optional values for
         * to_date and to_date_type.
         *
         * @return {Promise} containing the detailed breakdown of balance leaves
         */
        calculateBalanceChange: function (params) {
          $log.debug('LeaveRequestAPI.calculateBalanceChange', params);
          var deferred = $q.defer();

          if (params && (!params.contact_id || !params.from_date || !params.from_date_type)) {
            deferred.reject('contact_id, from_date and from_date_type in params are mandatory');
          }

          this.sendPOST('LeaveRequest', 'calculatebalancechange', params)
          .then(function (data) {
            deferred.resolve(data.values);
          });

          return deferred.promise;
        },

        /**
         * Create a new leave request with given params.
         *
         * @param {Object} params matched the API end point params with
         * mandatory values for contact_id, status_id, from_date, from_date_type
         * and optional values for to_date and to_date_type.
         * If to_date is given then to_date_type is also mandotory.
         *
         * @return {Promise} containing the leave request object additionally with id key set
         * else rejects the promise with error data
         */
        create: function (params) {
          $log.debug('LeaveRequestAPI.create', params);
          var deferred = $q.defer();

          if (params) {
            if (params.to_date && !params.to_date_type) {
              deferred.reject('to_date_type is mandatory');
            } else if (!params.contact_id || !params.from_date || !params.from_date_type || !params.status_id) {
              deferred.reject('contact_id, from_date, status_id and from_date_type params are mandatory');
            }
          }

          this.sendPOST('LeaveRequest', 'create', params)
          .then(function (data) {
            deferred.resolve(data.values[0]);
          });

          return deferred.promise;
        },

        /**
         * Calls the `delete` endpoint with the given leave request id
         *
         * @param  {int/string} id
         * @return {Promise}
         */
        delete: function (id) {
          return this.sendPOST('LeaveRequest', 'delete', { id: id });
        },

        /**
         * Calls the deletecomment backend API.
         *
         * @param {String} leaveRequestID - leave request ID
         * @param {String} attachmentID - attachment ID
         * @param {Object} params
         *
         * @return {Promise}
         */
        deleteAttachment: function (leaveRequestID, attachmentID, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID,
            attachment_id: attachmentID
          });

          return this.sendPOST('LeaveRequest', 'deleteattachment', params)
          .then(function (result) {
            return result.values;
          });
        },

        /**
         * Calls the deletecomment backend API.
         *
         * @param {String} commentID - comment ID
         * @param {Object} params
         *
         * @return {Promise}
         */
        deleteComment: function (commentID, params) {
          params = _.assign({}, params, {
            comment_id: commentID
          });

          return this.sendPOST('LeaveRequest', 'deletecomment', params)
          .then(function (commentsData) {
            return commentsData.values;
          });
        },

        /**
         * Calls the getattachments backend API.
         *
         * @param {String} leaveRequestID - ID of leave request
         * @param {Object} params
         *
         * @return {Promise}
         */
        getAttachments: function (leaveRequestID, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID
          });

          return this.sendGET('LeaveRequest', 'getattachments', params, false)
          .then(function (attachments) {
            return attachments.values;
          });
        },

        /**
         * Calls the getcomment backend API.
         *
         * @param {String} leaveRequestID - ID of leave request
         * @param {Object} params
         *
         * @return {Promise}
         */
        getComments: function (leaveRequestID, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID
          });

          return this.sendGET('LeaveRequest', 'getcomment', params, false)
          .then(function (commentsData) {
            return commentsData.values;
          });
        },

        /**
         * Validate params for a new new leave request. It can be used before
         * creating a leave request to validate data.
         *
         * @param {Object} params matched the API end point params with
         * values like contact_id, status_id, from_date, from_date_type etc.,
         * @return {Promise} returns an array of errors for invalid data else empty array
         */
        isValid: function (params) {
          $log.debug('LeaveRequestAPI.isValid', params);
          var deferred = $q.defer();

          this.sendPOST('LeaveRequest', 'isValid', params)
          .then(function (data) {
            if (data.count > 0) {
              deferred.reject(_(data.values).map().flatten().value());
            } else {
              deferred.resolve(data.values);
            }
          });

          return deferred.promise;
        },

        /**
         * Calls the addcomment backend API.
         *
         * @param {string} leaveRequestID - ID of Leave Request
         * @param {Object} comment - Comment object
         * @param {Object} params
         *
         * @return {Promise}
         */
        saveComment: function (leaveRequestID, comment, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID,
            text: comment.text,
            contact_id: comment.contact_id,
            created_at: comment.created_at
          });

          return this.sendPOST('LeaveRequest', 'addcomment', params)
          .then(function (commentsData) {
            return commentsData.values;
          });
        },

        /**
         * This method is used to update a leave request
         *
         * @param {object} params - Updated values of leave request
         * @return {Promise} Resolved with {Object} Updated Leave request
         */
        update: function (params) {
          $log.debug('LeaveRequestAPI.update', params);
          var deferred = $q.defer();

          if (!params.id) {
            deferred.reject('id is mandatory field');
          }

          this.sendPOST('LeaveRequest', 'create', params)
          .then(function (data) {
            deferred.resolve(data.values[0]);
          });

          return deferred.promise;
        }
      });
    }]);
});

define('leave-absences/shared/models/leave-request-model',[
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/instances/leave-request-instance',
  'leave-absences/shared/apis/leave-request-api',
  'common/models/model',
], function (models) {
  'use strict';

  models.factory('LeaveRequest', [
    '$log',
    'Model',
    'LeaveRequestAPI',
    'LeaveRequestInstance',
    function ($log, Model, leaveRequestAPI, instance) {
      $log.debug('LeaveRequest');

      return Model.extend({

        /**
         * Get all the Leave Requests.
         * It supports filters, pagination, sort and extra params
         *
         * @param {object} filters - Values the full list should be filtered by
         * @param {object} pagination
         *   `page` for the current page, `size` for number of items per page
         * @param {string} sort - The field and direction to order by
         * @param  {Object} params
         * @param  {Boolean} cache
         * @return {Promise} resolves with {Object}
         */
        all: function (filters, pagination, sort, params, cache) {
          return leaveRequestAPI.all(this.processFilters(filters), pagination, sort, params, cache)
            .then(function (response) {
              response.list = response.list.map(function (leaveRequest) {
                return instance.init(leaveRequest, true);
              });

              return response;
            });
        },

        /**
         * Get all the total change in balance that is caused by the
         * leave requests of a given absence type, or of all the absence types of a given contact and period.
         *
         * @param {string} contactId The ID of the Contact to get the balance change for
         * @param {string} periodId The ID of the Absence Period to get the balance change for
         * @param statuses {array} An array of OptionValue values which the list will be filtered by
         * @param isPublicHoliday {boolean} Based on the value of this param,
         * the calculation will include only the leave requests that aren't/are public holidays
         * @return {Promise} Resolved with {Object} Balance Change data
         */
        balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
          return leaveRequestAPI.balanceChangeByAbsenceType(contactId, periodId, statuses, isPublicHoliday);
        },

        /**
         * Gets the overall balance change after a leave request is created. The
         * corresponding API call will create and return the detailed breakdown of it in days.
         *
         * @param {Object} params matched the API end point params like
         * mandatory values for contact_id, from_date, from_date_type and optional values for
         * to_date and to_date_type.
         *
         * @return {Promise} containing the detailed breakdown of balance leaves
         */
        calculateBalanceChange: function (params) {
          $log.debug('LeaveRequestAPI.calculateBalanceChange');

          return leaveRequestAPI.calculateBalanceChange(params);
        }
      });
    }
  ]);
});

/* eslint-env amd */

define('leave-absences/shared/controllers/request-ctrl',[
  'common/angular',
  'leave-absences/shared/modules/controllers',
  'common/lodash',
  'common/moment',
  'common/services/api/option-group',
  'common/services/hr-settings',
  'common/models/contact',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/public-holiday-model'
], function (angular, controllers, _, moment) {
  'use strict';

  controllers.controller('RequestCtrl', [
    '$log', '$q', '$rootScope', 'Contact', 'dialog', 'AbsencePeriod', 'AbsenceType',
    'api.optionGroup', 'checkPermissions', 'Calendar', 'Entitlement', 'HR_settings',
    'LeaveRequest', 'PublicHoliday', 'shared-settings',
    function ($log, $q, $rootScope, Contact, dialog, AbsencePeriod, AbsenceType,
      OptionGroup, checkPermissions, Calendar, Entitlement, HRSettings,
      LeaveRequest, PublicHoliday, sharedSettings
    ) {
      $log.debug('RequestCtrl');
      var absenceTypesAndIds;
      var availableStatusesMatrix = {};
      var initialLeaveRequestAttributes = {}; // used to compare the change in leaverequest in edit mode
      var role = '';
      var NO_ENTITLEMENT_ERROR = 'No entitlement';

      this.absencePeriods = [];
      this.absenceTypes = [];
      this.calendar = {};
      this.canManage = false; // this flag is set on initialisation of the controller
      this.contactName = null;
      this.errors = [];
      this.isSelfRecord = false; // this flag is set on initialisation of the controller
      this.managedContacts = [];
      this.mode = ''; // can be edit, create, view
      this.newStatusOnSave = null;
      this.period = {};
      this.postContactSelection = false; // flag to track if user is selected for enabling UI
      this.requestDayTypes = [];
      this.requestStatuses = {};
      this.selectedAbsenceType = {};
      this.statusNames = sharedSettings.statusNames;
      this.submitting = false;
      this.supportedFileTypes = '';
      this.today = Date.now();
      this.balance = {
        closing: 0,
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        }
      };
      this.loading = {
        absenceTypes: true,
        showBalanceChange: false,
        fromDayTypes: false,
        toDayTypes: false
      };
      // TODO temp fix to allow pageChanged to be called from html as well from functions here with proper context
      var parentThis = this;
      this.pagination = {
        currentPage: 1,
        filteredbreakdown: this.balance.change.breakdown,
        numPerPage: 5,
        totalItems: this.balance.change.breakdown.length,
        /**
         * Called when user changes the page under selection. It filters the
         * breakdown to obtain the ones for currently selected page.
         */
        pageChanged: function () {
          // filter breakdowns
          var begin = (this.currentPage - 1) * this.numPerPage;
          var end = begin + this.numPerPage;

          this.filteredbreakdown = parentThis.balance.change.breakdown.slice(begin, end);
        }
      };
      this.uiOptions = {
        isChangeExpanded: false,
        multipleDays: true,
        userDateFormat: HRSettings.DATE_FORMAT,
        userDateFormatWithTime: HRSettings.DATE_FORMAT + ' HH:mm',
        showBalance: false,
        date: {
          from: {
            show: false,
            options: {
              startingDay: 1,
              showWeeks: false
            }
          },
          to: {
            show: false,
            options: {
              minDate: null,
              maxDate: null,
              startingDay: 1,
              showWeeks: false
            }
          },
          expiry: {
            show: false,
            options: {
              minDate: null,
              maxDate: null,
              startingDay: 1,
              showWeeks: false
            }
          }
        }
      };

      /**
       * Change handler when changing no. of days like Multiple Days or Single Day.
       * It will reset dates, day types, change balance.
       */
      this.changeInNoOfDays = function () {
        this._reset();
        this._calculateOpeningAndClosingBalance();
      };

      /**
       * Calculate change in balance, it updates local balance variables.
       *
       * @return {Promise} empty promise if all required params are not set otherwise promise from server
       */
      this.calculateBalanceChange = function () {
        var self = this;

        self._setDateAndTypes();

        if (!canCalculateChange.call(self)) {
          return $q.resolve();
        }

        self.errors = [];
        self.loading.showBalanceChange = true;
        return LeaveRequest.calculateBalanceChange(getParamsForBalanceChange.call(self))
          .then(function (balanceChange) {
            if (balanceChange) {
              self.balance.change = balanceChange;
              self._calculateOpeningAndClosingBalance();
              rePaginate.call(self);
            }
            self.loading.showBalanceChange = false;
          })
          .catch(handleError.bind(self));
      };

      /**
       * Checks if submit button can be enabled for user and returns true if succeeds
       *
       * @return {Boolean}
       */
      this.canSubmit = function () {
        var canSubmit = canCalculateChange.call(this);

        // check if user has changed any attribute
        if (this.isMode('edit')) {
          canSubmit = canSubmit && hasRequestChanged.call(this);
        }

        // check if manager has changed status
        if (this.canManage && this.requestStatuses) {
          // awaiting_approval will not be available in this.requestStatuses if manager has changed selection
          canSubmit = canSubmit && !!this.getStatusFromValue(this.newStatusOnSave);
        }

        // check if the selected date period is in absence period
        canSubmit = canSubmit && !!this.period.id;

        return canSubmit && !this.isMode('view');
      };

      /**
       * Checks if user can upload more file, it totals the number of already
       * uploaded files and those which are in queue and compares it to limit.
       *
       * @return {Boolean} true is user can upload more else false
       */
      this.canUploadMore = function () {
        return this.getFilesCount() < sharedSettings.fileUploader.queueLimit;
      };

      /**
      * Closes the error alerts if any
      */
      this.closeAlert = function () {
        this.errors = [];
      };

      /**
       * Deletes the leave request
       */
      this.deleteLeaveRequest = function () {
        dialog.open({
          title: 'Confirm Deletion?',
          copyCancel: 'Cancel',
          copyConfirm: 'Confirm',
          classConfirm: 'btn-danger',
          msg: 'This cannot be undone',
          onConfirm: function () {
            return this.directiveOptions.leaveRequest.delete()
              .then(function () {
                this.dismissModal();
                $rootScope.$emit('LeaveRequest::deleted', this.directiveOptions.leaveRequest);
              }.bind(this));
          }.bind(this)
        });
      };

      /**
       * Close the modal
       */
      this.dismissModal = function () {
        this.$modalInstance.dismiss({
          $value: 'cancel'
        });
      };

      /**
       * Calculates the total number of files associated with request.
       *
       * @return {Number} of files
       */
      this.getFilesCount = function () {
        var filesWithSoftDelete = _.filter(this.request.files, function (file) {
          return file.toBeDeleted;
        });

        return this.request.files.length + this.request.fileUploader.queue.length - filesWithSoftDelete.length;
      };

      /**
       * Format a date-time into user format and returns
       *
       * @return {String}
       */
      this.formatDateTime = function (dateTime) {
        return moment(dateTime, sharedSettings.serverDateTimeFormat).format(this.uiOptions.userDateFormat.toUpperCase() + ' HH:mm');
      };

      /**
       * Returns an array of statuses depending on the previous status value
       * This is used to populate the dropdown with array of statuses.
       *
       * @return {Array}
       */

      this.getStatuses = function () {
        if (!this.request || angular.equals({}, this.requestStatuses)) {
          return [];
        }

        if (!this.request.status_id) {
          return getAvailableStatusesForStatusName.call(this, 'none');
        }

        return getAvailableStatusesForCurrentStatus.call(this);
      };

      /**
       * Gets status object for given status value
       *
       * @param {String} value - value of the status
       * @return {Object} option group of type status or undefined if not found
       */
      this.getStatusFromValue = function (value) {
        return _.find(this.requestStatuses, function (status) {
          return status.value === value;
        });
      };

      /**
       * Initializes after contact is selected either directly or by manager
       *
       * @return {Promise}
       */
      this.initAfterContactSelection = function () {
        var self = this;
        self.postContactSelection = true;

        // when manager deselects contact it is called without a selected contact_id
        if (!self.request.contact_id) {
          return $q.reject('The contact id was not set');
        }

        return $q.all([
          self._loadAbsenceTypes(),
          self._loadCalendar()
        ])
          .then(function () {
            return loadDayTypes.call(self);
          })
          .then(function () {
            return initDates.call(self);
          })
          .then(function () {
            setInitialAbsenceTypes.call(self);
            initStatus.call(self);
            initContact.call(self);

            if (self.isMode('edit')) {
              initialLeaveRequestAttributes = angular.copy(self.request.attributes());

              if (self.request.from_date === self.request.to_date) {
                self.uiOptions.multipleDays = false;
              }
            }

            self.postContactSelection = false;
            return self.calculateBalanceChange();
          })
          .catch(function (error) {
            if (error !== NO_ENTITLEMENT_ERROR) {
              return $q.reject(error);
            }
          });
      };

      /**
       * Checks if the leave request has the given status
       *
       * @param {String} leaveStatus
       * @return {Boolean}
       */
      this.isLeaveStatus = function (leaveStatus) {
        var status = this.getStatusFromValue(this.request.status_id);

        return status ? status.name === leaveStatus : false;
      };

      /**
       * Checks if popup is opened in given leave type like `leave` or `sickness` or 'toil'
       *
       * @param {String} leaveTypeParam to check the leave type of current request
       * @return {Boolean}
       */
      this.isLeaveType = function (leaveTypeParam) {
        return this.request.request_type === leaveTypeParam;
      };

      /**
       * Checks if popup is opened in given mode
       *
       * @param {String} modeParam to open leave request like edit or view or create
       * @return {Boolean}
       */
      this.isMode = function (modeParam) {
        return this.mode === modeParam;
      };

      /**
       * Checks if popup is opened in given role
       *
       * @param {String} roleParam like manager, staff
       * @return {Boolean}
       */
      this.isRole = function (roleParam) {
        return role === roleParam;
      };

      /**
       * Dismiss modal on successful creation on submit
       */
      this.ok = function () {
        // todo handle closure to pass data back to callee
        this.$modalInstance.close({
          $value: this.request
        });
      };

      /**
       * Decides visiblity of remove attachment button
       * @param {Object} attachment - attachment object
       *
       * @return {Boolean}
       */
      this.removeAttachmentVisibility = function (attachment) {
        return !attachment.attachment_id || this.canManage;
      };

      /**
       * Submits the form, only if the leave request is valid, also emits event
       * to notify event subscribers about the the save.
       * Updates request based on role and mode
       */
      this.submit = function () {
        var originalStatus = this.request.status_id;

        if (this.isMode('view') || this.submitting) {
          return;
        }

        this.submitting = true;
        changeStatusBeforeSave.call(this);

        validateBeforeSubmit.call(this)
          .then(function () {
            return this.isMode('edit') ? updateRequest.call(this) : createRequest.call(this);
          }.bind(this))
          .catch(function (errors) {
            // if there is an error, put back the original status
            this.request.status_id = originalStatus;
            errors && handleError.call(this, errors);
          }.bind(this))
          .finally(function () {
            this.submitting = false;
          }.bind(this));
      };

      /**
       * This should be called whenever a date has been changed
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       * @param {Date} date - the selected date
       * @param {String} dayType - set to from if from date is selected else to
       * @return {Promise}
       */
      this.updateAbsencePeriodDatesTypes = function (date, dayType) {
        var self = this;
        var oldPeriodId = self.period.id;
        dayType = dayType || 'from';
        self.loading[dayType + 'DayTypes'] = true;

        return self._checkAndSetAbsencePeriod(date)
          .then(function () {
            var isInCurrentPeriod = oldPeriodId === self.period.id;

            if (!isInCurrentPeriod) {
              // partial reset is required when user has selected a to date and
              // then changes absence period from from date
              // no reset required for single days and to date changes
              if (self.uiOptions.multipleDays && dayType === 'from') {
                self.uiOptions.showBalance = false;
                self.uiOptions.toDate = null;
                self.request.to_date = null;
                self.request.to_date_type = null;
              }

              return $q.all([
                self._loadAbsenceTypes(),
                self._loadCalendar()
              ]);
            }
          })
          .then(function () {
            self._setMinMaxDate();

            return filterLeaveRequestDayTypes.call(self, date, dayType);
          })
          .then(function () {
            return self.updateBalance();
          })
          .catch(function (error) {
            self.errors = [error];

            self._setDateAndTypes();
          })
          .finally(function () {
            self.loading[dayType + 'DayTypes'] = false;
          });
      };

      /**
       * Whenever the absence type changes, update the balance opening.
       * Also the balance change needs to be recalculated, if the `from` and `to`
       * dates have been already selected
       */
      this.updateBalance = function () {
        this.selectedAbsenceType = getSelectedAbsenceType.call(this);
        // get the `balance` of the newly selected absence type
        this.balance.opening = this.selectedAbsenceType.remainder;

        this.calculateBalanceChange();
      };

      /**
       * Calculates and updates opening and closing balances
       */
      this._calculateOpeningAndClosingBalance = function () {
        this.balance.opening = this.selectedAbsenceType.remainder;
        // the change is negative so adding it will actually subtract it
        this.balance.closing = this.balance.opening + this.balance.change.amount;
      };

      /**
       * Finds if date is in any absence period and sets absence period for the given date
       *
       * @param {Date/String} date
       * @return {Promise} with true value if period found else rejected false
       */
      this._checkAndSetAbsencePeriod = function (date) {
        var formattedDate = moment(date).format(this.uiOptions.userDateFormat.toUpperCase());

        this.period = _.find(this.absencePeriods, function (period) {
          return period.isInPeriod(formattedDate);
        });

        if (!this.period) {
          this.period = {};
          // inform user if absence period is not found
          this.loading['fromDayTypes'] = false;
          return $q.reject('Please change date as it is not in any absence period');
        }

        return $q.resolve(true);
      };

      /**
       * Converts given date to server format
       *
       * @param {Date} date
       * @return {String} date converted to server format
       */
      this._convertDateToServerFormat = function (date) {
        return moment(date).format(sharedSettings.serverDateFormat);
      };

      /**
       * Converts given date to javascript date as expected by uib-datepicker
       *
       * @param {String} date from server
       * @return {Date}
       */
      this._convertDateFormatFromServer = function (date) {
        return moment(date, sharedSettings.serverDateFormat).toDate();
      };

      /**
       * Initializes the controller on loading the dialog
       *
       * @return {Promise}
       */
      this._init = function () {
        this.supportedFileTypes = _.keys(sharedSettings.fileUploader.allowedMimeTypes);
        initAvailableStatusesMatrix.call(this);

        return initRoles.call(this)
          .then(function () {
            return this._initRequest();
          }.bind(this))
          .then(function () {
            return loadStatuses.call(this);
          }.bind(this))
          .then(function () {
            initOpenMode.call(this);

            return this.canManage && !this.isMode('edit') && loadManagees.call(this);
          }.bind(this))
          .then(function () {
            return loadAbsencePeriods.call(this);
          }.bind(this))
          .then(function () {
            initAbsencePeriod.call(this);
            this._setMinMaxDate();

            return this.request.loadAttachments();
          }.bind(this))
          .then(function () {
            if (this.directiveOptions.selectedContactId) {
              this.request.contact_id = this.directiveOptions.selectedContactId;
            }
            // The additional check here prevents error being displayed on startup when no contact is selected
            if (this.request.contact_id) {
              return this.initAfterContactSelection();
            }
          }.bind(this))
          .catch(handleError.bind(this));
      };

      /**
       * Initialize request attributes based on directive
       *
       * @return {Object} attributes
       */
      this._initRequestAttributes = function () {
        var attributes = {};

        // if set indicates self leaverequest is either being managed or edited
        if (this.directiveOptions.leaveRequest) {
          // _.deepClone or angular.copy were not uploading files correctly
          attributes = this.directiveOptions.leaveRequest.attributes();
        } else if (!this.canManage) {
          attributes = { contact_id: this.directiveOptions.contactId };
        }

        return attributes;
      };

      /**
       * Initializes user's calendar (work patterns)
       *
       * @return {Promise}
       */
      this._loadCalendar = function () {
        var self = this;

        return Calendar.get(self.request.contact_id, self.period.id)
          .then(function (usersCalendar) {
            self.calendar = usersCalendar;
          });
      };

      /**
       * Initializes values for absence types and entitlements when the
       * leave request popup model is displayed
       *
       * @return {Promise}
       */
      this._loadAbsenceTypes = function () {
        var self = this;

        return AbsenceType.all(self.initParams.absenceType)
          .then(function (absenceTypes) {
            var absenceTypesIds = absenceTypes.map(function (absenceType) {
              return absenceType.id;
            });

            absenceTypesAndIds = {
              types: absenceTypes,
              ids: absenceTypesIds
            };

            return setAbsenceTypesFromEntitlements.call(self, absenceTypesAndIds);
          });
      };

      /**
       * Resets data in dates, types, balance.
       */
      this._reset = function () {
        this.uiOptions.toDate = this.uiOptions.fromDate;
        this.request.to_date_type = this.request.from_date_type;
        this.request.to_date = this.request.from_date;

        this.calculateBalanceChange();
      };

      /**
       * Sets dates and types for this.request from UI
       */
      this._setDates = function () {
        this.request.from_date = this.uiOptions.fromDate ? this._convertDateToServerFormat(this.uiOptions.fromDate) : null;
        this.request.to_date = this.uiOptions.toDate ? this._convertDateToServerFormat(this.uiOptions.toDate) : null;

        if (!this.uiOptions.multipleDays && this.uiOptions.fromDate) {
          this.uiOptions.toDate = this.uiOptions.fromDate;
          this.request.to_date = this.request.from_date;
        }
      };

      /**
       * Sets dates and types for this.request from UI
       */
      this._setDateAndTypes = function () {
        this._setDates();

        if (this.uiOptions.multipleDays) {
          this.uiOptions.showBalance = !!this.request.from_date && !!this.request.from_date_type &&
            !!this.request.to_date && !!this.request.to_date_type && !!this.period.id;
        } else {
          if (this.uiOptions.fromDate) {
            this.request.to_date_type = this.request.from_date_type;
          }

          this.uiOptions.showBalance = !!this.request.from_date && !!this.request.from_date_type && !!this.period.id;
        }
      };

      /**
       * Sets the min and max for to date from absence period. It also sets the
       * init/starting date which user can select from. For multiple days request
       * user can select to date which is one more than the the start date.
       */
      this._setMinMaxDate = function () {
        if (this.uiOptions.fromDate) {
          var nextFromDay = moment(this.uiOptions.fromDate).add(1, 'd').toDate();

          this.uiOptions.date.to.options.minDate = nextFromDay;
          this.uiOptions.date.to.options.initDate = nextFromDay;

          // also re-set to date if from date is changing and less than to date
          if (this.uiOptions.toDate && moment(this.uiOptions.toDate).isBefore(this.uiOptions.fromDate)) {
            this.uiOptions.toDate = this.uiOptions.fromDate;
          }
        } else {
          this.uiOptions.date.to.options.minDate = this._convertDateFormatFromServer(this.period.start_date);
          this.uiOptions.date.to.options.initDate = this.uiOptions.date.to.options.minDate;
        }

        this.uiOptions.date.to.options.maxDate = this._convertDateFormatFromServer(this.period.end_date);
      };

      /**
       * Checks if all params are set to calculate balance
       *
       * @param {Boolean} true if all present else false
       */
      function canCalculateChange () {
        return !!this.request.from_date && !!this.request.to_date &&
          !!this.request.from_date_type && !!this.request.to_date_type;
      }

      /**
       * Changes status of the leave request before saving it
       * When recording for yourself the status_id should be always set to awaitingApproval before saving
       * If manager or admin have changed the status through dropdown, assign the same before calling API
       */
      function changeStatusBeforeSave () {
        if (this.isSelfRecord) {
          this.request.status_id = this.requestStatuses[sharedSettings.statusNames.awaitingApproval].value;
        } else if (this.canManage) {
          this.request.status_id = this.newStatusOnSave || this.request.status_id;
        }
      }

      /**
       * Validates and creates the leave request
       *
       * @returns {Promise}
       */
      function createRequest () {
        return this.request.create()
          .then(function () {
            postSubmit.call(this, 'LeaveRequest::new');
          }.bind(this));
      }

      /**
       * This method will be used on the view to return a list of available
       * leave request day types (All day, Half-day AM, Half-day PM, Non working day,
       * Weekend, Public holiday) for the given date (which is the date
       * selected by the user via datepicker)
       *
       * If no date is passed, then no list is returned
       *
       * @param  {Date} date
       * @param  {String} dayType - set to from if from date is selected else to
       * @return {Promise} of array with day types
       */
      function filterLeaveRequestDayTypes (date, dayType) {
        var inCalendarList, listToReturn;
        var deferred = $q.defer();

        if (!date) {
          deferred.reject([]);
        }

        // Make a copy of the list
        listToReturn = this.requestDayTypes.slice(0);

        date = this._convertDateToServerFormat(date);
        PublicHoliday.isPublicHoliday(date)
          .then(function (result) {
            if (result) {
              listToReturn = listToReturn.filter(function (publicHoliday) {
                return publicHoliday.name === 'public_holiday';
              });
            } else {
              inCalendarList = getDayTypesFromDate.call(this, date, listToReturn);

              if (!inCalendarList.length) {
                // 'All day', 'Half-day AM', and 'Half-day PM' options
                listToReturn = listToReturn.filter(function (dayType) {
                  return dayType.name === 'all_day' || dayType.name === 'half_day_am' || dayType.name === 'half_day_pm';
                });
              } else {
                listToReturn = inCalendarList;
              }
            }

            setDayType.call(this, dayType, listToReturn);
            deferred.resolve(listToReturn);
          }.bind(this));

        return deferred.promise;
      }

      /**
       * Helper functions to get available statuses depending on the
       * current request status value.
       *
       * @return {Array}
       */
      function getAvailableStatusesForCurrentStatus () {
        var currentStatus = this.getStatusFromValue(this.request.status_id);

        return getAvailableStatusesForStatusName.call(this, currentStatus.name);
      }

      /**
       * Helper function that returns an array of the statuses available
       * for a specific status name.
       *
       * @return {Array}
       */
      function getAvailableStatusesForStatusName (statusName) {
        return _.map(availableStatusesMatrix[statusName], function (status) {
          return this.requestStatuses[status];
        }.bind(this));
      }

      /**
       * Helper function to obtain params for leave request calculateBalanceChange api call
       *
       * @return {Object} containing required keys for leave request
       */
      function getParamsForBalanceChange () {
        return _.pick(this.request, ['contact_id', 'from_date',
          'from_date_type', 'to_date', 'to_date_type'
        ]);
      }

      /**
       * Gets list of day types if its found to be weekend or non working in calendar
       *
       * @param {Date} date to Checks
       * @param {Array} listOfDayTypes array of day types
       * @return {Array} non-empty if found else empty array
       */
      function getDayTypesFromDate (date, listOfDayTypes) {
        var nameFilter = null;

        if (this.calendar.isNonWorkingDay(moment(date))) {
          nameFilter = 'non_working_day';
        } else if (this.calendar.isWeekend(moment(date))) {
          nameFilter = 'weekend';
        }

        return !nameFilter ? [] : listOfDayTypes.filter(function (day) {
          return day.name === nameFilter;
        });
      }

      /**
       * Gets currently selected absence type from leave request type_id
       *
       * @return {Object} absence type object
       */
      function getSelectedAbsenceType () {
        return _.find(this.absenceTypes, function (absenceType) {
          return absenceType.id === this.request.type_id;
        }.bind(this));
      }

      function handleError (errors) {
        // show errors
        this.errors = _.isArray(errors) ? errors : [errors];

        // reset loading Checks
        this.loading.showBalanceChange = false;
        this.loading.absenceTypes = false;
        this.loading.fromDayTypes = false;
        this.loading.toDayTypes = false;

        this.submitting = false;
      }

      /**
       * Checks if a leave request has been changed since opening the modal
       *
       * FileUploader property deleted because it will not be used
       * in object comparison
       *
       * @return {Boolean}
       */
      function hasRequestChanged () {
        // using angular.equals to automatically ignore the $$hashkey property
        return !angular.equals(
          _.omit(initialLeaveRequestAttributes, 'fileUploader'),
          _.omit(this.request.attributes(), 'fileUploader')
        ) || this.request.fileUploader.queue.length !== 0 ||
          (this.canManage && this.newStatusOnSave);
      }

      /**
       * Initialize available statuses matrix
       */
      function initAvailableStatusesMatrix () {
        var defaultStatuses = [
          sharedSettings.statusNames.moreInformationRequired,
          sharedSettings.statusNames.approved,
          sharedSettings.statusNames.rejected,
          sharedSettings.statusNames.cancelled
        ];

        availableStatusesMatrix['none'] = [
          sharedSettings.statusNames.moreInformationRequired,
          sharedSettings.statusNames.approved
        ];
        availableStatusesMatrix['awaiting_approval'] = defaultStatuses;
        availableStatusesMatrix['more_information_required'] = defaultStatuses;
        availableStatusesMatrix['rejected'] = defaultStatuses;
        availableStatusesMatrix['approved'] = defaultStatuses;
        availableStatusesMatrix['cancelled'] = [
          sharedSettings.statusNames.awaitingApproval
        ].concat(defaultStatuses);
      }

      /**
       * Initialize open mode of the dialog
       */
      function initOpenMode () {
        if (this.request.id) {
          this.mode = 'edit';

          var viewModeStatuses = [
            this.requestStatuses[sharedSettings.statusNames.approved].value,
            this.requestStatuses[sharedSettings.statusNames.adminApproved].value,
            this.requestStatuses[sharedSettings.statusNames.rejected].value,
            this.requestStatuses[sharedSettings.statusNames.cancelled].value
          ];

          if (this.isRole('staff') && viewModeStatuses.indexOf(this.request.status_id) > -1) {
            this.mode = 'view';
          }
        } else {
          this.mode = 'create';
        }
      }

      /**
       * Inits absence period for the current date
       */
      function initAbsencePeriod () {
        this.period = _.find(this.absencePeriods, function (period) {
          return period.current;
        });
      }

      /**
       * Initialize from and to dates and day types.
       * It will also set the day types.
       *
       * @return {Promise}
       */
      function initDates () {
        if (!this.isMode('create')) {
          var attributes = this.request.attributes();

          this.uiOptions.fromDate = this._convertDateFormatFromServer(this.request.from_date);

          return this.updateAbsencePeriodDatesTypes(this.uiOptions.fromDate, 'from')
            .then(function () {
              // to_date and type has been reset in above call so reinitialize from clone
              this.request.to_date = attributes.to_date;
              this.request.to_date_type = attributes.to_date_type;
              this.uiOptions.toDate = this._convertDateFormatFromServer(this.request.to_date);
              return this.updateAbsencePeriodDatesTypes(this.uiOptions.toDate, 'to');
            }.bind(this));
        } else {
          return $q.resolve();
        }
      }

      /**
       * Initialize roles
       */
      function initRoles () {
        role = 'staff';

        return checkPermissions(sharedSettings.permissions.admin.administer)
        .then(function (isAdmin) {
          role = isAdmin ? 'admin' : role;
        })
        .then(function () {
          // (role === 'staff') means it is not admin so need to check if manager
          return (role === 'staff') && checkPermissions(sharedSettings.permissions.ssp.manage)
          .then(function (isManager) {
            role = isManager ? 'manager' : role;
          });
        })
        .finally(function () {
          this.canManage = this.isRole('manager') || this.isRole('admin');
          this.isSelfRecord = this.directiveOptions.isSelfRecord;
        }.bind(this));
      }

      /**
       * Initialize status
       */
      function initStatus () {
        if (this.isRole('admin') || (this.isMode('create') && this.isRole('manager'))) {
          this.newStatusOnSave = this.requestStatuses[sharedSettings.statusNames.approved].value;
        }
      }

      /**
       * Initialize contact
       *
       * {Promise}
       */
      function initContact () {
        if (this.canManage) {
          return Contact.find(this.request.contact_id)
            .then(function (contact) {
              this.contactName = contact.display_name;
            }.bind(this));
        }

        return $q.resolve();
      }

      /**
       * Loads the managees of currently logged in user
       *
       * @return {Promise}
       */
      function loadManagees () {
        if (this.directiveOptions.selectedContactId) {
          // When in absence tab, because "loadManagees" is called only in manager mode,
          // and selectedContactId is not set for Manager Leave in SSP
          return Contact.find(this.directiveOptions.selectedContactId)
            .then(function (contact) {
              this.managedContacts = [contact];
            }.bind(this));
        } else if (this.isRole('admin')) {
          // When in Admin Dashboard
          return Contact.all()
            .then(function (contacts) {
              this.managedContacts = _.remove(contacts.list, function (contact) {
                // Removes the admin from the list of managees
                return contact.id !== this.directiveOptions.contactId;
              }.bind(this));
            }.bind(this));
        } else {
          // Everywhere else
          return Contact.find(this.directiveOptions.contactId)
            .then(function (contact) {
              return contact.leaveManagees();
            })
            .then(function (contacts) {
              this.managedContacts = contacts;
            }.bind(this));
        }
      }

      /**
       * Loads all absence periods
       */
      function loadAbsencePeriods () {
        var self = this;

        return AbsencePeriod.all()
          .then(function (periods) {
            self.absencePeriods = periods;
          });
      }

      /**
       * Initializes leave request day types
       *
       * @return {Promise}
       */
      function loadDayTypes () {
        var self = this;

        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
          .then(function (dayTypes) {
            self.requestDayTypes = dayTypes;
          });
      }

      /**
       * Initializes leave request statuses
       *
       * @return {Promise}
       */
      function loadStatuses () {
        var self = this;

        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (statuses) {
            self.requestStatuses = _.indexBy(statuses, 'name');
          });
      }

      /**
       * Maps absence types to be more compatible for UI selection
       *
       * @param {Array} absenceTypes
       * @param {Object} entitlements
       * @return {Array} of filtered absence types for given entitlements
       */
      function mapAbsenceTypesWithBalance (absenceTypes, entitlements) {
        return entitlements.map(function (entitlementItem) {
          var absenceType = _.find(absenceTypes, function (absenceTypeItem) {
            return absenceTypeItem.id === entitlementItem.type_id;
          });

          return {
            id: entitlementItem.type_id,
            title: absenceType.title + ' ( ' + entitlementItem.remainder.current + ' ) ',
            remainder: entitlementItem.remainder.current,
            allow_overuse: absenceType.allow_overuse
          };
        });
      }

      /**
       * Called after successful submission of leave request
       *
       * @param {String} eventName name of the event to emit
       */
      function postSubmit (eventName) {
        $rootScope.$emit(eventName, this.request);
        this.errors = [];
        // close the modal
        this.ok();
      }

      /**
       * Helper function to reset pagination for balance breakdow
       */
      function rePaginate () {
        this.pagination.totalItems = this.balance.change.breakdown.length;
        this.pagination.filteredbreakdown = this.balance.change.breakdown;
        this.pagination.pageChanged();
      }

      /**
       * Set initial values to absence types when opening the popup
       */
      function setInitialAbsenceTypes () {
        if (this.isMode('create')) {
          // Assign the first absence type to the leave request
          this.selectedAbsenceType = this.absenceTypes[0];
          this.request.type_id = this.selectedAbsenceType.id;
        } else {
          // Either View or Edit Mode
          this.selectedAbsenceType = getSelectedAbsenceType.call(this);
        }
      }

      /**
       * Sets entitlements and sets the absences type available for the user.
       * It depends on absenceTypesAndIds to be set to list of absence types and ids
       *
       * @param {Object} absenceTypesAndIds contains all absencetypes and their ids
       * @return {Promise}
       */
      function setAbsenceTypesFromEntitlements (absenceTypesAndIds) {
        var self = this;

        return Entitlement.all({
          contact_id: self.request.contact_id,
          period_id: self.period.id,
          type_id: { IN: absenceTypesAndIds.ids }
        }, true) // `true` because we want to use the 'future' balance for calculation
          .then(function (entitlements) {
            // create a list of absence types with a `balance` property
            self.absenceTypes = mapAbsenceTypesWithBalance(absenceTypesAndIds.types, entitlements);
            if (!self.absenceTypes.length) {
              return $q.reject(NO_ENTITLEMENT_ERROR);
            }
          });
      }

      /**
       * Sets the collection for given day types to sent list of day types,
       * also initializes the day types
       *
       * @param {String} dayType like `from` or `to`
       * @param {Array} listOfDayTypes collection of available day types
       */
      function setDayType (dayType, listOfDayTypes) {
        // will create either of leaveRequestFromDayTypes or leaveRequestToDayTypes key
        var keyForDayTypeCollection = 'request' + _.startCase(dayType) + 'DayTypes';

        this[keyForDayTypeCollection] = listOfDayTypes;

        if (this.isMode('create')) {
          this.request[dayType + '_date_type'] = this[keyForDayTypeCollection][0].value;
        }
      }

      /**
       * Validates and updates the leave request
       *
       * @returns {Promise}
       */
      function updateRequest () {
        return this.request.update()
          .then(function () {
            if (this.isRole('manager')) {
              postSubmit.call(this, 'LeaveRequest::updatedByManager');
            } else if (this.isRole('staff') || this.isRole('admin')) {
              postSubmit.call(this, 'LeaveRequest::edit');
            }
          }.bind(this));
      }

      /**
       * Validates a Leave request before submitting
       *
       * @returns {Promise}
       */
      function validateBeforeSubmit () {
        if (this.balance.closing < 0 && this.selectedAbsenceType.allow_overuse === '0') {
          // show an error
          return $q.reject(['You are not allowed to apply leave in negative']);
        }

        return this.request.isValid();
      }

      return this;
    }
  ]);
});

/* eslint-env amd */
define('leave-absences/shared/controllers/sub-controllers/leave-request-ctrl',[
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request-ctrl',
  'leave-absences/shared/models/instances/leave-request-instance'
], function (controllers) {
  controllers.controller('LeaveRequestCtrl', [
    '$controller', '$log', '$uibModalInstance', 'directiveOptions', 'LeaveRequestInstance',
    function ($controller, $log, $modalInstance, directiveOptions, LeaveRequestInstance) {
      $log.debug('LeaveRequestCtrl');

      var parentRequestCtrl = $controller('RequestCtrl');
      var vm = Object.create(parentRequestCtrl);

      vm.directiveOptions = directiveOptions;
      vm.$modalInstance = $modalInstance;
      vm.initParams = {
        absenceType: {
          is_sick: false
        }
      };

      /**
       * Initialize leaverequest based on attributes that come from directive
       */
      vm._initRequest = function () {
        var attributes = vm._initRequestAttributes();

        vm.request = LeaveRequestInstance.init(attributes);
      };

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController () {
        vm.loading.absenceTypes = true;

        vm._init()
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();

      return vm;
    }
  ]);
});

define('leave-absences/shared/models/instances/sickness-request-instance',[
  'common/lodash',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (_, modelInstances) {
  'use strict';

  modelInstances.factory('SicknessRequestInstance', [
    'LeaveRequestAPI',
    'LeaveRequestInstance',
    function (LeaveRequestAPI, LeaveRequestInstance) {

      return LeaveRequestInstance.extend({

        /**
         * Returns the default custom data
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          var sicknessCustomData = {
            sickness_reason: null,
            sickness_required_documents: '',
            request_type: 'sickness'
          };

          return _.assign({}, LeaveRequestInstance.defaultCustomData(), sicknessCustomData);
        },

        /**
         * Gets array of documents from comma separated string of documents
         *
         * @return {Array}
         */
        getDocumentArray: function () {
          var docsArray = this.sickness_required_documents ? this.sickness_required_documents.split(',') : [];

          return docsArray;
        },

        /**
         * Checks if given value is added for leave request list of document value ie., field required_documents
         *  otherwise add it to list of required documents (list is actually string of comma separated values for now)
         *
         * @param {String} documentValue required document value like '1'
         */
        toggleDocument: function (documentValue) {
          var docsArray = this.getDocumentArray();
          var index = docsArray.indexOf(documentValue);

          _.contains(docsArray, documentValue) ? docsArray.splice(index, 1) : docsArray.push(documentValue);
          this.sickness_required_documents = docsArray.join(',');
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['balance_change', 'dates', 'comments', 'fileUploader', 'files'], key)) {
            result[key] = this[key];
          }
        }
      });
    }
  ]);
});

define('leave-absences/shared/controllers/sub-controllers/sick-request-ctrl',[
  'common/lodash',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request-ctrl',
  'leave-absences/shared/models/instances/sickness-request-instance',
], function (_, controllers) {
  controllers.controller('SicknessRequestCtrl', [
    '$controller', '$log', '$q', '$uibModalInstance', 'api.optionGroup', 'directiveOptions', 'SicknessRequestInstance',
    function ($controller, $log, $q, $modalInstance, OptionGroup, directiveOptions, SicknessRequestInstance) {
      $log.debug('SicknessRequestCtrl');

      var parentRequestCtrl = $controller('RequestCtrl'),
        vm = Object.create(parentRequestCtrl);

      vm.directiveOptions = directiveOptions;
      vm.$modalInstance = $modalInstance;
      vm.initParams = {
        absenceType: {
          is_sick: true
        }
      };

      /**
       * Checks if submit button can be enabled for user and returns true if successful
       *
       * @return {Boolean}
       */
      vm.canSubmit = function () {
        return parentRequestCtrl.canSubmit.call(this) && !!vm.request.sickness_reason;
      };

      /**
       * Checks if given value is set for leave request list of document value ie., field sickness_required_documents
       *
       * @param {String} value
       * @return {Boolean}
       */
      vm.isDocumentInRequest = function (value) {
        return !!_.find(vm.sicknessDocumentTypes, function (document) {
          return document.value == value;
        });
      };

      /**
       * During initialization it will check if given value is set for leave request list
       * of document value ie., field sickness_required_documents in existing leave request
       *
       * @param {String} value
       * @return {Boolean}
       */
      vm.isChecked = function (value) {
        var docsArray = vm.request.getDocumentArray();

        return !!_.find(docsArray, function (document) {
          return document == value;
        });
      };

      /**
       * Initialize leaverequest based on attributes that come from directive
       */
      vm._initRequest = function () {
        var attributes = vm._initRequestAttributes();

        vm.request = SicknessRequestInstance.init(attributes);
      };

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController() {
        vm.loading.absenceTypes = true;

        vm._init()
          .then(function () {
            return $q.all([
              loadDocuments(),
              loadReasons()
            ]);
          })
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();

      /**
       * Initializes leave request documents types required for submission
       *
       * @return {Promise}
       */
      function loadDocuments() {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_required_document')
          .then(function (documentTypes) {
            vm.sicknessDocumentTypes = documentTypes;
          });
      }

      /**
       * Initializes leave request reasons and indexes them by name like accident etc.,
       *
       * @return {Promise}
       */
      function loadReasons() {
        return OptionGroup.valuesOf('hrleaveandabsences_sickness_reason')
          .then(function (reasons) {
            vm.sicknessReasons = _.indexBy(reasons, 'name');
          });
      }

      return vm;
    }
  ]);
});

define('leave-absences/shared/models/instances/toil-request-instance',[
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (modelInstances) {
  'use strict';

  modelInstances.factory('TOILRequestInstance', [
    'LeaveRequestAPI',
    'LeaveRequestInstance',
    function (LeaveRequestAPI, LeaveRequestInstance) {
      return LeaveRequestInstance.extend({

        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          var toilCustomData = {
            toilDurationHours: '0',
            toilDurationMinutes: '0',
            request_type: 'toil'
          };

          return _.assign({}, LeaveRequestInstance.defaultCustomData(), toilCustomData);
        },

        /**
         * Sets the duration hours and minutes from toil_duration on instantiation.
         *
         * @param {Object} attributes that need to be transformed
         * @return {Object} updated attributes object
         */
        transformAttributes: function (attributes) {
          var duration = Number(attributes.toil_duration);
          if (duration) {
            attributes.toilDurationHours = Math.floor(duration / 60).toString();
            attributes.toilDurationMinutes = (duration % 60).toString();
          }

          return attributes;
        },

        /**
         * Update duration
         */
        updateDuration: function () {
          this.toil_duration = Number(this.toilDurationHours) * 60 + Number(this.toilDurationMinutes);
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['balance_change', 'dates', 'comments', 'fileUploader', 'files', 'toilDurationHours', 'toilDurationMinutes'], key)) {
            result[key] = this[key];
          }
        }
      });
    }
  ]);
});

/* eslint-env amd, jasmine */

define('leave-absences/shared/controllers/sub-controllers/toil-request-ctrl',[
  'common/lodash',
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request-ctrl',
  'leave-absences/shared/models/instances/toil-request-instance'
], function (_, controllers) {
  controllers.controller('ToilRequestCtrl', [
    '$controller', '$log', '$q', '$uibModalInstance', 'api.optionGroup', 'AbsenceType', 'directiveOptions', 'TOILRequestInstance',
    function ($controller, $log, $q, $modalInstance, OptionGroup, AbsenceType, directiveOptions, TOILRequestInstance) {
      $log.debug('ToilRequestCtrl');

      var parentRequestCtrl = $controller('RequestCtrl');
      var vm = Object.create(parentRequestCtrl);

      vm.directiveOptions = directiveOptions;
      vm.$modalInstance = $modalInstance;
      vm.initParams = {
        absenceType: {
          allow_accruals_request: true
        }
      };

      /**
       * Calculate change in balance, it updates balance variables.
       * It overrides the parent's implementation
       *
       * @return {Promise} empty promise if all required params are not set otherwise promise from server
       */
      vm.calculateBalanceChange = function () {
        if (vm.request.toil_to_accrue) {
          vm.loading.showBalanceChange = true;
          vm._setDateAndTypes();
          vm.balance.change.amount = +vm.request.toil_to_accrue;
          vm._calculateOpeningAndClosingBalance();
          vm.uiOptions.showBalance = true;
          vm.request.to_date_type = vm.request.from_date_type = '1';
          vm.loading.showBalanceChange = false;
        }
      };

      /**
       * Calculates toil expiry date.
       *
       * @return {Promise}
       */
      vm.calculateToilExpiryDate = function () {
        // blocks the expiry date from updating if this is an existing request
        // and user is not a manager or admin.
        if (!vm.canManage && vm.request.id) {
          return $q.resolve(vm.request.toil_expiry_date);
        }

        return getReferenceDate().catch(function (errors) {
          if (errors.length) vm.errors = errors;
          return $q.reject(errors);
        }).then(function (referenceDate) {
          return AbsenceType.calculateToilExpiryDate(
            vm.request.type_id,
            referenceDate
          );
        })
        .then(function (expiryDate) {
          vm.request.toil_expiry_date = expiryDate;
          return expiryDate;
        });
      };

      /**
       * Checks if submit button can be enabled for user and returns true if successful
       *
       * @return {Boolean}
       */
      vm.canSubmit = function () {
        return !!vm.request.toil_duration && !!vm.request.toil_to_accrue &&
          !!vm.request.from_date && !!vm.request.to_date;
      };

      /**
       * Extends parent method. Fires calculation of expiry date when the
       * number of days changes and the expiry date can be calculated.
       */
      vm.changeInNoOfDays = function () {
        parentRequestCtrl.changeInNoOfDays.call(this);

        if (canCalculateExpiryDate()) {
          vm.calculateToilExpiryDate();
        }
      };

      /**
       * This should be called whenever a date has been changed
       * First it syncs `from` and `to` date, if it's in 'single day' mode
       * Then, if all the dates are there, it gets the balance change
       *
       * @param {Date} date - the selected date
       * @return {Promise}
       */
      vm.updateAbsencePeriodDatesTypes = function (date) {
        var oldPeriodId = vm.period.id;

        return vm._checkAndSetAbsencePeriod(date)
          .then(function () {
            var isInCurrentPeriod = oldPeriodId === vm.period.id;

            if (!isInCurrentPeriod) {
              if (vm.uiOptions.multipleDays) {
                vm.uiOptions.showBalance = false;
                vm.uiOptions.toDate = null;
                vm.request.to_date = null;
              }

              return $q.all([
                vm._loadAbsenceTypes(),
                vm._loadCalendar()
              ]);
            }
          })
          .then(function () {
            vm._setMinMaxDate();
            vm._setDates();
            vm.calculateToilExpiryDate();
            vm.updateBalance();
          })
          .catch(function (error) {
            vm.errors = [error];
          });
      };

      /**
       * Updates expiry date when user changes it on ui
       */
      vm.updateExpiryDate = function () {
        if (vm.uiOptions.expiryDate) {
          vm.request.toil_expiry_date = vm._convertDateToServerFormat(vm.uiOptions.expiryDate);
        }
      };

      /**
       * Initialize leaverequest based on attributes that come from directive
       */
      vm._initRequest = function () {
        var attributes = vm._initRequestAttributes();

        vm.request = TOILRequestInstance.init(attributes);
        // toil request does not have date type but leave request requires it for validation, hence setting it to All Day's value which is 1
        vm.request.to_date_type = vm.request.from_date_type = '1';
      };

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController () {
        vm.loading.absenceTypes = true;

        vm._init()
          .then(function () {
            initExpiryDate();

            return loadToilAmounts();
          })
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();

      /**
       * Determines if the expiry date can be calculated based on the
       * Number Of Days selected and the corresponding date field has value.
       *
       * @return {Boolean}
       */
      function canCalculateExpiryDate () {
        return (vm.uiOptions.multipleDays && vm.request.to_date) ||
          (!vm.uiOptions.multipleDays && vm.request.from_date);
      }

      /**
       * Returns a promise with a date that can be used to calculate the expiry
       * date. This date depends on the Multiple Days or Single Day options.
       *
       * @return {Promise}
       */
      function getReferenceDate () {
        if (vm.uiOptions.multipleDays) {
          return getReferenceDateForField({
            hasErrors: !vm.request.to_date && !vm.request.from_date,
            label: 'To Date',
            value: vm.request.to_date
          });
        } else {
          return getReferenceDateForField({
            hasErrors: !vm.request.from_date,
            label: 'From Date',
            value: vm.request.from_date
          });
        }
      }

      /**
       * Returns a reference date using the field object as source.
       * If the field has errors, it returns an error message.
       * If the field has no value, it returns an empty message since it still
       * is in the process of inserting values.
       * And if everything is ok it returns the field's date value.
       *
       * @return {Promise}
       */
      function getReferenceDateForField (field) {
        if (field.hasErrors) {
          var message = 'Please select ' + field.label + ' to find expiry date';
          return $q.reject([message]);
        }

        if (!field.value) {
          return $q.reject([]);
        } else {
          return $q.resolve(field.value);
        }
      }

      /**
       * Initialize expiryDate on UI from server's toil_expiry_date
       */
      function initExpiryDate () {
        if (vm.canManage) {
          vm.uiOptions.expiryDate = vm._convertDateFormatFromServer(vm.request.toil_expiry_date);
        }
      }

      /**
       * Initializes leave request toil amounts
       *
       * @return {Promise}
       */
      function loadToilAmounts () {
        return OptionGroup.valuesOf('hrleaveandabsences_toil_amounts')
          .then(function (amounts) {
            vm.toilAmounts = _.indexBy(amounts, 'value');
          });
      }

      return vm;
    }
  ]);
});

/* eslint-env amd */

define('leave-absences/shared/directives/leave-request-popup',[
  'common/lodash',
  'leave-absences/shared/modules/directives',
  'leave-absences/shared/controllers/sub-controllers/leave-request-ctrl',
  'leave-absences/shared/controllers/sub-controllers/sick-request-ctrl',
  'leave-absences/shared/controllers/sub-controllers/toil-request-ctrl'
], function (_, directives) {
  'use strict';

  directives.directive('leaveRequestPopup', ['$log', '$rootElement', '$uibModal', 'shared-settings', 'DateFormat',
    function ($log, $rootElement, $modal, settings, DateFormat) {
      $log.debug('leaveRequestPopup');

      /**
       * Gets leave type.
       * If leaveTypeParam exits then its a new request, else if request
       * object exists then its edit request call
       *
       * @param {String} leaveTypeParam
       * @param {Object} request leave request for edit calls
       * @return {String} leave type
       */
      function getLeaveType (leaveTypeParam, request) {
        // reset for edit calls
        if (request) {
          return request.request_type;
        } else if (leaveTypeParam) {
          return leaveTypeParam;
        }
      }

      return {
        scope: {
          contactId: '<',
          leaveRequest: '<',
          leaveType: '@',
          selectedContactId: '<',
          isSelfRecord: '<'
        },
        restrict: 'EA',
        link: function (scope, element) {
          var controller = _.capitalize(getLeaveType(scope.leaveType, scope.leaveRequest)) + 'RequestCtrl';

          element.on('click', function (event) {
            $modal.open({
              appendTo: $rootElement.children().eq(0),
              templateUrl: settings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup.html',
              animation: scope.animationsEnabled,
              controller: controller,
              controllerAs: '$ctrl',
              windowClass: 'chr_leave-request-modal',
              resolve: {
                directiveOptions: function () {
                  return {
                    contactId: scope.contactId,
                    leaveRequest: scope.leaveRequest,
                    selectedContactId: scope.selectedContactId,
                    isSelfRecord: scope.isSelfRecord
                  };
                },
                // to set HR_settings DateFormat
                format: ['DateFormat', function (DateFormat) {
                  // stores the data format in HR_setting.DATE_FORMAT
                  return DateFormat.getDateFormat();
                }]
              }
            });
          });
        }
      };
    }
  ]);
});

/* eslint-env amd */

define('leave-absences/my-leave/app',[
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/directives/loading',
  'common/models/option-group',
  'common/modules/dialog',
  'common/services/check-permissions',
  'common/services/angular-date/date-format',
  'leave-absences/shared/ui-router',
  'leave-absences/my-leave/modules/config',
  'leave-absences/my-leave/components/my-leave-container',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/components/staff-leave-report',
  'leave-absences/shared/components/staff-leave-calendar',
  'leave-absences/shared/components/leave-request-create-dropdown',
  'leave-absences/shared/components/leave-request-popup-comments-tab',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/public-holiday-model',
  'leave-absences/shared/modules/shared-settings'
], function (angular) {
  angular.module('my-leave', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.bootstrap',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.directives',
    'common.models',
    'common.services',
    'my-leave.config',
    'my-leave.components',
    'leave-absences.components',
    'leave-absences.directives',
    'leave-absences.models',
    'leave-absences.settings'
  ])
  .run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});

(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/my-leave': srcPath + '/my-leave'
    }
  });

  require(['leave-absences/shared/config'], function () {
    require([
      'leave-absences/my-leave/app'
    ],
    function (angular) {
      angular.bootstrap(document.querySelector('[data-leave-absences-my-leave]'), ['my-leave']);
    });
  });
})(CRM, require);

define("my-leave", function(){});

