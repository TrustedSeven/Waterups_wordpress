!function(){"use strict";var r,t={325:function(r,t,e){var n=e(558),o=e(772),a=e(595),i=e(438),l=e(886);function u(r,t){var e=Object.keys(r);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(r);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(r,t).enumerable}))),e.push.apply(e,n)}return e}function c(r){for(var t=1;t<arguments.length;t++){var e=null!=arguments[t]?arguments[t]:{};t%2?u(Object(e),!0).forEach((function(t){f(r,t,e[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(r,Object.getOwnPropertyDescriptors(e)):u(Object(e)).forEach((function(t){Object.defineProperty(r,t,Object.getOwnPropertyDescriptor(e,t))}))}return r}function f(r,t,e){return t in r?Object.defineProperty(r,t,{value:e,enumerable:!0,configurable:!0,writable:!0}):r[t]=e,r}function d(r,t){return function(r){if(Array.isArray(r))return r}(r)||function(r,t){var e=null==r?null:"undefined"!=typeof Symbol&&r[Symbol.iterator]||r["@@iterator"];if(null==e)return;var n,o,a=[],i=!0,l=!1;try{for(e=e.call(r);!(i=(n=e.next()).done)&&(a.push(n.value),!t||a.length!==t);i=!0);}catch(r){l=!0,o=r}finally{try{i||null==e.return||e.return()}finally{if(l)throw o}}return a}(r,t)||function(r,t){if(!r)return;if("string"==typeof r)return v(r,t);var e=Object.prototype.toString.call(r).slice(8,-1);"Object"===e&&r.constructor&&(e=r.constructor.name);if("Map"===e||"Set"===e)return Array.from(r);if("Arguments"===e||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(e))return v(r,t)}(r,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function v(r,t){(null==t||t>r.length)&&(t=r.length);for(var e=0,n=new Array(t);e<t;e++)n[e]=r[e];return n}var s,p="preview-vimeo",y={buttonstyle:"",playercolour:"",loadthumbnail:!0,thumbnailquality:!1};function b(r,t){var e=(0,a.Z)('<div aria-hidden="true" class="lazy-load-div"></div>');if(r.appendChild(e),window.llvConfig.vimeo.loadthumbnail){var n=function(r){if(!r)return"";var t=r.match(/_\d+x\d+/);if(t){var e=d(t[0].match(/\d+/g),2),n=e[0],o=e[1],a={basic:r.replace(t,"_".concat(640,"x",Math.round(o*(640/n)))),medium:r.replace(t,"_".concat(1280,"x",Math.round(o*(1280/n)))),max:r.replace(t,"")};return a[s.thumbnailquality]||a.basic}return r}(r.getAttribute("data-video-thumbnail"));n&&(0,o.bE)((0,i.Z)('[id="'.concat(t,'"]')),(function(r){return(0,o.X9)(r,n)}))}if(window.llvConfig.vimeo.show_title){var l=r.getAttribute("data-video-title"),u=window.llvConfig.vimeo.show_title&&l.length>0,c=(0,a.Z)('<div aria-hidden="true" class="lazy-load-info">\n        <div class="titletext vimeo">'.concat(l,"</div>\n      </div>"));u&&r.appendChild(c)}s.buttonstyle&&r.classList.add(s.buttonstyle)}function h(r){r.addEventListener("click",(function(r){var t=r.currentTarget;if(r.preventDefault(),"a"===t.tagName.toLowerCase()){var e,n=t.getAttribute("id"),o=t.getAttribute("href"),i=((e=new URL(o).search)?{queryParams:e.replace("?","").split("&").reduce((function(r,t){var e=d(t.split("="),2),n=e[0],o=e[1];return r[n]=o,r}),{})}:{queryParams:{}}).queryParams;t.classList.remove(p);var u=c(c({},i),{},{autoplay:1});s.playercolour&&(s.playercolour=s.playercolour.toString().replace(/[.#]/g,""),u.color=s.playercolour);var f=(0,a.Z)('<iframe src="'.concat(function(r){var t=r.videoId,e=r.queryParams;return"".concat(function(r){return"https://player.vimeo.com/video/".concat(r)}(t),"?").concat((0,l.Z)(e))}({videoId:n,queryParams:u}),'" style="height:').concat(Number(t.clientHeight),'px;width:100%" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>')),v=t.parentNode;v&&v.replaceChild(f,t)}}),!0)}function m(r){var t=r.rootNode;(0,i.Z)(".".concat(p),t).forEach((function(r){!function(r){var t=r,e=t.getAttribute("id");t.innerHTML="",b(t,e);var n=s.overlaytext.length>0,o=(0,a.Z)('<div aria-hidden="true" class="lazy-load-info-extra">\n      <div class="overlaytext">'.concat(s.overlaytext,"</div>\n    </div>"));n&&t.parentNode.insertBefore(o,null)}(r),(0,o.Ph)(r.parentNode),h(r)}))}var g=function(r){s=c(c({},y),r),(0,o.S1)({load:m,pluginOptions:s})};(0,n.Z)((function(){g(window.llvConfig.vimeo)}))}},e={};function n(r){var o=e[r];if(void 0!==o)return o.exports;var a=e[r]={exports:{}};return t[r](a,a.exports,n),a.exports}n.m=t,r=[],n.O=function(t,e,o,a){if(!e){var i=1/0;for(f=0;f<r.length;f++){e=r[f][0],o=r[f][1],a=r[f][2];for(var l=!0,u=0;u<e.length;u++)(!1&a||i>=a)&&Object.keys(n.O).every((function(r){return n.O[r](e[u])}))?e.splice(u--,1):(l=!1,a<i&&(i=a));if(l){r.splice(f--,1);var c=o();void 0!==c&&(t=c)}}return t}a=a||0;for(var f=r.length;f>0&&r[f-1][2]>a;f--)r[f]=r[f-1];r[f]=[e,o,a]},n.d=function(r,t){for(var e in t)n.o(t,e)&&!n.o(r,e)&&Object.defineProperty(r,e,{enumerable:!0,get:t[e]})},n.o=function(r,t){return Object.prototype.hasOwnProperty.call(r,t)},function(){var r={549:0};n.O.j=function(t){return 0===r[t]};var t=function(t,e){var o,a,i=e[0],l=e[1],u=e[2],c=0;if(i.some((function(t){return 0!==r[t]}))){for(o in l)n.o(l,o)&&(n.m[o]=l[o]);if(u)var f=u(n)}for(t&&t(e);c<i.length;c++)a=i[c],n.o(r,a)&&r[a]&&r[a][0](),r[a]=0;return n.O(f)},e=self.webpackChunklazy_load_for_videos=self.webpackChunklazy_load_for_videos||[];e.forEach(t.bind(null,0)),e.push=t.bind(null,e.push.bind(e))}();var o=n.O(void 0,[358],(function(){return n(325)}));o=n.O(o)}();