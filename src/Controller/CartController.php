<?php

namespace App\Controller;

use App\Form\CheckoutFormType;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

class CartController extends AbstractController
{
    private $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    /**
     * @Route("/cart", name="cart")
     */
    public function index(ProductRepository $productRepository)
    {
        $cart = $this->session->get('Cart');
        $Products = array();

        if($cart == true){
            foreach ($cart as $id => $product) {
                array_push($Products, ['Aantal' => $product['Aantal'], 'Product' => $productRepository->find($id)]);
            }
        }
            return $this->render('cart/index.html.twig', [
                'Products' => $Products,
            ]);
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function checkout (Request $request, ProductRepository $productRepository, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(CheckoutFormType::class);
        $form->handleRequest($request);
        $cart = $this->session->get("Cart", array());
        $Products = array();
        foreach($cart as $id => $product){
            array_push($Products, ["Aantal" => $product["Aantal"], "Product" => $productRepository->find($id)]);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $message = (new \Swift_Message('Bevestegings Email!'))
                ->setFrom('info@webshop.com')
                ->setReplyTo('1018265@mborijnland.nl')
                ->setTo($formData['Email'])
                ->setBody(
                    $this->renderView(
                        'emails/checkout.html.twig',
                        ["Naam" => $formData["Naam"], "Products" => $Products]
                    ),
                    'text/html'
                )
            ;
            $mailer->send($message);
            $this->session->set("Cart", array());
            return $this->redirectToRoute('product_index');
        }
        return $this->render('cart/checkout.html.twig', [
            "submitForm" => $form->createView(),
            "Products" => $Products,
        ]);
    }

}