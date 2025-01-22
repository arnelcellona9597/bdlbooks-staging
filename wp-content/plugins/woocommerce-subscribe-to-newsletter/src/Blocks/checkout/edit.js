/**
 * Checkout block edit function.
 */
import { useBlockProps } from '@wordpress/block-editor';
import { CheckboxControl } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';

const { label } = getSetting('wc-newsletter-subscription-checkout_data', '');

export const Edit = () => {
	return (
		<div {...useBlockProps()}>
			<CheckboxControl
				id="subscribe_to_newsletter"
				checked={false}
				disabled={true}
				label={label}
				className="wc-newsletter-subscription"
			/>
		</div>
	);
};

export const Save = ({}) => {
	useBlockProps.save();

	return null;
};
