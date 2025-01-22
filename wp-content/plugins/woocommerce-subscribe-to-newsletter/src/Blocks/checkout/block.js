/**
 * External dependencies.
 */
import { useEffect, useState } from '@wordpress/element';
import { CheckboxControl } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';

const {
	display,
	label,
	checked: initialChecked,
} = getSetting('wc-newsletter-subscription-checkout_data', '');
const Block = ({ checkoutExtensionData }) => {
	const [checked, setChecked] = useState(initialChecked);
	const { setExtensionData } = checkoutExtensionData;

	useEffect(() => {
		setExtensionData(
			'wc-newsletter-subscription',
			'subscribe_to_newsletter',
			display && checked
		);
	}, [checked, setExtensionData]);

	return (
		display && (
			<CheckboxControl
				id="subscribe_to_newsletter"
				checked={checked}
				onChange={setChecked}
				label={label}
				className="wc-newsletter-subscription"
			/>
		)
	);
};

export default Block;
