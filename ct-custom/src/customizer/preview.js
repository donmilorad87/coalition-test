/**
 * Customizer live preview: each design setting updates its CSS custom property without a reload.
 * The setting → variable map is printed by CtCustom\Customizer::enqueue_preview().
 */
const root = document.documentElement;

Object.entries( window.ctCustomizerVars || {} ).forEach(
	( [ id, { cssVar, unit, values } ] ) => {
		window.wp.customize( id, ( setting ) => {
			// Font settings store a slug; `values` maps it to the real font-family stack.
			setting.bind( ( value ) =>
				root.style.setProperty(
					cssVar,
					values ? ( values[ value ] ?? '' ) : `${ value }${ unit }`
				)
			);
		} );
	}
);
