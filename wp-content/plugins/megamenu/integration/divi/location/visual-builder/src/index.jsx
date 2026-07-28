// External dependencies.
import React from 'react';

// WordPress package dependencies.
const {
	addAction,
} = window?.vendor?.wp?.hooks;

// Divi package dependencies.
const {
	ModuleContainer,
	StyleContainer,
	elementClassnames,
} = window?.divi?.module;

const {
	registerModule,
} = window?.divi?.moduleLibrary;

import metadata from './module.json';

// ---------------------------------------------------------------------------
// Module styles (applied via Divi's style system)
// ---------------------------------------------------------------------------

const ModuleStyles = ( {
	attrs,
	elements,
	settings,
	orderClass,
	mode,
	state,
	noStyleTag,
} ) => (
	<StyleContainer mode={ mode } state={ state } noStyleTag={ noStyleTag }>
		{ elements.style( {
			attrName: 'module',
			styleProps: {
				disabledOn: {
					disabledModuleVisibility: settings?.disabledModuleVisibility,
				},
			},
		} ) }
	</StyleContainer>
);

const ModuleScriptData = ( { elements } ) => (
	<React.Fragment>
		{ elements.scriptData( { attrName: 'module' } ) }
	</React.Fragment>
);

const moduleClassnames = ( { classnamesInstance, attrs } ) => {
	classnamesInstance.add(
		elementClassnames( { attrs: attrs?.module?.decoration ?? {} } ),
	);
};

// ---------------------------------------------------------------------------
// Menu preview — class component to avoid the React hook dispatcher, which
// is broken in WP due to a react@18.3.1 / react-dom@18.2.0 version mismatch.
// Class component lifecycle (componentDidMount/Update) is unaffected.
// ---------------------------------------------------------------------------

class MenuPreview extends React.Component {
	constructor( props ) {
		super( props );
		this.state = { menuHtml: '', loading: false, fetchError: false };
		this._abortController = null;
	}

	componentDidMount() {
		this._fetch( this.props.locationSlug );
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.locationSlug !== this.props.locationSlug ) {
			this._fetch( this.props.locationSlug );
		}
	}

	componentWillUnmount() {
		if ( this._abortController ) {
			this._abortController.abort();
		}
	}

	_fetch( locationSlug ) {
		if ( this._abortController ) {
			this._abortController.abort();
		}

		if ( ! locationSlug ) {
			this.setState( { menuHtml: '', loading: false, fetchError: false } );
			return;
		}

		this._abortController = new AbortController();
		this.setState( { loading: true, fetchError: false } );

		const restUrl = window.maxMegaMenuRestUrl || '/wp-json/maxmegamenu/v1/';
		const nonce   = window.maxMegaMenuNonce || '';

		fetch( restUrl + 'render-menu?location=' + encodeURIComponent( locationSlug ), {
			credentials: 'include',
			headers: { 'X-WP-Nonce': nonce },
			signal: this._abortController.signal,
		} )
			.then( ( r ) => {
				if ( ! r.ok ) {
					throw new Error( 'HTTP ' + r.status );
				}
				return r.json();
			} )
			.then( ( data ) => {
				if ( ! data || typeof data !== 'object' ) {
					throw new Error( 'Invalid response' );
				}
				this.setState( { menuHtml: data.html || '', loading: false, fetchError: false } );
			} )
			.catch( ( err ) => {
				if ( err.name === 'AbortError' ) {
					return;
				}
				this.setState( { menuHtml: '', loading: false, fetchError: true } );
			} );
	}

	render() {
		const { locationSlug } = this.props;
		const { menuHtml, loading, fetchError } = this.state;

		if ( ! locationSlug ) {
			return <em style={ { opacity: 0.6 } }>Select a location in the Content settings panel.</em>;
		}

		if ( loading ) {
			return <em style={ { opacity: 0.6 } }>Loading menu&hellip;</em>;
		}

		if ( fetchError ) {
			return <em style={ { opacity: 0.6 } }>Preview could not be loaded.</em>;
		}

		if ( menuHtml ) {
			return <div dangerouslySetInnerHTML={ { __html: menuHtml } } />;
		}

		const locationLabel = ( window.maxMegaMenuLocations || {} )[ locationSlug ] || locationSlug;
		return <strong>{ locationLabel } (no menu assigned)</strong>;
	}
}

// ---------------------------------------------------------------------------
// Edit renderer
// Divi calls this as a plain function (no fiber), so no hooks here.
// Hooks live in MenuPreview which IS rendered through React's reconciler.
// ---------------------------------------------------------------------------

const MaxMegaMenuLocationModule = ( { attrs, id, name, elements } ) => {
	const locationSlug = attrs?.location?.innerContent?.desktop?.value ?? '';

	return (
		<ModuleContainer
			attrs={ attrs }
			elements={ elements }
			id={ id }
			moduleClassName="maxmegamenu_location"
			name={ name }
			scriptDataComponent={ ModuleScriptData }
			stylesComponent={ ModuleStyles }
			classnamesFunction={ moduleClassnames }
		>
			{ elements.styleComponents( { attrName: 'module' } ) }
			<div className="et_pb_module_inner maxmegamenu-divi-placeholder">
				<MenuPreview locationSlug={ locationSlug } />
			</div>
		</ModuleContainer>
	);
};

// ---------------------------------------------------------------------------
// Module registration
// ---------------------------------------------------------------------------

const megaMenuLocationModule = {
	metadata,
	renderers: {
		edit: MaxMegaMenuLocationModule,
	},
	placeholderContent: {
		location: {
			innerContent: {
				desktop: {
					value: '',
				},
			},
		},
	},
};

addAction(
	'divi.moduleLibrary.registerModuleLibraryStore.after',
	'maxMegaMenu.locationModule',
	() => {
		const locations = window.maxMegaMenuLocations || {};
		const enabled   = window.maxMegaMenuEnabledLocations || [];

		// divi/select expects options as an object: { value: { label } }
		const options = {
			'': { label: 'Select a location' },
			...Object.fromEntries(
				Object.entries( locations ).map( ( [ value, label ] ) => {
					const badge = enabled.includes( value ) ? '🟢 ' : '⚪ ';
					return [ value, { label: badge + String( label ) } ];
				} )
			),
		};

		const dynamicMetadata = JSON.parse( JSON.stringify( metadata ) );
		dynamicMetadata.attributes.location.settings.innerContent.item.component = {
			name: 'divi/select',
			type: 'field',
			props: { options },
		};

		// Auto-select the first active location when the module is inserted.
		// Do NOT set this as the attribute default — if the default matches the saved
		// value, Divi omits the attribute from the block and the frontend gets "".
		const firstEnabled = enabled.find( ( slug ) => slug in locations ) || '';

		registerModule( dynamicMetadata, {
			...megaMenuLocationModule,
			placeholderContent: {
				location: {
					innerContent: { desktop: { value: firstEnabled } },
				},
			},
		} );
	}
);
