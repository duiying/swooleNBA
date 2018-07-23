<?php
namespace app\index\controller;

class Index
{
	public function index() {
		return '';
	}

	public function test() {
		echo 'test';
		print_r(['a'=>'1']);
	}
}
