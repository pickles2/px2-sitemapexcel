pickles2/px2-sitemapexcel
=======================

<table class="def">
  <thead>
	<tr>
	  <th></th>
	  <th>Linux</th>
	  <th>Windows</th>
	</tr>
  </thead>
  <tbody>
	<tr>
	  <th>master</th>
	  <td align="center">
		<a href="https://travis-ci.org/pickles2/px2-sitemapexcel"><img src="https://secure.travis-ci.org/pickles2/px2-sitemapexcel.svg?branch=master"></a>
	  </td>
	  <td align="center">
		<a href="https://ci.appveyor.com/project/tomk79/px2-sitemapexcel"><img src="https://ci.appveyor.com/api/projects/status/epre91g8iqfjni08/branch/master?svg=true"></a>
	  </td>
	</tr>
	<tr>
	  <th>develop</th>
	  <td align="center">
		<a href="https://travis-ci.org/pickles2/px2-sitemapexcel"><img src="https://secure.travis-ci.org/pickles2/px2-sitemapexcel.svg?branch=develop"></a>
	  </td>
	  <td align="center">
		<a href="https://ci.appveyor.com/project/tomk79/px2-sitemapexcel"><img src="https://ci.appveyor.com/api/projects/status/epre91g8iqfjni08/branch/develop?svg=true"></a>
	  </td>
	</tr>
  </tbody>
</table>

