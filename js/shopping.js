// formを使わずjQueryの場合
$(function () {
  var entry_url = $("#entry_url").val();
  // hiddenのid="entry_url"の値を取得
  $("#cart_in").click(function () {

    var item_id = $("#item_id").val();
    // hiddenのid="item_id"の値を取得
    location.href = entry_url + "order/cart.php?item_id=" + item_id;
     // item_idをクエリパラメータとしてURLに追加してリダイレクト
  });
});



function suggest(query, ctg_id) {
  if (query.length == 0) {
     // クエリが空の場合、サジェストエリアをクリアして終了
    document.getElementById("suggestions").innerHTML = "";
    return;
  }

  $.ajax({
    url: 'suggest.php',
    type: 'GET',
    data: { query: query, ctg_id: ctg_id },
    success: function(response) {
      // コールバック
      document.getElementById("suggestions").innerHTML = response;
      // suggest.phpから返された値がsuggestionsに挿入される
    },
    error: function(xhr, status, error) {
      console.log("Error: " + error);
    }
  });
}

document.getElementById("searchBox").addEventListener("keyup", function() {
  // serchBoxとkeyupイベントが発火
  var query = this.value; 
  var ctg_id = document.getElementById("categorySelect").value;
  suggest(query, ctg_id);
  // 検索クエリとカテゴリIDをsuggest関数に渡す
});
