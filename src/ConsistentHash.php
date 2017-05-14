<?php
namespace Out001a;

/**
 * Class ConsistentHash
 *
 * 1. 指定哈希算法
 * 2. 将一个节点分成n个虚拟节点，虚拟节点按哈希算法映射到[0, 2^n - 1]的圆环上
 * 3. 可增加、删除节点，删除时会将该节点的所有虚拟节点的映射从圆环上删掉
 * 4. 将key按同样的哈希算法映射到[0, 2^n - 1]的圆环上，并顺时针查找到离key最近的节点
 */
class ConsistentHash
{
    private $_name      = '';
    private $_hashAlgo  = 'md5';

    private $_circle    = [];           // [index => key]，按key值升序
    private $_bucket    = [];           // [key => node]
    private $_capacity  = 1<<16;        // [0, 2 ^ 16 - 1]
    private $_virtNum   = (1<<8) - 1;

    /**
     * ConsistentHash constructor.
     * @param $name
     * @param string $hash_algo
     */
    public function __construct($name, $hash_algo = 'md5')
    {
        $this->_name = $name;
        if (function_exists($hash_algo)) {
            $this->_hashAlgo = $hash_algo;
        }
    }

    /**
     * 查找字符串映射到的节点
     *
     * @param string $str
     * @return string node
     */
    public function lookup($str)
    {
        $obj_key = $this->_hash($str);

        $index = $this->_locateInCircle($obj_key);
        if ($index >= count($this->_circle)) {
            $index = 0;
        }

        if (!isset($this->_circle[$index])) {
            return false;
        }

        $node_key = $this->_circle[$index];

        return $this->_bucket[$node_key];
    }

    /**
     * 增加节点
     *
     * @param string $node
     */
    public function addNode($node)
    {
        $this->_operateNode($node, function ($key) use ($node) {
            $index = $this->_locateInCircle($key);

            // key在circle内则不处理
            if (isset($this->_circle[$index]) && $key == $this->_circle[$index]) {
                return;
            }

            // add [index => key] to circle
            if ($index >= count($this->_circle)) {
                $this->_circle[] = $key;
            } else {
                for ($i = count($this->_circle); $i >= $index && $i > 0; $i--) {
                    $this->_circle[$i] = $this->_circle[$i-1];
                }
                $this->_circle[$index] = $key;
            }

            // add [key => node] to bucket
            $this->_bucket[$key] = $node;
        });
    }

    /**
     * 删除节点
     *
     * @param $node
     */
    public function removeNode($node)
    {
        $this->_operateNode($node, function ($key) {
            $index = $this->_locateInCircle($key);

            // key不在circle内则不处理
            if ($key != $this->_circle[$index]) {
                return;
            }

            $count = count($this->_circle);
            for ($i = $index; $i < $count - 1; $i++) {
                $this->_circle[$i] = $this->_circle[$i+1];
            }

            unset($this->_circle[$i]);
            unset($this->_bucket[$key]);
        });
    }

    private function _hash($str)
    {
        return hexdec(substr(call_user_func($this->_hashAlgo, $str), 0, 8)) % $this->_capacity;
    }

    private function _operateNode($node, Closure $operate)
    {
        for ($i = 0; $i < $this->_virtNum; $i++) {
            $virtual = "{$node}#{$i}";
            call_user_func($operate, $this->_hash($virtual));
        }
    }

    /**
     * 定位哈希串在circle中的位置
     *
     * @param $key
     */
    private function _locateInCircle($key)
    {
        $l = 0;
        $h = count($this->_circle) - 1;
        while ($l <= $h) {
            $m = intval(($l + $h) / 2);

            if ($this->_circle[$m] == $key) {
                return $m;
            }
            if ($this->_circle[$m] > $key) {
                $h = $m - 1;
            }
            if ($this->_circle[$m] < $key) {
                $l = $m + 1;
            }
        }
        return intval(($l + $h + 1) / 2);
    }

}