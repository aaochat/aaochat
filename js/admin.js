(()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}!function(t){var n={};function r(e){if(n[e])return n[e].exports;var o=n[e]={i:e,l:!1,exports:{}};return t[e].call(o.exports,o,o.exports,r),o.l=!0,o.exports}r.m=t,r.c=n,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(t,n){if(1&n&&(t=r(t)),8&n)return t;if(4&n&&"object"==e(t)&&t&&t.__esModule)return t;var o=Object.create(null);if(r.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&n&&"string"!=typeof t)for(var i in t)r.d(o,i,function(e){return t[e]}.bind(null,i));return o},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="/js",r(r.s=14)}({14:function(e,t){jQuery(document).ready((function(){jQuery(".side-menu-setting"),jQuery("#side-menu-save").on("click",(function(e){})),jQuery(".side-menu-display").on("click",(function(e){var t=jQuery(e.target);jQuery(".side-menu-display").removeClass("is-active"),t.addClass("is-active"),jQuery("#side-menu-always-displayed").val(t.attr("data-alwaysdiplayed")),jQuery("#side-menu-big-menu").val(t.attr("data-bigmenu"))})),jQuery(".side-menu-setting-live").on("change",(function(e){var t=jQuery(e.target),n=t.attr("name"),r=t.val();"opener"===n&&(r="url(".concat(OC.generateUrl("/apps/aaochat/img/".concat(r,".svg")).replace("/index.php",""),")")),"icon-invert-filter"!==n&&"icon-opacity"!==n||(r/=100),document.documentElement.style.setProperty("--side-menu-"+n,r)})),jQuery(".side-menu-toggler").on("click",(function(e){!function(e){jQuery(e).toggle()}(jQuery(e.target).attr("data-target"))})),jQuery("#categories-list .side-menu-setting-list").sortable({forcePlaceholderSize:!0,placeholder:"placeholder",stop:function(e,t){var n=[];jQuery("#categories-list .side-menu-setting-list-item").each((function(){n.push(jQuery(this).attr("data-id"))})),n=JSON.stringify(n),jQuery('input[name="categories-order"]').val(n)}})}))}})})();
//# sourceMappingURL=admin.js.map