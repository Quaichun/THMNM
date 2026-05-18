/* ============================================================
   ShopTech - public/js/main.js
   ============================================================ */

   document.addEventListener('DOMContentLoaded', () => {

    /* ════════════════════════════════════════════════════════════
       1. BANNER CAROUSEL
    ════════════════════════════════════════════════════════════ */
    const carousel = document.querySelector('.st-carousel');
    if (carousel) {
      const track   = carousel.querySelector('.st-carousel-track');
      const slides  = carousel.querySelectorAll('.st-carousel-slide');
      const dots    = carousel.querySelectorAll('.st-dot');
      const btnPrev = carousel.querySelector('.st-carousel-btn.prev');
      const btnNext = carousel.querySelector('.st-carousel-btn.next');
      let current = 0, timer;
  
      function goTo(idx) {
        current = (idx + slides.length) % slides.length;
        track.style.transform = `translateX(-${current * 100}%)`;
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
      }
      function startAuto() { timer = setInterval(() => goTo(current + 1), 4500); }
      function stopAuto()  { clearInterval(timer); }
  
      dots.forEach((dot, i) => dot.addEventListener('click', () => { stopAuto(); goTo(i); startAuto(); }));
      btnPrev?.addEventListener('click', () => { stopAuto(); goTo(current - 1); startAuto(); });
      btnNext?.addEventListener('click', () => { stopAuto(); goTo(current + 1); startAuto(); });
      carousel.addEventListener('mouseenter', stopAuto);
      carousel.addEventListener('mouseleave', startAuto);
  
      let touchStartX = 0;
      carousel.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
      carousel.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 40) { stopAuto(); goTo(dx < 0 ? current + 1 : current - 1); startAuto(); }
      });
  
      goTo(0); startAuto();
    }
  
    /* ════════════════════════════════════════════════════════════
       2. SEARCH AUTOCOMPLETE
    ════════════════════════════════════════════════════════════ */
    const searchWrap  = document.querySelector('.st-search');
    const searchInput = searchWrap?.querySelector('input');
    const products    = window.ST_PRODUCTS || [];
    let dropdown      = null;
  
    function buildDropdown() {
      if (dropdown) return;
      dropdown = document.createElement('div');
      dropdown.className = 'st-search-dropdown';
      searchWrap.appendChild(dropdown);
      document.addEventListener('click', e => {
        if (!searchWrap.contains(e.target)) closeDropdown();
      });
    }
  
    function openDropdown(items) {
      buildDropdown();
      if (!items.length) {
        dropdown.innerHTML = `<div class="st-search-no-result">🔍 Không tìm thấy sản phẩm nào</div>`;
      } else {
        const q  = searchInput.value.trim();
        const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
        dropdown.innerHTML = items.map(p => {
          const name  = p.name.replace(re, '<mark>$1</mark>');
          const price = parseInt(p.price).toLocaleString('vi-VN') + '₫';
          return `<div class="st-search-item" data-url="/Product/show/${p.id}">
            <div class="st-si-icon">🛒</div>
            <div class="st-si-info">
              <div class="st-si-name">${name}</div>
              <div class="st-si-cat">${p.cat || ''}</div>
            </div>
            <div class="st-si-price">${price}</div>
          </div>`;
        }).join('');
        dropdown.querySelectorAll('.st-search-item').forEach(item => {
          item.addEventListener('click', () => window.location.href = item.dataset.url);
        });
      }
      dropdown.classList.add('open');
    }
  
    function closeDropdown() { dropdown?.classList.remove('open'); }
  
    if (searchInput && products.length) {
      searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        if (!q) { closeDropdown(); return; }
        openDropdown(products.filter(p => p.name.toLowerCase().includes(q)).slice(0, 8));
      });
  
      searchInput.addEventListener('keydown', e => {
        if (!dropdown) return;
        const items = Array.from(dropdown.querySelectorAll('.st-search-item'));
        const idx   = items.findIndex(el => el.classList.contains('selected'));
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          items[idx]?.classList.remove('selected');
          items[(idx + 1) % items.length]?.classList.add('selected');
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          items[idx]?.classList.remove('selected');
          items[(idx - 1 + items.length) % items.length]?.classList.add('selected');
        } else if (e.key === 'Enter') {
          const active = dropdown.querySelector('.selected');
          if (active) { e.preventDefault(); window.location.href = active.dataset.url; }
        } else if (e.key === 'Escape') { closeDropdown(); }
      });
  
      searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim()) searchInput.dispatchEvent(new Event('input'));
      });
    }
  
    /* Grid search filter */
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        document.querySelectorAll('.st-card').forEach(c => {
          c.style.display = (!q || (c.dataset.name||'').toLowerCase().includes(q)) ? '' : 'none';
        });
      });
    }
  
    /* ════════════════════════════════════════════════════════════
       3. DUAL PRICE RANGE SLIDER
    ════════════════════════════════════════════════════════════ */
    const minSlider = document.getElementById('rangeMin');
    const maxSlider = document.getElementById('rangeMax');
    const labelMin  = document.getElementById('labelMin');
    const labelMax  = document.getElementById('labelMax');
    const fill      = document.querySelector('.st-range-fill');
  
    function fmt(v) { return parseInt(v).toLocaleString('vi-VN') + '₫'; }
  
    function updateSlider() {
      if (!minSlider || !maxSlider) return;
      let min = parseInt(minSlider.value), max = parseInt(maxSlider.value);
      if (min > max) { min = max; minSlider.value = min; }
      const total = parseInt(minSlider.max);
      if (fill) {
        fill.style.left  = (min / total * 100) + '%';
        fill.style.width = ((max - min) / total * 100) + '%';
      }
      if (labelMin) labelMin.textContent = fmt(min);
      if (labelMax) labelMax.textContent = fmt(max);
    }
  
    minSlider?.addEventListener('input', updateSlider);
    maxSlider?.addEventListener('input', updateSlider);
    if (minSlider) updateSlider();
  
    document.getElementById('btnPriceFilter')?.addEventListener('click', () => {
      const min = parseInt(minSlider?.value || 0);
      const max = parseInt(maxSlider?.value || 999999999);
      applyAllFilters(min, max);
    });
  
    /* ════════════════════════════════════════════════════════════
       4. CATEGORY FILTER
    ════════════════════════════════════════════════════════════ */
    document.querySelectorAll('input[name="cat-filter"]').forEach(r => {
      r.addEventListener('change', () => {
        const min = parseInt(minSlider?.value || 0);
        const max = parseInt(maxSlider?.value || 999999999);
        applyAllFilters(min, max);
      });
    });
  
    function applyAllFilters(min, max) {
      const grid = document.querySelector('.st-product-grid');
      if (!grid) return;
      const cat = document.querySelector('input[name="cat-filter"]:checked')?.value || 'all';
      const q   = (searchInput?.value || '').trim().toLowerCase();
      grid.querySelectorAll('.st-card').forEach(card => {
        const price   = parseInt(card.dataset.price || '0');
        const catOk   = cat === 'all' || card.dataset.cat === cat;
        const priceOk = price >= min && price <= max;
        const nameOk  = !q || (card.dataset.name || '').toLowerCase().includes(q);
        card.style.display = (catOk && priceOk && nameOk) ? '' : 'none';
      });
    }
  
