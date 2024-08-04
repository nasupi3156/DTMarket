
$(function(){
  
  $('#address_search').click(function(){
    var zip1 = $('#zip1').val();
    var zip2 = $('#zip2').val();

    var entry_url = $('#entry_url').val();
    
    if (zip1.match(/[0-9]{3}/) === null || zip2.match(/[0-9]{4}/) === null) {
      // 入力された郵便番号が正しい形式かどうかをチェック : 3桁、4桁じゃなければ、null
      alert('正確な郵便番号を入力してください。');
      return false;
    } else {
      // ajaxリクエストの送信
      $.ajax({
        type : "get",
        url : entry_url + "/postcode_search.php?zip1=" + escape(zip1) + "&zip2=" + escape(zip2),
        // postcode.phpでデータベースから値を取得、それを使いurlパラメータを構築
      }) .then(
        
        function(data){
          // 成功の処理
          if (data == 'no' || data == ''){
            alert('該当する郵便番号がありません');
          } else {
            // 郵便番号が見つかった場合の処理
            $('#address').val(data);
          }
        },
        function(data){
            // リクエストが失敗した場合の処理
          alert("読み込みに失敗しました。")
        },
      );
    }
  });
});





