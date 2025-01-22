/**
 * External dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies.
 */
import metadata from './block.json';
import { Edit, Save } from './edit';

/**
 * Register the block type.
 */
registerBlockType(metadata, {
	icon: 'email',
	edit: Edit,
	save: Save,
});
