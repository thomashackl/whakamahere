!function(e){var t={};function i(a){if(t[a])return t[a].exports;var n=t[a]={i:a,l:!1,exports:{}};return e[a].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.m=e,i.c=t,i.d=function(e,t,a){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(i.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(a,n,function(t){return e[t]}.bind(null,n));return a},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="",i(i.s=21)}({1:function(e,t,i){i.p=window.STUDIP.ABSOLUTE_URI_STUDIP+"plugins_packages/upa/WhakamaherePlugin/assets/"},12:function(e,t){
/*!
 * jQuery Timeline Plugin
 * ------------------------
 * Version: 1.0.5
 * Author: Ka2 ( https://ka2.org/ )
 * Repository: https://github.com/ka215/jquery.timeline
 * Lisenced: MIT
 */
!function(e){function t(t){var i,a,n,l=e(t),o=l.data("timeline");n=/-|\/|\s|\:/,a=o.timeline.attr("actual-start-datetime").split(n);var d=new Date(Number(a[0]),Number(a[1])-1,Number(a[2]),Number(a[3]),Number(a[4]),Number(a[5]));Number(a[0])<100&&d.setFullYear(Number(a[0]));var c,u,f=e("<div />",{addClass:"timeline-header"}),g=e("<div />",{addClass:"timeline-body"}),p=e("<div />",{addClass:"timeline-footer"}),y=e("<div />",{addClass:"timeline-wrapper"}),v=e("<table />",{addClass:"timeline-timetable timeline-scale"}),b=e("<div />",{addClass:"timeline-events"}),w=e("<table />",{addClass:"timeline-timetable timeline-grids"}),x=e("<div />",{addClass:"timeline-needle-pointer"}),M=e("<div />",{addClass:"timeline-events default-events"}),D=new Date(d),N={years:{medium_scale:"months",medium_cols:12,small_scale:"days",small_cols:Number(o.timeline.attr("min-grid-per"))},months:{medium_scale:"days",medium_cols:new Date(d.getFullYear(),d.getMonth()+1,0).getDate(),small_scale:"hours",small_cols:Number(o.timeline.attr("min-grid-per"))},days:{medium_scale:"hours",medium_cols:24,small_scale:"minutes",small_cols:Number(o.timeline.attr("min-grid-per"))}},k=o.timeline.attr("scale"),F=N[k].medium_scale,S=N[k].small_scale,T=Number(o.timeline.attr("min-grid-per"))*Number(o.timeline.attr("min-grid-size")),Y=[N[k].medium_cols];if(l.hasClass("timeline-container")||l.addClass("timeline-container"),l.find(".timeline-events").length>0&&(l.find(".timeline-events").children().clone().appendTo(M),function(t,i){if(e(t).children().length>0){var a=[],n=[],r=1;e(t).children().each(function(){if(e(this).data("timelineNode")){var t=new Function("return "+e(this).data("timelineNode"))();t.label=e(this).text(),t.eventId&&n.push(Number(t.eventId)),a.push(t)}}),a.length>0&&(r=n.length>0?Math.max.apply(null,n)+1:r,a.forEach(function(e,t,i){e.eventId||(i[t].eventId=r,r++)}),i.timeline.text(JSON.stringify(a)))}}(M,o)),"point"===o.timeline.attr("type")||"mixed"===o.timeline.attr("type"))var C=e("<canvas />",{addClass:"timeline-line-canvas"});if(l.empty(),"years"===o.timeline.attr("scale")?(D.setFullYear(D.getFullYear()+Number(o.timeline.attr("range"))),u=D.getTime(),D.setTime(u-1)):"months"===o.timeline.attr("scale")?(D.setMonth(D.getMonth()+Number(o.timeline.attr("range"))),u=D.getTime(),D.setTime(u-1)):(D.setDate(D.getDate()+Number(o.timeline.attr("range"))),u=D.getTime(),D.setTime(u-1)),"days"===F&&Number(o.timeline.attr("range"))>1)for(i=1;i<Number(o.timeline.attr("range"));i++)Y.push(new Date(d.getFullYear(),d.getMonth()+1+i,0).getDate());else for(i=1;i<Number(o.timeline.attr("range"));i++)Y.push(N[k].medium_cols);if(o.timeline.attr("show-headline")){var j,L,H,I,O;switch(o.timeline.attr("scale")){case"years":H=I="",1==o.timeline.attr("zerofill-year")&&(d.getFullYear()<100?H="00":d.getFullYear()<1e3&&(H="0"),D.getFullYear()<100?I="00":D.getFullYear()<1e3&&(I="0")),j=o.timeline.attr("datetime-prefix")+H+h(o.timeline.attr("datetime-format-year"),d),L=o.timeline.attr("datetime-prefix")+I+h(o.timeline.attr("datetime-format-year"),D);break;case"months":j=o.timeline.attr("datetime-prefix")+h(o.timeline.attr("datetime-format-month"),d),L=o.timeline.attr("datetime-prefix")+h(o.timeline.attr("datetime-format-month"),D);break;case"days":j=o.timeline.attr("datetime-prefix")+h(o.timeline.attr("datetime-format-day"),d),L=o.timeline.attr("datetime-prefix")+h(o.timeline.attr("datetime-format-day"),D)}O='<span class="timeline-from-date">'+j+'</span><span class="timeline-to-date">'+L+"</span>",f.append('<h3 class="timeline-headline">'+O+"</h3>")}var A,z,_,J,P="<tr>",E="<tr>",$="<tr>";for(c=m(Y)*N[k].small_cols,o.timeline.attr("total-cols",c),i=0;i<Number(o.timeline.attr("range"));i++){switch(P+='<th colspan="'+Y[i]*N[k].small_cols+'" class="scale-major scale-'+k+'">',A=new Date(d),k){case"years":A.setFullYear(A.getFullYear()+i),H="",1==o.timeline.attr("zerofill-year")&&(A.getFullYear()<100?H="00":A.getFullYear()<1e3&&(H="0")),_=H+h(o.timeline.attr("datetime-format-years"),A);break;case"months":A.setMonth(A.getMonth()+i),_=h(o.timeline.attr("datetime-format-months"),A);break;case"days":A.setDate(A.getDate()+i),_=h(o.timeline.attr("datetime-format-days"),A)}P+=_+"</th>"}for(P+="</tr>",i=0;i<m(Y);i++){switch(A=new Date(d),F){case"months":z=i%N[k].medium_cols,_=T<18?"":z+1,J=new Date(A.getFullYear(),A.getMonth()+i,1).getTime();break;case"days":A.setDate(A.getDate()+i),_=T<20?"":A.getDate(),J=A.getTime();break;case"hours":z=i%N[k].medium_cols,_=T<40?"":z+":00",J=A.setTime(A.getTime()+36e5*i)}E+='<th colspan="'+N[k].small_cols+'" class="scale-medium scale-'+F+'" data-cell-datetime="'+J+'">',E+=_+"</th>"}for(E+="</tr>",i=0;i<c;i++)$+='<th class="scale-small scale-'+S+'"><span class="spacer-cell"></span></th>';var W="<tr>";for(i=0;i<c;i++)W+='<td class="scale-small"><span class="spacer-cell"></span></td>';if(W+="</tr>",0==o.timeline.attr("show-pointer"))x.css("display","none");else{var G=s(r(!0),o);!1!==G?x.css("left",G+"px"):x.css("display","none")}var q=e("<div />",{addClass:"timeline-loader",css:{display:"block"}});q.append('<i class="jqtl-spinner"></i><span class="sr-only">Loading...</span>');var X='<div class="timeline-nav">',R=""===o.timeline.attr("navi-icon-left")?"jqtl-circle-left":o.timeline.attr("navi-icon-left"),U=""===o.timeline.attr("navi-icon-right")?"jqtl-circle-right":o.timeline.attr("navi-icon-right");return X+='<a href="javascript:void(0);" class="timeline-to-prev '+(/^jqtl-circle-.*$/.test(R)?"timeline-to-prev-default":"timeline-to-prev-custom")+'"><i class="'+R+'"></i></a>',X+='<a href="javascript:void(0);" class="timeline-to-next '+(/^jqtl-circle-.*$/.test(U)?"timeline-to-next-default":"timeline-to-next-custom")+'"><i class="'+U+'"></i></a>',X+="</div>",v.append("<thead>"+P+E+$+"</thead>"),w.append("<tbody>"+W+"</tbody>"),"point"===o.timeline.attr("type")||"mixed"===o.timeline.attr("type")?y.append(v.prop("outerHTML")+b.prop("outerHTML")+C.prop("outerHTML")+w.prop("outerHTML")+x.prop("outerHTML")):y.append(v.prop("outerHTML")+b.prop("outerHTML")+w.prop("outerHTML")+x.prop("outerHTML")),g.append(y),p.append(X),l.append(f),l.append(g),l.append(p),l.append(q.prop("outerHTML")),l}function i(t){var i=e(t),a=i.data("timeline");y="auto"===a.timeline.attr("timeline-height")||"number"!=typeof a.timeline.attr("timeline-height")?Number(a.timeline.attr("rows"))*Number(a.timeline.attr("row-height")):Number(a.timeline.attr("timeline-height"));var n={width:i.find(".timeline-timetable.timeline-scale").outerWidth(),height:63};i.find(".timeline-wrapper")[0].offsetHeight!=n.height+y&&(i.find(".timeline-wrapper").css("height",n.height+y+"px"),i.find(".timeline-events").css("height",y+"px"),i.find(".timeline-line-canvas").css("height",y+"px").attr("width",n.width).attr("height",y),i.find(".timeline-grids").css("height",y+"px")),a.timeline.attr("min-grid-size",Number(a.timeline.attr("min-grid-size"))<5?30:Number(a.timeline.attr("min-grid-size"))),i.find(".spacer-cell").width()!=a.timeline.attr("min-grid-size")-1&&i.find(".spacer-cell").css("width",a.timeline.attr("min-grid-size")-1+"px");var r=-1*((i.find(".timeline-body").outerHeight()-i.find(".timeline-scale").outerHeight())/2+i.find(".timeline-to-prev").outerHeight());return i.find(".timeline-to-prev").css("top",r+"px"),i.find(".timeline-to-next").css("top",r+"px"),i.find(".timeline-body").scroll(function(){var t=e(this).scrollLeft();t<1?i.find(".timeline-to-prev").hide():t>=n.width-e(this).outerWidth()-2?i.find(".timeline-to-next").hide():(i.find(".timeline-to-prev").show(),i.find(".timeline-to-next").show())}),i}function a(t){var i=e(t),a=i.data("timeline"),r=new Function("return "+a.timeline.text())(),s=new Date(o(a.timeline.attr("actual-start-datetime"))),m=new Date(s),d=a.timeline.attr("type"),c=a.timeline.attr("scale"),u=Number(a.timeline.attr("range")),h=Number(a.timeline.attr("row-height")),f=Number(a.timeline.attr("total-cols")),g=Number(a.timeline.attr("min-grid-per")),y=Number(a.timeline.attr("min-grid-size")),v={x:0,y:0,w:0},b=y*f-1;switch(i.find(".timeline-loader").css("display","block"),c){case"years":m.setYear(m.getFullYear()+u);break;case"months":m.setMonth(m.getMonth()+u-1);break;case"days":m.setDate(m.getDate()+u)}i.find(".timeline-events").empty(),r.forEach(function(t){if(t.start){var a,n=new Date(o(t.start)),r=null==t.end?new Date(o(t.start)):new Date(o(t.end)),u=g*y;if(l(n,s,m)){switch(c){case"years":v.x=Math.round((n-s)*b/(m-s));break;case"months":v.x=Math.floor((n-s)/864e5*u);break;case"days":v.x=Math.floor((n-s)/36e5*u)}if(v.y=void 0!==t.row?(t.row-1)*h:0,l(r,s,m)){switch(c){case"years":v.w=Math.floor((r-s)/2592e6*u-v.x);break;case"months":v.w=Math.floor((r-s)/864e5*u-v.x);break;case"days":v.w=Math.floor((r-s)/36e5*u-v.x)}0==v.w&&(v.w=1)}else switch(c){case"years":v.w=Math.floor((m-s)/2592e6*u-v.x);break;case"months":v.w=Math.floor((m-s)/864e5*u-v.x);break;case"days":v.w=Math.floor((m-s)/36e5*u-v.x)}}else if(l(r,s,m))switch(v.x=0,v.y=void 0!==t.row?(t.row-1)*h:0,c){case"years":v.w=Math.floor((r-s)/2592e6*u);break;case"months":v.w=Math.floor((r-s)/864e5*u);break;case"days":v.w=Math.floor((r-s)/36e5*u)}else if(l(s,n,r)&&l(m,n,r))switch(v.x=0,v.y=void 0!==t.row?(t.row-1)*h:0,c){case"years":v.w=Math.floor((m-s)/2592e6*u);break;case"months":v.w=Math.floor((m-s)/864e5*u);break;case"days":v.w=Math.floor((m-s)/36e5*u)}else v.w=0;if(v.w>0){if("point"===d){var f=t.margin?Number(t.margin):p;f=(f=f<0?0:f)>h/2?h/2-1:f,a=e("<div />",{addClass:"timeline-node timeline-event-pointer",id:"evt-"+t.eventId,css:{left:v.x-Math.floor(h/2)+f+"px",top:v.y+f+"px",width:h-2*f+"px",height:h-2*f+"px"},title:t.label}),t.bdColor?a.css("border-color",t.bdColor):t.bgColor&&a.css("border-color",t.bgColor),t.image&&a.css("background-image","url("+t.image+")"),t.relation&&e.each(t.relation,function(t,i){-1==e.inArray(t,["before","after","size"])||isNaN(i)?"curve"===t?-1!=e.inArray(i,["lt","rt","lb","rb"])&&a.attr("data-relay-curve",i):a.attr("data-relay-"+t,i):a.attr("data-relay-"+t,Number(i))})}else a=e("<div />",{addClass:"timeline-node timeline-text-truncate",id:"evt-"+t.eventId,css:{left:v.x+"px",top:v.y+"px",width:v.w+"px"},text:t.label}),t.color&&a.css("color",t.color),v.w<y&&a.css("padding-left","1.5rem").css("padding-right","0").css("text-overflow","clip");t.bgColor&&a.css("background-color",t.bgColor),t.extend&&e.each(t.extend,function(e,t){a.attr("data-"+e,t)}),i.find(".timeline-events").append(a.prop("outerHTML"))}}}),i.find(".timeline-loader").css("display","none"),"point"!==d&&"mixed"!==d||(n(i),i.find(".timeline-event-pointer").hover(function(t){var i;"mouseenter"===t.type?(i={left:parseInt(e(this).css("left")),top:parseInt(e(this).css("top")),width:parseInt(e(this).css("width")),height:parseInt(e(this).css("height"))},e(this).attr("data-default-axis",JSON.stringify(i)),e(this).hasClass("hovered")||e(this).addClass("hovered").animate({left:i.left-h/10,top:i.top-h/10,width:i.width+h/10*2,height:i.height+h/10*2},0)):"mouseleave"===t.type&&(i=e(this).data("defaultAxis"),e(this).css("left",i.left+"px").css("top",i.top+"px").css("width",i.width+"px").css("height",i.height+"px"),e(this).removeAttr("data-default-axis"),e(this).hasClass("hovered")&&e(this).removeClass("hovered"))}))}function n(t){function i(e,t,i){if("object"==typeof e&&"object"==typeof t){i=i||!1;var n,r={x:Math.abs((e.x-t.x)/g),y:Math.abs((e.y-t.y)/g)};if(a.beginPath(),a.moveTo(e.x,e.y),!1!==i){switch(i){case"lt":n={relayStartX:e.x,relayStartY:t.y+g,cpx:e.x,cpy:t.y,relayEndX:e.x+g,relayEndY:t.y};break;case"rt":n={relayStartX:t.x-g,relayStartY:e.y,cpx:t.x,cpy:e.y,relayEndX:t.x,relayEndY:e.y+g};break;case"lb":n={relayStartX:e.x,relayStartY:t.y-g,cpx:e.x,cpy:t.y,relayEndX:e.x+g,relayEndY:t.y};break;case"rb":n={relayStartX:t.x-g,relayStartY:e.y,cpx:t.x,cpy:e.y,relayEndX:t.x,relayEndY:e.y-g}}(r.x>1||r.y>1)&&a.lineTo(n.relayStartX,n.relayStartY),a.quadraticCurveTo(n.cpx,n.cpy,n.relayEndX,n.relayEndY)}a.lineTo(t.x,t.y),a.stroke()}}var a,n=t.find(".timeline-node.timeline-event-pointer"),r=t.find(".timeline-line-canvas")[0];r.getContext&&(a=r.getContext("2d"),n.each(function(){var t,n,l,s,o,m,d=null==e(this).data("relayLinecolor")?e(this).css("border-left-color"):e(this).data("relayLinecolor"),c=null==e(this).data("relayLinesize")?Math.round(g/10):e(this).data("relayLinesize");a.strokeStyle=d,a.lineWidth=c,a.lineJoin="round",a.lineCap="round",s={x:(g-a.lineWidth)/2,y:g/2},o=Math.floor((g-e(this)[0].offsetWidth)/2),t={x:e(this)[0].offsetLeft-o+s.x,y:Math.floor(e(this)[0].offsetTop/g)*g+s.y},null!=e(this).data("relayBefore")&&(e(this).data("relayBefore")>0?e("#evt-"+e(this).data("relayBefore")).length>0&&(o=Math.floor((g-e("#evt-"+e(this).data("relayBefore"))[0].offsetWidth)/2),n={x:e("#evt-"+e(this).data("relayBefore"))[0].offsetLeft-o+s.x,y:Math.floor(e("#evt-"+e(this).data("relayBefore"))[0].offsetTop/g)*g+s.y}):n={x:0,y:t.y},n&&(m=(n.y-t.y)/g,Math.abs(m)>0&&null!=e(this).data("relayCurve")&&-1!=e.inArray(e(this).data("relayCurve"),["lt","rt","lb","rb"])?i(n,t,e(this).data("relayCurve")):i(n,t))),null!=e(this).data("relayAfter")&&(e(this).data("relayAfter")>0?e("#evt-"+e(this).data("relayAfter")).length>0&&(o=Math.floor((g-e("#evt-"+e(this).data("relayAfter"))[0].offsetWidth)/2),l={x:e("#evt-"+e(this).data("relayAfter"))[0].offsetLeft-o+s.x,y:Math.floor(e("#evt-"+e(this).data("relayAfter"))[0].offsetTop/g)*g+s.y}):l={x:r.width,y:t.y},l&&(m=(t.y-l.y)/g,Math.abs(m)>0&&null!=e(this).data("relayCurve")&&-1!=e.inArray(e(this).data("relayCurve"),["lt","rt","lb","rb"])?i(t,l,e(this).data("relayCurve")):i(t,l)))}))}function r(t){var i=new Date;return t&&b().then(function(){i=e("body").data("serverDate"),e.removeData("body","serverDate")},function(){i=new Date}),i}function l(e,t,i){var a=new Date(e).getTime(),n=new Date(t).getTime(),r=new Date(i).getTime();return a-n>=0&&r-a>=0}function s(e,t){e="[object Date]"===Object.prototype.toString.call(e)?e:new Date(o(e));var i,a,n=t.timeline,r=new Date(o(n.attr("actual-start-datetime"))),s=new Date(r),m=n.attr("scale"),d=Number(n.attr("range")),c=Number(n.attr("total-cols")),u=Number(n.attr("min-grid-per")),h=Number(n.attr("min-grid-size")),f=h*c-1,g=u*h;switch(m){case"years":i=(s=new Date(s.setFullYear(s.getFullYear()+d))).getTime(),s.setTime(i-1);break;case"months":i=(s=new Date(s.setMonth(s.getMonth()+d))).getTime(),s.setTime(i-1);break;case"days":i=(s=new Date(s.setDate(s.getDate()+d))).getTime(),s.setTime(i-1)}if(l(e,r,s)){switch(m){case"years":a=Math.round((e-r)*f/(s-r));break;case"months":a=Math.floor((e-r)/864e5*g);break;case"days":a=Math.floor((e-r)/36e5*g)}return a}return!1}function o(e){return e.replace(/-/g,"/")}function m(e){return e.reduce(function(e,t){return e+t})}function d(e){var t=[];for(var i in e)e.hasOwnProperty(i)&&t.push(e[i]);return t}function c(e){var t=[];for(var i in e)e.hasOwnProperty(i)&&t.push(i);return t}function u(e,t){var i=function(e,t){return Array(e+1).join("0")}(t-1);return String(e).length==t?e:(i+e).substr(-1*e)}function h(t,i){t=t||"";var a="[object Date]"===Object.prototype.toString.call(i)?i:new Date(o(i)),n={Jan:"January",Feb:"February",Mar:"March",Apr:"April",May:"May",Jun:"June",Jul:"July",Aug:"August",Sep:"September",Oct:"October",Nov:"November",Dec:"December"},r={Sun:"Sunday",Mon:"Monday",Tue:"Tuesday",Wed:"Wednesday",Thu:"Thurseday",Fri:"Friday",Sat:"Saturday"},l=["am","pm"],s=t.split(""),m="",h=!1,f=function(e){var t=new Date(e.getFullYear(),e.getMonth()+1,1);return t.setTime(t.getTime()-1),t.getDate()},g=function(e){var t=e.getHours();return t>12?t-12:t},p=function(e){return e.getHours()>12?l[1]:l[0]};if(""===t)return a;if(e(".timeline-container").length>0){var y=e(".timeline-container").eq(0).data("timeline").timeline;n=y.attr("i18n-month")?JSON.parse(y.attr("i18n-month")):n,r=y.attr("i18n-day")?JSON.parse(y.attr("i18n-day")):r,l=y.attr("i18n-ma")?JSON.parse(y.attr("i18n-ma")):l}return s.forEach(function(e,t){var i,l,o,y;if(!1!==h)return h=!1,!0;switch(e){case"Y":case"o":i=a.getFullYear();break;case"y":i=(""+a.getFullYear()).slice(-2);break;case"m":i=("0"+(a.getMonth()+1)).slice(-2);break;case"n":i=a.getMonth()+1;break;case"F":i=d(n)[a.getMonth()];break;case"M":i=c(n)[a.getMonth()];break;case"d":i=("0"+a.getDate()).slice(-2);break;case"j":i=a.getDate();break;case"S":i=["st","nd","rd","th"][function(){var e=a.getDate();return 1==e||2==e||3==e||21==e||22==e||23==e||31==e?Number((""+e).slice(-1)-1):3}()];break;case"w":case"W":i=a.getDay();break;case"l":i=d(r)[a.getDay()];break;case"D":i=c(r)[a.getDay()];break;case"N":i=0===a.getDay()?7:a.getDay();break;case"a":i=p(a);break;case"A":i=p(a).toUpperCase();break;case"g":i=g(a);break;case"h":i=("0"+g(a)).slice(-2);break;case"G":i=a.getHours();break;case"H":i=("0"+a.getHours()).slice(-2);break;case"i":i=("0"+a.getMinutes()).slice(-2);break;case"s":i=("0"+a.getSeconds()).slice(-2);break;case"z":i=function(e){var t,i=new Date(e.getFullYear(),0,1),a=0;for(t=0;t<e.getMonth();t++)i.setMonth(t),a+=f(i);return a+e.getDate()}(a);break;case"t":i=f(a);break;case"L":i=function(e){var t,i=new Date(e.getFullYear(),0,1),a=0;for(t=0;t<12;t++)i.setMonth(t),a+=f(i);return 365===a?0:1}(a);break;case"c":l=a.getTimezoneOffset(),o=[Math.floor(Math.abs(l)/60),Math.abs(l)%60],y=l<0?"+":"-",i=a.getFullYear()+"-"+u(a.getMonth()+1,2)+"-"+u(a.getDate(),2)+"T",i+=u(a.getHours(),2)+":"+u(a.getMinutes(),2)+":"+u(a.getSeconds(),2),i+=y+u(o[0],2)+":"+u(o[1],2);break;case"r":l=a.getTimezoneOffset(),o=[Math.floor(Math.abs(l)/60),Math.abs(l)%60],y=l<0?"+":"-",i=c(r)[a.getDay()]+", "+a.getDate()+" "+c(n)[a.getMonth()]+" "+a.getFullYear()+" ",i+=u(a.getHours(),2)+":"+u(a.getMinutes(),2)+":"+u(a.getSeconds(),2)+" ",i+=y+u(o[0],2)+u(o[1],2);break;case"u":i=a.getTime();break;case"U":i=Date.parse(a)/1e3;break;case"\\":h=!0,i=s[t+1];break;default:i=e}m+=i}),m}function f(t){var i=e.Deferred(),a=(t.data("timeline").timeline.attr("langs-dir")||"./langs/")+t[0].lang+".json";return e.ajax({url:a,type:"get",dataType:"json"}).done(function(e){i.resolve(e)}).fail(function(){i.reject()}),i.promise()}var g,p=2,y=0,v={init:function(n){var l=e.extend({type:"bar",scale:"days",startDatetime:"currently",datetimePrefix:"",showHeadline:!0,datetimeFormat:{full:"j M Y",year:"Y",month:"M Y",day:"D, j M",years:"Y",months:"F",days:"j",meta:"Y/m/d H:i",metato:""},minuteInterval:30,zerofillYear:!1,range:3,rows:5,rowHeight:40,height:"auto",minGridPer:2,minGridSize:30,rangeAlign:"current",naviIcon:{left:"jqtl-circle-left",right:"jqtl-circle-right"},showPointer:!0,i18n:{},langsDir:"./langs/",httpLnaguage:!1},n);return this.each(function(){var s=e(this),m=s.data("timeline"),d=e("<div />",{title:s.find(".timeline-headline").text(),type:l.type,scale:l.scale,"start-datetime":l.startDatetime,"datetime-prefix":l.datetimePrefix,"show-headline":l.showHeadline?1:0,"datetime-format-full":l.datetimeFormat.full||"j M Y","datetime-format-year":l.datetimeFormat.year||"Y","datetime-format-month":l.datetimeFormat.month||"M Y","datetime-format-day":l.datetimeFormat.day||"D, j M","datetime-format-years":l.datetimeFormat.years||"Y","datetime-format-months":l.datetimeFormat.months||"F","datetime-format-days":l.datetimeFormat.days||"j","datetime-format-meta":l.datetimeFormat.meta||"Y/m/d H:i","datetime-format-metato":l.datetimeFormat.metato||"","minute-interval":l.minuteInterval,"zerofill-year":l.zerofillYear?1:0,range:l.range,rows:l.rows,"row-height":l.rowHeight,"timeline-height":l.height,"min-grid-per":l.minGridPer,"min-grid-size":l.minGridSize,"range-align":l.rangeAlign,"navi-icon-left":l.naviIcon.left||"jqtl-circle-left","navi-icon-right":l.naviIcon.right||"jqtl-circle-right","show-pointer":l.showPointer?1:0,"i18n-month":l.i18n.month?JSON.stringify(l.i18n.month):"","i18n-day":l.i18n.day?JSON.stringify(l.i18n.day):"","i18n-ma":l.i18n.ma?JSON.stringify(l.i18n.ma):"","langs-dir":l.langsDir,"http-language":l.httpLnaguage?1:0,text:""});if(s.on("click.timeline",".timeline-to-prev",v.dateback),s.on("click.timeline",".timeline-to-next",v.dateforth),s.on("click.timeline",".timeline-node",v.openEvent),s.on("align.timeline",v.alignment),s.on("afterRender.timeline",function(){e(this).off("afterRender.timeline")}),m)a(s);else{var c,u,h,p;switch(s.data("timeline",{target:s,timeline:d}),g=l.rowHeight,"currently"===l.startDatetime?c=r(!0):(c=new Date(o(l.startDatetime)),p=/-|\//,h=l.startDatetime.split(p),Number(h[0])<100&&c.setFullYear(Number(h[0]))),l.scale){case"years":u=c.getFullYear()+"/01/01 00:00:00";break;case"months":u=c.getFullYear()+"/"+(c.getMonth()+1)+"/01 00:00:00";break;case"days":u=c.getFullYear()+"/"+(c.getMonth()+1)+"/"+c.getDate()+" 00:00:00";break;default:u=c.getFullYear()+"/"+(c.getMonth()+1)+"/"+c.getDate()+" "+u.getHours()+":00:00"}s.data("timeline").timeline.attr("actual-start-datetime",u),w(l.httpLnaguage).always(function(e){s[0].lang=e}).then(function(){f(s).done(function(e){if(s.data("timeline").timeline.attr("i18n-month",JSON.stringify(e.month)),s.data("timeline").timeline.attr("i18n-day",JSON.stringify(e.day)),s.data("timeline").timeline.attr("i18n-ma",JSON.stringify(e.ma)),"format"in e)for(var r in e.format)s.data("timeline").timeline.attr("datetime-format-"+r,e.format[r]);t(s),i(s),s.trigger("align.timeline",[l.rangeAlign]),s.css("visibility","visible"),a(s),s.trigger("afterRender.timeline",[n])}).fail(function(){t(s),i(s),s.trigger("align.timeline",[l.rangeAlign]),s.css("visibility","visible"),a(s),s.trigger("afterRender.timeline",[n])})})}})},initialized:function(t){return this.each(function(){var i=e(this),a=i.data("timeline");a&&"function"==typeof t&&t(i,a)})},destroy:function(){return this.each(function(){var t=e(this),i=t.data("timeline");e(window).off(".timeline"),i&&(i.timeline.remove(),t.removeData("timeline"))})},render:function(n){return this.each(function(){var l,s,m,d,c=e(this),u=c.data("timeline");switch("type"in n&&u.timeline.attr("type",n.type),"scale"in n&&u.timeline.attr("scale",n.scale),"startDatetime"in n&&u.timeline.attr("start-datetime",n.startDatetime),"datetimePrefix"in n&&u.timeline.attr("datetime-prefix",n.datetimePrefix),"showHeadline"in n&&u.timeline.attr("show-headline",n.showHeadline?1:0),"datetimeFormat"in n&&(null!=typeof n.datetimeFormat.full&&u.timeline.attr("datetime-format-full",n.datetimeFormat.full),null!=typeof n.datetimeFormat.year&&u.timeline.attr("datetime-format-year",n.datetimeFormat.year),null!=typeof n.datetimeFormat.month&&u.timeline.attr("datetime-format-month",n.datetimeFormat.month),null!=typeof n.datetimeFormat.day&&u.timeline.attr("datetime-format-day",n.datetimeFormat.day),null!=typeof n.datetimeFormat.years&&u.timeline.attr("datetime-format-years",n.datetimeFormat.years),null!=typeof n.datetimeFormat.months&&u.timeline.attr("datetime-format-months",n.datetimeFormat.months),null!=typeof n.datetimeFormat.days&&u.timeline.attr("datetime-format-days",n.datetimeFormat.days),null!=typeof n.datetimeFormat.meta&&u.timeline.attr("datetime-format-meta",n.datetimeFormat.meta),null!=typeof n.datetimeFormat.metato&&u.timeline.attr("datetime-format-metato",n.datetimeFormat.metato)),"minuteInterval"in n&&u.timeline.attr("minute-interval",n.minuteInterval),"zerofillYear"in n&&u.timeline.attr("zerofill-year",n.zerofillYear?1:0),"range"in n&&u.timeline.attr("range",n.range),"rows"in n&&u.timeline.attr("rows",n.rows),"rowHeight"in n&&u.timeline.attr("row-height",n.rowHeight),"height"in n&&u.timeline.attr("timeline-height",n.height),"minGridPer"in n&&u.timeline.attr("min-grid-per",n.minGridPer),"minGridSize"in n&&u.timeline.attr("min-grid-size",n.minGridSize),"rangeAlign"in n&&u.timeline.attr("range-align",n.rangeAlign),"naviIcon"in n&&(null!=typeof n.naviIcon.left&&u.timeline.attr("navi-icon-left",n.naviIcon.left),null!=typeof n.naviIcon.right&&u.timeline.attr("navi-icon-right",n.naviIcon.right)),"showPointer"in n&&u.timeline.attr("show-pointer",n.showPointer?1:0),"i18n"in n&&(null!=typeof n.i18n.month&&u.timeline.attr("i18n-month",JSON.stringify(n.i18n.month)),null!=typeof n.i18n.day&&u.timeline.attr("i18n-day",JSON.stringify(n.i18n.day)),null!=typeof n.i18n.ma&&u.timeline.attr("i18n-ma",JSON.stringify(n.i18n.ma))),"langsDir"in n&&u.timeline.attr("langs-dir",n.langsDir),"httpLanguage"in n&&u.timeline.attr("http-language",n.httpLanguage),"currently"===u.timeline.attr("start-datetime")?l=r(!0):(l=new Date(o(u.timeline.attr("start-datetime"))),d=/-|\//,m=u.timeline.attr("start-datetime").split(d),Number(m[0])<100&&l.setFullYear(Number(m[0]))),u.timeline.attr("scale")){case"years":s=l.getFullYear()+"/01/01 00:00:00";break;case"months":s=l.getFullYear()+"/"+(l.getMonth()+1)+"/01 00:00:00";break;case"days":s=l.getFullYear()+"/"+(l.getMonth()+1)+"/"+l.getDate()+" 00:00:00";break;default:s=l.getFullYear()+"/"+(l.getMonth()+1)+"/"+l.getDate()+" "+s.getHours()+":00:00"}u.timeline.attr("actual-start-datetime",s),c.find(".timeline-container").empty().removeClass("timeline-container"),w(u.timeline.attr("http-language")).always(function(e){c[0].lang=e}).then(function(){f(c).done(function(e){if(u.timeline.attr("i18n-month",JSON.stringify(e.month)),u.timeline.attr("i18n-day",JSON.stringify(e.day)),u.timeline.attr("i18n-ma",JSON.stringify(e.ma)),"format"in e)for(var r in e.format)c.data("timeline").timeline.attr("datetime-format-"+r,e.format[r]);t(c),i(c),a(c),c.trigger("align.timeline",[u.timeline.attr("range-align")]),c.trigger("afterRender.timeline",[n])}).fail(function(){t(c),i(c),a(c),c.trigger("align.timeline",[u.timeline.attr("range-align")]),c.trigger("afterRender.timeline",[n])})})})},show:function(){return this.each(function(){e(this).css("display","block").css("visibility","visible")})},hide:function(){return this.each(function(){e(this).css("visibility","hidden").css("display","none")})},dateback:function(t){t.preventDefault();var i=e(this).parents(".timeline-container"),a=i.data("timeline"),n=i.find(".timeline-body")[0].clientWidth,r=i.find(".timeline-wrapper")[0].scrollWidth,l=i.find(".timeline-body").scrollLeft(),s=0;return r>n&&(s=(s=l/n>1?l-n:l-(r-n)/Number(a.timeline.attr("range")))<0?0:s,i.find(".timeline-body").animate({scrollLeft:s},300)),this},dateforth:function(t){t.preventDefault();var i=e(this).parents(".timeline-container"),a=i.data("timeline"),n=i.find(".timeline-body")[0].clientWidth,r=i.find(".timeline-wrapper")[0].scrollWidth,l=i.find(".timeline-body").scrollLeft(),s=0;return r>n&&(s=(s=(r-l)/n>1?l+n:l+(r-n)/Number(a.timeline.attr("range")))>r-n+1?r-n+1:s,i.find(".timeline-body").animate({scrollLeft:s},300)),this},alignment:function(){var t=arguments.length>1?Array.prototype.slice.call(arguments,1):[arguments[0]],i=t[0].toLowerCase(),a=void 0!==t[1]?String(t[1]).toLowerCase():0,n=e(this).find(".timeline-body")[0].clientWidth,l=e(this).find(".timeline-wrapper")[0].scrollWidth,m=0;if(l>n){var d,c=e(this).data("timeline");switch(i){case"left":m=0;break;case"right":m=l-n+1;break;case"center":m=(l-n)/2;break;case"current":m=(d=s(r(!0),c))>-1?d-n/2>l-n+1?l-n+1:d-n/2:l-n+1;break;case"latest":var u,f,g,p=new Function("return "+c.timeline.text())();e.each(p,function(e,t){f=h("U",t.start),0==e?(u=f,g=e):f>=u&&(u=f,g=e)}),m=(d=s(new Date(o(p[g].start)),c))>-1?d-n/2>l-n+1?l-n+1:d-n/2:l-n+1;break;default:m=0;var y="#"+i;e(y).length&&(m=(d=e(y).position().left)-n/2>l-n+1?l-n+1:d-n/2)}-1!=e.inArray(a,["slow","normal","fast"])||Number(a)>0?e(this).find(".timeline-body").animate({scrollLeft:m},a):e(this).find(".timeline-body").scrollLeft(m)}return this},getOptions:function(){var t=e(this).data("timeline");return{title:t.timeline.attr("title"),type:t.timeline.attr("type"),scale:t.timeline.attr("scale"),startDatetime:t.timeline.attr("start-datetime"),datetimePrefix:t.timeline.attr("datetime-prefix"),showHeadline:1==Number(t.timeline.attr("show-headline")),datetimeFormat:{full:t.timeline.attr("datetime-format-full"),year:t.timeline.attr("datetime-format-year"),month:t.timeline.attr("datetime-format-month"),day:t.timeline.attr("datetime-format-day"),years:t.timeline.attr("datetime-format-years"),months:t.timeline.attr("datetime-format-months"),days:t.timeline.attr("datetime-format-days")},minuteInterval:Number(t.timeline.attr("minute-interval")),zerofillYear:1==Number(t.timeline.attr("zerofill-year")),range:Number(t.timeline.attr("range")),rows:Number(t.timeline.attr("rows")),rowHeight:Number(t.timeline.attr("row-height")),height:"auto"===t.timeline.attr("timeline-height")?"auto":Number(t.timeline.attr("timeline-height")),minGridPer:Number(t.timeline.attr("min-grid-per")),minGridSize:Number(t.timeline.attr("min-grid-size")),rangeAlign:t.timeline.attr("range-align"),naviIcon:{left:t.timeline.attr("navi-icon-left"),right:t.timeline.attr("navi-icon-right")},showPointer:t.timeline.attr("show-pointer"),i18n:{month:JSON.parse(t.timeline.attr("i18n-month")),day:JSON.parse(t.timeline.attr("i18n-day")),ma:JSON.parse(t.timeline.attr("i18n-ma"))},langsDir:t.timeline.attr("langs-dir"),events:new Function("return "+t.timeline.text())()}},addEvent:function(t,i){return this.each(function(){var n=e(this),r=n.data("timeline"),l=new Function("return "+r.timeline.text())(),s=1,o=[s];t.length>0&&(e.each(l,function(e,t){o.push(Number(t.eventId))}),s=Math.max.apply(null,o)+1,e.each(t,function(e,t){t.eventId=s,s++,l.push(t)}),r.timeline.text(JSON.stringify(l))),a(n),e(this).trigger("align.timeline",["evt-"+(s-1),"fast"]),r&&"function"==typeof i&&i(n,r)})},removeEvent:function(){var t,i;return 0==arguments.length?(t="all",i=null):1==arguments.length?"function"==typeof arguments[0]?(t="all",i=arguments[0]):(t=arguments[0],i=null):(t=arguments[0],i=arguments[1]),this.each(function(){var n=e(this),r=n.data("timeline"),l=new Function("return "+r.timeline.text())();if("all"===t)l=[];else{var s=[];e.each(l,function(i,a){-1==e.inArray(a.eventId,t)&&s.push(a)}),l=s}r.timeline.text(JSON.stringify(l)),a(n),r&&"function"==typeof i&&i(n,r)})},updateEvent:function(t,i){return void 0!==t&&this.each(function(){var n,r=e(this),l=r.data("timeline"),s=new Function("return "+l.timeline.text())(),o=[];t.length>0&&e.each(t,function(e,t){o.push(t.eventId)}),s.length>0&&o.length>0&&(e.each(s,function(i,a){var r;-1!=e.inArray(a.eventId,o)&&(e.each(t,function(e,t){if(t.eventId==a.eventId)return r=t,n=t.eventId,!1}),s[i]=r)}),l.timeline.text(JSON.stringify(s))),a(r),e(this).trigger("align.timeline",["evt-"+n,"fast"]),l&&"function"==typeof i&&i(r,l)})},openEvent:function(t){var i=Number(e(t.target).attr("id").replace("evt-","")),a=t.delegateTarget;return""!==i&&0!=i&&e(a).each(function(){var t,a=e(this).data("timeline"),n=new Function("return "+a.timeline.text())(),r={start:a.timeline.attr("datetime-format-meta"),end:a.timeline.attr("datetime-format-metato")};e.each(n,function(e,a){if(a.eventId==i)return t=a,!1}),e(this).find(".timeline-node").each(function(){e(this).attr("id")==="evt-"+i?e(this).addClass("active"):e(this).removeClass("active")}),e(this).trigger("align.timeline",["evt-"+i,"fast"]),function(t,i){if(0==e(".timeline-event-view").length)return!0;e(".timeline-event-view").empty();var a,n=e("<div />",{addClass:"timeline-event-header"}),r=e("<h3 />",{addClass:"timeline-event-label"}),l=e("<div />",{addClass:"timeline-event-meta"}),s=e("<div />",{addClass:"timeline-event-body"}),o=e("<div />",{addClass:"timeline-event-footer"});return r.text(t.label),""===i.end&&(i.end=i.start),a='<span class="timeline-event-start-date">'+h(i.start,t.start)+"</span>",t.end&&(a+='<span class="timeline-event-date-separator"></span>',a+='<span class="timeline-event-end-date">'+h(i.end,t.end)+"</span>"),n.append(r.prop("outerHTML")+l.append(a).prop("outerHTML")),t.content&&s.html(t.content),e(".timeline-event-view").append(n.prop("outerHTML")+s.prop("outerHTML")+o.prop("outerHTML")),!0}(t,r)&&t.callback&&Function.call(null,"return "+t.callback)()})}};e.fn.timeline=function(t){return v[t]?v[t].apply(this,Array.prototype.slice.call(arguments,1)):"object"!=typeof t&&t?void e.error("Method "+t+" does not exist on jQuery.timeline."):v.init.apply(this,arguments)};var b=function(){return e.ajax({type:"GET"}).done(function(t,i,a){e("body").data("serverDate",new Date(o(a.getResponseHeader("Date"))))}).promise()},w=function(){var t=e.Deferred(),i=navigator.userLanguage||navigator.browserLanguage||navigator.language;return 0!=arguments.length&&arguments[0]?e.ajax({url:"//ajaxhttpheaders.appspot.com",data:{callback:"jQuery.Timeline"},dataType:"jsonp"}).done(function(e){var a,n;n=(a=e["Accept-Language"].split(";"))[0].split(","),a[1].split(","),e["X-Appengine-Country"],n.length>0&&(i=n[0]),t.resolve(i)}).fail(function(){t.reject()}):t.resolve(i),t.promise()}}(jQuery)},21:function(e,t,i){"use strict";i.r(t);i(1),i(12);const a={init:function(){const e=$("#phases");$.ajax(e.data("dates-url"),{dataType:"json",beforeSend:function(t,i){e.html($("<img>").attr("width",64).attr("height",64).attr("src",STUDIP.ASSETS_URL+"images/ajax-indicator-black.svg"))},success:function(t,i,n){if(null!=t&&t.length>0){let i=$("<ul>").addClass("timeline-events"),n=[];$.each(t,function(e,t){let r=$("<li>").attr("data-timeline-node","{ eventId: '"+t.id+"', start: '"+t.start+"', end: '"+t.end+"', content: '"+t.title+"', bgColor: '"+t.color+"', color: '"+a.getContrastColor(t.color)+"'}").attr("data-phase-color",t.color).attr("data-title",t.title).text(t.title);i.append(r),t.current&&n.push(t.id)}),e.append(i),e.timeline({startDatetime:"2019-04-01",type:"bar",range:6,rows:1,scale:"months",rangeAlign:"current",langsDir:e.data("plugin-url")+"/assets/timeline/langs/",minGridSize:5}),e.on("afterRender.timeline",function(){for(let e=0;e<n.length;e++)$("#evt-"+n[e]).addClass("timeline-current");n.length>0&&($(".timeline-events").css("height","48px"),$(".timeline-grids").css("height","48px")),$(".timeline-node").hover(function(t){const i=$("<div>").attr("id","timeline-fulltitle").css("background-color",$(this).css("background-color")).css("color",$(this).css("color")).html($(this).html());e.append(i),i.fadeIn()},function(e){$("#timeline-fulltitle").fadeOut().remove()})})}else e.html($("<div>").addClass("messagebox").addClass("messagebox_info").html(e.data("no-phases-message")))},error:function(e,t,i){alert(i)}})},getContrastColor:function(e){return e=e.replace("#",""),(299*parseInt(e.substr(0,2),16)+587*parseInt(e.substr(2,2),16)+114*parseInt(e.substr(4,2),16))/1e3>=144?"#000000":"#ffffff"}};var n=a;$(function(){n.init()})}});