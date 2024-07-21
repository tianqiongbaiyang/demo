<?php
/**
 * 创建共享内存表
 * 当前进程完成后，内存中的数据会被自动释放
 */
$table = new Swoole\Table(1024);

//内存表增加一列
$table->column('id', Swoole\Table::TYPE_INT);
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->column('age', Swoole\Table::TYPE_INT, 3);

//操作系统申请内存，创建表
$table->create();

//设置行的数据
$table->set('singwa_imooc', ['id' => 1, 'name' => 'singwa', 'age' => 30]);

$table->decr('singwa_imooc', 'age', 2);
$table->incr('singwa_imooc', 'age', 20);

//获取一行数据
print_r($table->get('singwa_imooc'));

echo 'delete start:' . PHP_EOL;
$table->del('singwa_imooc');
var_dump($table->get('singwa_imooc'));
