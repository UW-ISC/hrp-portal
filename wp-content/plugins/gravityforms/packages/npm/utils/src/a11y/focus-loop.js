import getFocusable from '../dom/get-focusable';

/**
 * @function focusLoop
 * @description Loop through focusable els inside a container. Bound to a keydown listener usually.
 *
 * @param {KeyboardEvent} e
 * @param {HTMLElement} trigger
 * @param {HTMLElement} container
 * @param {Function} onEscape
 */

export default function(
	e = {},
	trigger = null,
	container = null,
	onEscape = () => {}
) {
	if ( ! container || ! trigger ) {
		console.error(
			'You need to pass a container and trigger node to focusLoop.'
		);
		return;
	}
	// esc key, refocus the settings trigger in the editor preview for the active field
	if ( e.keyCode === 27 ) {
		trigger.focus();
		onEscape();
		return;
	}
	// not tab key, exit
	if ( e.keyCode !== 9 ) {
		return;
	}
	// get visible focusable items
	const focusable = getFocusable( container );
	// store first and last visible item
	const firstFocusableEl = focusable[ 0 ];
	const lastFocusableEl = focusable[ focusable.length - 1 ];

	// shiftkey was involved, we're going backwards, focus last el if we are leaving first
	if ( e.shiftKey ) {
		/* shift + tab */
		if ( document.activeElement === firstFocusableEl ) {
			lastFocusableEl.focus();
			e.preventDefault();
		}
		// regular tabbing direction, bring us back to first el at reaching end
	} /* tab */ else if ( document.activeElement === lastFocusableEl ) {
		firstFocusableEl.focus();
		e.preventDefault();
	}
}
