app.directive('browseButton', function(){
	return {
		restrict: 'A',
		link: function(scope, element, attrs){
			element.bind('click', function(e){
				document.getElementById('inputFile').click();
			});
		}
	};
});
