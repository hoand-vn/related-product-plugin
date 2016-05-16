<?php
namespace Plugin\RelatedProduct\tests\Web;
use Eccube\Tests\Web\AbstractWebTestCase;

/**
 * Class test of controller
 */
class FontRelatedProductControllerTest extends AbstractWebTestCase
{


    public function testDetailProduct()
    {
        $html = $this->client->request('GET',
            $this->app->url('product_detail',array('id'=>6))
        );
        $this->assertContains('関連商品',$html->html());

    }
}