<?php
/**
 * test for pickles2\px2-sitemapexcel
 */

class csv2xlsxTest extends PHPUnit_Framework_TestCase{

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');
	}

	/**
	 * `*.csv` to `*.xlsx` 変換のテスト
	 */
	public function testXlsx2CsvConvert(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx( __DIR__.'/testData/files/custom_column.csv', __DIR__.'/testData/files/dist/custom_column.xlsx' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/custom_column.xlsx' ) );

		// 値をチェック
		$objPHPExcel = \PHPExcel_IOFactory::load( __DIR__.'/testData/files/dist/custom_column.xlsx' );
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		$this->assertEquals( $objSheet->getCell('W8')->getCalculatedValue(), 'custom1' );
		$this->assertEquals( $objSheet->getCell('X8')->getCalculatedValue(), 'custom2' );
		$this->assertEquals( $objSheet->getCell('W9')->getCalculatedValue(), 'home-1' );
		$this->assertEquals( $objSheet->getCell('X9')->getCalculatedValue(), 'home-2' );
		$this->assertEquals( $objSheet->getCell('K13')->getCalculatedValue(), 'data:datascheme' );
		$this->assertEquals( $objSheet->getCell('K14')->getCalculatedValue(), 'javascript:alert(123);' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

	}//testXlsx2CsvConvert()

	/**
	 * 親ページがないページの変換テスト
	 */
	public function testHasNoParentConvert(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx( __DIR__.'/testData/files/has_no_parent.csv', __DIR__.'/testData/files/dist/has_no_parent.xlsx' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/has_no_parent.xlsx' ) );

		// 値をチェック
		$objPHPExcel = \PHPExcel_IOFactory::load( __DIR__.'/testData/files/dist/has_no_parent.xlsx' );
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		$this->assertEquals( $objSheet->getCell('Z8')->getCalculatedValue(), 'custom1' );
		$this->assertEquals( $objSheet->getCell('AA8')->getCalculatedValue(), 'custom2' );
		$this->assertEquals( $objSheet->getCell('Z9')->getCalculatedValue(), 'home-1' );
		$this->assertEquals( $objSheet->getCell('AA9')->getCalculatedValue(), 'home-2' );
		$this->assertEquals( $objSheet->getCell('A17')->getCalculatedValue(), 'EndOfData' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

	}//testHasNoParentConvert()




	/**
	 * `.px_execute.php` を実行し、標準出力値を返す
	 * @param string $path_entry_script エントリースクリプトのパス(testData起点)
	 * @param string $command コマンド(例: `/?PX=clearcache`)
	 * @return string コマンドの標準出力値
	 */
	private function px_execute( $path_entry_script, $command ){
		$output = $this->passthru( [
			'php', __DIR__.'/testData/'.$path_entry_script, $command
		] );
		clearstatcache();
		return $output;
	}

	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = escapeshellarg($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}

}
