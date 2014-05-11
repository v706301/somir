'use strict';

describe('Service: Carousel', function () {

  // load the service's module
  beforeEach(module('somirApp'));

  // instantiate service
  var Carousel;
  beforeEach(inject(function (_Carousel_) {
    Carousel = _Carousel_;
  }));

  it('should do something', function () {
    expect(!!Carousel).toBe(true);
  });

});
