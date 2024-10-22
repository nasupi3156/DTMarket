
CREATE DATABASE DTshopping_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;	

GRANT ALL PRIVILEGES ON DTshopping_db.* TO DTshopping_user@'localhost' IDENTIFIED BY 'DTshopping_pass' WITH GRANT OPTION;	

USE DTshopping_db;

  CREATE TABLE users (
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    family_name VARCHAR(20) NOT NULL,
    first_name VARCHAR(20) NOT NULL,
    family_name_kana VARCHAR(20) NOT NULL,
    first_name_kana VARCHAR(20) NOT NULL,
    sex TINYINT UNSIGNED NOT NULL, 
    year VARCHAR(4) NOT NULL,
    month VARCHAR(2) NOT NULL,
    day VARCHAR(2) NOT NULL,
    zip1 VARCHAR(3) NOT NULL,
    zip2 VARCHAR(4) NOT NULL,
    address VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    tel1 VARCHAR(5) NOT NULL,
    tel2 VARCHAR(4) NOT NULL,
    tel3 VARCHAR(4) NOT NULL,
    registed_at DATETIME NOT NULL,
    updated_at DATETIME,
    deleted_at DATETIME,
    is_deleted TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    is_admin BOOLEAN DEFAULT FALSE,
    is_removed BOOLEAN DEFAULT FALSE,
    is_logged_in BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (user_id)
  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


CREATE TABLE user_passwords (
  user_id INT UNSIGNED NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  PRIMARY KEY (user_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


CREATE TABLE categories (
  ctg_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (ctg_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


CREATE TABLE items (
  item_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_name VARCHAR(100) NOT NULL,
  detail TEXT NOT NULL,
  price INT UNSIGNED NOT NULL,
  image VARCHAR(50) NOT NULL,
  ctg_id TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (item_id),
  INDEX item_idx (ctg_id),
  FOREIGN KEY (ctg_id) REFERENCES categories(ctg_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

  -- ; カート(取引)	
CREATE TABLE carts (	
  crt_id int unsigned not null auto_increment,	
  customer_no int unsigned not null,	
  item_id int unsigned not null,	
  num tinyint(1) unsigned not null default 1,	
  quantity tinyint(1) unsigned not null default 1,
  is_deleted tinyint(1) unsigned not null default 0,	
  primary key( crt_id ),	
  index crt_idx( customer_no, is_deleted ),
  FOREIGN KEY (item_id) REFERENCES items(item_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  family_name VARCHAR(255) NOT NULL,
  first_name VARCHAR(255) NOT NULL,
  zip1 VARCHAR(10),
  zip2 VARCHAR(10),
  address VARCHAR(255),
  tel1 VARCHAR(20),
  tel2 VARCHAR(20),
  tel3 VARCHAR(20),
  shipping_fee INT UNSIGNED NOT NULL,
  total_price INT UNSIGNED NOT NULL,
  payment_method VARCHAR(50),
  purchase_date DATETIME NOT NULL,
  is_deleted TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE order_details (
  detail_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  image VARCHAR(255),
  item_name VARCHAR(255),
  quantity INT NOT NULL,
  price INT UNSIGNED NOT NULL,
  is_deleted TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (order_id) REFERENCES orders(order_id),
  FOREIGN KEY (item_id) REFERENCES items(item_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED,
  family_name VARCHAR(255) NOT NULL,
  first_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  contents TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_deleted TINYINT(1) DEFAULT 0 NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

  
CREATE TABLE sessions (	
  customer_no int unsigned not null auto_increment,	
  session_key varchar(32),	
  primary key(customer_no)	
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE postcodes (
  jis varchar(5),
  old_zip varchar(5),
  zip varchar(7),
  pref_kana varchar(100),
  city_kana varchar(100),
  town_kana varchar(100),
  pref varchar(100),
  city varchar(100),
  town varchar(100),
  comment1 tinyint(1)unsigned,
  comment2 tinyint(1)unsigned,
  comment3 tinyint(1)unsigned,
  comment4 tinyint(1)unsigned,
  comment5 tinyint(1)unsigned,
  comment6 tinyint(1)unsigned
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


; mac
LOAD DATA LOCAL INFILE '/Applications/MAMP/htdocs/DTMarket/mysql/postcodes.csv'
INTO TABLE `postcodes`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

; windows
LOAD DATA LOCAL INFILE 'C:\\xampp\\htdocs\\DTMarket\\mysql\\postcodes.csv'
INTO TABLE `postcodes`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES;


INSERT INTO categories VALUES ( 1, '野菜' );
INSERT INTO categories VALUES ( 2, '果物' );
INSERT INTO categories VALUES ( 3, 'その他' );


INSERT INTO items VALUES ( 1, 'たまねぎ', 'たまねぎ(愛媛県産)旬の季節は、秋から冬です。栄養素は、クエルセチン（強力な抗酸化物質）、ビタミンC、ビタミンB6、カリウムが豊富に含まれています。', 150, 'tamanegi.jpg', 1 );

INSERT INTO items VALUES ( 2, 'にんじん', 'にんじん(茨城県産)旬の季節は、秋から冬です。栄養素は、クエルセチン（強力な抗酸化物質）、ビタミンC、ビタミンB6、カリウムが豊富に含まれています。', 100, 'ninjin.jpg', 1 );

INSERT INTO items VALUES ( 3, 'ピーマン', 'ピーマン(高知県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンB6、ビタミンA、カプサイシン（特に赤ピーマン）が豊富に含まれています。', 120, 'pi-man.jpg', 1 );

INSERT INTO items VALUES ( 4, 'なす', 'なす(熊本県産)旬の季節は、夏です。栄養素は、ナスニン（抗酸化物質）、食物繊維、ビタミンB1、ビタミンB6が豊富に含まれています', 110, 'nasu.jpg', 1 );

INSERT INTO items VALUES ( 5, 'ねぎ', 'ねぎ(千葉県産)旬の季節は、冬です。栄養素は、アリシン（抗菌・抗ウイルス作用）、ビタミンC、鉄、食物繊維が豊富に含まれています', 110, 'negi.jpg', 1 );

INSERT INTO items VALUES ( 6,  'ほうれんそう', 'ほうれんそう(埼玉県産)旬の季節は、冬から秋です。栄養素は、β-カロテン、ビタミンC、ビタミンK、鉄、葉酸が豊富に含まれています。', 80, 'hourenso.jpg', 1 );

INSERT INTO items VALUES ( 7,  'みかん', 'みかん(愛媛県産)旬の季節は、冬です。栄養素は、ビタミンC、ビタミンA、カリウム、食物繊維が豊富に含まれています。', 250, 'mikan.jpg', 2 );

INSERT INTO items VALUES ( 8,  'しめじ', 'しめじ(新潟県産)旬の季節は、秋です。栄養素は、β-グルカン（免疫力を高める）、ビタミンD、ビタミンB群、鉄分が豊富に含まれています。', 100, 'simeji.jpg', 1 );

INSERT INTO items VALUES ( 9,  'はくさい', 'はくさい(茨城県産)旬の季節は、冬です。栄養素は、ビタミンC、ビタミンK、食物繊維、カルシウムが豊富に含まれています。', 200, 'hakusai.jpg', 1 );

INSERT INTO items VALUES ( 10,  'さつまいも', '旬の季節は、秋です。栄養素は、β-カロテン、ビタミンC、ポタシウム、食物繊維が豊富に含まれています。', 290, 'satumaimo.jpg', 1 );

INSERT INTO items VALUES ( 11,  'バナナ', 'バナナ(フィリピン産)一年中(輸入)、栄養素は、カリウム、ビタミンB6、ビタミンC、マグネシウムが豊富に含まれています。', 130, 'banana.jpg', 2 );

INSERT INTO items VALUES ( 12,  'キャベツ', 'キャベツ(群馬県産)旬の季節は、春から初夏、秋から冬です。栄養素は、ビタミンC、ビタミンK、食物繊維、葉酸が豊富に含まれています。', 160, 'kyabetu.jpg', 1 );

INSERT INTO items VALUES ( 13, 'エリンギ', 'エリンギ(長野県産)一年通して収穫できます。栄養素は、ビタミンD、食物繊維、カリウム、β-グルカンが豊富に含まれています。', 90, 'eringi.jpg', 1 );

INSERT INTO items VALUES ( 14,  'なし', 'なし(千葉県産)旬の季節は、秋です。栄養素は、ビタミンC、ビタミンK、食物繊維、カリウムが豊富に含まれています。', 350, 'nasi.jpg', 2 );

INSERT INTO items VALUES ( 15, 'ブロッコリー', 'ブロッコリー(茨城県産)旬の季節は、冬から春です。栄養素は、ビタミンC、ビタミンK、葉酸、カルシウム、硫黄化合物（発がん性物質の抑制に役立つ）が豊富に含まれています。', 180, 'burokori.jpg', 1 );

INSERT INTO items VALUES ( 16,  '豚肉', '豚肉(国産)年間通して食べられる人気の年間通して食べられる人気の食材です。栄養素は、ビタミンB1（チアミン）、高品質のタンパク質、鉄分、亜鉛、セレン(抗酸化物質であり、細胞の健康を守る)が豊富に含まれています。', 550, 'buta.jpg', 3 );

INSERT INTO items VALUES ( 17,  'アボカド', 'アドカド(海外産)1年中収穫されますが、旬は春です。栄養素は、不飽和脂肪酸、ビタミンE、カリウムが豊富に含まれています。', 100, 'abokado.jpg', 2 );

INSERT INTO items VALUES ( 18,  'ぶどう', 'ぶどう(山梨県産)旬の季節は夏の終わりから秋です。栄養素はビタミンCやポリフェノール（特にレスベラトロール）が豊富で、抗酸化作用があります。', 400, 'budo.jpg', 2 );

INSERT INTO items VALUES ( 19,  'ごぼう', 'ごぼう(青森県産)旬の季節は、秋から冬です。栄養素は、食物繊維が非常に豊富で、特にイヌリンの含有量が高いです。またカリウムやビタミンB群も含まれています。', 140, 'gobo.jpg', 1 );

INSERT INTO items VALUES ( 20,  'マイタケ', 'マイタケ(新潟県産)旬の季節は秋です。栄養素は、ビタミンD、特にマイタケにはβ-グルカンという免疫系をサポートする繊維質が含まれています。', 80, 'maitake.jpg', 1 );

INSERT INTO items VALUES ( 21,  'シイタケ', 'シイタケ(長野県産)旬の季節は秋から春です。栄養素は、ビタミンD、ビタミンB、銅、セレン(抗酸化ミネラルで、免疫力を高め、細胞の損傷を防ぎます)が豊富です。', 110, 'sitake.jpg', 1 );

INSERT INTO items VALUES ( 22,  'とうもろこし', 'とうもろこし(北海道産)旬の季節は、夏の終わりから秋です。栄養素は、ビタミンB群と食物繊維が含まれており、健康な消化系を支えるのに役立ちます。', 220, 'tomorokosi.jpg', 2 );


INSERT INTO items VALUES ( 23,  'りんご', 'りんご(青森県産)旬の季節は、秋です。栄養素は、食物繊維、ビタミンC、カリウム、ポリフェノールが豊富に含まれています。', 230, 'apple.jpg', 2 );

INSERT INTO items VALUES ( 24,  'ブラックベリー', 'ブラックベリー(海外産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンK、食物繊維、マンガンが豊富に含まれています。', 550, 'blackberry.jpg', 2 );

INSERT INTO items VALUES ( 25,  'ブルーベリー', 'ブルーベリー(群馬県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンK、マンガン、抗酸化物質が豊富に含まれています。', 360, 'mikan.jpg', 2 );

INSERT INTO items VALUES ( 26,  'カブ', 'カブ(千葉県産)旬の季節は、秋から冬です。栄養素は、ビタミンC、ビタミンK、葉酸、食物繊維が豊富に含まれています。', 150, 'mikan.jpg', 1 );

INSERT INTO items VALUES ( 27,  'トマト', 'トマト(熊本県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、カリウム、リコピンが豊富に含まれています。', 120, 'mikan.jpg', 1 );

INSERT INTO items VALUES ( 28,  '桃', '桃(福島県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、食物繊維、カリウムが豊富に含まれています。', 350, 'mikan.jpg', 2 );

INSERT INTO items VALUES ( 29,  'ラズベリー', 'ラズベリー(海外産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンK、食物繊維、マンガンが豊富に含まれています。', 600, 'razuberry.jpg', 2 );

INSERT INTO items VALUES ( 30,  'スイカ', 'スイカ(茨城県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、カリウム、リコピンが豊富に含まれています。', 1200, 'suika.jpg', 2 );

INSERT INTO items VALUES ( 31, 'びわ', 'びわ(長崎県産)旬の季節は、春です。栄養素は、ビタミンA、ビタミンC、カリウムが豊富に含まれています。', 560, 'biwa.jpg', 2 );

INSERT INTO items VALUES ( 32,  '牛肉', '牛肉(宮崎県産)旬の季節は、年中です。栄養素は、タンパク質、鉄分、ビタミンB12、亜鉛が豊富に含まれています。', 1200, 'beef.jpg', 3 );

INSERT INTO items VALUES ( 33,  'ミニトマト', 'ミニトマト(愛知県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、リコピン、カリウムが豊富に含まれています。', 180, 'minitomato.jpg', 1 );

INSERT INTO items VALUES ( 34,  'シシトウ', 'シシトウ(高知県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、カリウム、食物繊維が豊富に含まれています。', 140, 'sisito.jpg', 1 );

INSERT INTO items VALUES ( 35,  'マッシュルーム', 'マッシュルーム(静岡県産)旬の季節は、年中です。栄養素は、ビタミンB群、セレン、カリウム、食物繊維が豊富に含まれています。', 220, 'mushroom.jpg', 1 );

INSERT INTO items VALUES ( 36,  '松茸', '松茸(長野県産)旬の季節は、秋です。栄養素は、ビタミンD、カリウム、ナイアシン、食物繊維が豊富に含まれています。', 10000, 'matutake.jpg', 1 );

INSERT INTO items VALUES ( 37,  '栗', '栗(愛媛県産)旬の季節は、秋です。栄養素は、ビタミンC、カリウム、食物繊維、葉酸が豊富に含まれています。', 700, 'kuri.jpg', 1 );

INSERT INTO items VALUES ( 38,  'カリフラワー', 'カリフラワー(愛知県産)旬の季節は、秋から冬です。栄養素は、ビタミンC、ビタミンK、葉酸、食物繊維が豊富に含まれています。', 240, 'cauliflower.jpg', 1 );

INSERT INTO items VALUES ( 39,  'ビーツ', 'ビーツ(北海道県産)旬の季節は、秋から冬です。栄養素は、葉酸、マグネシウム、鉄分、ビタミンCが豊富に含まれています。', 400, 'beets.jpg', 1 );

INSERT INTO items VALUES ( 40,  'かぼちゃ', 'かぼちゃ(神奈川県産)旬の季節は、秋です。栄養素は、ビタミンA、ビタミンC、カリウム、食物繊維が豊富に含まれています。', 290, 'pumpkin.jpg', 1 );

INSERT INTO items VALUES ( 41, 'ドラゴンフルーツ', 'ドラゴンフルーツ(沖縄県産)旬の季節は夏です。栄養素としては、ビタミンC、ビタミンB、食物繊維、カリウムが豊富に含まれています。', 550, 'dragonFruits.jpg', 2 );

INSERT INTO items VALUES ( 42, 'アスパラガス', 'アスパラガス(北海道産)旬の季節は、春です。栄養素は、ビタミンK、葉酸、ビタミンC、ビタミンAが豊富に含まれています。', 360, 'asupara.jpg', 1 );

INSERT INTO items VALUES ( 43, '卵', '卵(国産)年中食べられる人気の食材です。栄養素は、タンパク質、ビタミンB12、ビタミンD、セレンが豊富に含まれています。', 250, 'egg.jpg', 3 );

INSERT INTO items VALUES ( 44, 'じゃがいも', 'じゃがいも(北海道産)旬の季節は、秋から冬です。栄養素は、ビタミンC、ビタミンB6、カリウム、食物繊維が豊富に含まれています。', 200, 'jagaimo.jpg', 1 );

INSERT INTO items VALUES ( 45, 'いちご', 'いちご(栃木県産)旬の季節は、冬から春です。栄養素は、ビタミンC、葉酸、食物繊維が豊富に含まれています。', 580, 'ichigo.jpg', 2 );

INSERT INTO items VALUES ( 46, 'レタス', 'レタス(長野県産)旬の季節は、春から夏です。栄養素は、ビタミンK、ビタミンA、葉酸、食物繊維が豊富に含まれています。', 200, 'retasu.jpg', 1 );

INSERT INTO items VALUES ( 47, 'ささみ', 'ささみ(国産)年中食べられる人気の食材です。栄養素は、タンパク質、ビタミンB6、ナイアシン、リンが豊富に含まれています。', 300, 'sasami.jpg', 3 );

INSERT INTO items VALUES ( 48, 'しそ', 'しそ(愛知県産)旬の季節は、夏です。栄養素は、ビタミンK、ビタミンA、カルシウム、鉄が豊富に含まれています。', 150, 'shiso.jpg', 1 );

INSERT INTO items VALUES ( 49, '大根', '大根(神奈川県産)旬の季節は、冬です。栄養素は、ビタミンC、葉酸、カリウム、食物繊維が豊富に含まれています。', 150, 'daikon.jpg', 1 );

INSERT INTO items VALUES ( 50, 'パッションフルーツ', 'パッションフルーツ(鹿児島県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、食物繊維、カリウムが豊富に含まれています。', 400, 'passion.
jpg', 2 );

INSERT INTO items VALUES ( 51, '山芋', '山芋(長野県産)旬の季節は、秋から冬です。栄養素は、ビタミンC、ビタミンB群、カリウム、食物繊維が豊富に含まれています。', 300, 'yamaimo.jpg', 1 );

INSERT INTO items VALUES ( 52, '菜の花', '菜の花(千葉県産)旬の季節は、春です。栄養素は、ビタミンC、ビタミンA、カルシウム、鉄が豊富に含まれています。', 250, 'nabana.jpg', 1 );

INSERT INTO items VALUES ( 53, 'チンゲンサイ', 'チンゲンサイ(静岡県産)旬の季節は、春と秋です。栄養素は、ビタミンA、ビタミンC、カルシウムが豊富に含まれています。', 150, 'chingensai.jpg', 1 );

INSERT INTO items VALUES ( 54, '枝豆', '枝豆(新潟県産)旬の季節は、夏です。栄養素は、タンパク質、ビタミンC、食物繊維が豊富に含まれています。', 300, 'edamame.jpg', 1 );

INSERT INTO items VALUES ( 55, 'エノキ', 'エノキ(長野県産)旬の季節は、秋から冬です。栄養素は、ビタミンB群、食物繊維が豊富に含まれています。', 70, 'enoki.jpg', 1 );

INSERT INTO items VALUES ( 56, 'ゴーヤ', 'ゴーヤ(沖縄県産)旬の季節は、夏です。栄養素は、ビタミンC、カリウムが豊富に含まれています。', 250, 'goya.jpg', 1 );

INSERT INTO items VALUES ( 57, '柿', '柿(奈良県産)旬の季節は、秋から冬です。栄養素は、ビタミンA、ビタミンC、食物繊維が豊富に含まれています。', 330, 'kaki.jpg', 2 );

INSERT INTO items VALUES ( 58, '小松菜', '小松菜(東京都産)旬の季節は、冬です。栄養素は、ビタミンC、カルシウム、鉄が豊富に含まれています。', 120, 'komatsuna.jpg', 1 );

INSERT INTO items VALUES ( 59, 'キウイ', 'キウイ(愛媛県産)旬の季節は、冬です。栄養素は、ビタミンC、ビタミンE、食物繊維が豊富に含まれています。', 210, 'kyui.jpg', 2 );

INSERT INTO items VALUES ( 60, 'きゅうり', 'きゅうり(千葉県産)旬の季節は、夏です。栄養素は、ビタミンK、ビタミンC、カリウムが豊富に含まれています。', 140, 'kyuri.jpg', 1 );

INSERT INTO items VALUES ( 61, 'マンゴー', 'マンゴー(宮崎県産)旬の季節は、夏です。栄養素は、ビタミンA、ビタミンC、食物繊維が豊富に含まれています。', 800, 'mango.jpg', 2 );

INSERT INTO items VALUES ( 62, 'メロン', 'メロン(茨城県産)旬の季節は、夏です。栄養素は、ビタミンA、ビタミンC、カリウムが豊富に含まれています。', 1300, 'melon.jpg', 2 );

INSERT INTO items VALUES ( 63, '水菜', '水菜(京都府産)旬の季節は、冬です。栄養素は、ビタミンC、ビタミンA、カルシウムが豊富に含まれています。', 110, 'mizuna.jpg', 1 );

INSERT INTO items VALUES ( 64, 'モロヘイヤ', 'モロヘイヤ(福島県産)旬の季節は、夏です。栄養素は、ビタミンA、ビタミンC、カルシウムが豊富に含まれています。', 210, 'molohia.jpg', 1 );

INSERT INTO items VALUES ( 65, 'もやし', 'もやし(栃木県産)年中食べられる人気の食材です。栄養素は、ビタミンC、食物繊維が豊富に含まれています。', 40, 'moyashi.jpg', 1 );

INSERT INTO items VALUES ( 66, 'ミョウガ', 'ミョウガ(高知県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンB群が豊富に含まれています。', 220, 'myoga.jpg', 1 );

INSERT INTO items VALUES ( 67, 'ニンニク', 'ニンニク(青森県産)旬の季節は、夏です。栄養素は、ビタミンB6、マンガン、セレンが豊富に含まれています。', 229, 'ninniku.jpg', 1 );

INSERT INTO items VALUES ( 68, 'ニラ', 'ニラ(栃木県産)旬の季節は、冬から春です。栄養素は、ビタミンA、ビタミンC、カルシウムが豊富に含まれています。', 120, 'nira.jpg', 1 );

INSERT INTO items VALUES ( 69, 'パクチー', 'パクチー(千葉県産)旬の季節は、春から初夏です。栄養素は、ビタミンC、ビタミンA、カルシウムが豊富に含まれています。', 240, 'pakuchi.jpg', 1 );

INSERT INTO items VALUES ( 70, 'パイナップル', 'パイナップル(沖縄県産)旬の季節は、夏です。栄養素は、ビタミンC、マンガンが豊富に含まれています。', 800, 'pineapple.jpg', 2 );

INSERT INTO items VALUES ( 71, '落花生', '落花生(千葉県産)旬の季節は、秋です。栄養素は、タンパク質、ビタミンE、ナイアシンが豊富に含まれています。', 310, 'rakkasei.jpg', 1 );

INSERT INTO items VALUES ( 72, 'レモン', 'レモン(広島県産)旬の季節は、冬です。栄養素は、ビタミンC、クエン酸が豊富に含まれています。', 140, 'lemon.jpg', 2 );

INSERT INTO items VALUES ( 73, '米', '米(新潟県産)旬の季節は、秋です。栄養素は、炭水化物、ビタミンB群が豊富に含まれています。', 400, 'rice.jpg', 3 );

INSERT INTO items VALUES ( 74, 'ルッコラ', 'ルッコラ(静岡県産)旬の季節は、春から夏です。栄養素は、ビタミンK、ビタミンA、カルシウムが豊富に含まれています。', 200, 'rukkora.jpg', 1 );

INSERT INTO items VALUES ( 75, 'さくらんぼ', 'さくらんぼ(山形県産)旬の季節は、春から夏です。栄養素は、ビタミンC、カリウム、食物繊維が豊富に含まれています。', 1000, 'sakuranbo.jpg', 2 );

INSERT INTO items VALUES ( 76, '里芋', '里芋(静岡県産)旬の季節は、秋から冬です。栄養素は、ビタミンC、カリウム、食物繊維が豊富に含まれています。', 400, 'satoimo.jpg', 1 );

INSERT INTO items VALUES ( 77, 'スモモ', 'スモモ(山梨県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、カリウムが豊富に含まれています。', 300, 'sumomo.jpg', 2 );

INSERT INTO items VALUES ( 78, '生姜', '生姜(高知県産)旬の季節は、夏です。栄養素は、ジンゲロール、ビタミンC、マグネシウムが豊富に含まれています。', 170, 'syoga.jpg', 1 );

INSERT INTO items VALUES ( 79, '春菊', '春菊(千葉県産)旬の季節は、秋から冬です。栄養素は、ビタミンA、ビタミンC、カルシウムが豊富に含まれています。', 190, 'syungiku.jpg', 1 );

INSERT INTO items VALUES ( 80, '筍', '筍(京都府産)旬の季節は、春です。栄養素は、ビタミンB群、食物繊維が豊富に含まれています。', 430, 'takenoko.jpg', 1 );

INSERT INTO items VALUES ( 81, '梅', '梅(和歌山県産)旬の季節は、夏です。栄養素は、クエン酸、ビタミンCが豊富に含まれています。', 290, 'ume.jpg', 2 );

INSERT INTO items VALUES ( 82, 'わさび', 'わさび(静岡県産)旬の季節は、春です。栄養素は、ビタミンC、食物繊維が豊富に含まれています。', 820, 'wasabi.jpg', 1 );

INSERT INTO items VALUES ( 83, '洋梨', '洋梨(山形県産)旬の季節は、秋です。栄養素は、食物繊維、ビタミンC、カリウムが豊富に含まれています。', 460, 'westernNashi.jpg', 2 );

INSERT INTO items VALUES ( 84, 'ゆず', 'ゆず(高知県産)旬の季節は、冬です。栄養素は、ビタミンC、カリウムが豊富に含まれています。', 330, 'yuzu.jpg', 2 );

INSERT INTO items VALUES ( 85, 'ズッキーニ', 'ズッキーニ(長野県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンA、カリウムが豊富に含まれています。', 180, 'zucchini.jpg', 1 );

INSERT INTO items VALUES ( 86, 'かぼす', 'かぼす(大分県産)旬の季節は、秋です。栄養素は、ビタミンC、クエン酸が豊富に含まれています。', 340, 'kabosu.jpg', 2 );

INSERT INTO items VALUES ( 87, 'レンコン', 'レンコン(茨城県産)旬の季節は、秋から冬です。栄養素は、ビタミンC、食物繊維、カリウムが豊富に含まれています。', 380, 'renkon.jpg', 1 );

INSERT INTO items VALUES ( 88, 'グレープ', 'グレープ(山梨県産)旬の季節は、夏から秋です。栄養素は、ビタミンC、ビタミンK、ポリフェノールが豊富に含まれています。', 770, 'grapefruit.jpg', 2 );

INSERT INTO items VALUES ( 89, 'オクラ', 'オクラ(鹿児島県産)旬の季節は、夏です。栄養素は、ビタミンC、ビタミンK、食物繊維が豊富に含まれています。', 190, 'okura.jpg', 1 );

INSERT INTO items VALUES ( 90, 'イチジク', 'イチジク(和歌山県産)旬の季節は、夏から秋です。栄養素は、ビタミンB6、食物繊維、カリウムが豊富に含まれています。', 680, 'ichijiku.jpg', 2 );

-- ; INSERT INTO items VALUES ( 31,  '牛乳', '牛乳(北海道産)1年中飲まれる人気の食材です。栄養素は、カルシウム、ビタミンD、ビタミンB12、リンが豊富に含まれています。', 210, 'mikan.jpg', 1 );

-- ; INSERT INTO items VALUES ( 45, 'マグロ', 'マグロ(国産、海外産)1年中食べられる人気の食材です。栄養素は、タンパク質、オメガ3脂肪酸、ビタミンD、鉄が豊富に含まれています。', 1500, 'maguro.jpg', 3 );

