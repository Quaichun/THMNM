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

/* ════════════════════════════════════════════════════════════
   DROPDOWN — mobile toggle, desktop dùng CSS hover
   ════════════════════════════════════════════════════════════ */
const isMobile = () => window.innerWidth <= 768;

document.querySelectorAll('.st-dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', e => {
    e.preventDefault();
    if (!isMobile()) return; // Desktop: CSS :hover xử lý

    const li = toggle.closest('.st-dropdown');
    const isOpen = li.classList.contains('open');

    // Đóng tất cả
    document.querySelectorAll('.st-dropdown').forEach(d => d.classList.remove('open'));

    // Mở cái được click nếu chưa open
    if (!isOpen) li.classList.add('open');
  });
});

// Click ngoài → đóng (mobile)
document.addEventListener('click', e => {
  if (!e.target.closest('.st-dropdown') && isMobile()) {
    document.querySelectorAll('.st-dropdown').forEach(d => d.classList.remove('open'));
  }
});

// Resize window: reset open state
window.addEventListener('resize', () => {
  if (!isMobile()) {
    document.querySelectorAll('.st-dropdown').forEach(d => d.classList.remove('open'));
  }
});

/* ════════════════════════════════════════════════════════════
   PAYMENT METHOD SELECTOR
   ════════════════════════════════════════════════════════════ */
document.querySelectorAll('.st-pay-option').forEach(opt => {
  opt.addEventListener('click', () => {
    document.querySelectorAll('.st-pay-option').forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    opt.querySelector('input[type="radio"]').checked = true;
  });
});

/* ════════════════════════════════════════════════════════════
   PLACE ORDER BUTTON — loading state
   ════════════════════════════════════════════════════════════ */
const placeOrderBtn = document.getElementById('placeOrderBtn');
if (placeOrderBtn) {
  document.getElementById('checkoutForm')?.addEventListener('submit', () => {
    placeOrderBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
    placeOrderBtn.disabled = true;
  });
}

/* ════════════════════════════════════════════════════════════
   MOBILE NAV TOGGLE
   ════════════════════════════════════════════════════════════ */
document.getElementById('navToggle')?.addEventListener('click', () => {
  document.getElementById('navLinks')?.classList.toggle('open');
});

/* ════════════════════════════════════════════════════════════
   AUTH — password toggle
   ════════════════════════════════════════════════════════════ */
function togglePw(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}

/* ════════════════════════════════════════════════════════════
   REGISTER — password strength + match checker
   ════════════════════════════════════════════════════════════ */
const pw1 = document.getElementById('pw1');
const pw2 = document.getElementById('pw2');

if (pw1) {
  pw1.addEventListener('input', () => {
    const val = pw1.value;
    const bar = document.getElementById('pwStrengthBar');
    const lbl = document.getElementById('pwStrengthLabel');
    let score = 0;
    if (val.length >= 6)               score++;
    if (val.length >= 10)              score++;
    if (/[A-Z]/.test(val))            score++;
    if (/[0-9]/.test(val))            score++;
    if (/[^A-Za-z0-9]/.test(val))    score++;

    const levels = [
      { w: '0%',   bg: '',               lbl: '',            col: '' },
      { w: '25%',  bg: '#ef4444',        lbl: 'Yếu',         col: '#ef4444' },
      { w: '50%',  bg: '#f59e0b',        lbl: 'Trung bình',  col: '#f59e0b' },
      { w: '75%',  bg: '#3b82f6',        lbl: 'Khá mạnh',    col: '#3b82f6' },
      { w: '90%',  bg: '#10b981',        lbl: 'Mạnh',        col: '#10b981' },
      { w: '100%', bg: '#059669',        lbl: 'Rất mạnh',    col: '#059669' },
    ];
    const lvl = levels[Math.min(score, 5)];
    if (bar) { bar.style.width = lvl.w; bar.style.background = lvl.bg; }
    if (lbl) { lbl.textContent = lvl.lbl; lbl.style.color = lvl.col; }

    if (pw2 && pw2.value) checkMatch();
  });
}

if (pw2) pw2.addEventListener('input', checkMatch);

function checkMatch() {
  const el = document.getElementById('pwMatch');
  if (!el || !pw1 || !pw2) return;
  if (pw2.value === '') { el.textContent = ''; return; }
  if (pw1.value === pw2.value) {
    el.textContent = '✅ Mật khẩu khớp';
    el.style.color = '#10b981';
  } else {
    el.textContent = '❌ Mật khẩu chưa khớp';
    el.style.color = '#ef4444';
  }
}

/* ════════════════════════════════════════════════════════════
   AVATAR UPLOAD
   ════════════════════════════════════════════════════════════ */

// ── Sidebar: click overlay → chọn file → submit ngay ──
const avatarFileInput = document.getElementById('avatarFileInput');
const avatarForm      = document.getElementById('avatarForm');
const avatarStatus    = document.getElementById('avatarStatus');

