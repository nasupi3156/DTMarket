// formを使わずjQueryの場合
$(function () {
  var entry_url = $("#entry_url").val();
  // hiddenのid="entry_url"の値を取得
  $("#cart_in").click(function () {

    var item_id = $("#item_id").val();
    // hiddenのid="item_id"の値を取得
    location.href = entry_url + "cart.php?item_id=" + item_id;
     // item_idをクエリパラメータとしてURLに追加してリダイレクト
    
  });
});


function suggest(query, ctg_id) {
  // クエリが空の場合、サジェストエリアをクリアして終了
  if (query.length == 0) {
    document.getElementById("suggestions").innerHTML = "";
    return;
  }

  // 空じゃなかったら、XMLHttpRequest(非同期通信、ページ全体をリロードすることなく)オブジェクトを作成
  var xhr = new XMLHttpRequest();
  
  // リクエストの状態が変わったときに呼び出される関数を定義
  xhr.onreadystatechange = function () {
    // 4でリクエストが完了、かつステータスが200で(リクエストが成功)の場合
    if (this.readyState == 4 && this.status == 200) {
      // サーバーからのレスポンスをサジェストエリアに表示
      document.getElementById("suggestions").innerHTML = this.responseText;
    }
  };

   // xhr.openでGETリクエストを初期化。クエリとカテゴリIDをエンコード(特殊文字が正しく処理)してURLに追加
  // URLパラメータを送信するためのキー(query)と値(ctg_id)、
  xhr.open("GET", "suggest.php?query=" + encodeURIComponent(query) + "&ctg_id" + encodeURIComponent(ctg_id), true);
  // 流れ : URLパラメータで検索、オブジェクト作成、成功したらデータベースから値を取得suggestionsで表示
  xhr.sent();
  // sendで初めてsuggest.phpが呼び出される
}


// 検索ボックスのsearchBoxに文字を入力、keyupイベントが発生
document.getElementById("searchBox").addEventListener("keyup", function() {
  // keyupイベントリスナーが起動、検索ボックスのqueryとカテゴリーのctg_idを取得  
  var query = this.value; 
  var ctg_id = document.getElementById("categorySelect").value;
  // イベントリスナーは、これらの値を引数としてsuggest関数を呼び出す
  suggest(query, ctg_id);
});