/* ════════════════════════════════════════════════════════════
   5. PRODUCT SORT
════════════════════════════════════════════════════════════ */
const sortSelect  = document.getElementById('sortProducts');
const productGrid = document.querySelector('.st-product-grid');

if (sortSelect && productGrid) {

  // Lưu thứ tự ban đầu
  const originalCards = Array.from(
    productGrid.querySelectorAll('.st-card')
  );

  sortSelect.addEventListener('change', () => {

    const cards = Array.from(
      productGrid.querySelectorAll('.st-card')
    );

    const dir = sortSelect.value;

    // Quay về mặc định
    if (!dir) {

      originalCards.forEach(card => {
        productGrid.appendChild(card);
      });

      return;
    }

    // Sắp xếp theo giá
    cards.sort((a, b) => {

      const pa = parseInt(a.dataset.price || 0);
      const pb = parseInt(b.dataset.price || 0);

      return dir === 'asc'
        ? pa - pb
        : pb - pa;
    });

    cards.forEach(card => {
      productGrid.appendChild(card);
    });
  });
}
  
    /* ════════════════════════════════════════════════════════════
       6. FILE UPLOAD PREVIEW
    ════════════════════════════════════════════════════════════ */
    const fileInput   = document.getElementById('imageInput');
    const previewBox  = document.getElementById('imgPreviewBox');
    const previewImg  = document.getElementById('imgPreviewImg');
    const previewName = document.getElementById('imgPreviewName');
    const previewSize = document.getElementById('imgPreviewSize');
    const removeBtn   = document.getElementById('imgRemoveBtn');
  
    if (fileInput) {
      fileInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
          if (previewImg)  previewImg.src = ev.target.result;
          if (previewName) previewName.textContent = file.name;
          if (previewSize) previewSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
          previewBox?.classList.add('show');
        };
        reader.readAsDataURL(file);
      });
      removeBtn?.addEventListener('click', () => {
        fileInput.value = '';
        previewBox?.classList.remove('show');
        if (previewImg) previewImg.src = '';
      });
    }
  
    /* ════════════════════════════════════════════════════════════
       7. DELETE CONFIRM
    ════════════════════════════════════════════════════════════ */
    document.querySelectorAll('.btn-delete-confirm').forEach(btn => {
      btn.addEventListener('click', e => {
        if (!confirm('Bạn có chắc chắn muốn xóa?')) e.preventDefault();
      });
    });
  
    /* ════════════════════════════════════════════════════════════
       8. SCROLL FADE-UP
    ════════════════════════════════════════════════════════════ */
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('fade-up'); obs.unobserve(e.target); } });
    }, { threshold: 0.06 });
    document.querySelectorAll('.st-card,.st-detail-layout,.st-form-card,.st-table-card').forEach(el => obs.observe(el));
  
    /* ════════════════════════════════════════════════════════════
       9. MOBILE TOGGLE
    ════════════════════════════════════════════════════════════ */
    document.querySelector('.st-toggle')?.addEventListener('click', () => {
      document.querySelector('.st-nav-links')?.classList.toggle('open');
    });
  
    /* ════════════════════════════════════════════════════════════
       10. ACTIVE NAV
    ════════════════════════════════════════════════════════════ */
    const path = window.location.pathname;
    document.querySelectorAll('.st-nav-links a').forEach(a => {
      if (path.startsWith(a.getAttribute('href'))) a.classList.add('active');
    });
  });

  /* ════════════════════════════════════════════════════════════
   CART: toast + badge bump
   ════════════════════════════════════════════════════════════ */

