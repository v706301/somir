'use strict';

angular.module('somirApp')
    .controller('MainCtrl', function ($scope, $interval, $http, Carousel) {
        /**
         * Slideshow block
         */
        var timerId = null;
        $scope.slideInterval = 5000;
        $scope.slides = [];

        $scope.load = function() {
            Carousel.get(10).success(function(result){
                swapSlides(result);
            });
        };

        function swapSlides(result) {
            var activeIndex = _.find($scope.slides,function(slide){return slide.active === true;});
            if (activeIndex >= 0) {
                if (result.length === $scope.slides.length) {
                    result.splice(activeIndex, 1, $scope.slides[activeIndex]);
                } else {
                    result.push($scope.slides[activeIndex]);
                }
            }
            $scope.slides = result;
        }

        $scope.load();
        timerId = $interval(function(){$scope.load();},600000);
        $scope.$on("$destroy", function(){
            if (angular.isDefined(timerId)) {
                $interval.cancel(timerId);
            }
            timerId = null;
        });

        /**
         * Search block
         */
        var searchResults = null;
        $scope.selectedSearchItem = null;

        $scope.search = function(val) {
            return $http.get('/handlers/search.php', {
                params: {
                    text: val,
                    limit: 10
                }
            }).then(function(res){
                    searchResults = res.data;
                    var justNames = [];
                    angular.forEach(searchResults, function(item){
                        justNames.push(item.text);
                    });
                    return justNames;
                });
        };
        $scope.searchItemSelected = function(item, model, label) {
            console.log(item);
        };
    });
