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

*pickles2/px2-sitemapexcel* は、[Pickles2](http://pickles2.pxt.jp/) のサイトマップを、エクセル形式(`*.xlsx`)で編集できるようにするプラグインです。

本来の Pickles2 のサイトマップは、CSV形式で管理されます。CSVはもっとも単純で基本的なデータ形式の1つで、コンピューターが処理するには扱いやすいフォーマットですが、人間が編集するには不便なこともありました。このプラグインを導入することで、より直感的に、グラフィカルに編集することができるようになります。

- `*.xlsx` 形式のサイトマップファイルを直接読み込むことができるようになります。
- ページの階層構造(`logical_path`)を、視覚的な階層構造で表現できます。
- エクセルの 色付きセル や テキスト装飾 などの編集機能を使い、美しい表をアレンジできます。
- セルの値として計算式を使うことができます。

エクセルファイルの操作には、 [phpoffice/phpexcel](https://github.com/PHPOffice/PHPExcel) を利用しています。

※旧名 pickles2/px2-sitemapexcel から px2-sitemapexcel へ名称変更されました。


## 導入手順 - Setup

### 1. composer.json に pickles2/px2-sitemapexcel を追加

require の項目に、"pickles2/px2-sitemapexcel" を追加します。

```
{
	〜 中略 〜
    "require": {
        "php": ">=5.3.0" ,
        "pickles2/px-fw-2.x": "2.0.*",
        "pickles2/px2-sitemapexcel": "2.0.*"
    },
	〜 中略 〜
}
```


追加したら、`composer update` を実行して変更を反映することを忘れずに。

```
$ composer update
```


### 2. config.php に、機能を追加

設定ファイル config.php (通常は `./px-files/config.php`) を編集します。
`before_sitemap` のどこか(例では最後)に、`tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec` を追加します。

```
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

Pickles2 の設定をJSON形式で編集している方は、config.json の該当箇所に追加してください。


### 3. サイトマップディレクトリのパーミッション設定

Mac OSX や Linux系のOSではこの操作が必要な場合があります。

```
$ cd {$documentRoot}
$ chmod -R 777 ./px-files/sitemaps
```

### 4. サイトマップディレクトリに sitemap.xlsx を設置

エクセルファイルは、このリポジトリに同梱されている [sitemap.xlsx](./tests/testData/standard/px-files/sitemaps/sitemap.xlsx) をサンプルに作成してみてください。

編集したファイルは、あなたの Pickles2 のサイトマップディレクトリ(通常は `./px-files/sitemaps`) に置きます。次回、ブラウザでアクセスした最初に、同名のCSVファイル(`sitemap.xlsx` なら、`sitemap.csv`)が自動的に更新されます。その後も、エクセルのタイムスタンプが更新されるたびに、CSVファイルは自動更新されます。

逆に、XLSXファイルよりも新しいCSVファイルがある場合は、XLSXファイルがCSVファイルの内容に従って更新されます。


## 更新履歴 - Change log

### px2-sitemapexcel 2.0.4 (2016年7月27日)

- LibreOffice形式の一時ファイルをスキップするようになった。
- ロール列(role)に対応。

### px2-sitemapexcel 2.0.3 (2016年2月9日)

- 簡易表現された扉ページに category_top_flg がセットされていた場合、最上位のエイリアスにのみ設定し、下層のフラグは削除するようになった。

### px2-sitemapexcel 2.0.2 (2015年8月7日)

- path, title ともに空白の行を見つけた場合、終了せずにスキップして次を探すようになった。
- export時に、sitemap.csv に定義したカスタム列が捨てられてしまう不具合を修正。

### pickles-sitemap-excel 2.0.1 (2015年3月19日)

- タイムゾーンの設定がされていない場合に、エラーが出力されないように制御。

### pickles-sitemap-excel 2.0.0 (2014年12月24日)

- First release.


## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>

## 開発者向け情報 - for Developer

### テスト - Test

```
$ php ./vendor/phpunit/phpunit/phpunit
```
