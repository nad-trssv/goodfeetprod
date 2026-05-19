
document.addEventListener("DOMContentLoaded", function () {
    const logoLarge = document.querySelector(".logoLarge");
    const logoSmall = document.querySelector(".logoSmall");

    let isScrolled = false; 

    window.addEventListener("scroll", function () {
      if (window.scrollY > 0 && !isScrolled) {
        isScrolled = true;

        logoLarge.classList.add("hide");

        setTimeout(() => {
          logoLarge.classList.add("d-none");

          logoSmall.classList.remove("d-none");
          setTimeout(() => {
            logoSmall.classList.add("show");
          }, 50); 
        }, 500); 

      } else if (window.scrollY === 0 && isScrolled) {
        isScrolled = false;

        logoSmall.classList.remove("show");

        setTimeout(() => {
          logoSmall.classList.add("d-none");

          logoLarge.classList.remove("d-none");
          setTimeout(() => {
            logoLarge.classList.remove("hide");
          }, 50);
        }, 500);
      }
    });
  });