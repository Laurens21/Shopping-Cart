<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\Product1Type;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function mysql_xdevapi\getSession;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        $cart = $this->session->get('Cart', []);


        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
            'cart' => $cart
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(Product1Type::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(Product1Type::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/{id}/add", name="product_add", methods={"GET","POST"})
     */
    public function add(Product $product, ProductRepository $productRepository): Response
    {
        $cart = $this->session->get('Cart');
        $id = $product->getId();
            if(isset($cart[$id])) {
                $cart[$id]['Aantal']++;
            } else {
                $cart[$id]['Aantal'] = 1;
            }
                
            $this->session->set('Cart', $cart);

            // var_dump($this->session->get('Cart'));
            
        return $this->redirectToRoute("cart");
    }

        /**
     * @Route("/{id}/del", name="product_del", methods={"GET","POST"})
     */
    public function del(Product $product, ProductRepository $productRepository): Response
    {
        $cart = $this->session->get('Cart');
        $id = $product->getId();
            if(isset($cart[$id])) {
                unset($cart[$id]);
            }
                
            $this->session->set('Cart', $cart);

            // var_dump($this->session->get('Cart'));
            
        return $this->redirectToRoute("cart");
    }
}
