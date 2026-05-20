const swiperContainer = document.querySelector('.swiper');

if (swiperContainer && typeof Swiper === 'function') {
  const swiper = new Swiper(swiperContainer, {
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

  swiperContainer.addEventListener('mouseenter', () => {
    swiper.autoplay.stop();
  });

  swiperContainer.addEventListener('mouseleave', () => {
    swiper.autoplay.start();
  });
}
