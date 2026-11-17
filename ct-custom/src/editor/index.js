/**
 * Block editor sidebar panel: "Page header" (display title + breadcrumb parent) for pages.
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { SelectControl, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEntityProp, store as coreStore } from '@wordpress/core-data';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';

const META_TITLE = 'ct_display_title';
const META_PARENT = 'ct_breadcrumb_parent';

const PageHeaderPanel = () => {
	const { postType, postId } = useSelect( ( select ) => ( {
		postType: select( editorStore ).getCurrentPostType(),
		postId: select( editorStore ).getCurrentPostId(),
	} ) );
	const [ meta, setMeta ] = useEntityProp( 'postType', 'page', 'meta' );
	const pages = useSelect(
		( select ) =>
			postType === 'page'
				? select( coreStore ).getEntityRecords( 'postType', 'page', {
						per_page: 100,
						status: 'publish',
						exclude: [ postId ],
						_fields: 'id,title',
					} )
				: null,
		[ postType, postId ]
	);

	if ( postType !== 'page' || ! meta ) {
		return null;
	}

	const options = [
		{ value: 0, label: __( '— Use page hierarchy —', 'ct-custom' ) },
		...( pages || [] ).map( ( page ) => ( {
			value: page.id,
			label: page.title.rendered || `#${ page.id }`,
		} ) ),
	];

	return (
		<PluginDocumentSettingPanel
			name="ct-custom-page-header"
			title={ __( 'Page header', 'ct-custom' ) }
		>
			<TextControl
				label={ __( 'Displayed title', 'ct-custom' ) }
				help={ __(
					'Shown as the page heading and last breadcrumb. Empty = page title.',
					'ct-custom'
				) }
				value={ meta[ META_TITLE ] || '' }
				onChange={ ( value ) =>
					setMeta( { ...meta, [ META_TITLE ]: value } )
				}
				__nextHasNoMarginBottom
			/>
			<SelectControl
				label={ __( 'Breadcrumb parent', 'ct-custom' ) }
				value={ meta[ META_PARENT ] || 0 }
				options={ options }
				onChange={ ( value ) =>
					setMeta( { ...meta, [ META_PARENT ]: Number( value ) } )
				}
				__nextHasNoMarginBottom
			/>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'ct-custom-page-header', { render: PageHeaderPanel } );
