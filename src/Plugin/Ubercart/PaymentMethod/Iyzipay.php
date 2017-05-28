<?php

namespace Drupal\uc_iyzipay\Plugin\Ubercart\PaymentMethod;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\uc_order\OrderInterface;
use Drupal\uc_payment\OffsitePaymentMethodPluginInterface;
use Drupal\uc_payment\PaymentMethodPluginBase;

/**
 * Defines the iyzipay payment method.
 *
 * @UbercartPaymentMethod(
 *   id = "iyzipay",
 *   name = @Translation("iyzipay"),
 *   redirect = "\Drupal\uc_iyzipay\Form\IyzipayForm",
 * )
 */
class Iyzipay extends PaymentMethodPluginBase implements OffsitePaymentMethodPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getDisplayLabel($label) {
    $build['#attached']['library'][] = 'uc_iyzipay/iyzipay.styles';
    $build['label'] = array(
      '#plain_text' => $label,
      '#suffix' => '<br />',
    );
    $build['image'] = array(
      '#theme' => 'image',
      '#uri' => drupal_get_path('module', 'uc_iyzipay') . '/images/iyzipay_logo.jpg',
      '#alt' => $this->t('iyzipay'),
      '#attributes' => array('class' => array('uc-iyzipay-logo')),
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'check' => FALSE,
      'checkout_type' => 'dynamic',
      'demo' => TRUE,
      'language' => 'en',
      'notification_url' => '',
      'secret_word' => 'tango',
      'sid' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['sid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Your iyzipay vendor account number.'),
      '#default_value' => $this->configuration['sid'],
    );
    $form['secret_word'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key'),
      '#description' => $this->t('The secret word entered in your iyzipay account Look and Feel settings.'),
      '#default_value' => $this->configuration['secret_word'],
    );
    $form['notification_url'] = array(
      '#type' => 'url',
      '#title' => $this->t('Notification URL'),
      '#description' => $this->t('Pass this URL to the <a href=":help_url">instant notification settings</a> parameter in your iyzipay account. This way, any refunds or failed fraud   reviews will automatically cancel the Ubercart order.', [':help_url' => Url::fromUri('https://www.iyzipay.com/static/va/documentation/INS/index.html')->toString()]),
      '#default_value' => $this->configuration['notification_url'],
    );
    $form['complate_url'] = array(
      '#type' => 'url',
      '#title' => $this->t('Complate URL'),
      '#description' => $this->t('Pass this URL to the <a href=":help_url">instant notification settings</a> parameter in your iyzipay account. This way, any refunds or failed fraud   reviews will automatically cancel the Ubercart order.', [':help_url' => Url::fromUri('https://www.iyzipay.com/static/va/documentation/INS/index.html')->toString()]),
      '#default_value' => $this->configuration['complate_url'],
    );
    $form['baseurl'] = array(
      '#type' => 'url',
      '#title' => $this->t('Base URL'),
      '#description' => $this->t('Pass this URL to the <a href=":help_url">instant notification settings</a> parameter in your iyzipay account. This way, any refunds or failed fraud   reviews will automatically cancel the Ubercart order.', [':help_url' => Url::fromUri('https://www.iyzipay.com/static/va/documentation/INS/index.html')->toString()]),
      '#default_value' => $this->configuration['baseurl'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    //$this->configuration['check'] = $form_state->getValue('check');
    //$this->configuration['checkout_type'] = $form_state->getValue('checkout_type');
    //$this->configuration['demo'] = $form_state->getValue('demo');
    //$this->configuration['language'] = $form_state->getValue('language');
    $config = \Drupal::service('config.factory')->getEditable('iyzipay.settings');
    $config->set('apikey', $this->configuration['sid'])->save();
		$config->set('secretkey', $this->configuration['secret_word'])->save();
		$config->set('baseurl', $this->configuration['baseurl'])->save();
		$config->set('complateurl', $this->configuration['complate_url'])->save();	
		$config->set('notificationurl', $this->configuration['notification_url'])->save();	
    //drupal_set_message($this->configuration['notification_url']);
    
    $this->configuration['notification_url'] = $form_state->getValue('notification_url');
    $this->configuration['secret_word'] = $form_state->getValue('secret_word');
    $this->configuration['sid'] = $form_state->getValue('sid');
    $this->configuration['baseurl'] = $form_state->getValue('baseurl');
    $this->configuration['complateurl'] = $form_state->getValue('complate_url');
    $this->configuration['notificationurl'] = $form_state->getValue('notification_url');


		//$_SESSION['Iyzipay']['sid']=$this->configuration['sid'];
		//$_SESSION['Iyzipay']['secret_word']=$this->configuration['secret_word'];
    // Set and save new message value.

		//$config->set('baseurl', $form_state->getValue('notification_url'))->save();
		//drupal_set_message(	"secretkey =".	$config->get('secretkey'));
  }

  /**
   * {@inheritdoc}
   */
  public function cartDetails(OrderInterface $order, array $form, FormStateInterface $form_state) {
    $build = array();
    $session = \Drupal::service('session');
    if ($this->configuration['check']) {
      $build['pay_method'] = array(
        '#type' => 'select',
        '#title' => $this->t('Select your payment type:'),
        '#default_value' => $session->get('pay_method') == 'CK' ? 'CK' : 'CC',
        '#options' => array(
          'CC' => $this->t('Credit card'),
          'CK' => $this->t('Online check'),
        ),
      );
      $session->remove('pay_method');
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function cartProcess(OrderInterface $order, array $form, FormStateInterface $form_state) {
    $session = \Drupal::service('session');
    if (NULL != $form_state->getValue(['panes', 'payment', 'details', 'pay_method'])) {
      $session->set('pay_method', $form_state->getValue(['panes', 'payment', 'details', 'pay_method']));
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function cartReviewTitle() {
    if ($this->configuration['check']) {
      return $this->t('Credit card/eCheck');
    }
    else {
      return $this->t('Credit card');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildRedirectForm(array $form, FormStateInterface $form_state, OrderInterface $order = NULL) {
		$istek=		\Drupal::request()->request->all();
		print_r($istek);


    $address = $order->getAddress('billing');
    if ($address->country) {
      $country = \Drupal::service('country_manager')->getCountry($address->country)->getAlpha3();
    }
    else {
      $country = '';
    }

    $data = array(
      'sid' => $this->configuration['sid'],
      'mode' => 'Iyzipay',
      'card_holder_name' => Unicode::substr($address->first_name . ' ' . $address->last_name, 0, 128),
      'street_address' => Unicode::substr($address->street1, 0, 64),
      'street_address2' => Unicode::substr($address->street2, 0, 64),
      'city' => Unicode::substr($address->city, 0, 64),
      'state' => $address->zone,
      'zip' => Unicode::substr($address->postal_code, 0, 16),
      'country' => $country,
      'email' => Unicode::substr($order->getEmail(), 0, 64),
      'phone' => Unicode::substr($address->phone, 0, 16),
      'purchase_step' => 'payment-method',

      'demo' => $this->configuration['demo'] ? 'Y' : 'N',
      'lang' => $this->configuration['language'],
      'merchant_order_id' => $order->id(),
      'pay_method' => 'CC',
      'x_receipt_link_url' => Url::fromRoute('uc_iyzipay.complete', ['cart_id' => \Drupal::service('uc_cart.manager')->get()->getId()], ['absolute' => TRUE])->toString(),

      'total' => uc_currency_format($order->getTotal(), FALSE, FALSE, '.'),
      'currency_code' => $order->getCurrency(),
      'cart_order_id' => $order->id(),
    );
/*
    $i = 0;
    foreach ($order->products as $product) {
      $i++;
      $data['li_' . $i . '_type'] = 'product';
      $data['li_' . $i . '_name'] = $product->title->value; // @todo: HTML escape and limit to 128 chars
      $data['li_' . $i . '_quantity'] = $product->qty->value;
      $data['li_' . $i . '_product_id'] = $product->model->value;
      $data['li_' . $i . '_price'] = uc_currency_format($product->price->value, FALSE, FALSE, '.');
    }
*/
   //$include_yolu = $_SERVER['DOCUMENT_ROOT'].'/modules/ubercart/payment/uc_iyzipay/iyzipay/samples/config.php';
	 $include_yolu = $_SERVER['DOCUMENT_ROOT'].'/libraries/iyzipay/IyzipayBootstrap.php';
	 
   require_once($include_yolu);

	

   $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
   $request->setLocale(\Iyzipay\Model\Locale::TR);
   $request->setConversationId($this->configuration['sid']);

   $request->setCurrency(\Iyzipay\Model\Currency::TL);
   $request->setBasketId("B67832");
   $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
   $request->setCallbackUrl("http://charity.webstudio.web.tr/cart/iyzipay/complete");
   $request->setEnabledInstallments(array(2, 3, 6, 9));

   $buyer = new \Iyzipay\Model\Buyer();
   $buyer->setId("BY789");
   $buyer->setName("John");
   $buyer->setSurname("Doe");
   $buyer->setGsmNumber("+905350000000");
   $buyer->setEmail("email@email.com");
   $buyer->setIdentityNumber("74300864791");
   $buyer->setLastLoginDate("2015-10-05 12:43:35");
   $buyer->setRegistrationDate("2013-04-21 15:12:09");
   $buyer->setRegistrationAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
   $buyer->setIp("85.34.78.112");
   $buyer->setCity("Istanbul");
   $buyer->setCountry("Turkey");
   $buyer->setZipCode("34732");

   $request->setBuyer($buyer);
   $shippingAddress = new \Iyzipay\Model\Address();
   $shippingAddress->setContactName("Jane Doe");
   $shippingAddress->setCity("Istanbul");
   $shippingAddress->setCountry("Turkey");
   $shippingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
   $shippingAddress->setZipCode("34742");
   $request->setShippingAddress($shippingAddress);

   $billingAddress = new \Iyzipay\Model\Address();
   $billingAddress->setContactName("Jane Doe");
   $billingAddress->setCity("Istanbul");
   $billingAddress->setCountry("Turkey");
   $billingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
   $billingAddress->setZipCode("34742");
   $request->setBillingAddress($billingAddress);


   $basketItems = array();

   $i = 0;
   $basketItem = array();
   $toplam_fiyat = 0;
   foreach ($order->products as $product) {

     $basketItems[$i] = new \Iyzipay\Model\BasketItem();
     $basketItems[$i]->setId($product->model->value);
     $basketItems[$i]->setName($product->title->value);
     $basketItems[$i]->setCategory1("product1");
     $basketItems[$i]->setCategory2("product2");
     $basketItems[$i]->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
     $basketItems[$i]->setPrice( ($product->price->value) * ($product->qty->value) );

    //drupal_set_message($product->qty->value);
     $i++;
     $data['li_' . $i . '_name'] = $product->title->value; // @todo: HTML escape and limit to 128 chars
     $data['li_' . $i . '_type'] = 'product';
     $data['li_' . $i . '_quantity'] = $product->qty->value;
     $data['li_' . $i . '_product_id'] = $product->model->value;
     $data['li_' . $i . '_price'] = uc_currency_format($product->price->value, FALSE, FALSE, '.');

     $toplam_fiyat= $toplam_fiyat + (uc_currency_format($product->price->value, FALSE, FALSE, '.')* ($product->qty->value) );
   }
   $request->setPrice($toplam_fiyat);
   $request->setPaidPrice($toplam_fiyat);
   $request->setBasketItems($basketItems);

/*
   $firstBasketItem = new \Iyzipay\Model\BasketItem();
   $firstBasketItem->setId("BI101");
   $firstBasketItem->setName("Binocular");
   $firstBasketItem->setCategory1("Collectibles");
   $firstBasketItem->setCategory2("Accessories");
   $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
   $firstBasketItem->setPrice("0.3");
   $basketItems[0] = $firstBasketItem;

   $secondBasketItem = new \Iyzipay\Model\BasketItem();
   $secondBasketItem->setId("BI102");
   $secondBasketItem->setName("Game code");
   $secondBasketItem->setCategory1("Game");
   $secondBasketItem->setCategory2("Online Game Items");
   $secondBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
   $secondBasketItem->setPrice("0.5");
   $basketItems[1] = $secondBasketItem;

   $thirdBasketItem = new \Iyzipay\Model\BasketItem();
   $thirdBasketItem->setId("BI103");
   $thirdBasketItem->setName("Usb");
   $thirdBasketItem->setCategory1("Electronics");
   $thirdBasketItem->setCategory2("Usb / Cable");
   $thirdBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
   $thirdBasketItem->setPrice("0.2");
   $basketItems[2] = $thirdBasketItem;
   $request->setBasketItems($basketItems);
*/
   //$checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, Config::options());
   //print_r($checkoutFormInitialize->getCheckoutFormContent());

    if ('direct' == $this->configuration['checkout_type']) {
      //$form['#attached']['library'][] = 'uc_iyzipay/iyzipay.direct';
    }

    $host = $this->configuration['demo'] ? 'sandbox' : 'www';
    $form['#action'] = "http://charity.webstudio.web.tr/cart/iyzipay/odeme";


    foreach ($data as $name => $value) {
      $form[$name] = array('#type' => 'hidden', '#value' => $value);
    }

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit order'),
    );

    return $form;
  }

}
/*
class Config
{
    public static function options()
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey("sandbox-mz89SNn9RuJIw8ZZdB8eeIZu3Vq4nW9S");
        $options->setSecretKey("sandbox-9sYAYshumBYTnwlEqXJTt2ML5m6jKHDj");
        $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        return $options;
    }
}
*/
?>
