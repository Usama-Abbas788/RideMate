// RideMate — Main JavaScript

document.addEventListener('DOMContentLoaded', function () {
  // ── Global JS Dialog Modal ──
  window.showDialog = function(message, title = 'Attention') {
    let dialogOverlay = document.getElementById('global-dialog');
    if (!dialogOverlay) {
      dialogOverlay = document.createElement('div');
      dialogOverlay.id = 'global-dialog';
      dialogOverlay.className = 'dialog-overlay';
      dialogOverlay.innerHTML = `
        <div class="dialog-box">
          <div class="dialog-icon">⚠️</div>
          <div class="dialog-title" id="global-dialog-title"></div>
          <div class="dialog-message" id="global-dialog-message"></div>
          <button class="btn btn-outline w-100" onclick="document.getElementById('global-dialog').classList.remove('show'); setTimeout(() => document.getElementById('global-dialog').remove(), 300);">OK, I understand</button>
        </div>
      `;
      document.body.appendChild(dialogOverlay);
    }
    
    document.getElementById('global-dialog-title').innerText = title;
    document.getElementById('global-dialog-message').innerText = message;
    
    // Force reflow for animation
    void dialogOverlay.offsetWidth;
    dialogOverlay.classList.add('show');
  };

  // ── Auto-hide alerts ──
  const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-10px)';
      setTimeout(() => alert.remove(), 500);
    }, 4500);
  });


  // ── Confirm dialogs ──
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      const msg = this.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // ── Ride search filter: highlight active ──
  const searchForm = document.getElementById('ride_search_form');
  if (searchForm) {
    // Set min date to today for date picker
    const dateInput = searchForm.querySelector('input[type="date"]');
    if (dateInput && !dateInput.value) {
      const today = new Date().toISOString().split('T')[0];
      dateInput.setAttribute('min', today);
    }
  }

  // ── Ride create: set min datetime to now ──
  const rideDateInput = document.getElementById('ride_date');
  if (rideDateInput) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    rideDateInput.setAttribute('min', now.toISOString().slice(0, 16));
  }

  // ── Toast notification system ──
  window.showToast = function (message, type = 'success') {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <span class="toast-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
      <span>${message}</span>
    `;
    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
      requestAnimationFrame(() => toast.classList.add('show'));
    });

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 400);
    }, 3500);
  };

  function createToastContainer() {
    const div = document.createElement('div');
    div.id = 'toast-container';
    document.body.appendChild(div);
    return div;
  }

  // ── Add toast styles dynamically ──
  const toastStyle = document.createElement('style');
  toastStyle.textContent = `
    #toast-container {
      position: fixed;
      bottom: 1.5rem;
      right: 1.5rem;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .toast {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      background: #1a2f4e;
      color: white;
      padding: 0.75rem 1.25rem;
      border-radius: 12px;
      font-size: 0.9rem;
      font-weight: 500;
      box-shadow: 0 8px 30px rgba(0,0,0,0.25);
      transform: translateX(120%);
      transition: transform 0.35s cubic-bezier(0.4,0,0.2,1), opacity 0.35s;
      opacity: 0;
      min-width: 260px;
      border-left: 4px solid transparent;
    }
    .toast.show { transform: translateX(0); opacity: 1; }
    .toast-success { border-color: #28c76f; }
    .toast-error   { border-color: #16a34a; }
    .toast-info    { border-color: #22c55e; }
    .toast-icon { font-size: 1rem; }
  `;
  document.head.appendChild(toastStyle);

  // ── Smooth scroll for anchor links ──
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ── Counter animation for stat numbers ──
  function animateCounter(el) {
    const target = parseInt(el.textContent.replace(/\D/g, ''));
    if (isNaN(target)) return;
    let current = 0;
    const step = Math.ceil(target / 60);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString() + (el.dataset.suffix || '');
      if (current >= target) clearInterval(timer);
    }, 20);
  }

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.counter-animate').forEach(el => observer.observe(el));

  // ── Active nav link ──
  const currentPath = window.location.pathname;
  document.querySelectorAll('.navbar nav a, .sidebar-nav a').forEach(link => {
    if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop())) {
      link.classList.add('active');
    }
  });

  // ── Submit button loading state ──
  function createSpinner() {
    const spinner = document.createElement('span');
    spinner.className = 'spinner';
    spinner.setAttribute('aria-hidden', 'true');
    return spinner;
  }

  function setButtonLoading(button) {
    if (!button || button.disabled) return;
    button.disabled = true;
    button.classList.add('btn-loading');

    if (button.tagName === 'INPUT') {
      button.dataset.originalText = button.value;
      button.value = 'Please wait...';
    } else {
      button.dataset.originalText = button.innerHTML;
      button.innerHTML = '<span class="btn-loading-text">Please wait...</span>';
      button.appendChild(createSpinner());
    }
  }

  document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.skipLoading === 'true') return;

    let submitButton = event.submitter || document.activeElement;
    if (submitButton && !(submitButton instanceof HTMLButtonElement || submitButton instanceof HTMLInputElement)) {
      submitButton = null;
    }

    if (!submitButton) {
      submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
    }

    if (!submitButton) return;

    setButtonLoading(submitButton);
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(button => {
      if (button !== submitButton) button.disabled = true;
    });
  });

});
