        (function($) {
            // --- Helper Functions ---
            function hslToRgb(h, s, l) { /* ... (unchanged) ... */ 
                let r, g, b;
                if (s == 0) { r = g = b = l; } else {
                    const hue2rgb = (p, q, t) => {
                        if (t < 0) t += 1; if (t > 1) t -= 1;
                        if (t < 1 / 6) return p + (q - p) * 6 * t;
                        if (t < 1 / 2) return q;
                        if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
                        return p;
                    };
                    const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                    const p = 2 * l - q;
                    r = hue2rgb(p, q, h + 1 / 3); g = hue2rgb(p, q, h); b = hue2rgb(p, q, h - 1 / 3);
                }
                return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
            }
            function rgbToHex(r, g, b) { /* ... (unchanged) ... */ 
                 return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
            }
            function hexToRgb(hex) { /* ... (unchanged) ... */
                const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
                hex = hex.replace(shorthandRegex, (m, r, g, b) => r + r + g + g + b + b);
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? { r: parseInt(result[1], 16), g: parseInt(result[2], 16), b: parseInt(result[3], 16) } : null;
            }
            function rgbToHsl(r, g, b) { /* ... (unchanged) ... */ 
                r /= 255; g /= 255; b /= 255;
                const max = Math.max(r, g, b), min = Math.min(r, g, b);
                let h, s, l = (max + min) / 2;
                if (max == min) { h = s = 0; } else {
                    const d = max - min;
                    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                    switch (max) {
                        case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                        case g: h = (b - r) / d + 2; break;
                        case b: h = (r - g) / d + 4; break;
                    }
                    h /= 6;
                }
                return [h, s, l];
            }
            // HSV to HSL conversion
            function hsvToHsl(h, s, v) {
                const l = v * (1 - s / 2);
                const sl = (l === 0 || l === 1) ? 0 : (v - l) / Math.min(l, 1 - l);
                return [h, sl, l];
            }
            // HSL to HSV conversion
            function hslToHsv(h, s, l) {
                const v = l + s * Math.min(l, 1 - l);
                const sv = (v === 0) ? 0 : 2 * (1 - l / v);
                return [h, sv, v];
            }

            function parseRgbaString(rgbaString) { /* ... (unchanged) ... */
                if (typeof rgbaString !== 'string') return null;
                const match = rgbaString.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d\.]+))?\)$/i);
                if (match) {
                    return { r: parseInt(match[1]), g: parseInt(match[2]), b: parseInt(match[3]), a: match[4] !== undefined ? parseFloat(match[4]) : 1 };
                }
                return null;
            }
            function resolveCssVariable(variableString) { /* ... (unchanged) ... */
                if (!variableString || typeof variableString !== 'string' || !variableString.toLowerCase().startsWith('var(')) { return null; }
                const tempEl = document.createElement('div');
                document.body.appendChild(tempEl);
                tempEl.style.display = 'none'; tempEl.style.color = variableString;
                let resolvedColorString = window.getComputedStyle(tempEl).color;
                document.body.removeChild(tempEl);
                if (!resolvedColorString || resolvedColorString === variableString || resolvedColorString === 'initial' || resolvedColorString === 'inherit' || resolvedColorString === 'unset' || resolvedColorString === 'currentColor') { return null; }
                if (resolvedColorString === 'transparent') { return { hex: rgbToHex(0,0,0), alpha: 0}; }
                const rgba = parseRgbaString(resolvedColorString);
                if (rgba) { return { hex: rgbToHex(rgba.r, rgba.g, rgba.b), alpha: rgba.a };}
                return null; 
            }
            function getDefinedCssVariables() { /* ... (unchanged) ... */
                const cssVariables = new Set();
                try {
                    for (const sheet of document.styleSheets) {
                        try {
                            const rules = sheet.cssRules || sheet.rules;
                            if (!rules) continue;
                            for (const rule of rules) {
                                if (rule.type === CSSRule.STYLE_RULE && rule.style && typeof rule.style.getPropertyValue === 'function') { 
                                    for (let i = 0; i < rule.style.length; i++) {
                                        const propName = rule.style[i];
                                        if (propName.startsWith('--')) {
                                            cssVariables.add(propName);
                                        }
                                    }
                                } else if (rule.type === CSSRule.STYLE_RULE && rule.styleDeclaration) { 
                                     for (const propName of rule.styleDeclaration) {
                                        if (propName.startsWith('--')) {
                                            cssVariables.add(propName);
                                        }
                                    }
                                }
                            }
                        } catch (e) { /* Silently ignore inaccessible stylesheets */ }
                    }
                } catch (e) { /* Silently ignore if stylesheets cannot be accessed */ }
                return Array.from(cssVariables).sort();
            }


            // --- Main Plugin Definition ---
            $.fn.customColorPicker = function(optionsOrMethodName, valueToSet) {
                let returnValue = this; 

                if (typeof optionsOrMethodName === 'string') {
                    const methodName = optionsOrMethodName;
                    if (methodName === 'set') {
                        return this.each(function() { 
                            const api = $(this).data('customColorPickerApi');
                            if (api && typeof api.setValue === 'function') {
                                api.setValue(valueToSet);
                            }
                        });
                    } else if (methodName === 'get') {
                        const api = $(this).first().data('customColorPickerApi'); 
                        if (api && typeof api.getValue === 'function') {
                            returnValue = api.getValue();
                        } else {
                            returnValue = undefined;
                        }
                    }
                } else {
                    const settings = $.extend({
                        defaultColor: '#CCCCCC', 
                        palette: [], 
                        showCssVarPalette: true, 
                        onColorChange: function(colorString, inputElement) {}
                    }, optionsOrMethodName); 

                    this.each(function() {
                        const $originalInput = $(this); 
                        let $textDisplayInput; 
                        let $swatchElement;    
                        let $swatchColorElement; 
                        let $swatchTextIndicator; // Renamed from $swatchVarIndicator
                        const instanceApi = $originalInput.data('customColorPickerApi') || {}; 

                        if (!$originalInput.data('color-picker-enhanced')) {
                            const $wrapper = $('<div class="mega-custom-color-input-wrapper"></div>');
                            $swatchElement = $('<span class="mega-custom-color-input-swatch"></span>');
                            $swatchColorElement = $('<div class="mega-custom-color-input-swatch-color"></div>'); 
                            $swatchTextIndicator = $('<span class="mega-swatch-text-indicator">VAR</span>'); // Create indicator with "VAR"
                            $swatchColorElement.append($swatchTextIndicator); 
                            $swatchElement.append($swatchColorElement); 

                            $textDisplayInput = $('<input type="text" class="mega-color-picker-input-text">');
                            
                            const originalIdVal = $originalInput.attr('id');
                            if (originalIdVal) { 
                                $wrapper.attr('id', originalIdVal); 
                                $originalInput.removeAttr('id');      
                            }

                            if ($originalInput.attr('name')) { $textDisplayInput.attr('name', $originalInput.attr('name') + '-text-display'); }
                            if ($originalInput.attr('placeholder')) { $textDisplayInput.attr('placeholder', $originalInput.attr('placeholder'));}
                            $textDisplayInput.val($originalInput.val()); 
                            $wrapper.append($swatchElement).append($textDisplayInput);
                            $originalInput.after($wrapper).addClass('mega-plugin-enhanced'); 
                            $originalInput.data('color-picker-enhanced', true);
                            $originalInput.data('swatch-element', $swatchElement); 
                            $originalInput.data('swatch-color-element', $swatchColorElement); 
                            $originalInput.data('swatch-text-indicator', $swatchTextIndicator); 
                            $originalInput.data('text-display-input', $textDisplayInput);
                        } else { 
                            $swatchElement = $originalInput.data('swatch-element');
                            $swatchColorElement = $originalInput.data('swatch-color-element');
                            $swatchTextIndicator = $originalInput.data('swatch-text-indicator');
                            $textDisplayInput = $originalInput.data('text-display-input');
                        }
                        const $uiWrapper = $textDisplayInput.parent('.mega-custom-color-input-wrapper');
                        instanceApi.currentSettings = $.extend({}, instanceApi.currentSettings || {}, settings);

                        const pluginInstance = this; 
                        let $pickerContainer, $paletteContainerElement, $cssVarPaletteContainer, 
                            $mainCanvas, $hueCanvas, 
                            $opacitySlider, $opacityDisplay, $previewColor, $valueInputInPicker, 
                            $selectorDot, $hueIndicator;
                        let mainCanvasEl, hueCanvasEl; 
                        let mainCtx, hueCtx;
                        let currentHslHue = 0, currentHslSaturation = 0, currentHslLightness = 0, currentAlpha = 1;
                        let isDraggingMain = false, isDraggingHue = false, isDraggingOpacity = false;
                        const uiWrapperId = $uiWrapper.attr('id') || $originalInput.attr('name') || 'picker';
                        const pickerInstanceId = uiWrapperId + '-colorpicker-instance-' + Date.now();
                        let isCssVarMode = false; 
                        let currentCssVarString = ''; 

                        function initializeColor() {
                            let inputValue = $originalInput.val() || '';
                            let baseHexForHslState = instanceApi.currentSettings.defaultColor; 
                            let alphaForHslState = 1.0;
                            isCssVarMode = false; 
                            currentCssVarString = ''; 

                            if (inputValue.toLowerCase().startsWith('var(')) {
                                currentCssVarString = inputValue; 
                                isCssVarMode = true;              
                                const resolved = resolveCssVariable(inputValue);
                                if (resolved) { 
                                    baseHexForHslState = resolved.hex;
                                    alphaForHslState = resolved.alpha;
                                } 
                            } else if (inputValue.toLowerCase() === 'transparent') { 
                                baseHexForHslState = rgbToHex(0,0,0); 
                                alphaForHslState = 0;
                            } else if (inputValue.toLowerCase().startsWith('rgba') || inputValue.toLowerCase().startsWith('rgb(') ) {
                                const parsedRgba = parseRgbaString(inputValue);
                                if (parsedRgba) { 
                                    baseHexForHslState = rgbToHex(parsedRgba.r, parsedRgba.g, parsedRgba.b); 
                                    alphaForHslState = parsedRgba.a; 
                                }
                            } else if (inputValue.startsWith('#')) {
                                const rgb = hexToRgb(inputValue);
                                if (rgb) { baseHexForHslState = inputValue.toUpperCase(); alphaForHslState = 1.0;}
                            } else if (inputValue === '') { }
                            
                            currentAlpha = alphaForHslState;
                            const rgbForHsl = hexToRgb(baseHexForHslState);
                            if (rgbForHsl) {
                                const hsl = rgbToHsl(rgbForHsl.r, rgbForHsl.g, rgbForHsl.b);
                                currentHslHue = hsl[0]; currentHslSaturation = hsl[1]; currentHslLightness = hsl[2];
                            } else { 
                                 const defaultRgb = hexToRgb(instanceApi.currentSettings.defaultColor);
                                 const defaultHsl = rgbToHsl(defaultRgb.r, defaultRgb.g, defaultRgb.b);
                                 currentHslHue = defaultHsl[0]; currentHslSaturation = defaultHsl[1]; currentHslLightness = defaultHsl[2];
                                 currentAlpha = 1.0;
                            }
                        }
                        
                        function getCurrentColorStringForOutput() { 
                            if (isCssVarMode) return currentCssVarString; 
                            if (currentAlpha === 0) return "transparent"; 
                            const [r, g, b] = hslToRgb(currentHslHue, currentHslSaturation, currentHslLightness);
                            if (currentAlpha < 1) {
                                let alphaStr = currentAlpha.toFixed(2); alphaStr = parseFloat(alphaStr).toString(); 
                                return `rgba(${r},${g},${b},${alphaStr})`; 
                            }
                            return `rgb(${r},${g},${b})`; 
                        }
                        
                        function getVisualRgbaColor() { 
                            let r,g,b,a;
                            if (isCssVarMode) {
                                const resolved = resolveCssVariable(currentCssVarString);
                                if (resolved) { 
                                    const rgbParts = hexToRgb(resolved.hex);
                                    r = rgbParts.r; g = rgbParts.g; b = rgbParts.b; a = resolved.alpha;
                                } else { 
                                    const rgbParts = hexToRgb(instanceApi.currentSettings.defaultColor);
                                    r = rgbParts.r; g = rgbParts.g; b = rgbParts.b; a = 1.0;
                                }
                            } else { 
                                [r, g, b] = hslToRgb(currentHslHue, currentHslSaturation, currentHslLightness);
                                a = currentAlpha;
                            }
                            return `rgba(${r},${g},${b},${a})`; 
                        }
                        
                        function _updateOpacitySliderBackground() {
                            if (!$opacitySlider) return;
                            const [r, g, b] = hslToRgb(currentHslHue, currentHslSaturation, currentHslLightness); 
                            const gradient = `linear-gradient(to right, rgba(${r},${g},${b},0), rgba(${r},${g},${b},1))`;
                            $opacitySlider.css('background-image', gradient);
                        }

                        function updateVisualsWithCurrentColor() {
                            const outputStringForDisplay = getCurrentColorStringForOutput();
                            const visualRgba = getVisualRgbaColor();
                            
                            if ($textDisplayInput) $textDisplayInput.val(outputStringForDisplay); 
                            
                            if ($swatchColorElement) {
                                if (isCssVarMode) {
                                    $swatchColorElement.css({
                                        'background-image': 'none', 
                                        'background-color': '#f0f0f0' 
                                    });
                                    if ($swatchTextIndicator) $swatchTextIndicator.show();
                                } else {
                                    $swatchColorElement.css({
                                        'background-image': 'none',
                                        'background-color': visualRgba
                                    });
                                    if ($swatchTextIndicator) $swatchTextIndicator.hide();
                                }
                            }

                            if ($pickerContainer && $pickerContainer.is(':visible')) { 
                                if ($previewColor) $previewColor.css('background-color', visualRgba);
                                if ($valueInputInPicker) $valueInputInPicker.val(outputStringForDisplay); 
                                if ($opacitySlider) {
                                    $opacitySlider.val(Math.round(currentAlpha * 100));
                                    _updateOpacitySliderBackground(); 
                                }
                                if ($opacityDisplay) $opacityDisplay.text(Math.round(currentAlpha * 100) + '%');
                            }
                        }

                        instanceApi._drawPalette = function() { /* ... (unchanged, uses instanceApi.currentSettings) ... */ 
                            if (!$paletteContainerElement) return;
                            $paletteContainerElement.empty();
                            const combinedPaletteColors = new Set();
                            if (instanceApi.currentSettings.palette && Array.isArray(instanceApi.currentSettings.palette)) {
                                instanceApi.currentSettings.palette.forEach(colorString => {
                                    if (typeof colorString === 'string' && colorString.startsWith('#')) {
                                        const rgb = hexToRgb(colorString);
                                        if (rgb) combinedPaletteColors.add(colorString.toUpperCase());
                                    } else { console.warn("ColorPicker: Invalid color in predefined palette:", colorString); }
                                });
                            }
                            $('.mega-color-picker-input.mega-plugin-enhanced').not($originalInput).each(function() {
                                const otherOriginalInput = $(this); 
                                const currentInputVal = otherOriginalInput.val();
                                let baseHexForPalette = null;
                                if (currentInputVal.toLowerCase().startsWith('var(')) {
                                    const resolved = resolveCssVariable(currentInputVal);
                                    if (resolved) baseHexForPalette = resolved.hex;
                                    else baseHexForPalette = instanceApi.currentSettings.defaultColor; 
                                } else if (currentInputVal.toLowerCase() === 'transparent') {
                                    baseHexForPalette = instanceApi.currentSettings.defaultColor; 
                                } else if (currentInputVal.toLowerCase().startsWith('rgba') || currentInputVal.toLowerCase().startsWith('rgb(')) {
                                    const parsedRgba = parseRgbaString(currentInputVal);
                                    if (parsedRgba) baseHexForPalette = rgbToHex(parsedRgba.r, parsedRgba.g, parsedRgba.b);
                                } else if (currentInputVal.startsWith('#')) {
                                    const rgb = hexToRgb(currentInputVal);
                                    if (rgb) baseHexForPalette = rgbToHex(rgb.r, rgb.g, rgb.b);
                                }
                                if (baseHexForPalette) combinedPaletteColors.add(baseHexForPalette.toUpperCase());
                            });
                            combinedPaletteColors.forEach(hex => {
                                const $swatch = $('<div class="mega-palette-swatch"></div>')
                                    .css('background-color', hex).attr('title', hex)
                                    .on('click', function() {
                                        isCssVarMode = false; currentCssVarString = ''; 
                                        const rgb = hexToRgb(hex);
                                        if (rgb) {
                                            const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
                                            currentHslHue = hsl[0]; currentHslSaturation = hsl[1]; currentHslLightness = hsl[2];
                                        }
                                        currentAlpha = 1.0; 
                                        handleColorSelectionChange(true); 
                                    });
                                $paletteContainerElement.append($swatch);
                            });
                        }

                        instanceApi._drawCssVarPalette = function() { /* ... (unchanged, uses instanceApi.currentSettings) ... */
                            if (!instanceApi.currentSettings.showCssVarPalette || !$cssVarPaletteContainer) { 
                                if($cssVarPaletteContainer) $cssVarPaletteContainer.hide();
                                return;
                            }
                            $cssVarPaletteContainer.empty();
                            const definedVariables = getDefinedCssVariables();
                            let validVarsFound = 0;
                            definedVariables.forEach(varName => {
                                const varString = `var(${varName})`;
                                const resolved = resolveCssVariable(varString);
                                if (resolved) { 
                                    validVarsFound++;
                                    const displayColor = `rgba(${hexToRgb(resolved.hex).r}, ${hexToRgb(resolved.hex).g}, ${hexToRgb(resolved.hex).b}, ${resolved.alpha})`;
                                    const $swatch = $('<div class="mega-css-var-palette-swatch"></div>')
                                        .css('background-color', displayColor)
                                        .attr('title', varString) 
                                        .on('click', function() {
                                            isCssVarMode = true;
                                            currentCssVarString = varString;
                                            $originalInput.val(currentCssVarString); 
                                            initializeColor(); 
                                            handleColorSelectionChange(true); 
                                        });
                                    $cssVarPaletteContainer.append($swatch);
                                }
                            });
                            if (validVarsFound > 0) { $cssVarPaletteContainer.show(); } 
                            else { $cssVarPaletteContainer.hide(); }
                        }


                        function _createPickerDOM() { /* ... (unchanged, uses instanceApi.currentSettings) ... */
                            if ($originalInput.data('picker-container-ref')) { 
                                $pickerContainer = $originalInput.data('picker-container-ref');
                                $paletteContainerElement = $pickerContainer.find('.mega-color-picker-palette'); 
                                if (instanceApi.currentSettings.showCssVarPalette) $cssVarPaletteContainer = $pickerContainer.find('.mega-css-var-palette'); 
                                $mainCanvas = $pickerContainer.find('.mega-color-picker-main'); mainCanvasEl = $mainCanvas[0];
                                $selectorDot = $pickerContainer.find('.mega-color-picker-selector-dot');
                                $hueCanvas = $pickerContainer.find('.mega-color-picker-hue-slider'); hueCanvasEl = $hueCanvas[0];
                                $hueIndicator = $pickerContainer.find('.mega-hue-slider-indicator');
                                $opacitySlider = $pickerContainer.find('.mega-color-picker-opacity-slider');
                                $opacityDisplay = $pickerContainer.find('.mega-color-picker-opacity-display');
                                $previewColor = $pickerContainer.find('.mega-color-picker-preview-color');
                                $valueInputInPicker = $pickerContainer.find('.mega-color-picker-value-input');
                                if (mainCanvasEl) mainCtx = mainCanvasEl.getContext('2d');
                                if (hueCanvasEl) hueCtx = hueCanvasEl.getContext('2d');
                                $pickerContainer.data('picker-instance', pluginInstance);
                                return;
                            }
                            $pickerContainer = $('<div class="mega-color-picker-container"></div>').data('picker-instance', pluginInstance); 
                            $paletteContainerElement = $('<div class="mega-color-picker-palette"></div>'); 
                            $pickerContainer.append($paletteContainerElement);
                            if (instanceApi.currentSettings.showCssVarPalette) { 
                                $cssVarPaletteContainer = $('<div class="mega-css-var-palette"></div>'); 
                                $pickerContainer.append($cssVarPaletteContainer);
                            }
                            mainCanvasEl = $('<canvas class="mega-color-picker-main"></canvas>')[0]; $mainCanvas = $(mainCanvasEl);
                            $selectorDot = $('<div class="mega-color-picker-selector-dot"></div>');
                            $pickerContainer.append($mainCanvas).append($selectorDot);
                            const hueWrapper = $('<div style="position:relative;"></div>'); 
                            hueCanvasEl = $('<canvas class="mega-color-picker-hue-slider"></canvas>')[0]; $hueCanvas = $(hueCanvasEl);
                            $hueIndicator = $('<div class="mega-hue-slider-indicator"></div>');
                            hueWrapper.append($hueCanvas).append($hueIndicator); $pickerContainer.append(hueWrapper);
                            const $opacityContainer = $('<div class="mega-color-picker-opacity-slider-container"></div>');
                            $opacitySlider = $('<input type="range" min="0" max="100" value="100" class="mega-color-picker-opacity-slider">');
                            $opacityDisplay = $('<span class="mega-color-picker-opacity-display">100%</span>');
                            $opacityContainer.append($opacitySlider).append($opacityDisplay); $pickerContainer.append($opacityContainer);
                            const $valueInputWrapper = $('<div class="mega-color-picker-value-input-wrapper"></div>');
                            const $previewBox = $('<div class="mega-color-picker-preview"></div>'); 
                            $previewColor = $('<div class="mega-color-picker-preview-color"></div>'); 
                            $previewBox.append($previewColor);
                            $valueInputInPicker = $('<input type="text" class="mega-color-picker-value-input">'); 
                            $valueInputWrapper.append($previewBox).append($valueInputInPicker); 
                            $pickerContainer.append($valueInputWrapper);
                            $('body').append($pickerContainer);
                            const canvasActualWidth = 280 - (15 * 2); 
                            mainCanvasEl.width = canvasActualWidth; mainCanvasEl.height = 150; 
                            hueCanvasEl.width = canvasActualWidth; hueCanvasEl.height = 20;  
                            mainCtx = mainCanvasEl.getContext('2d'); hueCtx = hueCanvasEl.getContext('2d');
                            _bindPickerInternalEvents();
                            $originalInput.data('picker-container-ref', $pickerContainer); 
                        }
                        function _drawMainColorCanvas() { 
                            if (!mainCtx || !mainCanvasEl || mainCanvasEl.width === 0 || mainCanvasEl.height === 0) return;
                            const width = mainCanvasEl.width; const height = mainCanvasEl.height;
                            mainCtx.clearRect(0,0,width,height);
                            const baseRgb = hslToRgb(currentHslHue, 1, 0.5); 
                            mainCtx.fillStyle = `rgb(${baseRgb[0]}, ${baseRgb[1]}, ${baseRgb[2]})`; 
                            mainCtx.fillRect(0, 0, width, height);
                            const whiteGradient = mainCtx.createLinearGradient(0, 0, width, 0);
                            whiteGradient.addColorStop(0, 'rgba(255,255,255,1)'); 
                            whiteGradient.addColorStop(1, 'rgba(255,255,255,0)');
                            mainCtx.fillStyle = whiteGradient; 
                            mainCtx.fillRect(0, 0, width, height);
                            const blackGradient = mainCtx.createLinearGradient(0, 0, 0, height);
                            blackGradient.addColorStop(0, 'rgba(0,0,0,0)'); 
                            blackGradient.addColorStop(1, 'rgba(0,0,0,1)');
                            mainCtx.fillStyle = blackGradient; 
                            mainCtx.fillRect(0, 0, width, height);
                            const [h_dot, s_dot_hsv, v_dot_hsv] = hslToHsv(currentHslHue, currentHslSaturation, currentHslLightness);
                            const x = s_dot_hsv * width; 
                            const y = (1 - v_dot_hsv) * height;
                            $selectorDot.css({ top: $mainCanvas.position().top + y + 'px', left: $mainCanvas.position().left + x + 'px' });
                        }
                        function _drawHueSlider() { /* ... (unchanged) ... */ 
                             if (!hueCtx || !hueCanvasEl || hueCanvasEl.width === 0 || hueCanvasEl.height === 0) return;
                            const width = hueCanvasEl.width; const height = hueCanvasEl.height;
                            hueCtx.clearRect(0,0,width,height);
                            const gradient = hueCtx.createLinearGradient(0, 0, width, 0);
                            for (let i = 0; i <= 360; i += 6) { gradient.addColorStop(i / 360, `hsl(${i}, 100%, 50%)`); }
                            hueCtx.fillStyle = gradient; hueCtx.fillRect(0, 0, width, height);
                            const indicatorX = currentHslHue * width; 
                            $hueIndicator.css({ left: indicatorX + 'px' }); 
                        }
                        
                        function handleColorSelectionChange(isCommittedChange = false) {
                            const finalColorString = getCurrentColorStringForOutput();
                            $originalInput.val(finalColorString); 
                            
                            updateVisualsWithCurrentColor(); 
                        
                            if ($pickerContainer && $pickerContainer.is(':visible')) {
                                _drawMainColorCanvas(); 
                                _drawHueSlider();
                            }
                            instanceApi.currentSettings.onColorChange.call($originalInput, finalColorString, $originalInput); 
                        }

                        function updateColorFromMainCanvas(event) { 
                            if(!mainCanvasEl) return;
                            isCssVarMode = false; currentCssVarString = ''; 
                            const rect = mainCanvasEl.getBoundingClientRect();
                            let x = event.clientX - rect.left; let y = event.clientY - rect.top;
                            const width = mainCanvasEl.width; const height = mainCanvasEl.height;
                            x = Math.max(0, Math.min(width, x)); y = Math.max(0, Math.min(height, y));
                            
                            const s_hsv = x / width;
                            const v_hsv = 1 - (y / height);
                            const [h, s_hsl, l_hsl] = hsvToHsl(currentHslHue, s_hsv, v_hsv); 
                            
                            currentHslSaturation = s_hsl;
                            currentHslLightness = l_hsl;
                            
                            handleColorSelectionChange();
                        }
                        function updateColorFromHueSlider(event) { 
                            if(!hueCanvasEl) return;
                            isCssVarMode = false; currentCssVarString = ''; 
                            const rect = hueCanvasEl.getBoundingClientRect();
                            let x = event.clientX - rect.left; const width = hueCanvasEl.width;
                            x = Math.max(0, Math.min(width, x)); 
                            currentHslHue = x / width; 
                            handleColorSelectionChange();
                        }
                        function updateColorFromOpacitySlider(event) { 
                            isCssVarMode = false; currentCssVarString = ''; 
                            currentAlpha = parseFloat($(event.target).val()) / 100;
                            handleColorSelectionChange();
                        }

                        function _showPicker() { 
                            _createPickerDOM(); 
                            if(instanceApi._drawPalette) instanceApi._drawPalette(); 
                            if(instanceApi._drawCssVarPalette) instanceApi._drawCssVarPalette(); 

                            $('.mega-color-picker-container').not($pickerContainer).hide(); 
                            const uiWrapperPos = $uiWrapper.offset();
                            const uiWrapperHeight = $uiWrapper.outerHeight();
                            
                            let pickerLeft = uiWrapperPos.left;
                            let pickerTop = uiWrapperPos.top + uiWrapperHeight + 5;

                            const pickerWidth = $pickerContainer.outerWidth();
                            const pickerHeight = $pickerContainer.outerHeight(); 

                            const windowWidth = $(window).width();
                            const windowHeight = $(window).height(); 
                            const scrollLeft = $(window).scrollLeft();
                            const scrollTop = $(window).scrollTop();

                            if (pickerLeft + pickerWidth > windowWidth + scrollLeft) {
                                pickerLeft = windowWidth + scrollLeft - pickerWidth - 5; 
                            }
                            if (pickerLeft < scrollLeft) {
                                pickerLeft = scrollLeft + 5; 
                            }
                            if (pickerTop + pickerHeight > windowHeight + scrollTop) {
                                let potentialTop = uiWrapperPos.top - pickerHeight - 5; 
                                if (potentialTop < scrollTop) { 
                                    pickerTop = windowHeight + scrollTop - pickerHeight - 5; 
                                    if (pickerTop < scrollTop) pickerTop = scrollTop + 5; 
                                } else {
                                    pickerTop = potentialTop;
                                }
                            }
                            if (pickerTop < scrollTop) {
                                pickerTop = scrollTop + 5;
                            }

                            $pickerContainer.css({ 
                                top: pickerTop + 'px', 
                                left: pickerLeft + 'px' 
                            }).fadeIn(200);
                            
                            $originalInput.val($textDisplayInput.val()); 
                            initializeColor(); 
                            updateVisualsWithCurrentColor(); 
                            _drawMainColorCanvas(); _drawHueSlider();
                        }
                        function _hidePicker() { /* ... (unchanged) ... */ 
                            if ($pickerContainer) { $pickerContainer.fadeOut(200); }
                        }
                        function _bindPickerInternalEvents() { 
                            if ($mainCanvas) $mainCanvas.on('mousedown', function(e) { e.preventDefault(); isDraggingMain = true; updateColorFromMainCanvas(e); });
                            if ($hueCanvas) $hueCanvas.on('mousedown', function(e) { e.preventDefault(); isDraggingHue = true; updateColorFromHueSlider(e); });
                            if ($opacitySlider) {
                                $opacitySlider.on('input', updateColorFromOpacitySlider); 
                                $opacitySlider.on('mousedown', function() { isDraggingOpacity = true; });
                            }
                            // CSS Var Palette swatches have their own click handlers set in _drawCssVarPalette

                            $(document).on(`mousemove.${pickerInstanceId}`, function(e) {
                                if (!$pickerContainer || !$pickerContainer.is(':visible')) return;
                                if (isDraggingMain) updateColorFromMainCanvas(e);
                                if (isDraggingHue) updateColorFromHueSlider(e);
                            }).on(`mouseup.${pickerInstanceId}`, function() { 
                                if (!$pickerContainer || !$pickerContainer.is(':visible')) return;
                                if (isDraggingMain || isDraggingHue || isDraggingOpacity) {
                                    handleColorSelectionChange(true); 
                                }
                                isDraggingMain = false; isDraggingHue = false; isDraggingOpacity = false;
                            });
                            
                            if($valueInputInPicker) {
                                $valueInputInPicker.on('change', function() { 
                                    const value = $(this).val().trim();
                                    $originalInput.val(value); 
                                    initializeColor(); 
                                    handleColorSelectionChange(true); 
                                });
                            }
                            if($pickerContainer) $pickerContainer.on('click', function(e) { e.stopPropagation(); });
                        }
                        
                        // --- API Methods ---
                        instanceApi.setValue = function(newValue) {
                            $originalInput.val(newValue);    
                            initializeColor();              
                            handleColorSelectionChange(true); 
                        };
                        instanceApi.getValue = function() {
                            return $originalInput.val();
                        };
                        $originalInput.data('customColorPickerApi', instanceApi); 

                        initializeColor(); 
                        updateVisualsWithCurrentColor(); 

                        $uiWrapper.on(`click.${pickerInstanceId}`, function(e) { 
                            e.preventDefault(); 
                            e.stopPropagation(); 
                            _showPicker(); 
                            if ($textDisplayInput) { $textDisplayInput.focus(); } 
                        });
                        
                        if ($textDisplayInput) {
                            $textDisplayInput.on(`blur.${pickerInstanceId} keydown.${pickerInstanceId}`, function(e) {
                                if (e.type === 'keydown' && e.key !== 'Enter') { return; }
                                $originalInput.val($textDisplayInput.val()); 
                                initializeColor(); 
                                updateVisualsWithCurrentColor(); 
                            });
                        }

                        $(document).on(`click.${pickerInstanceId}`, function(e) {
                            if ($pickerContainer && $pickerContainer.is(':visible')) {
                                if (!$uiWrapper.is(e.target) && $uiWrapper.has(e.target).length === 0 &&
                                    !$pickerContainer.is(e.target) && $pickerContainer.has(e.target).length === 0) {
                                    _hidePicker();
                                }
                            }
                        });
                    });
                }
                return returnValue;
            };

        }(jQuery));