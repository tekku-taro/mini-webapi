<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-green.svg?style=flat-square" alt="license"></a>

# Mini-WebAPI

## はじめに

このフレームワークはWeb上に`REST API`を簡単に構築することを目的に開発されたソフトです。
PHPで作成され、リクエストやリスポンスやデータベースの操作が各クラスで
予め処理されていますので、開発者はそれぞれのエンドポイントでのロジックを記述する
だけでAPIサービスを完成させられます。

## REST APIについて

REST APIとは、ウェブアップリケーションの機能を外部から呼び出すためのインターフェースの一種です。
RESTの原則に従って設計されるため、REST APIあるいはRESTful APIと呼びます。
REST APIでは、状態管理等を行わず、全てのリクエストがそれ自体で完結した処理を行うこと、URIを通して、全ての情報が一意に表現されていること、リソースの操作には、HTTPメソッド(GET,POST,PUT,DELETE)を利用することなどが定められています。
リクエストを処理した後、結果データを`json`や`xml`の形式で返し、通信結果をステータスコードで返します。

## 主な特徴

- シンプルで汎用性の高いREST APIを構築できる
- `JWTトークン`を使った認証用のAPIが予め組み込まれている
- `Laravelフレームワーク`でも使われている`illuminate/database`ライブラリを利用してモデルクラスで直感的にテーブルデータを操作できる
- モデルのvalidationルールを簡潔に記述できる
- APIやモデルクラスの雛形をコマンドラインで作成できる

## サーバー・クライアント間のデータフロー

![JWT認証とリソースの操作](resources/JWT認証とリソースの操作.png?raw=true "フロー図")

### 処理の流れ ###

1. クライアントから認証エンドポイント（`SessionsAPI`）に認証情報を送信

   ```json
   url:hostname/api/sessions
   method:POST
   requestBody:{"name":"taro","password":"pass"}
   ```

   

2. 認証エンドポイントで認証されたら`jwtトークン`を作成して返す

   ```json
   "access_token":ACCESSTOKEN_STRING, "refresh_token":REFRESHTOKEN_STRING, "message":"you are ..."
   ```

   

3. クライアントはサーバー上のリソースエンドポイント（`ZipcodesAPI`）に向けてリクエストを送信。その際に、`jwtトークン`の中の`アクセストークン`を`Authrization Header`にセットする。

   ```
   url:hostname/api/zipcodes
   method:GET
   Authorization:Bearer ACCESSTOKEN_STRING
   ```

   

4. リソースエンドポイントではリクエストを処理し、結果を`json`データで返す

   ```json
   "data": [
       {
           "id": 1,
           "zipcode": "001-0000",
           "address": "北海道札幌市北区以下に掲載がない場合"
       },
       ...
   ],
   "count": 1000,
   "message": "1000 Zipcode records found"
   ```

   

5. `アクセストークン`の期限が切れた時は、認証エンドポイント（`SessionsAPI`）にトークンの更新をリクエスト。その際に、`jwtトークン`の中の`リフレッシュトークン`を`Authrization Header`にセットする。

   ```
   url:hostname/api/sessions
   method:PUT
   Authorization:Bearer REFRESHTOKEN_STRING
   ```

   

6. 認証エンドポイントでリフレッシュトークンを検証し、認証されたら`jwtトークン`を新たに作成して返す

   ```json
   "access_token":NEW_ACCESSTOKEN, "refresh_token":NEW_REFRESHTOKEN, "message":"you are ..."
   ```

   



## ディレクトリ構造

```
root/api
　├ app
  │  ├── Api                 # APIクラス
  │  │    ├── Admin          # 管理者用API
  │  │    ├── Session        # 認証用API  
  │  |    └── ...            # 一般APIクラス
  │  └── Models              # モデルクラス
  │
　├ bootstrap                 # システム起動時に呼び出すファイル
　├ console                   # コマンドライン用機能
　├ libraries			    # アップリケーション用のライブラリ
　│   ├── AppCore             # コアクラス
  │   └── ...                 # その他のクラス
  │
　├ logs
　├ resources/
  ├ route					# リクエスト処理
　├ tests/
　├ vendor/   
　├ .env						# 設定ファイル
　├ .htaccess  				 # アクセスを全てindex.phpにリダイレクト
　├ index.php
　├ README.md 
　└ その他のフォルダ・ファイル
```

## インストール

```bash
# mini-webapiをウェブサーバー上にクローン
git clone https://github.com/tekku-taro/mini-webapi.git

# プロジェクトのディレクトリに移動
cd mini-webapi

# PHPのパッケージをcomposerでインストール
composer install

# resources/webapi.sqlファイルをデータベースにインポートする
mysql -u username -p database_name < webapi.sql
```

## 設定

### 設定ファイル`.env`

プロジェクトフォルダ内の`.env`ファイルをご自分の環境に合わせて変更してください。

```
APP_NAME=taro/webapi_fw
# 開発・運用の切り替え
APP_ENV=development    #development/production
# 接続するデータベースの設定
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_NAME=webapi
DB_USER=root
DB_PASS=
# 提供するAPIリスト
API=users,sessions,zipcodes
# ログの書き出しのOn/Off
LOG=On # On/Off
```



## エンドポイントの追加

例えば、データベースのstaffsテーブルをリソースにして、操作するエンドポイントの場合

1. `staffs`テーブルをデータベースに作成する。
2. `App/Models`フォルダ内に他のモデルクラスを複製するか、後述の雛形作成方法で`Eloquent`を継承した`Staff`クラスを作成する。
3. `App/Api`フォルダ内に他の一般APIクラスを複製するか、後述の雛形作成方法で`StaffsAPI`クラスを作成する。
4. `StaffsAPI`にリクエストメソッドに対応したアクションメソッドを作成し、それぞれに必要な処理を記述する。
   - `GET` : `getIndex()` または `get()`       # ルートにIDが設定されていれば（cf. api/zipcodes/1） get()に送られる
   - `POST` : `post()`                                    # レコードの新規作成
   - `PUT` : `put()`                                        # レコードの更新
   - `DELETE` : `delete()`                              # レコードの削除

### アクションメソッドのパラメータ

リクエストの際に、urlに渡される`GETパラメータ`は、メソッドのパラメータとして取得できます。
例えば、`url: hostname/api/zipcodes?zipcode=001-0000`ならば

```php
// ZipcodesAPIクラス内
function get($zipcode)
{
    echo $zipcode; // "001-0000"
}
```



## テンプレートから雛形を作成する

コマンドラインまたはbashで、`console/maker.bat`あるいは`console/maker.sh`ファイルを実行してAPI/Modelファイルの雛形を`console/output`フォルダ内に作成できます。

### 作成方法

```bash
# 方法１：maker.batを実行
console/maker.bat make:api ZipcodesAPI
console/maker.bat make:model Zipcode # データベース内にzipcodesというテーブルがあることが前提

# 方法２：maker.shを実行
console/maker.sh make:api ZipcodesAPI
console/maker.sh make:model Zipcode # データベース内にzipcodesというテーブルがあることが前提

```





## 動作環境

- Apache HTTP Server ( mode_rewrite モジュールを有効化 )
- PHP 7.2
- mySql, Maria DB等のPDOで接続できるデータベース



## ライセンス (License)

**Mini-WebAPI**は[MIT license](https://opensource.org/licenses/MIT)のもとで公開されています。
**Mini-WebAPI** is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
