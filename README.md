> ### install by composer
```
composer require terry/easysolr:dev-master
```
> ### make tests
```
cd {$vendor directory}/terry/easysolr
phpunit
```
- The test cases cannot work because of the solrzs was a private solr cloud service
- You can modify the the stub test code to adapt your solr service
> ### use EasySolr

- instantiated EasySolr object

```
// $solrEntryPoint is the entry of solr query api, e.g. http://solr-host/solr/collection-name/
// $settings is query settings, e.g. ['wt' = 'json']
// $logLevel follow the level rules which decleared in \Psr\Log
$solr = new EasySolr($solrEntryPoint, $settings = [], $logLevel = \Psr\Log\LogLevel::WARNING);

```
- query docs
```
// the statements below equal to 
// query data by http://solr-host/solr/collection-name/select?q=(wx_name:rmrbwx)&wt=json&fl=wx_name,name,title,url,posttime_date,read_num_1&start=2&rows=2&sort=read_num_1 asc
$res = $solr->query([
            'criteria' => [
                [['wx_name', 'rmrbwx']]
            ],
            'fields' => ['wx_name', 'name', 'title', 'url', 'posttime_date', 'read_num_1'],
            'limit' => 2,
            'page' => 2,
            'sort' => 'read_num_1 asc'
        ]);
        
// criteria support nested query, if you want to build a query like "(name1:value1) OR ((name2:value2) AND (name3:value3))"
// you should just set the value of key 'criteria' like this
[['name1', 'value1']],
  [[
      [['name2', 'value2']],
      [['name3', 'value3'], 'AND']
  ], 'OR']
```
- query a doc
```
// the statements below equals to query data by
// http://solr-host/solr/collection-name/select?q=(sn:ce949ee86c1250865120d136f54497dd)&wt=json&fl=title,sn,posttime&start=0&rows=1&sort=
$doc = $solr->queryDoc([
            'criteria' => [
                [['sn', 'ce949ee86c1250865120d136f54497dd']],
            ],
            'fields' => ['title', 'sn', 'posttime'],
        ]);
```
- aggregate
```
the statements below equals to query data by 
// http://solr-host/solr/collection-name/select?q=*:*&wt=json&fl=&start=0&rows=0&sort=&facet=on&facet.limit=3&facet.field=wx_name&facet.mincount=1&facet.offset=0
$res = $solr->groupBy('wx_name', ['limit' => 3]);
// you can add more facet configs
// http://solr-host/solr/collection-name/select?q=(posttime_date:20180201)&wt=json&fl=&start=0&rows=0&sort=&facet=on&facet.limit=10&facet.mincount=10&facet.page=2&facet.field=wx_name&facet.offset=10
$res = $solr->groupBy('wx_name', [
            'limit' => 10,
            'mincount' => 10,
            'page' => 2,
        ], [
            'criteria' => [
                [['posttime_date', '20180201']]
            ]
        ]);
```