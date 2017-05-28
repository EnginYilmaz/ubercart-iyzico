<?php
namespace Drupal\uc_iyzipay\Controller;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\uc_cart\CartManagerInterface;
use Drupal\uc_order\Entity\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
/**
 * Controller routines for uc_iyzipay.
 */
class IyzipayController extends ControllerBase {
  /**
   * The cart manager.
   *
   * @var \Drupal\uc_cart\CartManager
   */
  protected $cartManager;
  /**
   * Constructs a IyzipayController.
   *
   * @param \Drupal\uc_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   */
  public function __construct(CartManagerInterface $cart_manager) {
    $this->cartManager = $cart_manager;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // @todo: Also need to inject logger
    return new static(
      $container->get('uc_cart.manager')
    );
  }
  /**
   * Finalizes Iyzipay transaction.
   *
   * @param int $cart_id
   *   The cart identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   */
  public function complete($cart_id = 0, Request $request1) {
		$conversation_id = $_SESSION['Iyzipay']['conversation_id'];
		$token= $_POST["token"];
		$include_yolu = $_SERVER['DOCUMENT_ROOT'].'/libraries/iyzipay/IyzipayBootstrap.php';
		require_once($include_yolu);
		$request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
		$request->setLocale(\Iyzipay\Model\Locale::TR);
		$request->setToken($token);
		
		$options = new \Iyzipay\Options();
   	$config = \Drupal::config('iyzipay.settings');
	 	$apikey=$config->get('apikey');
	 	$secretkey= $config->get('secretkey');
	 	$baseurl= $config->get('baseurl');
	  
	  $options = new \Iyzipay\Options();
    $options->setApiKey($apikey);
    $options->setSecretKey($secretkey);
    $options->setBaseUrl($baseurl);
    
		$checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, $options);
		print_r($checkoutForm->getPaymentStatus());
		print_r($checkoutForm->getErrorMessage());
		print_r($checkoutForm->getStatus());
		if ($token) {
			$order = Order::load($conversation_id);
      $order->setStatusId('payment_received')->save();
	    return [
	      '#markup' => t ("odeme tamamlandı"),
	    ];
		} else {
	    return [
	      '#markup' => t ("odeme yapılmadı"),
	    ];			
		}
	}
  /**
   * React on INS messages from Iyzipay.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   */
  public function odeme($cart_id = 0, Request $request1) {
		$include_yolu = $_SERVER['DOCUMENT_ROOT'].'/libraries/iyzipay/IyzipayBootstrap.php';
  	require_once($include_yolu);
		$order = Order::create(array(
		  'uid' => 1,
			'order_status' => uc_order_state_default('post_checkout'),
		));
		
		$order->save();
		uc_order_comment_save($order->id(), $this->currentUser()->id(), $this->t('Iyzico ödeme yöntemi tarafından eklenmiş bir ödeme ancak ödeme henüz yapıldı mı kontrol etmelisiniz.'), 'admin');
		$product = \Drupal::entityTypeManager()->getStorage('uc_order_product')->create(array(
		'billing-first-name' => "deneme",
		'qty' => 1,
		'order_id' => $order->id(),
		//'nid' => 3,
		));
		$config = \Drupal::config('iyzipay.settings');
		$product->save();
		$_SESSION['Iyzipay']['conversation_id'] = $order->id();
		uc_order_product_save($order->id(), $product);
		$this->conversationID= $order->id();
		$request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
		$request->setLocale(\Iyzipay\Model\Locale::TR);
		$request->setPrice("1");
		$request->setPaidPrice("1.2");
		$request->setCurrency(\Iyzipay\Model\Currency::TL);
		$request->setBasketId("B67832");
		$request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
		
		$complateurl= $config->get('complateurl');
		$request->setCallbackUrl($complateurl);
		
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
	 	$toplam_fiyat=0;
   	$basketItems = array();
   	for ($i=0;$i<3;$i++) {
		 $j= $i+1;
		 $fiyat= \Drupal::request()->request->get('li_' . $j . '_price');
		 $miktar = \Drupal::request()->request->get('li_' . $j . '_quantity');
		 if ( \Drupal::request()->request->get('li_' . $j. '_price') != 0) {
		   $basketItems[$i] = new \Iyzipay\Model\BasketItem();
		   $basketItems[$i]->setId("BY789");
		   $basketItems[$i]->setName(\Drupal::request()->request->get('li_' . $j . '_name'));
		   $basketItems[$i]->setCategory1(\Drupal::request()->request->get('li_' . $j . '_type'));
		   $basketItems[$i]->setCategory2("product3");
		   $basketItems[$i]->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
			 $tutar = $fiyat * $miktar;
		   $basketItems[$i]->setPrice( $tutar );
			 $toplam_fiyat= $toplam_fiyat + $tutar;
	 		}
   	}
    $request->setPrice($toplam_fiyat);
    $request->setPaidPrice($toplam_fiyat);
    $request->setBasketItems($basketItems);
	 
	  $options = new \Iyzipay\Options();
	 	$apikey=$config->get('apikey');
	 	$secretkey= $config->get('secretkey');
	 	$baseurl= $config->get('baseurl');
	 		 
	  $options = new \Iyzipay\Options();
    $options->setApiKey($apikey);
    $options->setSecretKey($secretkey);
    $options->setBaseUrl($baseurl);
        
    $checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, $options);
    print_r($checkoutFormInitialize->getStatus());
	  print_r($checkoutFormInitialize->getErrorMessage());
	  print_r($checkoutFormInitialize->getCheckoutFormContent());
		return [
    	'#markup' => t ("5526080000000006 deneme kredi kartı numarası</br> <div id=\"iyzipay-checkout-form\" class=\"responsive\"></div>"),
   ];
  }
  public function notification(Request $request) {
    $values = $request->request;
    \Drupal::logger('uc_iyzipay')->notice('Received Iyzipay notification with following data: @data', ['@data' => print_r($values->all(), TRUE)]);
    if ($values->has('message_type') && $values->has('md5_hash') && $values->has('message_id')) {
      $order_id = $values->get('vendor_order_id');
      $order = Order::load($order_id);
      $plugin = \Drupal::service('plugin.manager.uc_payment.method')->createFromOrder($order);
      $configuration = $plugin->getConfiguration();
      // Validate the hash
      $secret_word = $configuration['secret_word'];
      $sid = $configuration['sid'];
      $iyzipay_order_id = $values->get('sale_id');
      $iyzipay_invoice_id = $values->get('invoice_id');
      $hash = strtoupper(md5($iyzipay_order_id . $sid . $iyzipay_invoice_id . $secret_word));
      if ($hash != $values->get('md5_hash')) {
        \Drupal::logger('uc_iyzipay')->notice('Iyzipay notification #@num had a wrong hash.', ['@num' => $values->get('message_id')]);
        die('Hash Incorrect');
      }
      if ($values->get('message_type') == 'FRAUD_STATUS_CHANGED') {
        switch ($values->get('fraud_status')) {
// @todo: I think this still needs a lot of work, I don't see anywhere that it
// validates the INS against an order in the DB then changes order status if the
// payment was successful, like PayPal IPN does ...
          case 'pass':
            break;
          case 'wait':
            break;
          case 'fail':
            // @todo uc_order_update_status($order_id, uc_order_state_default('canceled'));
            $order->setStatusId('canceled')->save();
            uc_order_comment_save($order_id, 0, $this->t('Order have not passed Iyzipay fraud review.'));
            die('fraud');
            break;
        }
      }
      elseif ($values->get('message_type') == 'REFUND_ISSUED') {
        // @todo uc_order_update_status($order_id, uc_order_state_default('canceled'));
        $order->setStatusId('canceled')->save();
        uc_order_comment_save($order_id, 0, $this->t('Order have been refunded through Iyzipay.'));
        die('refund');
      }
    }
    die('ok');
  }
}
