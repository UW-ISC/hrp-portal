/*! For license information please see gp-nested-forms.js.LICENSE.txt */
(()=>{var e={584:function(e,t,o){var n,i;n=function(){var e=!1;function t(e){this.opts=function(){for(var e=1;e<arguments.length;e++)for(var t in arguments[e])arguments[e].hasOwnProperty(t)&&(arguments[0][t]=arguments[e][t]);return arguments[0]}({},{onClose:null,onOpen:null,beforeOpen:null,beforeClose:null,stickyFooter:!1,footer:!1,cssClass:[],closeLabel:"Close",closeMethods:["overlay","button","escape"]},e),this.init()}function o(){this.modalBoxFooter&&(this.modalBoxFooter.style.width=this.modalBox.clientWidth+"px",this.modalBoxFooter.style.left=this.modalBox.offsetLeft+"px")}return t.prototype.init=function(){if(!this.modal)return function(){this.modal=document.createElement("div"),this.modal.classList.add("tingle-modal"),0!==this.opts.closeMethods.length&&-1!==this.opts.closeMethods.indexOf("overlay")||this.modal.classList.add("tingle-modal--noOverlayClose"),this.modal.style.display="none",this.opts.cssClass.forEach((function(e){"string"==typeof e&&this.modal.classList.add(e)}),this),-1!==this.opts.closeMethods.indexOf("button")&&(this.modalCloseBtn=document.createElement("button"),this.modalCloseBtn.type="button",this.modalCloseBtn.classList.add("tingle-modal__close"),this.modalCloseBtnIcon=document.createElement("span"),this.modalCloseBtnIcon.classList.add("tingle-modal__closeIcon"),this.modalCloseBtnIcon.innerHTML='<svg viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><path d="M.3 9.7c.2.2.4.3.7.3.3 0 .5-.1.7-.3L5 6.4l3.3 3.3c.2.2.5.3.7.3.2 0 .5-.1.7-.3.4-.4.4-1 0-1.4L6.4 5l3.3-3.3c.4-.4.4-1 0-1.4-.4-.4-1-.4-1.4 0L5 3.6 1.7.3C1.3-.1.7-.1.3.3c-.4.4-.4 1 0 1.4L3.6 5 .3 8.3c-.4.4-.4 1 0 1.4z" fill="#000" fill-rule="nonzero"/></svg>',this.modalCloseBtnLabel=document.createElement("span"),this.modalCloseBtnLabel.classList.add("tingle-modal__closeLabel"),this.modalCloseBtnLabel.innerHTML=this.opts.closeLabel,this.modalCloseBtn.appendChild(this.modalCloseBtnIcon),this.modalCloseBtn.appendChild(this.modalCloseBtnLabel)),this.modalBox=document.createElement("div"),this.modalBox.classList.add("tingle-modal-box"),this.modalBoxContent=document.createElement("div"),this.modalBoxContent.classList.add("tingle-modal-box__content"),this.modalBox.appendChild(this.modalBoxContent),-1!==this.opts.closeMethods.indexOf("button")&&this.modal.appendChild(this.modalCloseBtn),this.modal.appendChild(this.modalBox)}.call(this),function(){this._events={clickCloseBtn:this.close.bind(this),clickOverlay:function(e){var t=this.modal.offsetWidth-this.modal.clientWidth,o=e.clientX>=this.modal.offsetWidth-15,n=this.modal.scrollHeight!==this.modal.offsetHeight;"MacIntel"===navigator.platform&&0==t&&o&&n||-1!==this.opts.closeMethods.indexOf("overlay")&&!function(e,t){for(;(e=e.parentElement)&&!e.classList.contains("tingle-modal"););return e}(e.target)&&e.clientX<this.modal.clientWidth&&this.close()}.bind(this),resize:this.checkOverflow.bind(this),keyboardNav:function(e){-1!==this.opts.closeMethods.indexOf("escape")&&27===e.which&&this.isOpen()&&this.close()}.bind(this)},-1!==this.opts.closeMethods.indexOf("button")&&this.modalCloseBtn.addEventListener("click",this._events.clickCloseBtn),this.modal.addEventListener("mousedown",this._events.clickOverlay),window.addEventListener("resize",this._events.resize),document.addEventListener("keydown",this._events.keyboardNav)}.call(this),document.body.appendChild(this.modal,document.body.firstChild),this.opts.footer&&this.addFooter(),this},t.prototype._busy=function(t){e=t},t.prototype._isBusy=function(){return e},t.prototype.destroy=function(){null!==this.modal&&(this.isOpen()&&this.close(!0),function(){-1!==this.opts.closeMethods.indexOf("button")&&this.modalCloseBtn.removeEventListener("click",this._events.clickCloseBtn),this.modal.removeEventListener("mousedown",this._events.clickOverlay),window.removeEventListener("resize",this._events.resize),document.removeEventListener("keydown",this._events.keyboardNav)}.call(this),this.modal.parentNode.removeChild(this.modal),this.modal=null)},t.prototype.isOpen=function(){return!!this.modal.classList.contains("tingle-modal--visible")},t.prototype.open=function(){if(!this._isBusy()){this._busy(!0);var e=this;return"function"==typeof e.opts.beforeOpen&&e.opts.beforeOpen(),this.modal.style.removeProperty?this.modal.style.removeProperty("display"):this.modal.style.removeAttribute("display"),document.getSelection().removeAllRanges(),this._scrollPosition=window.pageYOffset,document.body.classList.add("tingle-enabled"),document.body.style.top=-this._scrollPosition+"px",this.setStickyFooter(this.opts.stickyFooter),this.modal.classList.add("tingle-modal--visible"),"function"==typeof e.opts.onOpen&&e.opts.onOpen.call(e),e._busy(!1),this.checkOverflow(),this}},t.prototype.close=function(e){if(!this._isBusy()){if(this._busy(!0),"function"==typeof this.opts.beforeClose&&!this.opts.beforeClose.call(this))return void this._busy(!1);document.body.classList.remove("tingle-enabled"),document.body.style.top=null,window.scrollTo({top:this._scrollPosition,behavior:"instant"}),this.modal.classList.remove("tingle-modal--visible");var t=this;t.modal.style.display="none","function"==typeof t.opts.onClose&&t.opts.onClose.call(this),t._busy(!1)}},t.prototype.setContent=function(e){return"string"==typeof e?this.modalBoxContent.innerHTML=e:(this.modalBoxContent.innerHTML="",this.modalBoxContent.appendChild(e)),this.isOpen()&&this.checkOverflow(),this},t.prototype.getContent=function(){return this.modalBoxContent},t.prototype.addFooter=function(){return function(){this.modalBoxFooter=document.createElement("div"),this.modalBoxFooter.classList.add("tingle-modal-box__footer"),this.modalBox.appendChild(this.modalBoxFooter)}.call(this),this},t.prototype.setFooterContent=function(e){return this.modalBoxFooter.innerHTML=e,this},t.prototype.getFooterContent=function(){return this.modalBoxFooter},t.prototype.setStickyFooter=function(e){return this.isOverflow()||(e=!1),e?this.modalBox.contains(this.modalBoxFooter)&&(this.modalBox.removeChild(this.modalBoxFooter),this.modal.appendChild(this.modalBoxFooter),this.modalBoxFooter.classList.add("tingle-modal-box__footer--sticky"),o.call(this),this.modalBoxContent.style["padding-bottom"]=this.modalBoxFooter.clientHeight+20+"px"):this.modalBoxFooter&&(this.modalBox.contains(this.modalBoxFooter)||(this.modal.removeChild(this.modalBoxFooter),this.modalBox.appendChild(this.modalBoxFooter),this.modalBoxFooter.style.width="auto",this.modalBoxFooter.style.left="",this.modalBoxContent.style["padding-bottom"]="",this.modalBoxFooter.classList.remove("tingle-modal-box__footer--sticky"))),this},t.prototype.addFooterBtn=function(e,t,o){var n=document.createElement("button");return n.innerHTML=e,n.addEventListener("click",o),"string"==typeof t&&t.length&&t.split(" ").forEach((function(e){n.classList.add(e)})),this.modalBoxFooter.appendChild(n),n},t.prototype.resize=function(){console.warn("Resize is deprecated and will be removed in version 1.0")},t.prototype.isOverflow=function(){return window.innerHeight<=this.modalBox.clientHeight},t.prototype.checkOverflow=function(){this.modal.classList.contains("tingle-modal--visible")&&(this.isOverflow()?this.modal.classList.add("tingle-modal--overflow"):this.modal.classList.remove("tingle-modal--overflow"),!this.isOverflow()&&this.opts.stickyFooter?this.setStickyFooter(!1):this.isOverflow()&&this.opts.stickyFooter&&(o.call(this),this.setStickyFooter(!0)))},{modal:t}},void 0===(i=n.call(t,o,t,e))||(e.exports=i)}},t={};function o(n){var i=t[n];if(void 0!==i)return i.exports;var r=t[n]={exports:{}};return e[n].call(r.exports,r,r.exports,o),r.exports}o.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return o.d(t,{a:t}),t},o.d=(e,t)=>{for(var n in t)o.o(t,n)&&!o.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},o.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";var e=["input","select","textarea","a[href]","button","[tabindex]","audio[controls]","video[controls]",'[contenteditable]:not([contenteditable="false"])',"details>summary:first-of-type","details"],t=e.join(","),n="undefined"==typeof Element?function(){}:Element.prototype.matches||Element.prototype.msMatchesSelector||Element.prototype.webkitMatchesSelector,i=function(e){var t=parseInt(e.getAttribute("tabindex"),10);return isNaN(t)?function(e){return"true"===e.contentEditable}(e)?0:"AUDIO"!==e.nodeName&&"VIDEO"!==e.nodeName&&"DETAILS"!==e.nodeName||null!==e.getAttribute("tabindex")?e.tabIndex:0:t},r=function(e,t){return e.tabIndex===t.tabIndex?e.documentOrder-t.documentOrder:e.tabIndex-t.tabIndex},a=function(e){return"INPUT"===e.tagName},s=function(e,t){return!(t.disabled||function(e){return a(e)&&"hidden"===e.type}(t)||function(e,t){if("hidden"===getComputedStyle(e).visibility)return!0;var o=n.call(e,"details>summary:first-of-type")?e.parentElement:e;if(n.call(o,"details:not([open]) *"))return!0;if(t&&"full"!==t){if("non-zero-area"===t){var i=e.getBoundingClientRect(),r=i.width,a=i.height;return 0===r&&0===a}}else for(;e;){if("none"===getComputedStyle(e).display)return!0;e=e.parentElement}return!1}(t,e.displayCheck)||function(e){return"DETAILS"===e.tagName&&Array.prototype.slice.apply(e.children).some((function(e){return"SUMMARY"===e.tagName}))}(t)||function(e){if(a(e)||"SELECT"===e.tagName||"TEXTAREA"===e.tagName||"BUTTON"===e.tagName)for(var t=e.parentElement;t;){if("FIELDSET"===t.tagName&&t.disabled){for(var o=0;o<t.children.length;o++){var n=t.children.item(o);if("LEGEND"===n.tagName)return!n.contains(e)}return!0}t=t.parentElement}return!1}(t))},l=function(e,t){return!(!s(e,t)||function(e){return function(e){return a(e)&&"radio"===e.type}(e)&&!function(e){if(!e.name)return!0;var t,o=e.form||e.ownerDocument,n=function(e){return o.querySelectorAll('input[type="radio"][name="'+e+'"]')};if("undefined"!=typeof window&&void 0!==window.CSS&&"function"==typeof window.CSS.escape)t=n(window.CSS.escape(e.name));else try{t=n(e.name)}catch(e){return console.error("Looks like you have a radio button with a name attribute containing invalid CSS selector characters and need the CSS.escape polyfill: %s",e.message),!1}var i=function(e,t){for(var o=0;o<e.length;o++)if(e[o].checked&&e[o].form===t)return e[o]}(t,e.form);return!i||i===e}(e)}(t)||i(t)<0)},d=e.concat("iframe").join(","),c=function(e,t){if(t=t||{},!e)throw new Error("No node provided");return!1!==n.call(e,d)&&s(t,e)};function f(e,t){var o=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),o.push.apply(o,n)}return o}function u(e,t,o){return t in e?Object.defineProperty(e,t,{value:o,enumerable:!0,configurable:!0,writable:!0}):e[t]=o,e}var m,p=(m=[],{activateTrap:function(e){if(m.length>0){var t=m[m.length-1];t!==e&&t.pause()}var o=m.indexOf(e);-1===o||m.splice(o,1),m.push(e)},deactivateTrap:function(e){var t=m.indexOf(e);-1!==t&&m.splice(t,1),m.length>0&&m[m.length-1].unpause()}}),g=function(e){return setTimeout(e,0)},h=function(e,t){var o=-1;return e.every((function(e,n){return!t(e)||(o=n,!1)})),o},v=function(e){for(var t=arguments.length,o=new Array(t>1?t-1:0),n=1;n<t;n++)o[n-1]=arguments[n];return"function"==typeof e?e.apply(void 0,o):e},b=function(e){return e.target.shadowRoot&&"function"==typeof e.composedPath?e.composedPath()[0]:e.target},_=function(e,o){var a,s=(null==o?void 0:o.document)||document,d=function(e){for(var t=1;t<arguments.length;t++){var o=null!=arguments[t]?arguments[t]:{};t%2?f(Object(o),!0).forEach((function(t){u(e,t,o[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(o)):f(Object(o)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(o,t))}))}return e}({returnFocusOnDeactivate:!0,escapeDeactivates:!0,delayInitialFocus:!0},o),m={containers:[],tabbableGroups:[],nodeFocusedBeforeActivation:null,mostRecentlyFocusedNode:null,active:!1,paused:!1,delayInitialFocusTimer:void 0},_=function(e,t,o){return e&&void 0!==e[t]?e[t]:d[o||t]},y=function(e){return!(!e||!m.containers.some((function(t){return t.contains(e)})))},w=function(e){var t=d[e];if("function"==typeof t){for(var o=arguments.length,n=new Array(o>1?o-1:0),i=1;i<o;i++)n[i-1]=arguments[i];t=t.apply(void 0,n)}if(!t){if(void 0===t||!1===t)return t;throw new Error("`".concat(e,"` was specified but was not a node, or did not return a node"))}var r=t;if("string"==typeof t&&!(r=s.querySelector(t)))throw new Error("`".concat(e,"` as selector refers to no known node"));return r},F=function(){var e=w("initialFocus");if(!1===e)return!1;if(void 0===e)if(y(s.activeElement))e=s.activeElement;else{var t=m.tabbableGroups[0];e=t&&t.firstTabbableNode||w("fallbackFocus")}if(!e)throw new Error("Your focus-trap needs to have at least one focusable element");return e},C=function(){if(m.tabbableGroups=m.containers.map((function(e){var o,a,s,d,c,f,u,m=(a=[],s=[],(d=e,c=(o=o||{}).includeContainer,f=l.bind(null,o),u=Array.prototype.slice.apply(d.querySelectorAll(t)),c&&n.call(d,t)&&u.unshift(d),u.filter(f)).forEach((function(e,t){var o=i(e);0===o?a.push(e):s.push({documentOrder:t,tabIndex:o,node:e})})),s.sort(r).map((function(e){return e.node})).concat(a));if(m.length>0)return{container:e,firstTabbableNode:m[0],lastTabbableNode:m[m.length-1]}})).filter((function(e){return!!e})),m.tabbableGroups.length<=0&&!w("fallbackFocus"))throw new Error("Your focus-trap must have at least one container with at least one tabbable node in it at all times")},I=function e(t){!1!==t&&t!==s.activeElement&&(t&&t.focus?(t.focus({preventScroll:!!d.preventScroll}),m.mostRecentlyFocusedNode=t,function(e){return e.tagName&&"input"===e.tagName.toLowerCase()&&"function"==typeof e.select}(t)&&t.select()):e(F()))},x=function(e){var t=w("setReturnFocus",e);return t||!1!==t&&e},E=function(e){var t=b(e);y(t)||(v(d.clickOutsideDeactivates,e)?a.deactivate({returnFocus:d.returnFocusOnDeactivate&&!c(t)}):v(d.allowOutsideClick,e)||e.preventDefault())},k=function(e){var t=b(e),o=y(t);o||t instanceof Document?o&&(m.mostRecentlyFocusedNode=t):(e.stopImmediatePropagation(),I(m.mostRecentlyFocusedNode||F()))},B=function(e){if(function(e){return"Escape"===e.key||"Esc"===e.key||27===e.keyCode}(e)&&!1!==v(d.escapeDeactivates,e))return e.preventDefault(),void a.deactivate();(function(e){return"Tab"===e.key||9===e.keyCode})(e)&&function(e){var t=b(e);C();var o=null;if(m.tabbableGroups.length>0){var n=h(m.tabbableGroups,(function(e){return e.container.contains(t)}));if(n<0)o=e.shiftKey?m.tabbableGroups[m.tabbableGroups.length-1].lastTabbableNode:m.tabbableGroups[0].firstTabbableNode;else if(e.shiftKey){var i=h(m.tabbableGroups,(function(e){var o=e.firstTabbableNode;return t===o}));if(i<0&&m.tabbableGroups[n].container===t&&(i=n),i>=0){var r=0===i?m.tabbableGroups.length-1:i-1;o=m.tabbableGroups[r].lastTabbableNode}}else{var a=h(m.tabbableGroups,(function(e){var o=e.lastTabbableNode;return t===o}));if(a<0&&m.tabbableGroups[n].container===t&&(a=n),a>=0){var s=a===m.tabbableGroups.length-1?0:a+1;o=m.tabbableGroups[s].firstTabbableNode}}}else o=w("fallbackFocus");o&&(e.preventDefault(),I(o))}(e)},O=function(e){if(!v(d.clickOutsideDeactivates,e)){var t=b(e);y(t)||v(d.allowOutsideClick,e)||(e.preventDefault(),e.stopImmediatePropagation())}},M=function(){if(m.active)return p.activateTrap(a),m.delayInitialFocusTimer=d.delayInitialFocus?g((function(){I(F())})):I(F()),s.addEventListener("focusin",k,!0),s.addEventListener("mousedown",E,{capture:!0,passive:!1}),s.addEventListener("touchstart",E,{capture:!0,passive:!1}),s.addEventListener("click",O,{capture:!0,passive:!1}),s.addEventListener("keydown",B,{capture:!0,passive:!1}),a},L=function(){if(m.active)return s.removeEventListener("focusin",k,!0),s.removeEventListener("mousedown",E,!0),s.removeEventListener("touchstart",E,!0),s.removeEventListener("click",O,!0),s.removeEventListener("keydown",B,!0),a};return(a={activate:function(e){if(m.active)return this;var t=_(e,"onActivate"),o=_(e,"onPostActivate"),n=_(e,"checkCanFocusTrap");n||C(),m.active=!0,m.paused=!1,m.nodeFocusedBeforeActivation=s.activeElement,t&&t();var i=function(){n&&C(),M(),o&&o()};return n?(n(m.containers.concat()).then(i,i),this):(i(),this)},deactivate:function(e){if(!m.active)return this;clearTimeout(m.delayInitialFocusTimer),m.delayInitialFocusTimer=void 0,L(),m.active=!1,m.paused=!1,p.deactivateTrap(a);var t=_(e,"onDeactivate"),o=_(e,"onPostDeactivate"),n=_(e,"checkCanReturnFocus");t&&t();var i=_(e,"returnFocus","returnFocusOnDeactivate"),r=function(){g((function(){i&&I(x(m.nodeFocusedBeforeActivation)),o&&o()}))};return i&&n?(n(x(m.nodeFocusedBeforeActivation)).then(r,r),this):(r(),this)},pause:function(){return m.paused||!m.active||(m.paused=!0,L()),this},unpause:function(){return m.paused&&m.active?(m.paused=!1,C(),M(),this):this},updateContainerElements:function(e){var t=[].concat(e).filter(Boolean);return m.containers=t.map((function(e){return"string"==typeof e?s.querySelector(e):e})),m.active&&C(),this}}).updateContainerElements(e),a},y=o(584),w=o.n(y),F=window.ko;!function(e){String.prototype.gformFormat||(String.prototype.gformFormat=function(){var e=arguments;return this.replace(/{(\d+)}/g,(function(t,o){return void 0!==e[o]?e[o]:t}))}),window.GPNestedForms=function(n){var i=this;for(var r in n)n.hasOwnProperty(r)&&(i[r]=n[r]);i.destroy=function(){var t;null===(t=i.modal)||void 0===t||t.destroy(),e(document).off(".{0}".gformFormat(i.getNamespace())),window.gform.removeHook("action","gform_list_post_item_add",10,i.getNamespace()),window.gform.removeHook("action","gform_list_post_item_delete",10,i.getNamespace()),gform.removeFilter("gform_calculation_formula",10,"gpnf_{0}_{1}".gformFormat(i.formId,i.fieldId))},i.init=function(){if(i.id=i.getDebugId(),i.$fieldContainer=e("#field_{0}_{1}".gformFormat(i.formId,i.fieldId)),i.$parentFormContainer=i.$fieldContainer.parents("form").first(),i.$modalSource=e(".gpnf-nested-form-{0}-{1}".gformFormat(i.formId,i.fieldId)),i.isActive=!1,i.initialized=!1,void 0!==window["GPNestedForms_{0}_{1}".gformFormat(i.formId,i.fieldId)]){var t=window["GPNestedForms_{0}_{1}".gformFormat(i.formId,i.fieldId)];i.entries=t.entries,t.destroy()}i.initKnockout(),i.initCalculations(),window["GPNestedForms_{0}_{1}".gformFormat(i.formId,i.fieldId)]=i,i.finalizeInit()},i.inHiddenPage=function(){var t=e("#gform_source_page_number_{0}".gformFormat(i.formId)).val(),o=i.$parentFormContainer.find("#gform_page_{0}_{1}".gformFormat(i.formId,t));return!(!o.length||o.find(i.$fieldContainer).length)},i.finalizeInit=function(){if(i.inHiddenPage())console.debug("Nested form is not visible. Skipping loading.");else{var t=i.initSession();e(document).on("click.{0}".gformFormat(i.getNamespace()),"#field_{0}_{1} .gpnf-add-entry".gformFormat(i.formId,i.fieldId),i.openAddModal),i.initModal(),i.addColorStyles(),gform.applyFilters("gpnf_fetch_form_html_on_load",!0,i.formId,i.fieldId,i)&&t.always(i.getFormHtml),i.initialized=!0}},i.initSession=function(){return void 0===window["gpnfSessionPromise_"+i.formId]&&(window["gpnfSessionPromise_"+i.formId]=e.post(i.ajaxUrl,i.sessionData,(function(e){gform.doAction("gpnf_session_initialized",i)}))),window["gpnfSessionPromise_"+i.formId]},i.initModal=function(){i.modalArgs=gform.applyFilters("gpnf_modal_args",{labels:i.modalLabels,closeLabel:i.modalLabels.closeScreenReaderLabel,colors:i.modalColors,footer:!0,stickyFooter:i.modalStickyFooter,closeMethods:["button"],cssClass:[i.modalClass,"gpnf-modal","gpnf-modal-{0}-{1}".gformFormat(i.formId,i.fieldId)],onOpen:function(){i.isActive=!0,!i.trap&&i.enableFocusTrap&&(i.trap=_(i.modal.modalBox.parentElement,{escapeDeactivates:!1,allowOutsideClick:!0})),i.trap&&i.trap.activate()},onClose:function(){i.clearModalContent(),i.setParentFocus(),i.trap&&i.trap.deactivate(),i.isActive=!1},beforeOpen:function(){i.$modal.attr("role","dialog").attr("aria-label",i.getModalTitle()).attr("aria-modal","true"),i.$modal.find(".tingle-modal__close").attr("aria-label",i.modalLabels.closeScreenReaderLabel)},beforeClose:function(){return!0}},i.formId,i.fieldId,i),i.modal?i.$modal=e(i.modal.modal):(i.modal=new(w().modal)(i.modalArgs),i.$modal=e(i.modal.modal),i.bindResizeEvents(),e(document).on("gpnf_post_render.{0}".gformFormat(i.getNamespace()),(function(t,o,n){var r=e("#gform_wrapper_"+o);o==i.nestedFormId&&r.length>0&&i.isActive&&(i.scrollToTop(),n&&(i.initFormScripts(n),setTimeout((function(){var e=i.$modal.find("input, select, textarea").filter(":visible").first();if(e.hasClass("datepicker")){var t=e.prop("disabled");null==e||e.datepicker("disable"),t||e.prop("disabled",!1)}e.focus(),e.hasClass("datepicker")&&(null==e||e.datepicker("enable"))})),i.addModalButtons(),i.observeDefaultButtons(),i.triggerButtonAnimationOnSubmit()))})))},i.initKnockout=function(){F.dataFor(i.$fieldContainer[0])?F.dataFor(i.$fieldContainer[0]).entries(i.prepareEntriesForKnockout(i.entries)):(i.viewModel=new t(i.prepareEntriesForKnockout(i.entries),i),i.addRowIdComputedToEntries(i.viewModel.entries),F.applyBindings(i.viewModel,i.$fieldContainer[0]))},i.initCalculations=function(){gform.addFilter("gform_calculation_formula",i.parseCalcs,10,"gpnf_{0}_{1}".gformFormat(i.formId,i.fieldId)),i.runCalc(i.formId)},i.openAddModal=function(t){t.preventDefault(),t.target.disabled=!0;var n=new o(t.target,i.spinnerUrl,"");i.getFormHtml().done((function(o){i.setModalContent(o),i.openModal(e(t.target))})).always((function(){n.destroy(),t.target.disabled=!1}))},i.openModal=function(t){window.gform.applyFilters("gpnf_should_open_modal",!0,i.modal,i.formId,i.fieldId,i)&&(i.saveParentFocus(t),i.modal.open(),(i.isGF25||parseInt(jQuery.fn.jquery)<3)&&e(document).trigger("gpnf_post_render",[i.nestedFormId,"1"]),i.initIframe(i.nestedFormId))},i.saveParentFocus=function(e){i.parentFocus=e},i.setParentFocus=function(){var e;switch(typeof i.parentFocus){case"undefined":return;case"function":e=i.parentFocus.apply();break;default:e=i.parentFocus}e.focus()},i.scrollToTop=function(){var t=e(i.modal.modal)[0];t.scroll?t.scroll({top:0,left:0,behavior:"smooth"}):t.scrollTop=0},i.observeDefaultButtons=function(){var t=i.getDefaultButtonObserver();i.getDefaultButtons().each((function(){t.observe(e(this)[0],{attributes:!0,childList:!0})}))},i.getDefaultButtonObserver=function(){return new MutationObserver((function(e){e.forEach((function(e){"attributes"!=e.type||"style"!=e.attributeName&&"disabled"!=e.attributeName||i.addModalButtons()}))}))},i.triggerButtonAnimationOnSubmit=function(){i.$modal.on("keypress",(function(e){i.$modal.data("gpnfEnterPressed",13===e.which)})),i.$modal.find("form").on("submit",(function(){i.$modal.data("gpnfEnterPressed")&&(i.$modal.data("gpnfEnterPressed",!1),e(i.modal.modalBoxFooter).find(".gpnf-btn-submit, .gpnf-btn-next").addClass("gpnf-spinner"))}))},i.setModalContent=function(t,o){e(document).off("gform_post_render.gpnf"),i.setMode(void 0===o?"add":"edit"),e(i.modal.modalBoxContent).html(void 0!==t?t:i.formHtml).prepend('<div class="gpnf-modal-header" style="background-color:{1}">{0}</div>'.gformFormat(i.getModalTitle(),i.modalHeaderColor)),i.$modal.find('input[name="gpnf_nested_form_field_id"]').val(i.fieldId),i.addModalButtons(),i.stashFormData();var n=i.getDefaultButtonObserver();i.getDefaultButtons().each((function(){n.observe(e(this)[0],{attributes:!0,childList:!0})}))},i.clearModalContent=function(){e(i.modal.modalBoxContent).html("")},i.setMode=function(e){i.mode=e},i.getMode=function(){return i.mode?i.mode:"add"},i.getModalTitle=function(){return"add"===i.getMode()?i.modalArgs.labels.title:i.modalArgs.labels.editTitle},i.hasPendingUploads=function(){var t=!1;return!(!gfMultiFileUploader||!gfMultiFileUploader.uploaders)&&(e.each(gfMultiFileUploader.uploaders,(function(e,o){if(o.total.queued>0)return t=!0,!1})),t)},i.addModalButtons=function(){i.modal.modalBoxFooter.innerHTML="";var t=window.gform.applyFilters("gpnf_modal_button_css_classes","tingle-btn tingle-btn--default gpnf-btn-cancel","cancel",i.formId,i.fieldId,i);i.modal.addFooterBtn(i.modalArgs.labels.cancel,t,(function(){i.handleCancelClick(e(this))})),i.getDefaultButtons().each((function(){var t=e(this),o=null,n="function"==typeof window.jQuery.fn.wc_gravity_form;if("none"!==t[0].style.display||n&&""===t[0].style.display){var r="submit"===t.attr("type")||"image"===t.attr("type")?i.getSubmitButtonLabel():t.val(),a=["tingle-btn","tingle-btn--primary"],s=t.is(":disabled");t.hasClass("gform_previous_button")?(a.push("gpnf-btn-previous"),o="previous"):t.hasClass("gform_next_button")?(o="next",a.push("gpnf-btn-next")):(o="submit",a.push("gpnf-btn-submit")),a=window.gform.applyFilters("gpnf_modal_button_css_classes",a.join(" "),o,i.formId,i.fieldId,i);var l=i.modal.addFooterBtn(r,a,(function(o){if(i.hasPendingUploads()){var n="undefined"!=typeof gform_gravityforms?gform_gravityforms.strings:{};alert(n.currently_uploading)}else e(o.target).addClass("gpnf-spinner"),t.click()}));s&&e(l).prop("disabled",!0)}}));var o=window.gform.applyFilters("gpnf_modal_button_css_classes","tingle-btn tingle-btn--default gpnf-btn-cancel-mobile","cancel-mobile",i.formId,i.fieldId,i);if(i.modal.addFooterBtn(i.modalArgs.labels.cancel,o,(function(){i.handleCancelClick(e(this))})),"edit"==i.mode&&e(i.modal.modalBoxContent).find(".gform_wrapper").length>0){var n=window.gform.applyFilters("gpnf_modal_button_css_classes","tingle-btn tingle-btn--danger tingle-btn--pull-left gpnf-btn-delete","delete",i.formId,i.fieldId,i);i.modal.addFooterBtn(i.modalArgs.labels.delete,n,(function(){var t=e(this),o=!1!==i.modalArgs.labels.confirmAction&&""!==i.modalArgs.labels.confirmAction;!t.data("isConfirming")&&o?(t.data("isConfirming",!0).text(i.modalArgs.labels.confirmAction),setTimeout((function(){t.data("isConfirming",!1).text(i.modalArgs.labels.delete)}),3e3)):(i.getEntryRow(i.getCurrentEntryId()).find(".delete-button").click(),i.modal.close())}))}},i.getSubmitButtonLabel=function(){var e=i.getMode();return"add"===e&&i.modalArgs.labels.submit?i.modalArgs.labels.submit:"edit"===e&&i.modalArgs.labels.editSubmit?i.modalArgs.labels.editSubmit:i.getModalTitle()},i.addColorStyles=function(){i.$style&&"function"==typeof i.$style.remove&&i.$style.remove(),i.$style='<style type="text/css"> \t\t\t\t\t.gpnf-modal-{0}-{1} .tingle-btn--primary { background-color: {2}; } \t\t\t\t\t.gpnf-modal-{0}-{1} .tingle-btn--default { background-color: {3}; } \t\t\t\t\t.gpnf-modal-{0}-{1} .tingle-btn--danger { background-color: {4}; } \t\t\t\t</style>'.gformFormat(i.formId,i.fieldId,i.modalArgs.colors.primary,i.modalArgs.colors.secondary,i.modalArgs.colors.danger),e("head").append(i.$style)},i.getDefaultButtons=function(){return e("#gform_page_{0}_{1} .gform_page_footer, #gform_{0} .gform_footer, #gform_{0} .gfield--type-submit".gformFormat(i.nestedFormId,i.getCurrentPage())).first().find('input[type="button"], input[type="submit"], input[type="image"], button')},i.handleCancelClick=function(e){var t=window.gform.applyFilters("gpnf_disable_new_cancel_confirmation",!1===i.modalArgs.labels.confirmAction||""===i.modalArgs.labels.confirmAction);e.data("isConfirming")?i.modal.close():i.hasChanges()&&!t?(e.data("isConfirming",!0).removeClass("tingle-btn--default").addClass("tingle-btn--danger").text(i.modalArgs.labels.confirmAction),setTimeout((function(){e.data("isConfirming",!1).removeClass("tingle-btn--danger").addClass("tingle-btn--default").text(i.modalArgs.labels.cancel)}),3e3)):i.modal.close()},i.setMode=function(e){i.mode=e},i.getMode=function(){return i.mode?i.mode:"add"},i.stashFormData=function(){i.formData=i.$modal.find("form").serialize()},i.hasChanges=function(){return i.$modal.find("form").serialize()!==i.formData},i.bindResizeEvents=function(){e(document).on("gpnf_post_render.{0}".gformFormat(i.getNamespace()),(function(){setTimeout((function(){i.modal.checkOverflow()}),0)})),e(document).on("gform_post_conditional_logic.{0}".gformFormat(i.getNamespace()),(function(e,t){i.nestedFormId==t&&i.modal.checkOverflow()})),gform.addAction("gform_list_post_item_add",i.modal.checkOverflow.bind(i.modal),10,i.getNamespace()),gform.addAction("gform_list_post_item_delete",i.modal.checkOverflow.bind(i.modal),10,i.getNamespace())},i.isBound=function(e){return!!F.dataFor(e)},i.prepareEntriesForKnockout=function(e){for(var t=0;t<e.length;t++)e[t]=i.prepareEntryForKnockout(e[t]);return e},i.addRowIdComputedToEntries=function(e){for(var t=0;t<e().length;t++)e.splice(t,1,i.addRowIdComputedToEntry(e()[t]));return e},i.prepareEntryForKnockout=function(t){var o=e.extend({},t);for(var n in o)if(t.hasOwnProperty(n)){var r=t[n];!1===r.label&&(r.label=""),t["f"+n]=r}return i.addRowIdComputedToEntry(t)},i.addRowIdComputedToEntry=function(t){var o=F.computed((function(){var o=e.map(i.viewModel?i.viewModel.entries():i.entries,(function(e){return parseInt(e.id)})),n=o.indexOf(parseInt(t.id))+1;return window.gform.applyFilters("gpnf_row_id_value",n,t,i)}),i.viewModel),n={label:o,value:o};return t.row_id=n,t.frow_id=n,t},i.refreshMarkup=function(){return e.post(i.ajaxUrl,{action:"gpnf_refresh_markup",nonce:GPNFData.nonces.refreshMarkup,gpnf_parent_form_id:i.formId,gpnf_nested_form_field_id:i.fieldId,gpnf_context:i.ajaxContext},(function(e){i.formHtml=e}))},i.editEntry=function(t,n){var r=new o(n,i.spinnerUrl,"");n.css({visibility:"hidden"}),e.post(i.ajaxUrl,{action:"gpnf_edit_entry",nonce:GPNFData.nonces.editEntry,gpnf_entry_id:t,gpnf_parent_form_id:i.formId,gpnf_nested_form_field_id:i.fieldId,gpnf_context:i.ajaxContext},(function(e){r.destroy(),n.css({visibility:"visible"}),i.setModalContent(e,"edit"),i.openModal((function(){return i.$parentFormContainer.find('[data-entryid="'+t+'"]').find(".edit").find("a, button")}))}))},i.deleteEntry=function(t,n,r){if(!1!==window.gform.applyFilters("gpnf_should_delete",!0,t,n,i)){r=e.extend({},{showSpinner:!0},r);var a=null;r.showSpinner&&(a=new o(n,i.spinnerUrl,"")),n.css({visibility:"hidden"}),e.post(i.ajaxUrl,{action:"gpnf_delete_entry",nonce:GPNFData.nonces.deleteEntry,gpnf_entry_id:t.id},(function(e){a&&a.destroy(),n.css({visibility:"visible"}),e?e.success?(i.viewModel.entries.remove((function(e){return e.id===t.id||e===t})),window.gform.applyFilters("gpnf_fetch_form_html_after_delete",!0,i.formId,i.fieldId,i)&&i.refreshMarkup()):console.log("Error:"+e.data):console.log("Error: no response.")}))}},i.duplicateEntry=function(t,n){var r=new o(n,i.spinnerUrl,"");n.css({visibility:"hidden"}),e.post(i.ajaxUrl,{action:"gpnf_duplicate_entry",nonce:GPNFData.nonces.duplicateEntry,gpnf_entry_id:t,gpnf_parent_form_id:i.formId,gpnf_nested_form_field_id:i.fieldId},(function(e){r.destroy(),n.css({visibility:"visible"}),e.success&&GPNestedForms.loadEntry(e.data),gform.doAction("gpnf_post_duplicate_entry",e.data.entry,e)}))},i.getFormHtml=function(){return i.formHtml?e.when(i.formHtml):i.refreshMarkup()},i.initFormScripts=function(t){window.gform.doAction("gpnf_init_nested_form",i.nestedFormId,i),e(document).trigger("gform_post_render",[i.nestedFormId,t]),window.gformInitDatepicker&&(i.trap&&gform.addFilter("gform_datepicker_options_pre_init",(function(e,t){if(t!=i.nestedFormId)return e;var o=e.beforeShow;e.beforeShow=function(e,t){o(e,t),i.trap.deactivate({returnFocus:!1})};var n=e.onClose;return e.onClose=function(e,t){n(e,t),i.trap.activate()},e})),i.$modal.find(".datepicker").each((function(){e(this).removeClass("hasDatepicker"),gformInitSingleDatepicker(e(this))}))),i.handleParentMergeTag(),gform.addAction("gform_post_conditional_logic_field_action",(function(e,t,o,n,r){if(i.nestedFormId==e){var a=gf_get_input_id_by_html_id(o);i.handleParentMergeTag([a])}}))},i.runCalc=function(){e(document).trigger("gform_post_conditional_logic",[i.formId,[],!1])},i.parseCalcs=function(t,o,n,r){if(n!=i.formId)return t;var a=getMatchGroups(t,/{[^{]*?:([0-9]+):(sum|total|count|set)=?([0-9]*)}/i);return e.each(a,(function(e,n){var r=n[0],a=n[1],s=n[2],l=n[3],d=0;if(a==i.fieldId){if(gformIsHidden(i.$fieldContainer.find("> :first-child")))return 0;var c=window.gform.applyFilters("gpnf_calc_entries",i.viewModel.entries(),{search:r,nestedFormFieldId:a,func:s,targetFieldId:l},i.fieldId,i.formId,i,o);switch(s){case"sum":var f=0;c.forEach((function(e){var t=0;void 0!==e[l]&&(t=e[l].value?gformToNumber(e[l].value):0),f+=parseFloat(t)})),d=f;break;case"total":f=0,c.forEach((function(e){f+=parseFloat(e.total)})),d=f;break;case"count":d=c.length;break;case"set":var u=[];c.forEach((function(e){var t=0;void 0!==e[l]&&e[l].value&&(t=gformToNumber(e[l].value)),u.push(t)})),d=u.join(", ")}d=window.gform.applyFilters("gpnf_calc_replacement_value",d,{search:r,nestedFormFieldId:a,func:s,targetFieldId:l},c,i.fieldId,i.formId,i,o),t=t.replace(r,d)}})),t},i.handleParentMergeTag=function(t){var o;if(void 0!==t){var n=[];e.each(t,(function(e,t){n.push("#field_{0}_{1}".gformFormat(i.nestedFormId,t))})),o=i.$modal.find(n.join(",")).find(":input")}else o=i.$modal.find(":input");o.each((function(){var t,o,n,r=e(this).data("gpnf-value");if(!r)return!0;function a(t){var o=e(t);r=o.val();var n=i.getParentMergeTags(r);if(n.length){for(var a=0,s=n.length;a<s;a++)r=r.replace(n[a][0],"");o.val(r).change().trigger("chosen:updated")}}if(0!==i.$modal.find(".gform_validation_error").length)return a(this),!0;var s=i.getParentMergeTags(r);if(!s)return!0;for(var l=0;l<s.length;l++){var d=s[l][1];if(isNaN(d))return!0;var c=i.$parentFormContainer.find("#input_"+i.formId+"_"+d.split(".").join("_"));(c.hasClass("gfield_radio")||c.hasClass("gfield_checkbox"))&&(c=c.find("input:checked"));var f=[];-1!==s[l][0].indexOf(":label")?c.each((function(){var t=e(this);t.hasClass("gfield_select")?f.push(t.find("option:selected").text()):f.push(t.parent().find("label").text())})):c.each((function(){f.push(e(this).val())})),f=f.join(", "),f=gform.applyFilters("gpnf_parent_merge_tag_value",c.length?f:"",d,i.formId,i),r=r.replace(s[l][0],f)}r=r.trim();var u=e(this).val();if("edit"===i.mode&&!gform.applyFilters("gpnf_replace_parent_merge_tag_on_edit",!1,i.formId))return a(this),e(this).siblings('input[name^="gwro_hidden_capture_"]').val(u),!0;if(e(this).siblings('input[name^="gwro_hidden_capture_"]').val(r),u!=r){e(this).val(r).change().trigger("chosen:updated").trigger("gpnfUpdatedFromParentMergeTag");var m=null===(t=e(this).prop("id"))||void 0===t?void 0:t.split("_"),p=null==m?void 0:m[2],g=null===(n=null===(o=null===window||void 0===window?void 0:window.tinyMCE)||void 0===o?void 0:o.editors)||void 0===n?void 0:n["input_".concat(i.nestedFormId,"_").concat(p)];g&&g.setContent(r)}}))},i.getParentMergeTags=function(e){for(var t=[],o=/{Parent:(\d+(\.\d+)?)[^\}]*}/i;o.test(e);){var n=t.length;t[n]=o.exec(e),e=e.replace(""+t[n][0],"")}return t},i.getCurrentPage=function(){var t=e("#gform_source_page_number_{0}".gformFormat(i.nestedFormId)).val();return Math.max(1,parseInt(t))},i.getCurrentEntryId=function(){return i.$modal.find('input[name="gpnf_entry_id"]').val()},i.getEntryRow=function(t){return e('.gpnf-nested-entries [data-entryid="'+t+'"]')},i.getDebugId=function(){return"xxxxxxxx".replace(/[xy]/g,(function(e){var t=16*Math.random()|0;return("x"==e?t:3&t|8).toString(16)}))},i.getNamespace=function(){return"gpnf-{0}-{1}".gformFormat(i.formId,i.fieldId)},i.initIframe=function(t){e("#gform_ajax_frame_{0}".gformFormat(t)).off("load").on("load",(function(){var o=e(this).contents().find("*").html();if(o.indexOf("GF_AJAX_POSTBACK")>=0){var n=e(this).contents().find("#gform_wrapper_{0}".gformFormat(t)),i=e(this).contents().find("#gform_confirmation_wrapper_{0}".gformFormat(t)).length>0,r=o.indexOf("gformRedirect(){")>=0,a=n.length>0&&!r&&!i,s=e("#gform_wrapper_{0}".gformFormat(t));if(a){s.html(n.html()),n.hasClass("gform_validation_error")?s.addClass("gform_validation_error"):s.removeClass("gform_validation_error"),setTimeout((function(){}),50),window.gformInitPriceFields&&gformInitPriceFields();var l=e("#gform_source_page_number_{0}".gformFormat(t)).val();e(document).trigger("gform_page_loaded",[t,l]),window["gf_submitting_{0}".gformFormat(t)]=!1}else if(!r){var d=e(this).contents().find(".GF_AJAX_POSTBACK").html();d||(d=o),setTimeout((function(){s.replaceWith(d),e(document).trigger("gform_confirmation_loaded",[t]),window["gf_submitting_{0}".gformFormat(t)]=!1}),50)}e(document).trigger("gpnf_post_render",[t,l])}}))},GPNestedForms.deleteEntry=function(e,t,o){i.deleteEntry(e,t,o)},GPNestedForms.loadEntry=function(e){var t=window["GPNestedForms_{0}_{1}".gformFormat(e.formId,e.fieldId)],o=!0,n=t.prepareEntryForKnockout(e.fieldValues);if(n.id=e.entryId,"edit"==e.mode){var r=i.getEntryRow(n.id).index();t.viewModel.entries.remove((function(e){return e.id==n.id})),t.viewModel.entries.splice(r,0,n)}else t.viewModel.entries.push(n),o=window.gform.applyFilters("gpnf_fetch_form_html_after_add",o,i.formId,i.fieldId,i);window.gform.applyFilters("gpnf_fetch_form_html_after_add_or_edit",o,i.formId,i.fieldId,i,e)&&t.refreshMarkup(),t.isActive&&t.modal.close()},i.init()};var t=function(t,o){var n=this;n.entries=F.observableArray(t),n.entries.subscribe((function(){o.$parentFormContainer.trigger("change")})),n.isMaxed=F.computed((function(){var e=gform.applyFilters("gpnf_entry_limit_max",o.entryLimitMax,o.formId,o.fieldId,o);return""!==e&&n.entries().length>=e})),n.entryIds=F.computed((function(){var t=[];return e.each(n.entries(),(function(e,o){t.push(o.id)})),t}),n),n.runCalc=F.computed((function(){return o.runCalc(),n.entries().length}),n),n.editEntry=function(t,n){o.editEntry(t.id,e(n.target))},n.deleteEntry=function(t,n){o.deleteEntry(t,e(n.target))},n.duplicateEntry=function(t,n){o.duplicateEntry(t.id,e(n.target))}};function o(e,t,o){return t=void 0!==t&&t?t:gf_global.base_url+"/images/spinner.gif",o=void 0!==o?o:"",this.elem=e,this.image='<img class="gfspinner" src="'+t+'" style="'+o+'" />',this.init=function(){return this.spinner=jQuery(this.image),jQuery(this.elem).after(this.spinner),this},this.destroy=function(){jQuery(this.spinner).remove()},this.init()}}(jQuery)})()})();
//# sourceMappingURL=gp-nested-forms.js.map