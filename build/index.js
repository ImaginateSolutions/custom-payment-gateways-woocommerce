/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/fields.js":
/*!***********************!*\
  !*** ./src/fields.js ***!
  \***********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

const {
  TextInput,
  SortSelect,
  Textarea,
  CheckboxControl,
  ValidatedTextInput
} = window.wc.blocksComponents;
const Fields = props => {
  const {
    attributes,
    event
  } = props;
  const {
    type,
    inclass
  } = attributes;
  switch (type) {
    case 'text':
    case 'number':
    case 'password':
    case 'tel':
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(TextInput, {
        ...attributes,
        onChange: event,
        className: `wc-block-components-text-input ${inclass}`
      });
    case 'url':
    case 'email':
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(ValidatedTextInput, {
        ...attributes,
        onChange: event,
        className: `wc-block-components-text-input ${inclass}`
      });
    case 'checkbox':
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(CheckboxControl, {
        ...attributes,
        onChange: event,
        className: `wc-block-components-checkbox__input ${inclass}`
      });
    case 'select':
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(SortSelect, {
        ...attributes,
        onChange: event
      });
    case 'textarea':
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
        className: "wc-block-components-textarea-wrap",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(Textarea, {
          ...attributes,
          onTextChange: event,
          placeholder: attributes.label,
          className: `wc-block-components-textarea ${inclass}`
        })
      });
    default:
      return null;
  }
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Fields);

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _fields__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./fields */ "./src/fields.js");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);






const {
  extensionCartUpdate
} = wc.blocksCheckout;
const {
  registerPaymentMethod
} = window.wc.wcBlocksRegistry;
const {
  getSetting
} = window.wc.wcSettings;
const {
  PAYMENT_STORE_KEY
} = window.wc.wcBlocksData;
[...Array(1)].map((e, i) => {
  const settings = getSetting(`alg_custom_gateway_${i + 1}_data`, {});
  const label = (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_1__.decodeEntities)(settings.title);
  var newlabels = '';
  var newvalues = '';
  var newobject = [];
  const Content = props => {
    const {
      eventRegistration,
      emitResponse,
      useState
    } = props;
    const {
      onPaymentSetup
    } = eventRegistration;
    const [fieldValues, setFieldValues] = useState([{}]);
    const onInputsChanges = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((value, index, label) => {
      setFieldValues(prevValues => {
        const newValues = [...prevValues];
        if (!newValues[index]) newValues[index] = {};
        newValues[index] = {
          ...newValues[index],
          label: label
        };
        newValues[index] = {
          ...newValues[index],
          value: value
        };
        return newValues;
      });
    }, [setFieldValues]);
    newobject = fieldValues;
    newlabels = fieldValues.map(item => item.label);
    newvalues = fieldValues.map(item => item.value);
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
      const unsubscribe = onPaymentSetup(async () => {
        // Here we can do any processing we need, and then emit a response.
        // For example, we might validate a custom field, or perform an AJAX request, and then emit a response indicating it is valid or not.

        const missingRequiredFields = settings.fields.some((field, index) => {
          if ('checkbox' === field.type) {
            return field.required && !newobject[index]?.value;
          } else {
            return field.required && (!newobject[index]?.value || newobject[index]?.value.trim() === '');
          }
        });
        if (missingRequiredFields) {
          return {
            type: emitResponse.responseTypes.ERROR,
            message: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Please fill in all required fields.', 'custom-payment-gateways-woocommerce')
          };
        }
        if (newlabels && newvalues) {
          return {
            type: emitResponse.responseTypes.SUCCESS,
            meta: {
              paymentMethodData: {
                customGatewayIS: true,
                GatewayISData: newvalues.join(','),
                GatewayISNames: newlabels.join(',')
              }
            }
          };
        }
        return {
          type: emitResponse.responseTypes.ERROR,
          message: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('There was an error.', 'custom-payment-gateways-woocommerce')
        };
      });
      // Unsubscribes when this component is unmounted.
      return () => {
        unsubscribe();
      };
    }, [emitResponse.responseTypes, onPaymentSetup, setFieldValues]);
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.Fragment, {
      children: [(0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_1__.decodeEntities)(settings.description || ''), settings?.fields?.map((item, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_fields__WEBPACK_IMPORTED_MODULE_3__["default"], {
        attributes: {
          ...item,
          errorId: `${item.name}-error`,
          ...(item.type === 'checkbox' ? {
            checked: fieldValues[index]?.['value']
          } : {
            value: fieldValues[index]?.['value'] || ''
          }),
          ...(item.type === 'select' ? {
            options: item.options?.map((label, index) => ({
              key: label,
              label: label
            }))
          } : {})
        },
        event: value => onInputsChanges(item.type === 'select' ? value.target.value : value, index, item.label)
      }, item.name || index))]
    });
  };
  const Label = props => {
    const {
      PaymentMethodLabel
    } = props.components;
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(PaymentMethodLabel, {
      text: label
    });
  };
  window.onload = function () {
    const iconElement = document.querySelector('#radio-control-wc-payment-method-options-alg_custom_gateway_1__label');
    if (iconElement && settings.icon) {
      const imgElement = document.createElement('img');
      imgElement.src = settings.icon;
      imgElement.alt = settings.title;
      imgElement.style.width = '24px';
      imgElement.style.height = '24px';
      iconElement.appendChild(imgElement);
    }
  };
  registerPaymentMethod({
    name: `alg_custom_gateway_${i + 1}`,
    gatewayId: `alg_custom_gateway_${i + 1}`,
    label: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(Label, {}),
    content: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(Content, {
      useState: _wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState
    }),
    edit: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(Content, {
      useState: _wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState
    }),
    canMakePayment: () => true,
    ariaLabel: label || 'gateway',
    supports: {
      features: settings.supports
    }
  });
});
wp?.hooks?.addAction('experimental__woocommerce_blocks-checkout-set-active-payment-method', 'extension-fees', function (paymentMethod) {
  var update_cart = extensionCartUpdate({
    namespace: 'extension-fees',
    data: {
      payment_method: paymentMethod.value
    }
  });
});

/***/ }),

/***/ "./src/style.scss":
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["ReactJSXRuntime"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/html-entities":
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
/***/ ((module) => {

module.exports = window["wp"]["htmlEntities"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkcustom_payment_gateways_woocommerce"] = self["webpackChunkcustom_payment_gateways_woocommerce"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map