if (avatarFileInput && avatarForm) {
  avatarFileInput.addEventListener('change', () => {
    const file = avatarFileInput.files[0];
    if (!file) return;

    // Validate phía client
    const maxMB = 3;
    const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!allowed.includes(file.type)) {
      showAvatarStatus('❌ Chỉ chấp nhận JPG, PNG, GIF, WEBP', '#ef4444');
      avatarFileInput.value = '';
      return;
    }
    if (file.size > maxMB * 1024 * 1024) {
      showAvatarStatus('❌ Ảnh vượt quá 3MB', '#ef4444');
      avatarFileInput.value = '';
      return;
    }

    // Preview ngay trong sidebar
    const reader = new FileReader();
    reader.onload = ev => {
      const wrap = document.getElementById('avatarWrap');
      if (!wrap) return;
      const existing = wrap.querySelector('.st-avatar-img, .st-avatar-circle');
      const img = document.createElement('img');
      img.src = ev.target.result;
      img.className = 'st-avatar-img';
      img.id = 'avatarPreview';
      if (existing) existing.replaceWith(img);
      else wrap.prepend(img);
    };
    reader.readAsDataURL(file);

    showAvatarStatus('⏳ Đang tải lên...', '#3b82f6');
    avatarForm.submit();
  });
}

function showAvatarStatus(msg, color) {
  if (!avatarStatus) return;
  avatarStatus.textContent = msg;
  avatarStatus.style.color = color;
}

// ── Detail section: preview trước khi lưu ──
const avatarFileInput2 = document.getElementById('avatarFileInput2');
const avatarNewPreview = document.getElementById('avatarNewPreview');
const avatarNewImg     = document.getElementById('avatarNewImg');
const avatarNewName    = document.getElementById('avatarNewName');
const avatarNewSize    = document.getElementById('avatarNewSize');
const avatarRemoveBtn  = document.getElementById('avatarRemoveBtn');
const avatarSaveBtn    = document.getElementById('avatarSaveBtn');
const avatarFileHidden = document.getElementById('avatarFileHidden');
const avatarFormDetail = document.getElementById('avatarFormDetail');
const detailPreview    = document.getElementById('avatarDetailPreview');
const detailImg        = document.getElementById('avatarDetailImg');
const detailInitial    = document.getElementById('avatarDetailInitial');
const dropZone         = document.getElementById('avatarDropZone');

function handleAvatarFile(file) {
  if (!file) return;

  const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
  if (!allowed.includes(file.type)) {
    alert('Chỉ chấp nhận JPG, PNG, GIF, WEBP');
    return;
  }
  if (file.size > 3 * 1024 * 1024) {
    alert('Ảnh không được vượt quá 3MB');
    return;
  }

  const reader = new FileReader();
  reader.onload = ev => {
    const src = ev.target.result;

    // Preview nhỏ (box bên dưới input)
    if (avatarNewImg)  avatarNewImg.src = src;
    if (avatarNewName) avatarNewName.textContent = file.name;
    if (avatarNewSize) avatarNewSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
    avatarNewPreview?.classList.add('show');

    // Preview lớn (circle 120px)
    if (detailPreview) {
      if (detailImg) {
        detailImg.src = src;
      } else if (detailInitial) {
        const img = document.createElement('img');
        img.src = src;
        img.id  = 'avatarDetailImg';
        detailInitial.replaceWith(img);
      }
      detailPreview.classList.add('has-new');
    }

    // Chuyển file sang form ẩn để submit
    const dt = new DataTransfer();
    dt.items.add(file);
    if (avatarFileHidden) avatarFileHidden.files = dt.files;
    if (avatarSaveBtn) avatarSaveBtn.style.display = 'inline-flex';
  };
  reader.readAsDataURL(file);
}

// Chọn file qua input
avatarFileInput2?.addEventListener('change', () => {
  handleAvatarFile(avatarFileInput2.files[0]);
});

// Nút xóa preview
avatarRemoveBtn?.addEventListener('click', () => {
  if (avatarNewImg)  avatarNewImg.src = '';
  if (avatarNewName) avatarNewName.textContent = '—';
  if (avatarNewSize) avatarNewSize.textContent = '—';
  avatarNewPreview?.classList.remove('show');
  if (avatarSaveBtn) avatarSaveBtn.style.display = 'none';
  if (avatarFileInput2) avatarFileInput2.value = '';
  if (avatarFileHidden) avatarFileHidden.value = '';
  detailPreview?.classList.remove('has-new');
});

// Drag & drop
dropZone?.addEventListener('dragover', e => {
  e.preventDefault();
  dropZone.classList.add('dragover');
});
dropZone?.addEventListener('dragleave', () => {
  dropZone.classList.remove('dragover');
});
dropZone?.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('dragover');
  const file = e.dataTransfer.files[0];
  if (file) handleAvatarFile(file);
});