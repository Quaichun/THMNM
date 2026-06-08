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
       2. SEARCH AUTOCOMPLETE (LIVE SEARCH)
    ════════════════════════════════════════════════════════════ */
    const searchWrap  = document.querySelector('.st-search');
    const searchInput = searchWrap?.querySelector('input');
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
  
    async function openDropdown(q) {
      buildDropdown();
      dropdown.innerHTML = `<div class="st-search-loading" style="padding:15px;text-align:center;">Đang tìm...</div>`;
      dropdown.classList.add('open');

      try {
        const res = await fetch(`/Product/liveSearch?q=${encodeURIComponent(q)}`);
        const items = await res.json();

        if (!items.length) {
          dropdown.innerHTML = `<div class="st-search-no-result" style="padding:15px;text-align:center;">🔍 Không tìm thấy sản phẩm nào</div>`;
        } else {
          dropdown.innerHTML = items.map(p => {
            const price = parseInt(p.price).toLocaleString('vi-VN') + '₫';
            const img = p.image ? `/${p.image}` : '';
            return `<div class="st-search-item" data-url="/Product/show/${p.id}">
              <div class="st-si-img-wrap" style="width:40px;height:40px;overflow:hidden;border-radius:4px;flex-shrink:0;">
                ${img ? `<img src="${img}" style="width:100%;height:100%;object-fit:cover;">` : '📦'}
              </div>
              <div class="st-si-info" style="flex:1;min-width:0;padding-left:10px;">
                <div class="st-si-name" style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${p.name}</div>
                <div class="st-si-cat" style="font-size:0.75rem;opacity:0.7;">${p.category_name || ''}</div>
              </div>
              <div class="st-si-price" style="font-weight:700;color:var(--primary);">${price}</div>
            </div>`;
          }).join('');
          
          dropdown.querySelectorAll('.st-search-item').forEach(item => {
            item.addEventListener('click', () => window.location.href = item.dataset.url);
          });
        }
      } catch (e) {
        dropdown.innerHTML = `<div class="st-search-no-result">Có lỗi xảy ra</div>`;
      }
    }
  
    function closeDropdown() { dropdown?.classList.remove('open'); }
  
    if (searchInput) {
      let searchTimer;
      searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = searchInput.value.trim();
        if (q.length < 2) { closeDropdown(); return; }
        searchTimer = setTimeout(() => openDropdown(q), 300);
      });
  
      searchInput.addEventListener('keydown', e => {
        if (!dropdown || !dropdown.classList.contains('open')) return;
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
    }
  
    /* ════════════════════════════════════════════════════════════
       3. ADVANCED AJAX FILTERING
    ════════════════════════════════════════════════════════════ */
    const productGrid = document.querySelector('.st-product-grid');
    const loadMoreWrap = document.getElementById('loadMoreWrap');
    const noResultMsg = document.getElementById('noResultMsg');
    
    let currentLimit = window.ST_LIST_CONFIG?.limit || 10;
    let currentOffset = window.ST_LIST_CONFIG?.count || 0;
    let totalCount = window.ST_LIST_CONFIG?.total || 0;
    let isLoading = false;
    let isLastPage = currentOffset >= totalCount;

    // Initialize UI
    if (productGrid && loadMoreWrap) {
        renderLoadMoreWidget();
    }

    // Dual Range Slider logic
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


    async function fetchFilteredProducts(isLoadMore = false) {
        if (isLoading || (isLoadMore && isLastPage) || !productGrid) return;
        
        if (!isLoadMore) {
            currentOffset = 0;
            isLastPage = false;
            productGrid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:50px;"><div class="st-loading-spinner" style="border: 4px solid rgba(0,0,0,.1); border-left-color: var(--primary); border-radius: 50%; width: 40px; height: 40px; animation: st-spin 1s linear infinite; margin: 0 auto 15px;"></div><p>Đang tìm sản phẩm...</p></div>';
        }
        
        isLoading = true;

        const filters = {
            category: document.querySelector('input[name="cat-filter"]:checked')?.value === 'all' ? '' : document.querySelector('input[name="cat-filter"]:checked')?.value,
            min_price: document.getElementById('rangeMin')?.value || 0,
            max_price: document.getElementById('rangeMax')?.value || 999999999,
            search: searchInput?.value.trim() || '',
            specs: {}
        };

        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            if (radio.name.startsWith('spec-filter-') && radio.value !== '') {
                const specName = radio.name.replace('spec-filter-', '');
                filters.specs[specName] = radio.value;
            }
        });

        const formData = new FormData();
        formData.append('filters[category]', filters.category);
        formData.append('filters[min_price]', filters.min_price);
        formData.append('filters[max_price]', filters.max_price);
        formData.append('filters[search]', filters.search);
        formData.append('offset', currentOffset);
        formData.append('limit', currentLimit);
        for (const [key, val] of Object.entries(filters.specs)) {
            formData.append(`filters[specs][${key}]`, val);
        }

        try {
            const res = await fetch('/Product/ajaxFilter', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                if (!isLoadMore) productGrid.innerHTML = '';
                totalCount = data.total || 0;

                if (data.products.length === 0 && !isLoadMore) {
                    productGrid.style.display = 'none';
                    if (noResultMsg) noResultMsg.style.display = 'block';
                    isLastPage = true;
                } else {
                    productGrid.style.display = 'grid';
                    if (noResultMsg) noResultMsg.style.display = 'none';
                    data.products.forEach(p => productGrid.appendChild(createProductCard(p)));
                    currentOffset += data.products.length;
                    if (currentOffset >= totalCount) isLastPage = true;
                }
                renderLoadMoreWidget();
            }
        } catch (e) { console.error(e); } finally { isLoading = false; }
    }

    function createProductCard(p) {
        const div = document.createElement('div');
        div.className = 'st-card fade-up';
        div.dataset.price = p.price;
        div.dataset.name = p.name;
        const price = parseInt(p.price).toLocaleString('vi-VN') + '₫';
        const img = p.image ? `<img class="st-card-img" src="/${p.image}" alt="">` : '<div class="st-card-img-placeholder">📦</div>';
        
        const isAdmin = window.ST_USER?.isAdmin || false;
        const isLoggedIn = window.ST_USER?.isLoggedIn || false;

        div.innerHTML = `
            <a href="/Product/show/${p.id}">${img}</a>
            <div class="st-card-body">
                ${p.category_name ? `<span class="st-card-cat">${p.category_name}</span>` : ''}
                <div class="st-card-name"><a href="/Product/show/${p.id}" style="color:inherit;">${p.name}</a></div>
                <div class="st-card-price">${price}</div>
                <div class="st-card-actions">
                    <a href="/Product/show/${p.id}" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Xem</a>
                    ${isAdmin ? `
                        <a href="/Product/edit/${p.id}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                        <a href="/api/product/${p.id}" class="btn btn-danger btn-sm btn-delete-confirm st-api-delete-product"><i class="bi bi-trash"></i></a>
                    ` : ''}
                    ${isLoggedIn && !isAdmin ? `
                        <a href="/Product/addToCart/${p.id}" class="btn btn-success btn-sm ajax-add-cart-dynamic"><i class="bi bi-cart-plus"></i> Thêm giỏ</a>
                    ` : ''}
                </div>
            </div>`;
        
        bindProductDeleteButtons(div);
        /*
        div.querySelector('.btn-delete-confirm')?.addEventListener('click', e => {
            if (!confirm('Bạn có chắc chắn muốn xóa?')) e.preventDefault();
        });
        */

        div.querySelector('.ajax-add-cart-dynamic')?.addEventListener('click', e => {
            e.preventDefault();
            const url = e.currentTarget.getAttribute('href');
            if (typeof handleAddToCart === 'function') {
                handleAddToCart(url, e.currentTarget);
            }
        });

        return div;
    }

    function renderLoadMoreWidget() {
        if (!loadMoreWrap || (totalCount === 0 && !isLoading)) {
            if (loadMoreWrap) loadMoreWrap.innerHTML = '';
            return;
        }

        const pct = totalCount > 0 ? Math.min(100, Math.round((currentOffset / totalCount) * 100)) : 0;
        
        if (isLastPage) {
            loadMoreWrap.innerHTML = `
                <div class="st-load-more-wrap">
                    <div class="st-lm-progress">
                        <div class="st-lm-progress-fill" style="width:100%"></div>
                    </div>
                    <div class="st-lm-counter">
                        Đang hiển thị <strong>${currentOffset}</strong> / ${totalCount} sản phẩm
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:center;">
                        <div class="st-lm-all-done">
                            <i class="bi bi-check-circle"></i> Bạn đã xem hết tất cả sản phẩm
                        </div>
                        ${totalCount > currentLimit ? `
                            <button class="st-lm-btn" id="collapseBtn">
                                <span class="st-lm-btn-icon"><i class="bi bi-chevron-up"></i></span>
                                <span>Thu gọn</span>
                            </button>
                        ` : ''}
                    </div>
                </div>`;
            document.getElementById('collapseBtn')?.addEventListener('click', () => {
                window.scrollTo({ top: productGrid.offsetTop - 100, behavior: 'smooth' });
                fetchFilteredProducts();
            });
        } else {
            loadMoreWrap.innerHTML = `
                <div class="st-load-more-wrap">
                    <div class="st-lm-progress">
                        <div class="st-lm-progress-fill" style="width:${pct}%"></div>
                    </div>
                    <div class="st-lm-counter">
                        Đang hiển thị <strong>${currentOffset}</strong> / ${totalCount} sản phẩm
                    </div>
                    <button class="st-lm-btn" id="loadMoreBtn">
                        <span class="st-lm-btn-icon"><i class="bi bi-chevron-down"></i></span>
                        <span>Xem thêm ${Math.min(totalCount - currentOffset, currentLimit)} sản phẩm</span>
                    </button>
                </div>`;
            document.getElementById('loadMoreBtn')?.addEventListener('click', () => fetchFilteredProducts(true));
        }
    }

    document.querySelectorAll('input[name="cat-filter"]').forEach(r => r.addEventListener('change', () => fetchFilteredProducts()));
    document.querySelectorAll('input[name^="spec-filter-"]').forEach(r => r.addEventListener('change', () => fetchFilteredProducts()));
    document.getElementById('btnPriceFilter')?.addEventListener('click', () => fetchFilteredProducts());
    
    const sortSelect = document.getElementById('sortProducts');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            const dir = sortSelect.value;
            if (!dir) return fetchFilteredProducts();
            const cards = Array.from(productGrid.querySelectorAll('.st-card'));
            cards.sort((a,b) => {
                const pa = parseInt(a.dataset.price);
                const pb = parseInt(b.dataset.price);
                return dir === 'asc' ? pa - pb : pb - pa;
            });
            cards.forEach(c => productGrid.appendChild(c));
        });
    }

    const style = document.createElement('style');
    style.innerHTML = `@keyframes st-spin { to { transform: rotate(360deg); } }`;
    document.head.appendChild(style);
  
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
    function showProductApiErrors(errors) {
      if (!errors) return 'Co loi xay ra.';
      if (Array.isArray(errors)) return errors.join('\n');
      return Object.values(errors).join('\n');
    }

    document.querySelectorAll('.st-api-product-form').forEach(form => {
      form.addEventListener('submit', async e => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const oldHtml = submitBtn?.innerHTML;
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Dang luu...';
        }

        try {
          const response = await fetch(form.action, {
            method: form.dataset.apiMethod || 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
          });
          const data = await response.json();

          if (!response.ok || !data.success) {
            alert(data.message || showProductApiErrors(data.errors));
            return;
          }

          window.location.href = '/Product/list';
        } catch (err) {
          alert('Co loi khi goi API san pham.');
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = oldHtml;
          }
        }
      });
    });

    function bindProductDeleteButtons(scope = document) {
      scope.querySelectorAll('.st-api-delete-product').forEach(btn => {
        if (btn.dataset.boundApiDelete === '1') return;
        btn.dataset.boundApiDelete = '1';
        btn.addEventListener('click', async e => {
          e.preventDefault();
          if (!confirm('Ban co chac chan muon xoa?')) return;

          try {
            const response = await fetch(btn.href, {
              method: 'DELETE',
              headers: { 'Accept': 'application/json' },
              credentials: 'same-origin'
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
              alert(data.message || showProductApiErrors(data.errors));
              return;
            }

            window.location.href = '/Product/list';
          } catch (err) {
            alert('Co loi khi xoa san pham.');
          }
        });
      });
    }

    bindProductDeleteButtons();

    document.querySelectorAll('.btn-delete-confirm:not(.st-api-delete-product)').forEach(btn => {
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
  
  async function handleAddToCart(url, btn) {
    if (!url || !btn) return;

    btn.style.pointerEvents = 'none';
    btn.style.opacity = '0.7';

    try {
      const response = await fetch(url + (url.includes('?') ? '&ajax=1' : '?ajax=1'), {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });

      if (!response.ok) throw new Error('Request failed');
      const data = await response.json();
      if (!data || !data.success) throw new Error('Add failed');

      const cartBtn = document.getElementById('cartNavBtn');
      if (cartBtn) {
        cartBtn.classList.remove('bump');
        void cartBtn.offsetWidth;
        cartBtn.classList.add('bump');
      }

      const count = parseInt(data.cart_count || 0, 10);
      const cartBadge = document.getElementById('cartBadge');
      if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline-flex' : 'none';
      }

      document.querySelectorAll('.st-dd-badge').forEach(el => {
        el.textContent = count;
        el.style.display = count > 0 ? 'inline-flex' : 'none';
      });

      showToast(data.message || 'Đã thêm vào giỏ hàng!', '✅');
    } catch (err) {
      showToast('Có lỗi khi thêm vào giỏ hàng', '⚠️');
    } finally {
      btn.style.pointerEvents = '';
      btn.style.opacity = '';
    }
  }
  
  // Add to cart qua AJAX
  document.querySelectorAll('a[href*="addToCart"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      handleAddToCart(btn.getAttribute('href'), btn);
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
const navLinks   = document.getElementById('navLinks');
const navToggle  = document.getElementById('navToggle');
const navBackdrop = document.getElementById('navBackdrop');

function toggleMenu() {
  navLinks?.classList.toggle('open');
  navBackdrop?.classList.toggle('open');
  document.body.style.overflow = navLinks?.classList.contains('open') ? 'hidden' : '';
}

navToggle?.addEventListener('click', toggleMenu);
navBackdrop?.addEventListener('click', toggleMenu);

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



