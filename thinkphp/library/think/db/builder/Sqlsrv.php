<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\db\builder;

use think\db\Builder;
use think\db\Query;

/**
 * Sqlsrv数据库驱动
 */
class Sqlsrv extends Builder
{
    protected $selectSql       = 'SELECT T1.* FROM (SELECT thinkphp.*, ROW_NUMBER() OVER (%ORDER%) AS ROW_NUMBER FROM (SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%) AS thinkphp) AS T1 %LIMIT%%COMMENT%';
    protected $selectInsertSql = 'SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%';
    protected $updateSql       = 'UPDATE %TABLE% SET %SET% FROM %TABLE% %JOIN% %WHERE% %LIMIT% %LOCK%%COMMENT%';
    protected $deleteSql       = 'DELETE FROM %TABLE% %USING% FROM %TABLE% %JOIN% %WHERE% %LIMIT% %LOCK%%COMMENT%';
    protected $insertSql       = 'INSERT INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%';
    protected $insertAllSql    = 'INSERT INTO %TABLE% (%FIELD%) %DATA% %COMMENT%';

    /**
     * order分析
     * @access protected
     * @param Query     $query        查询对象
     * @param mixed     $order
     * @return string
     */
    protected function parseOrder(Query $query, $order)
    {
        if (is_array($order)) {
            $array = [];

            foreach ($order as $key => $val) {
                if (is_numeric($key)) {
                    if (false === strpos($val, '(')) {
                        $array[] = $this->parseKey($query, $val);
                    } elseif ('[rand]' == $val) {
                        $array[] = $this->parseRand($query);
                    } else {
                        $array[] = $val;
                    }
                } else {
                    $sort    = in_array(strtolower(trim($val)), ['asc', 'desc']) ? ' ' . $val : '';
                    $array[] = $this->parseKey($query, $key) . ' ' . $sort;
                }
            }

            $order = implode(',', $array);
        }

        return !empty($order) ? ' ORDER BY ' . $order : ' ORDER BY rand()';
    }

    /**
     * 随机排序
     * @access protected
     * @param Query     $query        查询对象
     * @return string
     */
    protected function parseRand(Query $query)
    {
        return 'rand()';
    }

    /**
     * 字段和表名处理
     * @access protected
     * @param Query     $query        查询对象
     * @param string    $key
     * @return string
     */
    protected function parseKey(Query $query, $key)
    {
        $key = trim($key);

        if (strpos($key, '.') && !preg_match('/[,\'\"\(\)\[\s]/', $key)) {
            list($table, $key) = explode('.', $key, 2);
            $alias             = $query->getOptions('alias');
            if (isset($alias[$table])) {
                $table = $alias[$table];
            } elseif ('__TABLE__' == $table) {
                $table = $query->getTable();
            }
        }

        if (!is_numeric($key) && !preg_match('/[,\'\"\*\(\)\[.\s]/', $key)) {
            $key = '[' . $key . ']';
        }

        if (isset($table)) {
            $key = '[' . $table . '].' . $key;
        }

        return $key;
    }

    /**
     * limit
     * @access protected
     * @param Query     $query        查询对象
     * @param mixed     $limit
     * @return string
     */
    protected function parseLimit(Query $query, $limit)
    {
        if (empty($limit)) {
            return '';
        }

        $limit = explode(',', $limit);

        if (count($limit) > 1) {
            $limitStr = '(T1.ROW_NUMBER BETWEEN ' . $limit[0] . ' + 1 AND ' . $limit[0] . ' + ' . $limit[1] . ')';
        } else {
            $limitStr = '(T1.ROW_NUMBER BETWEEN 1 AND ' . $limit[0] . ")";
        }

        return 'WHERE ' . $limitStr;
    }

    public function selectInsert(Query $query, $fields, $table)
    {
        $this->selectSql = $this->selectInsertSql;

        return parent::selectInsert($query, $fields, $table);
    }

}
