<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Settings;

class Paypal extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Paypal Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
	return [
            'paypal_id' => [
                'title'       => 'codalia.membership::lang.settings.paypal_id',
                'description' => 'codalia.membership::lang.settings.paypal_id_description',
                'default'     => '',
                'type'        => 'string',
	    ],
            'paypal_url' => [
                'title'       => 'codalia.membership::lang.settings.paypal_url',
                'description' => 'codalia.membership::lang.settings.paypal_url_description',
                'default'     => '',
                'type'        => 'string',
	    ],
            'notify_url' => [
                'title'       => 'codalia.membership::lang.settings.notify_url',
                'description' => 'codalia.membership::lang.settings.notify_url_description',
                'default'     => '',
                'type'        => 'string',
	    ],
            'return_url' => [
                'title'       => 'codalia.membership::lang.settings.return_url',
                'description' => 'codalia.membership::lang.settings.return_url_description',
                'default'     => '',
                'type'        => 'string',
	    ],
            'cancel_url' => [
                'title'       => 'codalia.membership::lang.settings.cancel_url',
                'description' => 'codalia.membership::lang.settings.cancel_url_description',
                'default'     => '',
                'type'        => 'string',
	    ],
	];
    }

    public function onRun()
    {
      $path = explode('/', $this->currentPageUrl());
      $product = $path[count($path) - 2];
      $action = end($path);

      $this->page['product'] = $product; 
      $this->page['action'] = $action; 
      $this->page['paypal_id'] = $this->property('paypal_id'); 
      $this->page['paypal_url'] = $this->property('paypal_url'); 
      $this->page['subscription_fee'] = Settings::get('subscription_fee', 0);

      //$postData = $this->getPostData($product);
      //$this->goToPaypal($postData);
    }

    public function goToPaypal($postData)
    {
        $postItems = [];
	foreach ($postData as $key => $value) {
	    $postItems[] = $key.'='.$value;
	}

	$postString = implode ('&', $postItems);
      file_put_contents('debog_file.txt', print_r($postString, true));
    }

    public function getPostData($product)
    {
	$postData = ['business' => $this->property('paypal_id'),
		     'cmd' => '_xclick',
		     'item_name' => $product,
		     'item_number' => $product.'01',
		     'amount' => Settings::get('subscription_fee', 0),
		     'currency_code' => 'EUR',
		     'notify_url' => url('/').'/paypal/'.$product.'/notify',
		     'return' => url('/').'/paypal/'.$product.'/return',
		     'cancel_return' => url('/').'/paypal/'.$product.'/cancel'
	];

	return $postData;
    }
}
