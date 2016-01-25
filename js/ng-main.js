
var app = angular.module('capacityTickets', ['ui.router','ngTouch','angular-spinkit','angular.filter','ui.bootstrap']);

app.config(function($stateProvider, $urlRouterProvider) {
	$urlRouterProvider.otherwise("/capacity");
	$stateProvider
		.state('home', {
			url: "/home",
			templateUrl: "parts/home.html"
		})
		.state('capacity', {
			url: "/capacity",
			templateUrl: "parts/capacity.html"
		})
		.state('groups', {
			url: "/groups",
			templateUrl: "parts/groups.html"
		})

});

//app.factory('capacityDataFactory', function($http) {
//	var capacityData = {};
//	
//	capacityData.getCapacityData = function () {
//		
//	}
//});

app.controller('slotsCtrl', function($scope, $http, $filter) {
	$scope.getCurrentSlot = function() { //Function for outputting the current time slot.
		var coeff = 30 * 60 * 1000;
		var currentDate = new Date();
		$scope.currentSlotTime = Math.floor(currentDate / coeff) * coeff;
		$scope.currentSlot = $filter('date')($scope.currentSlotTime, "h:mm a");
		return $scope.currentSlot;
	}
	$scope.getDataset = function() { //Function for retrieving CenterEdge data (via PHP as JSON).
		$scope.capacityLoading = true;
		$http.get("ticketcapacity_dataset.php")
		.success(function (response) {
			$scope.timeslots = response.flightTickets;
		});
		$scope.currentSlot = $scope.getCurrentSlot();
		$scope.capacityLoading = false;
	}
	$scope.getDataset(); //Instantiate the dataset.
	$scope.viewTime = $scope.currentSlot; //Initiate the jumpers view with the active time slot.
	$scope.getSlot = function() { //Functions to set the time slot being viewed.
		return $scope.viewTime;
	}
	$scope.setSlot = function(slot) {
		$scope.viewTime = slot;
		$scope.predicate = '-Count';
		$scope.reverse = true;
	}
	$scope.predicate = '-Count'; //Logic for sorting jumper data.
	$scope.reverse = true;
	$scope.order = function(predicate) {
		$scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
		$scope.predicate = predicate;
	};
	$scope.query = {}; //Logic for searching jumper data.
    $scope.queryBy = '$';
	$scope.viewingWidth = window.innerWidth;
	
	$scope.customerData = []; //Function for retrieving customer data from CenterEdge.
	$scope.getCustomerData = function(custKey) {
		$http.get("customer_data.php", {
			params: {cust_key: custKey}
		})
		.success(function (response) {
			$scope.customerData.unshift(response.customerData);
			if ($scope.customerData.length == 4) {
				$scope.customerData.pop();
			} else {}
		});
	}
	
});

app.controller('ModalDemoCtrl', function($scope, $http, $modal, $log) {
	$scope.animationsEnabled = true;
	$scope.open = function (size) {
		var modalInstance = $modal.open({
			animation: $scope.animationsEnabled,
			templateUrl: 'parts/weeklyGM_popup.html',
			controller: 'ModalInstanceCtrl',
			size: size
		});
		modalInstance.result.then(function (selectedItem) {
			$scope.selected = selectedItem;
		});
	};
	$scope.toggleAnimation = function () {
		$scope.animationsEnabled = !$scope.animationsEnabled;
	};
});

app.controller('ModalInstanceCtrl', function ($scope, $modalInstance, $http) {
	$http.get("weekly_GM_report.php")
	.success(function (response) {
		$scope.weeklyGmReportData = response.weeklyGmReport;
	});
	$scope.ok = function () {
		$modalInstance.close($scope.selected);
	};
});

app.filter('passMeBy', function() {
	return function(input) {
		var output = [];
		var output2 = [];
		var curTime = new Date();
		var coeff = 30 * 60 * 1000;
		var curSlot = Math.floor(curTime/ coeff) * coeff;
		angular.forEach(input, function(timeslot) {
			if (timeslot.slotTimeUnix >= curSlot - 1800000) {
				output.push(timeslot)
			}
			else {
				output2.push(timeslot)
			}
		})
		return output.concat(output2);
	}
});