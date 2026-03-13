const swiper = new Swiper('.swiper', {
  autoplay: {
    delay: 4000,
    disableOnInteraction: false
  },
  direction: 'horizontal',
  loop: true,
  speed: 2500,
  watchSlidesProgress: true,
  parallax: true,

  pagination: {
    el: ".swiper-pagination",
    type: "fraction",
  },
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  }
});

// Hover event to stop autoplay
const swiperContainer = document.querySelector('.swiper');

swiperContainer.addEventListener('mouseenter', () => {
  swiper.autoplay.stop();
});

swiperContainer.addEventListener('mouseleave', () => {
  swiper.autoplay.start();
});
