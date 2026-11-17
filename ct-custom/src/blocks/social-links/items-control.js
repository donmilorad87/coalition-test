/**
 * Inspector repeater for social items: image, name, URL; add / remove / reorder.
 */
import { __, sprintf } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, Flex, PanelBody, TextControl } from '@wordpress/components';

const newId = () =>
	window.crypto?.randomUUID?.() ?? `${ Date.now() }-${ Math.random() }`;

/**
 * Items from Appearance → Theme Settings (printed as window.ctCustomDefaults.socials).
 *
 * @return {Array<Object>} Items.
 */
const settingsItems = () =>
	( window.ctCustomDefaults?.socials || [] ).map( ( item ) => ( {
		id: newId(),
		name: item.name,
		url: item.url,
		imageId: item.image_id,
		imageUrl: item.image_url,
	} ) );

const ItemImage = ( { item, onChange } ) => (
	<MediaUploadCheck>
		<MediaUpload
			allowedTypes={ [ 'image' ] }
			value={ item.imageId }
			onSelect={ ( media ) =>
				onChange( {
					imageId: media.id,
					imageUrl: media.sizes?.thumbnail?.url ?? media.url,
				} )
			}
			render={ ( { open } ) => (
				<Flex
					justify="flex-start"
					gap={ 2 }
					style={ { marginBottom: 12 } }
				>
					{ item.imageUrl && (
						<img
							src={ item.imageUrl }
							alt=""
							width="32"
							height="32"
							style={ { objectFit: 'contain' } }
						/>
					) }
					<Button variant="secondary" size="compact" onClick={ open }>
						{ item.imageId
							? __( 'Replace image', 'ct-custom' )
							: __( 'Select image', 'ct-custom' ) }
					</Button>
					{ !! item.imageId && (
						<Button
							variant="link"
							isDestructive
							onClick={ () =>
								onChange( { imageId: 0, imageUrl: '' } )
							}
						>
							{ __( 'Remove', 'ct-custom' ) }
						</Button>
					) }
				</Flex>
			) }
		/>
	</MediaUploadCheck>
);

const ItemsControl = ( { items, setItems } ) => {
	const update = ( index, changes ) =>
		setItems(
			items.map( ( item, i ) =>
				i === index ? { ...item, ...changes } : item
			)
		);
	const remove = ( index ) =>
		setItems( items.filter( ( _, i ) => i !== index ) );
	const move = ( index, offset ) => {
		const next = [ ...items ];
		const [ item ] = next.splice( index, 1 );
		next.splice( index + offset, 0, item );
		setItems( next );
	};
	const add = () =>
		setItems( [
			...items,
			{ id: newId(), name: '', url: '', imageId: 0, imageUrl: '' },
		] );

	return (
		<>
			{ items.length === 0 && (
				<p className="components-base-control__help">
					{ __(
						'No items yet: the links from Appearance → Theme Settings are shown.',
						'ct-custom'
					) }
				</p>
			) }

			{ items.map( ( item, index ) => (
				<PanelBody
					key={ item.id ?? index }
					title={
						item.name ||
						sprintf(
							/* translators: %d: item number. */
							__( 'Item %d', 'ct-custom' ),
							index + 1
						)
					}
					initialOpen={ ! item.name }
				>
					<ItemImage
						item={ item }
						onChange={ ( changes ) => update( index, changes ) }
					/>
					<TextControl
						label={ __( 'Name', 'ct-custom' ) }
						help={ __(
							'Used as the accessible label. Facebook, Twitter, LinkedIn and Pinterest get a built-in icon when no image is set.',
							'ct-custom'
						) }
						value={ item.name }
						onChange={ ( name ) => update( index, { name } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						type="url"
						label={ __( 'URL', 'ct-custom' ) }
						placeholder="https://"
						value={ item.url }
						onChange={ ( url ) => update( index, { url } ) }
						__nextHasNoMarginBottom
					/>
					<Flex
						justify="flex-start"
						gap={ 1 }
						style={ { marginTop: 12 } }
					>
						<Button
							icon="arrow-up-alt2"
							label={ __( 'Move up', 'ct-custom' ) }
							size="small"
							disabled={ index === 0 }
							onClick={ () => move( index, -1 ) }
						/>
						<Button
							icon="arrow-down-alt2"
							label={ __( 'Move down', 'ct-custom' ) }
							size="small"
							disabled={ index === items.length - 1 }
							onClick={ () => move( index, 1 ) }
						/>
						<Button
							icon="trash"
							label={ __( 'Remove item', 'ct-custom' ) }
							size="small"
							isDestructive
							onClick={ () => remove( index ) }
						/>
					</Flex>
				</PanelBody>
			) ) }

			<Flex justify="flex-start" gap={ 2 } style={ { padding: 16 } }>
				<Button variant="secondary" onClick={ add }>
					{ __( 'Add item', 'ct-custom' ) }
				</Button>
				{ items.length === 0 && (
					<Button
						variant="tertiary"
						onClick={ () => setItems( settingsItems() ) }
					>
						{ __( 'Start from Theme Settings', 'ct-custom' ) }
					</Button>
				) }
			</Flex>
		</>
	);
};

export default ItemsControl;
