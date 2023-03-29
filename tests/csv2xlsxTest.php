<?php
/**
 * test for pickles2\px2-sitemapexcel
 */

class csv2xlsxTest extends PHPUnit\Framework\TestCase{

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setup() : void{
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
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx( __DIR__.'/testData/files/custom_column.csv', __DIR__.'/testData/files/dist/custom_column.xlsx' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/custom_column.xlsx' ) );

		// 値をチェック
		$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load( __DIR__.'/testData/files/dist/custom_column.xlsx' );
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
	}

	/**
	 * 親ページがないページの変換テスト
	 */
	public function testHasNoParentConvert(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx( __DIR__.'/testData/files/has_no_parent.csv', __DIR__.'/testData/files/dist/has_no_parent.xlsx' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/has_no_parent.xlsx' ) );

		// 値をチェック
		$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load( __DIR__.'/testData/files/dist/has_no_parent.xlsx' );
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		$this->assertEquals( $objSheet->getCell('Z8')->getCalculatedValue(), 'custom1' );
		$this->assertEquals( $objSheet->getCell('AA8')->getCalculatedValue(), 'custom2' );
		$this->assertEquals( $objSheet->getCell('Z9')->getCalculatedValue(), 'home-1' );
		$this->assertEquals( $objSheet->getCell('AA9')->getCalculatedValue(), 'home-2' );
		$this->assertEquals( $objSheet->getCell('B12')->getCalculatedValue(), 'Category 1-2' );
		$this->assertEquals( $objSheet->getCell('N14')->getCalculatedValue(), '/category2.html' );
		$this->assertEquals( $objSheet->getCell('A17')->getCalculatedValue(), 'EndOfData' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);
	}

	/**
	 * トップページがないCSVの変換テスト
	 */
	public function testHasNoToppageConvert(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx( __DIR__.'/testData/files/has_no_toppage.csv', __DIR__.'/testData/files/dist/has_no_toppage.xlsx' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/has_no_toppage.xlsx' ) );

		// 値をチェック
		$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load( __DIR__.'/testData/files/dist/has_no_toppage.xlsx' );
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		$this->assertEquals( $objSheet->getCell('Z8')->getCalculatedValue(), 'custom1' );
		$this->assertEquals( $objSheet->getCell('AA8')->getCalculatedValue(), 'custom2' );
		$this->assertEquals( $objSheet->getCell('B11')->getCalculatedValue(), 'Category 1-2' );
		$this->assertEquals( $objSheet->getCell('N13')->getCalculatedValue(), '/category2.html' );
		$this->assertEquals( $objSheet->getCell('A16')->getCalculatedValue(), 'EndOfData' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);
	}

	/**
	 * ブログマップCSVの変換テスト
	 */
	public function testBlogmapConvert(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx(
			__DIR__.'/testData/files/blogmap.csv',
			__DIR__.'/testData/files/dist/blogmap.xlsx',
			array('target'=>'blogmap')
		);
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/blogmap.xlsx' ) );

		// 値をチェック
		$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load( __DIR__.'/testData/files/dist/blogmap.xlsx' );
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		$this->assertEquals( $objSheet->getCell('A8')->getCalculatedValue(), 'title' );
		$this->assertEquals( $objSheet->getCell('A9')->getCalculatedValue(), 'Page 0/10' );
		$this->assertEquals( $objSheet->getCell('B15')->getCalculatedValue(), '/page/page_6_10.html' );
		$this->assertEquals( $objSheet->getCell('C17')->getCalculatedValue(), '2023-01-09' );
		$this->assertEquals( $objSheet->getCell('D12')->getCalculatedValue(), '2023-01-04' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);
	}

	/**
	 * ブログマップCSVの変換テスト (列の欠損と追加)
	 */
	public function testBlogmapLessAndMoreConvert(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx(
			__DIR__.'/testData/files/blogmap_less_and_more.csv',
			__DIR__.'/testData/files/dist/blogmap_less_and_more.xlsx',
			array('target'=>'blogmap')
		);
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/blogmap_less_and_more.xlsx' ) );

		// 値をチェック
		$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load( __DIR__.'/testData/files/dist/blogmap_less_and_more.xlsx' );
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		$this->assertEquals( $objSheet->getCell('A8')->getCalculatedValue(), 'title' );
		$this->assertEquals( $objSheet->getCell('A9')->getCalculatedValue(), 'Page 0/10' );
		$this->assertEquals( $objSheet->getCell('B15')->getCalculatedValue(), '/page/page_6_10.html' );
		$this->assertEquals( $objSheet->getCell('C17')->getCalculatedValue(), '2023-01-09' );
		$this->assertEquals( $objSheet->getCell('D12')->getCalculatedValue(), '2023-01-04' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);
	}




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
