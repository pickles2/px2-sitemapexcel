<?php
/**
 * test for tomk79\pickles-sitemap-excel
 * 
 * $ cd (project dir)
 * $ ./vendor/phpunit/phpunit/phpunit pickles2/php/tests/pickles-sitemap-excelTest.php picklesSitemapExcel
 */
// require_once( __DIR__.'/../filesystem.php' );

class picklesSitemapExcelTest extends PHPUnit_Framework_TestCase{

	public function setup(){
	}

	/**
	 * 絶対パス解決のテスト
	 */
	public function testGetRealpath(){

		// $this->assertEquals(
		// 	$this->fs->get_realpath('/'),
		// 	realpath('/')
		// );

		// $this->assertEquals(
		// 	$this->fs->get_realpath('./mktest/aaa.txt'),
		// 	$this->fs->localize_path(realpath('.').'/mktest/aaa.txt')
		// );

		// $this->assertEquals(
		// 	$this->fs->get_realpath('./mktest/./aaa.txt', __DIR__),
		// 	$this->fs->localize_path(__DIR__.'/mktest/aaa.txt')
		// );

		// $this->assertEquals(
		// 	$this->fs->get_realpath(__DIR__.'/./mktest/../aaa.txt'),
		// 	$this->fs->localize_path(__DIR__.'/aaa.txt')
		// );

		// $this->assertEquals(
		// 	$this->fs->get_realpath('C:\\mktest\\aaa.txt'),
		// 	$this->fs->localize_path('C:/mktest/aaa.txt')
		// );

		// $this->assertEquals(
		// 	$this->fs->get_realpath('\\\\mktest\\aaa.txt'),
		// 	$this->fs->localize_path('//mktest/aaa.txt')
		// );

		// $this->assertEquals(
		// 	$this->fs->get_realpath('../../../mktest/aaa.txt','/aaa/'),
		// 	$this->fs->localize_path('/mktest/aaa.txt')
		// );

		// $this->assertEquals(
		// 	$this->fs->get_realpath('/mktest/','/aaa/'),
		// 	DIRECTORY_SEPARATOR.'mktest'.DIRECTORY_SEPARATOR
		// );

	}

}
