<?php
/**
 * Author: huanw2010@gmail.com
 * Date: 2019/12/26 11:37
 */
require __DIR__ . '/../../autoload.php';

$solr = new terry\solr\EasySolr("http://solrzs01/solr/wx_documents_2019_11", [], \Psr\Log\LogLevel::ERROR);
$res = $solr->query([
    'criteria' => [
        [['wx_name', 'rmrbwx']]
    ],
    'fields' => ['wx_name', 'name', 'title', 'url', 'posttime_date', 'read_num_1'],
    'limit' => 2,
    'page' => 2,
    'sort' => 'read_num_1 asc'
]);
print_r($res);