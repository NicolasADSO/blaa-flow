import { CountUp } from 'countup.js';

document.addEventListener('DOMContentLoaded', () => {
  /* ============================
     📊 Contadores (stats animados)
     ============================ */
  const counters = document.querySelectorAll('.counter');

  const parseLocaleNumber = (str) => {
    if (!str) return NaN;
    const cleaned = str.replace(/\s/g, '').replace(/\./g, '').replace(',', '.');
    return Number(cleaned);
  };

  const animateCounter = (el) => {
    if (el.dataset.counted === '1') return;
    const endValue = parseLocaleNumber(el.textContent.trim());
    if (!Number.isFinite(endValue)) return;

    const options = { duration: 2.5, separator: '.', decimal: ',' };
    const countUp = new CountUp(el, endValue, options);

    if (!countUp.error) {
      countUp.start(() => {
        el.classList.add('animate-fade-slide-up');
        setTimeout(() => el.classList.remove('animate-fade-slide-up'), 800);
      });
      el.dataset.counted = '1';
    } else {
      console.error(countUp.error);
    }
  };

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.35 });
    counters.forEach((c) => io.observe(c));
  } else {
    counters.forEach(animateCounter);
  }

  /* ============================
     🌊 Ripple reutilizable
     ============================ */
  function attachRipple(elements) {
    elements.forEach((el) => {
      if (!el) return;
      const style = window.getComputedStyle(el);
      if (style.position === 'static') el.style.position = 'relative';
      if (style.overflow === 'visible') el.style.overflow = 'hidden';

      const makeRipple = (x, y) => {
        const ripple = document.createElement('span');
        ripple.className = '_ripple';
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
        el.appendChild(ripple);
      };

      el.addEventListener('pointerdown', (e) => {
        const rect = el.getBoundingClientRect();
        makeRipple(e.clientX - rect.left, e.clientY - rect.top);
      }, { passive: true });

      el.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const rect = el.getBoundingClientRect();
        makeRipple(rect.width / 2, rect.height / 2);
      });
    });
  }

  // Ripple en sidebar, header/topbar y botón de login
  attachRipple(document.querySelectorAll('.fi-sidebar-nav a'));
  attachRipple(document.querySelectorAll('.fi-header button, .fi-header a, .fi-topbar button, .fi-topbar a'));
  attachRipple(document.querySelectorAll('.fi-auth-card button[type="submit"]'));

  /* ============================
     📌 Header “scrolled” state
     ============================ */
  const header = document.querySelector('.fi-header') || document.querySelector('.fi-topbar');
  const onScroll = () => {
    if (!header) return;
    (window.scrollY > 10) ? header.classList.add('scrolled') : header.classList.remove('scrolled');
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ============================
     🎟 Login: variante split-screen (opcional)
     ============================ */
  const isLogin = !!document.querySelector('.fi-auth-card');
  const ENABLE_LOGIN_SPLIT = false; // cámbialo a true si quieres la imagen a la izquierda
  if (isLogin && ENABLE_LOGIN_SPLIT) {
    document.documentElement.classList.add('login-split');
  }

  /* ============================
     🎨 Tema del Login (light | dark | auto)
     ============================ */
  const LOGIN_THEME = 'light'; // ← cambia a 'dark' o 'auto' si lo prefieres

  if (isLogin) {
    const root = document.documentElement;
    root.classList.remove('theme-dark');

    if (LOGIN_THEME === 'dark') {
      root.classList.add('theme-dark');
    } else if (LOGIN_THEME === 'auto') {
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        root.classList.add('theme-dark');
      }
    }
  }
});
