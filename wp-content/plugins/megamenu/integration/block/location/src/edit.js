/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useEffect } from '@wordpress/element';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { SelectControl, Placeholder, Disabled, PanelBody } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
import metadata from './block.json';
const { name } = metadata;

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( { setAttributes, attributes } ) {

    const enabledLocations = window.max_mega_menu_enabled_locations || [];

    useEffect( () => {
        if ( ! attributes.location ) {
            const firstEnabled = enabledLocations.find(
                ( slug ) => slug in window.max_mega_menu_locations
            );
            if ( firstEnabled ) {
                setAttributes( { location: firstEnabled } );
            }
        }
    }, [] );

    if ( window.max_mega_menu_locations.length === 0 ) {
        return (
            <div {...useBlockProps()}>
                {__('Error: max_mega_menu_locations missing.', 'megamenu')}
            </div>
        );
    }

    const options = Object.keys( window.max_mega_menu_locations ).map( ( location ) => {
        const label = window.max_mega_menu_locations[ location ];
        const badge = location === '' ? '' : ( enabledLocations.includes( location ) ? '🟢 ' : '⚪ ' );
        return { value: location, label: badge + label };
    } );

    if ( options.length === 1 ) {
        const menuLocationsUrl =
            window.max_mega_menu_block_admin?.menu_locations_url || '#';
        return (
            <div {...useBlockProps()}>
                {createInterpolateElement(
                    __(
                        'No locations found. Go to <a>Mega Menu > Menu Locations</a> to create a new menu location.',
                        'megamenu'
                    ),
                    {
                        a: <a href={menuLocationsUrl} />,
                    }
                )}
            </div>
        );
    }

    const onSaveMenu = (value) => {
        setAttributes({location: String( value )});
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Menu Location', 'megamenu')}>
                    <SelectControl
                        __next40pxDefaultSize
                        label={__('Select a location', 'megamenu')}
                        options={options}
                        value={attributes.location}
                        onChange={onSaveMenu}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...useBlockProps()}>
                {attributes.location && attributes.location !== '' ? (
                    <Disabled>
                        <ServerSideRender block={name} attributes={attributes} />
                    </Disabled>
                ) : (
                    <Placeholder label={__('Max Mega Menu', 'megamenu')}>
                        <p>{ __( 'Select a location in the block settings panel.', 'megamenu' ) }</p>
                    </Placeholder>
                )}
            </div>
        </>
    );
}
