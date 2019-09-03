<?php
namespace App\Controller;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
        foreach ($cart as $id => $product) {
            array_push($Products, ['Aantal' => $product['Aantal'], 'Product' => $productRepository->find($id)]);
        }
        return $this->render('cart/index.html.twig', [
            'Products' => $Products,
        ]);
    }
}