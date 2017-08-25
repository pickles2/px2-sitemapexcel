<?php
/**
 * test for pickles2\px2-sitemapexcel
 */

class xlsx2csvTest extends PHPUnit_Framework_TestCase{

	/**
	 * Current Directory
	 */
	private $cd;

	/**
	 * Pickles Framework
	 */
	private $px;

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setup(){
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');
		$this->fs = new \tomk79\filesystem();

		$this->cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$this->px = new picklesFramework2\px('./px-files/');

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');
	}

	/**
	 * teardown
	 */
	public function teardown(){
		chdir($this->cd);
		$this->px->__destruct();// <- required on Windows
		unset($this->px);
	}

	/**
	 * ページIDのないエイリアスの変換テスト
	 */
	public function testAliasWithoutPageIdConvert(){

		$px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($this->px);
		$px2_sitemapexcel->xlsx2csv( __DIR__.'/testData/files/alias_without_page_id.xlsx', __DIR__.'/testData/files/dist/alias_without_page_id.csv' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/alias_without_page_id.csv' ) );
		$csv = $this->fs->read_csv( __DIR__.'/testData/files/dist/alias_without_page_id.csv' );
		// var_dump($csv);
		$this->assertEquals( $csv[6][0], 'alias:/category2/index.html' );
		$this->assertEquals( $csv[6][2], 'sitemapExcel_auto_id_alias_without_page_id-1' );
		$this->assertEquals( $csv[6][3], 'Category 2 (Alias)' );
		$this->assertEquals( $csv[7][0], '/category2/index.html' );
		$this->assertEquals( $csv[7][2], '' );
		$this->assertEquals( $csv[7][3], 'Category 2 Page 1' );
		$this->assertEquals( $csv[7][8], 'sitemapExcel_auto_id_alias_without_page_id-1' );
		$this->assertEquals( $csv[10][0], 'alias:/category3/index.html' );
		$this->assertEquals( $csv[10][2], 'sitemapExcel_auto_id_alias_without_page_id-2' );
		$this->assertEquals( $csv[10][3], 'Category 3' );
		$this->assertEquals( $csv[10][8], '' );
		$this->assertEquals( $csv[11][0], '/category3/index.html' );
		$this->assertEquals( $csv[11][2], 'sitemapExcel_auto_id_alias_without_page_id-3' );
		$this->assertEquals( $csv[11][3], 'Category 3 Page 1' );
		$this->assertEquals( $csv[11][8], 'sitemapExcel_auto_id_alias_without_page_id-2' );

	}//testAliasWithoutPageIdConvert()

	/**
	 * logcal_path 列を持ったXLSXの変換テスト
	 */
	public function testHasLogicalPathConvert(){

		$px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($this->px);
		$px2_sitemapexcel->xlsx2csv( __DIR__.'/testData/files/has_logical_path.xlsx', __DIR__.'/testData/files/dist/has_logical_path.csv' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/has_logical_path.csv' ) );
		$csv = $this->fs->read_csv( __DIR__.'/testData/files/dist/has_logical_path.csv' );
		// var_dump($csv);
		$this->assertEquals( $csv[1][8], '' );
		$this->assertEquals( $csv[2][0], '/category1/index.html' );
		$this->assertEquals( $csv[2][8], '' );
		$this->assertEquals( $csv[2][9], '1' );// list_flg 自動付与
		$this->assertEquals( $csv[3][8], '/category1/' );
		$this->assertEquals( $csv[6][0], 'alias:/category2/index.html' );
		$this->assertEquals( $csv[6][2], 'sitemapExcel_auto_id_has_logical_path-1' );
			// ↑ エイリアスにはCSV上に自動的にIDが振られる。(それ以外は、PxFW2がCSVのパース時にIDを自動付与するので、 `px2-sitemapexcel` の処理としては自動で振らない)
		$this->assertEquals( $csv[6][3], 'Category 2 (Alias)' );
		$this->assertEquals( $csv[6][8], '/category1/' );
		$this->assertEquals( $csv[6][9], '1' );// list_flg 自動付与
		$this->assertEquals( $csv[7][0], '/category2/index.html' );
		$this->assertEquals( $csv[7][2], '' );
		$this->assertEquals( $csv[7][3], 'Category 2 Page 1' );
		$this->assertEquals( $csv[7][8], '/category1/' );
		$this->assertEquals( $csv[7][9], '1' );// list_flg 自動付与

	}//testHasLogicalPathConvert()

	/**
	 * タイトル列終端の右のセルに値が入っている場合のテスト
	 */
	public function testTitle(){

		$px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($this->px);
		$px2_sitemapexcel->xlsx2csv( __DIR__.'/testData/files/has_value_right_of_title_col.xlsx', __DIR__.'/testData/files/dist/has_value_right_of_title_col.csv' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/has_value_right_of_title_col.csv' ) );
		$csv = $this->fs->read_csv( __DIR__.'/testData/files/dist/has_value_right_of_title_col.csv' );
		// var_dump($csv);
		$this->assertEquals( $csv[1][8], '' );
		$this->assertEquals( $csv[2][0], '/category1/index.html' );
		$this->assertEquals( $csv[2][8], '' );
		$this->assertEquals( $csv[2][9], '1' );// list_flg 自動付与
		$this->assertEquals( $csv[3][8], '/category1/' );
		$this->assertEquals( $csv[6][0], '/category2/index.html' );
		$this->assertEquals( $csv[6][2], '' );
		$this->assertEquals( $csv[6][3], 'Category 2' );
		$this->assertEquals( $csv[6][8], '/category1/' );
		$this->assertEquals( $csv[6][9], '1' );// list_flg 自動付与
		$this->assertEquals( $csv[7][0], '/category2/page1.html' );
		$this->assertEquals( $csv[7][2], '' );
		$this->assertEquals( $csv[7][3], 'Category 2 Page 1' );
		$this->assertEquals( $csv[7][8], '/category1/' );
		$this->assertEquals( $csv[7][9], '1' );// list_flg 自動付与

	} // testTitle()

}
