import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

const FIELDS = [
	{ key: 'company', label: __( 'Company name', 'ct-custom' ) },
	{ key: 'address', label: __( 'Address', 'ct-custom' ), multiline: true },
	{ key: 'phone', label: __( 'Phone', 'ct-custom' ) },
	{ key: 'fax', label: __( 'Fax', 'ct-custom' ) },
];

const Edit = ( { attributes, setAttributes } ) => {
	const defaults = window.ctCustomDefaults || {};

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Contact details', 'ct-custom' ) }>
					<p className="components-base-control__help">
						{ __(
							'Leave a field empty to use the value from Appearance → Theme Settings.',
							'ct-custom'
						) }
					</p>
					{ FIELDS.map( ( { key, label, multiline } ) => {
						const Control = multiline
							? TextareaControl
							: TextControl;
						return (
							<Control
								key={ key }
								label={ label }
								value={ attributes[ key ] }
								placeholder={ defaults[ key ] || '' }
								onChange={ ( value ) =>
									setAttributes( { [ key ]: value } )
								}
								__nextHasNoMarginBottom
							/>
						);
					} ) }
					<ToggleControl
						label={ __( 'Show "copy" buttons', 'ct-custom' ) }
						checked={ attributes.showCopy }
						onChange={ ( showCopy ) =>
							setAttributes( { showCopy } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block={ metadata.name }
				attributes={ attributes }
			/>
		</div>
	);
};

export default Edit;
