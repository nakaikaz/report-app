app.factory('Report', ['$http', 'API', function($http, API){
	var obj = {};
	obj.get = function(q){
		return $http.get(API.baseUrl + q).then(function(response){
			return response.data;
		});
	}
	obj.post = function(q, data, config){
		return $http.post(API.baseUrl + q, data, config).then(function(response){
			return response.data;
		});
	}
	obj.put = function(q, data, config){
		return $http.put(API.baseUrl + q, data, config).then(function(response){
			return response.data;
		});
	}
	obj.delete = function(q, config){
		return $http.delete(API.baseUrl + q, config).then(function(response){
			return response.data;
		});
	}
	return obj;
}]);
