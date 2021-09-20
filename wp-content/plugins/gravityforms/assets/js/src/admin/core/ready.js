/**
 * @module
 * @exports ready
 * @description The core dispatcher for the dom ready event in javascript.
 */

import common from 'common';
import { ready } from '@gravityforms/utils';
import Flyout from '@gravityforms/components/js/flyout';

/**
 * @function bindEvents
 * @description Bind global event listeners here,
 */

const bindEvents = () => {};

/**
 * @function init
 * @description The core dispatcher for init across the codebase.
 */

const init = () => {
	// initialize global events

	bindEvents();

	// initialize common modules

	common();

	const flyout = new Flyout( {
		content: 'Hello',
		position: 'absolute',
		target: '.gflow-inbox.gflow-grid',
		title: 'Inbox Settings',
		triggers: '[data-js="inbox-settings"]',
		wrapperClasses: 'gform-flyout gform-flyout--inbox-settings',
	} );

	console.log( flyout );

	// initialize admin modules

	console.info(
		'Gravity Forms Admin: Initialized all javascript that targeted document ready.'
	);
};

/**
 * @function domReady
 * @description Export our dom ready enabled init.
 */

const domReady = () => {
	ready( init );
};

export default domReady;
