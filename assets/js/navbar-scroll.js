setTimeout(function() {
  var navbar = document.querySelector('.section-3');
  var hamburgerMenu = document.querySelector('.hambuger-menu-main');
  
  if (navbar) {
    window.addEventListener('scroll', function() {
      var scrollPosition = window.scrollY;
      navbar.style.setProperty('background-color', scrollPosition > 50 ? '#fff' : '#ffffff0d', 'important');
    });
    
    navbar.style.setProperty('background-color', window.scrollY > 50 ? '#fff' : '#ffffff0d', 'important');
  }
  
  if (hamburgerMenu) {
    hamburgerMenu.style.display = window.innerWidth <= 991 ? 'flex' : '';
  }
}, 500);
