app.directive('editableText', function(){
  return {
    restrict: 'A',
    require: 'ngModel', // モデルとのバインディングを保持するためngModelの機能を使う
    link: function(scope, element, attrs, ngModel){
      // 描画する
      ngModel.$render = function(){
        element.html(ngModel.$viewValue);
      };
      // ダブルクリックイベントで編集可能にする
      element.on('dblclick', function(){
        var EDITING_PROP = 'editing';
        if(!element.hasClass(EDITING_PROP)){
          // 編集可能状態クラスを付加
          element.addClass(EDITING_PROP);
          // 要素を非表示にして、テキストボックスを要素の隣に付加
          element.css('display', 'none').after('<textarea id="tmpTextArea" class="form-control">' + ngModel.$viewValue  + '</textarea>');
          // 上で付加したテキストボックスのAngularJSオブジェクトを取得
          var textAareaElem = angular.element(document.getElementById('tmpTextArea'));
          // focusを失うかEnterキーが押されたら、テキストボックスの値を元の要素にセットし、付加したテキストボックスを削除
          function done(){
            var inputValue = textAareaElem.val() || this.defaultValue;
            element.removeClass(EDITING_PROP).text(inputValue);
            textAareaElem.remove();
            // 元の要素を表示する
            element.css('display', 'block');
            // ビューの更新
            scope.$apply(function(){
              ngModel.$setViewValue(inputValue);
            });
          }
          textAareaElem.on('blur', done);
          textAareaElem.on('keydown', function(e){ e.keyCode === 13 ? done() : angular.noop() });
          textAareaElem.on('$destroy', function(){
            // 編集可能テキストボックスからイベントハンドラを全て削除
            textAareaElem.off();
          });
          // テキストボックスにフォーカスする
          textAareaElem[0].focus();
        }
      });
    }
  };
});
