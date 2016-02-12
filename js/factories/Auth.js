app.factory('Auth', ['$http', 'API', function($http, API){
	var obj = {};
	obj.get = function(q){
		return $http.get(API.baseUrl + q).then(function(response){
			return response.data;
		});
	}
	obj.post = function(q, object){
		return $http.post(API.baseUrl + q, object).then(function(response){
			return response.data;
		});
	}
	obj.put = function(q, object){
		return $http.post(API.baseUrl + q, object).then(function(response){
			return response.data;
		});
	}
	obj.delete = function(q){
		return $http.delete(API.baseUrl + q).then(function(response){
			return response.data;
		});
	}
	return obj;
}]);
