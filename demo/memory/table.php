<?php
// swoole_table一个基于共享内存和锁实现的超高性能,并发数据结构,用于解决多进程/多线程数据共享和同步加锁问题
// 创建内存表
$table = new swoole_table(1024);

// 内存表增加一列
$table->column('id', $table::TYPE_INT, 4);
$table->column('name', $table::TYPE_STRING, 64);
$table->column('age', $table::TYPE_INT, 3);
$table->create();

// 设置行的数据
$table->set('wyx', ['id' => 1, 'name'=> 'wyx', 'age' => 30]);
// 另外一种方案
$table['wyx2'] = [
    'id' 	=> 2,
    'name' 	=> 'wyx2',
    'age' 	=> 31,
];

// 获取一行数据
print_r($table->get('wyx'));

// 自减操作
$table->decr('wyx2', 'age', 2);
print_r($table['wyx2']);

echo "delete start:".PHP_EOL;
// 删除数据
$table->del('wyx2');

print_r($table['wyx2']);
