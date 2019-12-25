<?php

namespace terry\solr;

use terry\solr\EasySolr;

class SolrTest extends \Codeception\Test\Unit
{
    /**
     * @var \terry\solr\UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {

    }

    public function testBuildFacet()
    {
        $params = [
            'page' => 1,
        ];

        $facet = EasySolr::buildFacet($params);
//        echo "\n";
//        echo $facet;
        $this->assertEquals("facet=on&facet.page=1&facet.limit=10&facet.mincount=1&facet.offset=0", $facet);
    }
}