*pickles2/px2-sitemapexcel* は、[Pickles 2](https://pickles2.pxt.jp/) のサイトマップを、 Excel 形式 (`*.xlsx`) で編集できるようにするプラグインです。

本来の Pickles 2 のサイトマップは、CSV形式で記述されます。 CSVはもっとも単純で基本的なデータ形式の1つで、コンピューターが処理するには扱いやすいフォーマットですが、人間が編集するには不便なこともありました。
このプラグインを導入することで、より直感的でグラフィカルな Excel 形式 (`*.xlsx`) で編集することができるようになります。

- Excel 形式 (`*.xlsx`)のサイトマップファイルを直接読み込むことができるようになります。
  - `*.xlsx` を更新すると、次のアクセス時に自動的に読み込まれ、 `*.csv` の内容が上書きされます。
  - `*.csv` を更新した場合は、逆に `*.xlsx` が上書きされます。タイムスタンプが新しい方を正として、古い方が上書きされます。
  - この挙動は、 `master_format` オプション および `files_master_format` オプションで変更することができます。
- ページの階層構造(`logical_path`)を、視覚的な階層構造で表現できます。
- エクセルの 色付きセル や テキスト装飾 などの編集機能を使い、美しい表をアレンジできます。
- セル値に Excel の計算式を使うことができます。
- `*.xlsx` の `A1` のセルに、サイトマップの設定が記述されています。
  - 設定例 :  `row_definition=8&row_data_start=9&skip_empty_col=20&version=2.0.5`
  - *row_definition* : 定義行番号
  - *row_data_start* : データ行の開始行番号
  - *skip_empty_col* : 定義行に値のない列がある場合、その先を読み込む列数
  - *version* : この `*.xlsx` ファイルを生成した *pickles2/px2-sitemapexcel* のバージョン番号
- `A` 列に、文字列 `EndOfData` を見つけたら、それより下の行はスキャンされません。コメント欄を追加したり、ページ数のカウンターを設置するなど、自由に使えます。
- 拡張カラム `**delete_flg` に `1` をセットすると、CSVに出力されなくなります。
- 1枚目のシートを使用します。2枚目以降のシートは読み取りません。

Excel ファイルの操作には、 [phpoffice/phpexcel](https://github.com/PHPOffice/PHPExcel) を利用しています。

※旧名 pickles-sitemap-excel-2 から px2-sitemapexcel へ名称変更されました。


## 導入手順 - Setup

### 1. composer.json に pickles2/px2-sitemapexcel を追加

require の項目に、"pickles2/px2-sitemapexcel" を追加します。

```
$ composer require pickles2/px2-sitemapexcel;
```


### 2. config.php に、機能を追加

設定ファイル config.php (通常は `./px-files/config.php`) を編集します。
`before_sitemap` のどこか(例では最後)に、`tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec` を追加します。

```php
<?php
	/* 中略 */

	// funcs: Before sitemap
	$conf->funcs->before_sitemap = [
		 // PX=config
		'picklesFramework2\commands\config::register' ,

		 // PX=phpinfo
		'picklesFramework2\commands\phpinfo::register' ,

		// PX=clearcache
		'picklesFramework2\commands\clearcache::register' ,

		// sitemapExcel
		'tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec'
	];
```

Pickles 2 の設定をJSON形式で編集している方は、`config.json` の該当箇所に追加してください。


### 3. サイトマップディレクトリのパーミッション設定

macOS や Linux系 の OS ではこの操作が必要な場合があります。

```bash
$ cd {$documentRoot}
$ chmod -R 777 ./px-files/sitemaps
```

### 4. サイトマップディレクトリに sitemap.xlsx を設置

エクセルファイルは、このリポジトリに同梱されている [sitemap.xlsx](./tests/testData/standard/px-files/sitemaps/sitemap.xlsx) をサンプルに作成してみてください。

編集したファイルは、あなたの Pickles 2 のサイトマップディレクトリ(通常は `./px-files/sitemaps/`) に置きます。次回、ブラウザでアクセスした最初に、同名のCSVファイル(`sitemap.xlsx` なら、`sitemap.csv`)が自動的に更新されます。その後も、エクセルのタイムスタンプが更新されるたびに、CSVファイルは自動更新されます。

逆に、XLSXファイルよりも新しいCSVファイルがある場合は、XLSXファイルがCSVファイルの内容に従って更新されます。


## オプション - Options

```php
<?php
	// funcs: Before sitemap
	$conf->funcs->before_sitemap = [
		// sitemapExcel
		'tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec('.json_encode(array(
			// `master_format`
			// マスターにするファイルフォーマットを指定します。
			//   - `timestamp` = タイムスタンプが新しい方をマスターにする(デフォルト)
			//   - `xlsx` = XLSXをマスターにする
			//   - `csv` = CSVをマスターにする
			//   - `pass` = 変換しない
			// のいずれかを指定します。
			'master_format'=>'timestamp',

			// `files_master_format`
			// サイトマップファイル名ごとにマスターにするファイルフォーマットを指定します。
			// ここに設定されていないファイルは、 `master_format` の設定に従います。
			'files_master_format'=>array(
				'timestamp_sitemap'=>'timestamp',
				'csv_master_sitemap'=>'csv',
				'xlsx_master_sitemap'=>'xlsx',
				'no_convert'=>'pass',
			),

			// `files_master_format_blogs`
			// ブログファイル名ごとにマスターにするファイルフォーマットを指定します。
			// ここに設定されていないファイルは、 `master_format` の設定に従います。
			'files_master_format_blogs'=>array(
				'timestamp_sitemap'=>'timestamp',
				'csv_master_sitemap'=>'csv',
				'xlsx_master_sitemap'=>'xlsx',
				'no_convert'=>'pass',
			),
		)).')'
	];
```


## 更新履歴 - Change log

### pickles2/px2-sitemapexcel v2.3.1 (2023年11月13日)

- パフォーマンスに関する改善。

### pickles2/px2-sitemapexcel v2.3.0 (2023年4月22日)

- ブログマップの変換に対応した。
- オプション `files_master_format_blogs` を追加した。
- 生成される xlsx ファイルのセルに罫線が表示されない不具合を修正した。
- `xlsx2csv` が、`**delete_flg` 列を出力しないようになった。
- その他、内部コードの修正。

### pickles2/px2-sitemapexcel v2.2.2 (2023年3月11日)

- 内部コードの細かい修正。

### pickles2/px2-sitemapexcel v2.2.1 (2023年2月11日)

- 内部コードの細かい修正。

### pickles2/px2-sitemapexcel v2.2.0 (2022年1月8日)

- サポートするPHPのバージョンを `>=7.3.0` に変更。
- PHP 8.1 に対応した。

### pickles2/px2-sitemapexcel v2.1.0 (2020年6月21日)

- Excelファイルの解析ライブラリを PHPExcel から PhpSpreadsheet へ移行した。
- PhpSpreadsheet に合わせて、システム要件を更新。 PHP 7.1.x 以下が対象外となり、いくつかのPHP拡張が要件に追加された。

### pickles2/px2-sitemapexcel v2.0.12 (2020年6月21日)

- PHP 7.4 系で起きる PHPExcel の Warning を非表示にした。

### pickles2/px2-sitemapexcel v2.0.11 (2019年5月21日)

- 標準型 と 文字列型 以外のセルフォーマットのときに、 前後の空白文字を削除するようになった。

### pickles2/px2-sitemapexcel v2.0.10 (2019年4月19日)

- 更新の必要があることを確認してから lock するようになった。
- CSV -> XLSX の変換時に、 `data` スキーマを扱えるようになった。
- CSV -> XLSX の変換時に、 サイトマップツリーが正常に成立していないページを、各リストの末尾に追記するようになった。

### pickles2/px2-sitemapexcel v2.0.9 (2019年1月11日)

- lockファイル生成の処理を改善した。
- xlsm 拡張子のファイルを読み込めるようになった。

### pickles2/px2-sitemapexcel v2.0.8 (2017年9月14日)

- xlsx->csv 変換時に消費するメモリ量を削減するように修正した。
- title列のすぐ右の列に値が入っている場合に、エイリアスとして解釈されてしまう不具合を修正。

### pickles2/px2-sitemapexcel v2.0.7 (2017年5月29日)

- CSVの標準仕様に `proc_type` を追加。
- プラグインオプション `master_format`, `files_master_format` を追加。
- xlsx から csv への変換時に自動発行されるページIDの命名規則を変更。
- xlsxファイルに `logical_path` 列がある場合、この値を優先して適用するようになった。

### pickles2/px2-sitemapexcel v2.0.6 (2016年10月20日)

- サイトマップ形式変換中にプロセスをロックするようになった。

### pickles2/px2-sitemapexcel v2.0.5 (2016年8月24日)

- 日付やパーセントなどの特殊なセルフォーマットが設定されたセルで、可能な限りフォーマットが適用された値で置き換えるようになった。
- サイトマップCSV に `http://〜〜` , `//〜〜` が含まれているときに、xlsx へ正常に変換できない不具合を修正。
- サイトマップXLSX に `http://〜〜/` , `//〜〜/` が含まれているとき、CSVへの変換時に `index.html` を付加してしまう不具合を修正。
- サイトマップからIDの登録エラーを検出した際に異常終了する不具合を修正。

### pickles2/px2-sitemapexcel v2.0.4 (2016年7月27日)

- LibreOffice形式の一時ファイルをスキップするようになった。
- ロール列(role)に対応。

### pickles2/px2-sitemapexcel v2.0.3 (2016年2月9日)

- 簡易表現された扉ページに category_top_flg がセットされていた場合、最上位のエイリアスにのみ設定し、下層のフラグは削除するようになった。

### tomk79/px2-sitemapexcel v2.0.2 (2015年8月7日)

- path, title ともに空白の行を見つけた場合、終了せずにスキップして次を探すようになった。
- export時に、sitemap.csv に定義したカスタム列が捨てられてしまう不具合を修正。

### tomk79/pickles-sitemap-excel-2 v2.0.1 (2015年3月19日)

- タイムゾーンの設定がされていない場合に、エラーが出力されないように制御。

### tomk79/pickles-sitemap-excel-2 v2.0.0 (2014年12月24日)

- First release.


## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>


## 開発者向け情報 - for Developer

### テスト - Test

```
$ php ./vendor/phpunit/phpunit/phpunit
```
