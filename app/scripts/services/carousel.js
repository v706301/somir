'use strict';

angular.module('somirApp')
    .service('Carousel', function Carousel($http) {
        var url = '/handlers/carousel.php';

        this.get = function (slideCount) {
            if (!(angular.isNumber(slideCount) && slideCount > 0)) {
                slideCount = 10;
            }
            return $http({
                url: url,
                method: 'GET',
                params: {count: slideCount}
            });
        };
    });
