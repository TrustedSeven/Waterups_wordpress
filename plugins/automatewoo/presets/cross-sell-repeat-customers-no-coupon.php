<?php

$email_content = <<<EMAIL
Hi {{ customer.first_name | fallback: 'there' }},

Thank you so much for your recent orders. We've found some other items that we think you'll love:

{{ order.related_products }}

See you soon,
Your friends at {{ shop.title }}
EMAIL;

return [
	'title'       => 'Cross sell: Target repeat customers',
	'description' => 'Trigger an email to encourage a repeat purchase from repeat customers by showing them complementary products or services depending on what they\'ve already purchased.',
	'type'        => 'automatic',
	'trigger'     => [
		'name'    => 'order_completed',
		'options' => [
			'validate_order_status_before_queued_run' => '1',
		],
	],
	'rules'       => [
		[
			[
				'name'    => 'customer_2nd_last_order_date',
				'compare' => 'is_in_the_last',
				'value'   => [
					'timeframe' => '30',
					'measure'   => 'days',
				],
			],
		],
	],
	'timing'      => [
		'type'  => 'delayed',
		'delay' => [
			'unit'  => 'h',
			'value' => 1,
		],
	],
	'actions'     => [
		[
			'name'    => 'send_email',
			'options' => [
				'to'            => '{{ customer.email }}',
				'subject'       => 'Psst ... here are some more items you may love!',
				'email_heading' => 'Check these out! 👀',
				'preheader'     => '',
				'template'      => 'default',
				'email_content' => $email_content,
			],
		],
	],
];
