/* eslint-disable no-shadow */
/* Polyfills */
import 'core-js/es/array/includes';
import 'core-js/es/array/find';
import 'core-js/es/object/assign';
import 'core-js/es/object/values';
import 'core-js/es/object/entries';

/*
 * String.format was deprecated in GF 2.7.1 and will be removed in GF 2.8 in favor of String.prototype.gformFormat.
 *
 * As we support older versions of GF, we need to add String.prototype.gformFormat if it doesn't exist.
 */
// @ts-ignore
if (!String.prototype.gformFormat) {
	// @ts-ignore
	String.prototype.gformFormat = function() {
		// eslint-disable-next-line prefer-rest-params
		const args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) {
			return typeof args[number] !== 'undefined' ? args[number] : match;
		});
	};
}

import GPPopulateAnything, {
	fieldMap,
	formID,
} from './classes/GPPopulateAnything';
import GPPALiveMergeTags from './classes/GPPALiveMergeTags';
import deepmerge from 'deepmerge';

const gppaMergedFieldMaps: { [formId: string]: fieldMap } = {};

window.gppaForms = {};
window.gppaLiveMergeTags = {};

for (const prop in window) {
	if (
		window.hasOwnProperty(prop) &&
		(prop.indexOf('GPPA_FILTER_FIELD_MAP') === 0 ||
			prop.indexOf('GPPA_FIELD_VALUE_OBJECT_MAP') === 0)
	) {
		const formId = prop.split('_').pop() as string;
		const map = (window as any)[prop];

		if (!(formId in gppaMergedFieldMaps)) {
			gppaMergedFieldMaps[formId] = {};
		}

		gppaMergedFieldMaps[formId] = deepmerge(
			gppaMergedFieldMaps[formId],
			map[formId]
		);
	}
}

const maybeRegisterForm = (formId: formID, fieldMap = {}) => {
	if (!(formId in window.gppaLiveMergeTags)) {
		if (!(formId in window.gppaForms)) {
			window.gppaForms[formId] = new GPPopulateAnything(formId, fieldMap);
		}

		window.gppaLiveMergeTags[formId] = new GPPALiveMergeTags(formId);
	}
};

for (const [formId, fieldMap] of Object.entries(gppaMergedFieldMaps)) {
	maybeRegisterForm(formId, fieldMap);
}

/**
 * WooCommerce Gravity Forms Product Add-Ons appears to add the ID to the form after page load so
 * div[id^="gform_wrapper_"] was added as a fallback.
 */
jQuery('form[id^="gform_"], div[id^="gform_wrapper_"]').each((index, el) => {
	const formId = jQuery(el)
		?.attr('id')
		?.replace(/^gform_(wrapper_)?/, '');

	if (!formId) {
		return;
	}

	maybeRegisterForm(formId);
});

window.gform.addAction('gpnf_init_nested_form', (formId: any) => {
	maybeRegisterForm(formId);
});

/**
 * Initialize GPPA JS for a specific form
 * This is not currently used internally by GPPA but allows external scripts to register GPPA on demand.
 * Currently used in GW Cache Buster. See HS#23661
 *
 * @since 1.0-beta-4.167
 *
 * @param number formId  Form ID to initialize
 */
window.gform.addAction('gppa_register_form', (formId: number) => {
	maybeRegisterForm(formId);
});
