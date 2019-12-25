<?php
/**
 * Author: huanw2010@gmail.com
 * Date: 2018/7/17 18:52
 */

namespace terry\solr;

use GuzzleHttp;
use Psr\Log\LogLevel;

class EasySolr
{
    private $point;
    private $settings = [
        'wt' => 'json',
    ];
    private $logger;
    private $fields = '';
    private $limit = 10;
    private $page = 1;
    private $sort = '';
    private $facet = '';
    private $socketConfig = [
        'timeout' => 3,
        'proxy' => null,
    ];

    public function __construct($point, $settings = [], $logLevel = LogLevel::WARNING)
    {
        $this->point = $point;
        if (isset($settings['sockect'])) {
            $this->socketConfig = $settings['sockect'];
            unset($settings['sockect']);
        }
        $this->settings = array_merge($this->settings, $settings);
        $this->logger = new EasyLogger($logLevel);
    }

    public function request($query)
    {
        $client = new GuzzleHttp\Client($this->socketConfig);

        $res = $client->get($this->prepareUrl($query))->getBody()->getContents();

        $this->logger->debug($res);

        if ($this->settings['wt'] == 'json') {
            return json_decode($res, true);
        }

        return $res;
    }

    /**
     * build facet string
     * @param $params
     * @return string
     */
    public static function buildFacet($params)
    {

        $facet = 'facet=on&';
        if (!isset($params['limit'])) {
            $params['limit'] = 10;
        }
        $page = isset($params['page']) ? intval($params['page']) : 1;
        if (!isset($params['mincount'])) {
            $params['mincount'] = 1;
        }

        $offset = ($page - 1) * intval($params['limit']);
        foreach ($params as $field => $value) {
            $params['facet.' . $field] = $value;
            unset($params[$field]);
        }
        $params['facet.offset'] = $offset;

        return $facet . http_build_query($params);
    }

    /**
     * query data
     * @param array $params
     */
    public function query($params = [])
    {
        $query = '*:*';
        if (!empty($params['criteria'])) {
            $query = self::buildQuery($params['criteria']);
        }

        if (!empty($params['fields'])) {
            $this->fields = implode(',', array_map(function ($value) {
                return trim($value);
            }, $params['fields']));
        }

        if (isset($params['limit'])) {
            $this->limit = intval($params['limit']);
        }

        if (!empty($params['page'])) {
            $this->page = abs(intval($params['page']));
        }

        if (!empty($params['sort'])) {
            $this->sort = $params['sort'];
        }

        if (!empty($params['facet']) && is_array($params['facet'])) {
            $this->facet = self::buildFacet($params['facet']);
        }
        return self::request($query);
    }

    /**
     * @param $params
     * @return array
     */
    public function queryDocs($params)
    {
        $res = $this->query($params);
        if (isset($res['response']['docs'])) {
            return $res['response']['docs'];
        }

        return [];
    }

    /**
     * @param $params
     * @return array|mixed
     */
    public function queryDoc($params)
    {
        $params['limit'] = 1;
        $docs = $this->queryDocs($params);
        return empty($docs[0]) ? [] : $docs[0];
    }

    /**
     * [
     *  ['name1' ,'value1', 'AND'],
     *  ['name2','value2', 'OR']
     * ]
     * @param $params
     */
    public static function buildQuery($params)
    {
        $query = '';
        if (is_array($params)) {
            foreach ($params as $name => $param) {
                $logic = isset($param[1]) ? $param[1] : 'AND';
                $pre = $query ? $query . ' ' . $logic : '';
                if (is_array($param[0][0])) {
                    $query = $pre . ' (' . self::buildQuery($param[0]) . ')';
                } else {
                    $query = $pre . ' (' . $param[0][0] . ':' . $param[0][1] . ')';
                }
            }
        }

        return trim($query);
    }

    public function prepareUrl($query)
    {
        $start = ($this->page - 1) * $this->limit;
        $rows = $this->limit;
        $url = rtrim($this->point, "/") . '/select?q=' . $query . '&' . http_build_query($this->settings) . '&fl=' . $this->fields . '&start=' . $start . '&rows=' . $rows . '&sort=' . $this->sort;
        if (!empty($this->facet)) {
            $url .= "&" . $this->facet;
        }

        $this->logger->info($url);
        return $url;
    }

    /**
     * get statistics by facet
     * @param $field
     * @param array $facetSettings
     * @param array $params
     * @return array
     */
    public function groupBy($field, $facetSettings = [], $params = [])
    {
        if (!isset($params['limit'])) {
            $params['limit'] = 0;
        }
        $facetSettings['field'] = $field;
        $params['facet'] = $facetSettings;
        $res = $this->query($params);
        if ($params['limit'] > 0) {
            return $res;
        }
        return isset($res['facet_counts']) ? $res['facet_counts'] : [];
    }

}