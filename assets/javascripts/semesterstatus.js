!function(e){var t={};function n(r){if(t[r])return t[r].exports;var u=t[r]={i:r,l:!1,exports:{}};return e[r].call(u.exports,u,u.exports,n),u.l=!0,u.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var u in e)n.d(r,u,function(t){return e[t]}.bind(null,u));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=74)}({14:function(e,t,n){n.p=window.STUDIP.ABSOLUTE_URI_STUDIP+"plugins_packages/upa/WhakamaherePlugin/assets/"},74:function(e,t,n){"use strict";n.r(t);n(14);var r={init:function(){$("select").on("change",(function(){$.post($("#semesters").data("update-url"),{semester:$(this).data("semester-id"),status:$(this).children("option:selected").val(),security_token:$('input[name="security_token"]').val()}).done((function(e){$("#update-status").html(e)})).fail((function(e,t,n){$("#update-status").html(n)}))}))}};$((function(){r.init()}))}});