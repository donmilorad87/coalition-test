import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import ItemsControl from './items-control';

const Edit = ( { attributes, setAttributes } ) => (
	<div { ...useBlockProps() }>
		<InspectorControls>
			<PanelBody title={ __( 'Items', 'ct-custom' ) }>
				<ItemsControl
					items={ attributes.items }
					setItems={ ( items ) => setAttributes( { items } ) }
				/>
			</PanelBody>
			<PanelBody title={ __( 'Behaviour', 'ct-custom' ) }>
				<ToggleControl
					label={ __( 'Open links in a new tab', 'ct-custom' ) }
					checked={ attributes.openInNewTab }
					onChange={ ( openInNewTab ) =>
						setAttributes( { openInNewTab } )
					}
					__nextHasNoMarginBottom
				/>
				<ToggleControl
					label={ __( 'Show "share this page" button', 'ct-custom' ) }
					checked={ attributes.showShare }
					onChange={ ( showShare ) => setAttributes( { showShare } ) }
					__nextHasNoMarginBottom
				/>
			</PanelBody>
		</InspectorControls>
		<ServerSideRender block={ metadata.name } attributes={ attributes } />
	</div>
);

export default Edit;