// Toast helper
function showToast(msg, icon = '🛒') {
    let toast = document.getElementById('stToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'stToast';
      toast.className = 'st-toast';
      document.body.appendChild(toast);
    }
    toast.innerHTML = `<span>${icon}</span><span>${msg}</span>`;
    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), 2800);
  }
  
  // Badge bump animation khi click "Thêm vào giỏ hàng"
  document.querySelectorAll('a[href*="addToCart"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const cartBtn = document.getElementById('cartNavBtn');
      if (cartBtn) {
        cartBtn.classList.remove('bump');
        void cartBtn.offsetWidth; // reflow để restart animation
        cartBtn.classList.add('bump');
      }
      showToast('Đã thêm vào giỏ hàng!', '✅');
    });
  });
  
  /* ════════════════════════════════════════════════════════════
     CART: quantity button hover pulse
     ════════════════════════════════════════════════════════════ */
  document.querySelectorAll('.st-qty-btn').forEach(btn => {
    btn.addEventListener('mousedown', () => {
      btn.style.transform = 'scale(0.9)';
    });
    btn.addEventListener('mouseup',   () => {
      btn.style.transform = '';
    });
  });
  
  /* ════════════════════════════════════════════════════════════
     CART: confirm xóa sản phẩm (ghi đè lên btn-delete-confirm
     chỉ cho nút remove trong cart để khỏi hiện popup bootstrap)
     ════════════════════════════════════════════════════════════ */
  document.querySelectorAll('.st-cart-remove').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm('Xóa sản phẩm này khỏi giỏ hàng?')) e.preventDefault();
    });
  });