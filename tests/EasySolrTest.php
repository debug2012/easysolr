<?php
/**
 * Author: huanw2010@gmail.com
 * Date: 2018/7/17 19:12
 */
use PHPUnit\Framework\TestCase;
use terry\solr\EasySolr;

final class EasySolrTest extends TestCase
{
    public function testBuildQuery()
    {
        $params = [
            [['name1', 'value1'], 'AND'],
            [['name2', 'value2'], 'OR']
        ];

        echo PHP_EOL;
        $query = EasySolr::buildQuery($params);
//        echo $query;
        $this->assertEquals("(name1:value1) OR (name2:value2)", $query);

        $params = [
            [['name1', 'value1'], 'AND'],
            [['name2', 'value2']]
        ];

        echo PHP_EOL;
        $query = EasySolr::buildQuery($params);
//        echo $query;
        $this->assertEquals("(name1:value1) AND (name2:value2)", $query);

        $params = [
            [['name1', 'value1'], 'AND'],
            [['name2', '"value2"']]
        ];

        echo PHP_EOL;
        $query = EasySolr::buildQuery($params);
//        echo $query;
        $this->assertEquals("(name1:value1) AND (name2:\"value2\")", $query);

        $params = [
            [['name1', 'value1']],
            [[
                [['name2', 'value2']],
                [['name3', 'value3'], 'AND']
            ], 'OR']
        ];

        echo PHP_EOL;
        $query = EasySolr::buildQuery($params);
//        echo $query;
        $this->assertEquals("(name1:value1) OR ((name2:value2) AND (name3:value3))", $query);
    }

    public function testQuery()
    {
        echo "\n";
        $solr = new EasySolr("http://solrzs01/solr/wx_documents_2019_11", [], \Psr\Log\LogLevel::ERROR);
        $res = $solr->query([
            'criteria' => [
                [['wx_name', 'rmrbwx']]
            ],
            'fields' => ['wx_name', 'name', 'title', 'url', 'posttime_date', 'read_num_1'],
            'limit' => 2,
            'page' => 2,
            'sort' => 'read_num_1 asc'
        ]);

//        print_r($res);
        $this->assertArrayHasKey('responseHeader', $res);
    }

    public function testQueryDocs()
    {
        $solr = new EasySolr("http://solrzs01/solr/wx_documents_2019_11", [], \Psr\Log\LogLevel::ERROR);
        $res = $solr->queryDocs([
            'criteria' => [
                [['wx_name', 'rmrbwx']]
            ],
            'fields' => ['wx_name', 'name', 'title', 'url', 'posttime_date', 'read_num_1'],
            'limit' => 2,
            'page' => 2,
            'sort' => 'read_num_1 asc'
        ]);

        $this->assertArrayHasKey(0, $res);
    }

    public function testQueryDoc()
    {
        $solr = new EasySolr("http://solrzs01/solr/wx_documents_2019_11", [], \Psr\Log\LogLevel::WARNING);
        $doc = $solr->queryDoc([
            'criteria' => [
                [['sn', 'ef58ec587b983de3073fc715b23f5e2a']],
            ],
            'fields' => ['title', 'sn', 'posttime'],
        ]);

//        print_r($doc);
        $this->assertArrayHasKey("sn", $doc);
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

    public function testGroupBy()
    {
        $solr = new EasySolr("http://solrzs01/solr/wx_documents_2019_11", [], \Psr\Log\LogLevel::INFO);
        $res = $solr->groupBy('wx_name', ['limit' => 3]);
//        print_r($res);
        $this->assertArrayHasKey("facet_fields", $res);

        $res = $solr->groupBy('wx_name', [
            'limit' => 10,
            'mincount' => 10,
            'page' => 2,
        ], [
            'criteria' => [
                [['posttime_date', '20180201']]
            ]
        ]);
//        print_r($res);
        $this->assertArrayHasKey("facet_fields", $res);
    }
}