document.addEventListener("DOMContentLoaded", () => {
  const counters = document.querySelectorAll('.counter');
  const speed = 200;

  counters.forEach(counter => {
    const updateCount = () => {
      const target = +counter.getAttribute('data-target');
      const count = +counter.innerText;

      const increment = Math.ceil(target / speed);

      if (count < target) {
        counter.innerText = count + increment;
        setTimeout(updateCount, 10);
      } else {
        counter.innerText = target.toLocaleString();
      }
    };

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          updateCount();
          observer.unobserve(counter);
        }
      });
    }, { threshold: 1 });

    observer.observe(counter);
  });

const text = "Welcome to SkillUp Academy!";
const heroText = document.getElementById('hero-text');
let index = 0;

function type() {
  if (index < text.length) {
    heroText.innerHTML += text.charAt(index);
    index++;
    setTimeout(type, 100);
  }
}

window.onload = type;

});
