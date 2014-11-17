<?php
/**
 * pickles-sitemap-excel.php
 */
namespace tomk79\pickles2\sitemap_excel;

class pickles_sitemap_excel{
	private $px;

	/**
	 * entry
	 */
	static public function exec($px){
		new self($px);
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		require_once( __DIR__.'/daos/import.php' );
		require_once( __DIR__.'/daos/export.php' );
		$this->px = $px;

		$path_base = $this->px->get_path_homedir().'sitemaps/';
		$sitemap_files = $this->px->fs()->ls( $path_base );
		foreach( $sitemap_files as $filename ){
			$basename = $this->px->fs()->trim_extension($filename);
			$extension = $this->px->fs()->get_extension($filename);
			switch( strtolower($extension) ){
				// case 'xls':
				case 'xlsx':
					if( !$this->px->fs()->is_newer_a_than_b( $path_base.$basename.'.csv', $path_base.$filename ) ){
						$import = (new pxplugin_sitemapExcel_daos_import($this->px, $this))->import($path_base.$filename, $path_base.$basename.'.csv');
					}
					break;
			}
		}
	}

	/**
	 * PHPExcelHelper を生成する
	 */
	public function factory_PHPExcelHelper(){
		require_once( __DIR__.'/helper/PHPExcelHelper.php' );
		$phpExcelHelper = new pxplugin_sitemapExcel_helper_PHPExcelHelper($this->px);
		return $phpExcelHelper;
	}// factory_PHPExcelHelper()

}
