<?php
namespace Plugin\RelatedProduct\Tests\Web;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

/**
 * Class test of controller
 */
class RelatedProductControllerTest extends AbstractAdminWebTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test routing
     *
     */
    public function testRoutingAdminProductRegistration()
    {
        $html = $this->client->request('GET',
            $this->app->url('admin_product_product_new')
        );

        $this->assertContains('関連商品',$html->html());
    }

    
    /**
     * Create data
     *
     */
    public function createFormData()
    {
        $faker = $this->getFaker();
        $form = array(
            '_token' => 'dummy',
            'name' => $faker->numerify('Hoa ####'),
            'class' => array('product_type'=>1, 'price02'=>50,'stock_unlimited'=>1),
            'description_detail'=>$faker->numerify('Hoa desc ####'),
            'Status'=>1,
            'related_collection' =>array(0=>array('ChildProduct'=>1,'content'=>$faker->numerify('Hoa content ####')))
        );
        return $form;
    }
    /**
     * Test edit
     *
     */
    public function testCreateProductRelated()
    {
        $formData = $this->createFormData();
        $html = $this->client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        /*
         * way 1: use content to identify
         */
        //$relatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(array('content'=>$formData['related_collection'][0]['content']));

        /*
         * way 2: can not use this way. we have to use object product
         *
         * $relatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(array('content'=>$formData['related_collection'][0]['content'],'childProductId'=>$formData['related_collection'][0]['ChildProduct']));
        */

        /*
         * way 3: use object product
         */
        $childProduct = $this->app['eccube.repository.product']->find($formData['related_collection'][0]['ChildProduct']);
        $relatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(array('content'=>$formData['related_collection'][0]['content'], 'ChildProduct' => $childProduct ));


        $this->expected = $formData['related_collection'][0]['ChildProduct'];
        $this->actual = $relatedProduct->getChildProduct()->getId();
        $this->verify();

        /*
         * another way : on client
         */
//        $this->assertContains('edit',$this->client->getResponse()->headers->get('location'));
    }
    /**
     * Filling data
     *
     */
    public function fillingFormData( $productId,$childProductId )
    {
        $faker = $this->getFaker();
        $Product = $this->app['eccube.repository.product']->find($productId);
        $form = array(

            '_token' => 'dummy',
            'name' => $Product->getName(),
            'class' => $Product->getClassCategories()['__unselected2']['#'],
            'description_detail'=>$Product->getDescriptionDetail().'###6677##',
            'Status'=>1,//$Product->getStatus(),
            'related_collection' =>array(0=>array('ChildProduct'=>$childProductId,'content'=>$faker->numerify('Hoa content ####')))
        );
        return $form;
    }
    public function testUpdateProductRelated()
    {
        $productId = 6;
        $childProductId = 4;
        $formData = $this->fillingFormData( $productId, $childProductId );

        $this->client->request(
            'POST',
            $this->app->url('admin_product_product_edit',array('id'=>$productId)),
            array('admin_product' => $formData)
        );
        $childProduct = $this->app['eccube.repository.product']->find($childProductId);
        $relatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(array('ChildProduct'=>$childProduct ));

        $this->expected = $productId;
        $this->actual = $relatedProduct->getProduct()->getId();
        $this->verify();

    }
    public function createSearchData( )
    {
        $form = array(

            '_token' => 'dummy',
            'id' => 'hoa',
            'category_id' => 5,
            'product_id'=>8
        );
        return $form;
    }
    public function testSearchProduct()
    {
        $formData = $this->createSearchData( );

        $html = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search'),
            array('admin_search' => $formData),
            array(),
            array( 'HTTP_X-Requested-With' => 'XMLHttpRequest' )
        );
        $this->assertContains('決定',$html->html());
    }

}