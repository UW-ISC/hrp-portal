declare global {
	interface Window {
		tinymce: typeof import('tinymce') & {
			Env: any;
			$: JQueryStatic;
			editors: any;
		};
		tinyMCEPreInit: any;
		wpActiveEditor: any;
		quicktags: any;
	}
}

export function reInitTinyMCEEditor(formId: number, fieldId: number) {
	const editorId = `input_${formId}_${fieldId}`;

	window.tinymce.EditorManager.remove(window.tinymce.editors[editorId]);

	const init = window.tinyMCEPreInit.mceInit[editorId];
	const $wrap = window.tinymce.$('#wp-' + editorId + '-wrap');

	if (
		($wrap.hasClass('tmce-active') ||
			!window.tinyMCEPreInit.qtInit.hasOwnProperty(editorId)) &&
		!init.wp_skip_init
	) {
		window.tinymce.init(init);

		if (!window.wpActiveEditor) {
			window.wpActiveEditor = editorId;
		}
	}

	if (typeof window.quicktags !== 'undefined') {
		for (const id in window.tinyMCEPreInit.qtInit) {
			window.quicktags(window.tinyMCEPreInit.qtInit[id]);

			if (!window.wpActiveEditor) {
				window.wpActiveEditor = id;
			}
		}
	}
}
