<?php

namespace Drupal\auto_add_to_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce;

use Drupal\commerce_cart;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;


use Drupal\commerce_cart\CartManager;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_cart\CartSessionInterface;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;



class CartsController extends ControllerBase {


    /**
     * 
     * The cart manager.
     * 
     * @var \Drupal\commerce_cart\CartManagerInterface
     * 
     * Credit: https://www.valuebound.com/resources/blog/how-to-add-a-product-programmatically-to-drupal-commerce-cart
     */
    
    protected $cartManager;
    
    /**
    * The cart provider.
    *
    * @var \Drupal\commerce_cart\CartProviderInterface
    */
    protected $cartProvider;
    
    /**
    * The cart session.
    *
    * @var \Drupal\commerce_cart\CartSessionInterface
    */
   protected $cartSession;
    
    /**
    * The order type resolver.
    *
    * @var \Drupal\commerce_order\Resolver\OrderTypeResolverInterface
    */
    protected $orderTypeResolver;


    public function __construct(CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, OrderTypeResolverInterface $order_type_resolver, CartSessionInterface $cart_session) {
        $this->cartManager = $cart_manager;
        $this->cartProvider = $cart_provider;
        $this->cartSession = $cart_session;
        $this->orderTypeResolver = $order_type_resolver;
    }
    
    /**
    * {@inheritdoc}
    */
    public static function create(ContainerInterface $container) {
     return new static(
       $container->get('commerce_cart.cart_manager'),
       $container->get('commerce_cart.cart_provider'),
       $container->get('commerce_order.chain_order_type_resolver'),
       $container->get('commerce_cart.cart_session')
     );
    }
    

    public function addToCart($productId) {
        
        $destination = \Drupal::service('path.current')->getPath();
        $productObj = Product::load($productId);
        
        $product_variation_id = $productObj->get('variations')
                ->getValue()[0]['target_id'];
        $storeId = $productObj->get('stores')->getValue()[0]['target_id'];
        $variationobj = \Drupal::entityTypeManager()
                ->getStorage('commerce_product_variation')
                ->load($product_variation_id);
        
        $store = \Drupal::entityTypeManager()
                ->getStorage('commerce_store')
                ->load($storeId);
        
        // Add item to cart
        $cart = $this->cartProvider->getCart('default', $store);
        
        if (!$cart) {
            $cart = $this->cartProvider->createCart('default', $store);
        }
        
        $line_item_type_storage = \Drupal::entityTypeManager()
                ->getStorage('commerce_order_item_type');
        
        // Process to place order programatically. 
        $cart_manager = \Drupal::service('commerce_cart.cart_manager');
        $line_item = $cart_manager->addEntity($cart, $variationobj);
        
        
        // Redirect to checkout (skit view cart)
        $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->create([
            'type' => "default",
            'store_id' => $store->id(),
            'uid' => $this->currentUser()->id(),
            'cart' => FALSE,
        ]);
        
        
        $order_item = \Drupal\commerce_order\Entity\OrderItem::create([
          'type' => 'default',
          'purchased_entity' => $variationobj,
          'quantity' => 1,
          'unit_price' => $variationobj->getPrice(),
        ]);
        $order_item->save();

        
        $order->addItem($order_item);
        $order->save();

        // Add the order as a completed cart to allow anonymous checkout access.
        // @todo Find a better way for this.
        $this->cartSession->addCartId($order->id(), CartSessionInterface::COMPLETED);
       // $this->setRedirectDestination('commerce_checkout.form', ['commerce_order' => $order->id()]);        
        
        $url = Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id()]);
        return new RedirectResponse($url->toString());
    }
}
