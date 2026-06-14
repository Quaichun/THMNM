/* ============================================================
   ShopTech - public/js/main.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ════════════════════════════════════════════════════════════
       1. BANNER CAROUSEL
    ════════════════════════════════════════════════════════════ */
    const carousel = document.querySelector('.st-carousel');
    if (carousel) {
        const track = carousel.querySelector('.st-carousel-track');
        const slides = carousel.querySelectorAll('.st-carousel-slide');
        const dots = carousel.querySelectorAll('.st-dot');
        const btnPrev = carousel.querySelector('.st-carousel-btn.prev');
        const btnNext = carousel.querySelector('.st-carousel-btn.next');
        let current = 0, timer;

        function goTo(idx) {
            current = (idx + slides.length) % slides.length;
            track.style.transform = `translateX(-${current * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
        }
        function startAuto() { timer = setInterval(() => goTo(current + 1), 4500); }
        function stopAuto() { clearInterval(timer); }

        dots.forEach((dot, i) => dot.addEventListener('click', () => { stopAuto(); goTo(i); startAuto(); }));
        btnPrev?.addEventListener('click', () => { stopAuto(); goTo(current - 1); startAuto(); });
        btnNext?.addEventListener('click', () => { stopAuto(); goTo(current + 1); startAuto(); });
        carousel.addEventListener('mouseenter', stopAuto);
        carousel.addEventListener('mouseleave', startAuto);

        let touchStartX = 0;
        carousel.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        carousel.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(dx) > 40) { stopAuto(); goTo(dx < 0 ? current + 1 : current - 1); startAuto(); }
        });

        goTo(0); startAuto();
    }

    /* ════════════════════════════════════════════════════════════
       2. SEARCH AUTOCOMPLETE (LIVE SEARCH)
    ════════════════════════════════════════════════════════════ */
    const searchWrap = document.querySelector('.st-search');
    const searchInput = searchWrap?.querySelector('input');
    let dropdown = null;

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
            const res = await apiFetch(`/api/product/search?q=${encodeURIComponent(q)}`, { skipAuthRedirect: true });
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
            const idx = items.findIndex(el => el.classList.contains('selected'));
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
    let currentPage = 1;
    let totalCount = window.ST_LIST_CONFIG?.total || 0;
    let isLoading = false;
    let isLastPage = (window.ST_LIST_CONFIG?.count || 0) >= totalCount;

    if (productGrid && loadMoreWrap) {
        renderLoadMoreWidget();
    }

    async function fetchFilteredProducts(isLoadMore = false) {
        if (isLoading || (isLoadMore && isLastPage) || !productGrid) return;

        if (!isLoadMore) {
            currentPage = 1;
            isLastPage = false;
            productGrid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:50px;"><div class="st-loading-spinner" style="border: 4px solid rgba(0,0,0,.1); border-left-color: var(--primary); border-radius: 50%; width: 40px; height: 40px; animation: st-spin 1s linear infinite; margin: 0 auto 15px;"></div><p>Đang tìm sản phẩm...</p></div>';
        } else {
            currentPage++;
        }

        isLoading = true;

        const cat = document.querySelector('input[name="cat-filter"]:checked')?.value;
        const params = new URLSearchParams();
        if (cat && cat !== 'all') params.append('category', cat);
        params.append('min_price', document.getElementById('rangeMin')?.value || 0);
        params.append('max_price', document.getElementById('rangeMax')?.value || 999999999);
        params.append('q', searchInput?.value.trim() || '');
        params.append('sort', document.getElementById('sortProducts')?.value || '');
        
        // Collect spec filters
        document.querySelectorAll('input[type="radio"][name^="spec-filter-"]:checked').forEach(radio => {
            const val = radio.value;
            if (val) {
                const specName = radio.name.replace('spec-filter-', '');
                params.append(`spec_${specName}`, val);
            }
        });

        params.append('page', currentPage);
        params.append('limit', currentLimit);

        try {
            const res = await apiFetch(`/api/product?${params.toString()}`);
            const data = await res.json();
            if (data.success) {
                if (!isLoadMore) productGrid.innerHTML = '';
                totalCount = data.pagination?.total || 0;

                if (data.products.length === 0 && !isLoadMore) {
                    productGrid.style.display = 'none';
                    if (noResultMsg) noResultMsg.style.display = 'block';
                    isLastPage = true;
                } else {
                    productGrid.style.display = 'grid';
                    if (noResultMsg) noResultMsg.style.display = 'none';
                    data.products.forEach(p => productGrid.appendChild(createProductCard(p)));
                    if (data.products.length < currentLimit) isLastPage = true;
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
                        <a href="/Product/delete/${p.id}" class="btn btn-danger btn-sm btn-delete-confirm"><i class="bi bi-trash"></i></a>
                    ` : ''}
                    ${isLoggedIn && !isAdmin ? `
                        <a href="/api/cart/add/${p.id}" class="btn btn-success btn-sm ajax-add-cart-dynamic"><i class="bi bi-cart-plus"></i> Thêm giỏ</a>
                    ` : ''}
                </div>
            </div>`;

        div.querySelector('.btn-delete-confirm')?.addEventListener('click', async e => {
            e.preventDefault();
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                const res = await apiFetch(`/api/product/delete/${p.id}`, { method: 'DELETE' });
                const result = await res.json();
                if (result.success) {
                    div.remove();
                    showToast('Đã xóa sản phẩm', '✅');
                } else alert(result.message);
            }
        });

        div.querySelector('.ajax-add-cart-dynamic')?.addEventListener('click', e => {
            e.preventDefault();
            handleAddToCart(e.currentTarget.getAttribute('href'), e.currentTarget);
        });

        return div;
    }

    function renderLoadMoreWidget() {
        if (!loadMoreWrap) return;
        const currentCount = productGrid.querySelectorAll('.st-card').length;
        const total = totalCount > 0 ? totalCount : (window.ST_LIST_CONFIG?.total || 0);
        const pct = total > 0 ? Math.min(100, Math.round((currentCount / total) * 100)) : 0;

        if (isLastPage || currentCount >= total) {
            let collapseBtnHtml = '';
            if (currentCount > currentLimit) {
                collapseBtnHtml = `
                    <button class="st-lm-btn st-lm-collapse-btn" id="collapseBtn">
                        <span class="st-lm-btn-icon"><i class="bi bi-chevron-up"></i></span>
                        <span>Thu gọn</span>
                    </button>`;
            }
            loadMoreWrap.innerHTML = `
                <div class="st-load-more-wrap">
                    <div class="st-lm-progress"><div class="st-lm-progress-fill" style="width:100%"></div></div>
                    <div class="st-lm-counter">Đang hiển thị <strong>${currentCount}</strong> / ${total} sản phẩm</div>
                    <div class="st-lm-all-done"><i class="bi bi-check-circle"></i> Bạn đã xem hết tất cả sản phẩm</div>
                    ${collapseBtnHtml}
                </div>`;
            document.getElementById('collapseBtn')?.addEventListener('click', () => {
                fetchFilteredProducts(false); // Reset to first page
                window.scrollTo({ top: productGrid.offsetTop - 120, behavior: 'smooth' });
            });
        } else {
            loadMoreWrap.innerHTML = `
                <div class="st-load-more-wrap">
                    <div class="st-lm-progress"><div class="st-lm-progress-fill" style="width:${pct}%"></div></div>
                    <div class="st-lm-counter">Đang hiển thị <strong>${currentCount}</strong> / ${total} sản phẩm</div>
                    <button class="st-lm-btn" id="loadMoreBtn">
                        <span class="st-lm-btn-icon"><i class="bi bi-chevron-down"></i></span>
                        <span>Xem thêm sản phẩm</span>
                    </button>
                </div>`;
            document.getElementById('loadMoreBtn')?.addEventListener('click', () => fetchFilteredProducts(true));
        }
    }

    const minSlider = document.getElementById('rangeMin');
    const maxSlider = document.getElementById('rangeMax');
    const labelMin = document.getElementById('labelMin');
    const labelMax = document.getElementById('labelMax');
    const fill = document.querySelector('.st-range-fill');

    function updateSlider() {
        if (!minSlider || !maxSlider) return;
        let min = parseInt(minSlider.value), max = parseInt(maxSlider.value);
        if (min > max) { min = max; minSlider.value = min; }
        const total = parseInt(minSlider.max);
        if (fill) {
            fill.style.left = (min / total * 100) + '%';
            fill.style.width = ((max - min) / total * 100) + '%';
        }
        if (labelMin) labelMin.textContent = parseInt(min).toLocaleString('vi-VN') + '₫';
        if (labelMax) labelMax.textContent = parseInt(max).toLocaleString('vi-VN') + '₫';
    }

    minSlider?.addEventListener('input', updateSlider);
    maxSlider?.addEventListener('input', updateSlider);
    if (minSlider) updateSlider();

    document.querySelectorAll('input[name="cat-filter"]').forEach(r => r.addEventListener('change', () => fetchFilteredProducts()));
    document.getElementById('btnPriceFilter')?.addEventListener('click', () => fetchFilteredProducts());
    document.querySelectorAll('input[name^="spec-filter-"]').forEach(radio => {
        radio.addEventListener('change', () => fetchFilteredProducts());
    });
    document.getElementById('sortProducts')?.addEventListener('change', () => fetchFilteredProducts());

    /* ════════════════════════════════════════════════════════════
       Toast & Cart helpers
    ════════════════════════════════════════════════════════════ */
    window.showToast = function(msg, icon = '🛒') {
        let toast = document.getElementById('stToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'stToast'; toast.className = 'st-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span>${icon}</span><span>${msg}</span>`;
        toast.classList.add('show');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => toast.classList.remove('show'), 2800);
    };

    window.handleAddToCart = async function(url, btn) {
        if (!url || !btn) return;
        btn.style.pointerEvents = 'none'; btn.style.opacity = '0.7';

        try {
            const res = await apiFetch(url, { method: 'POST' });
            const data = await res.json();
            if (data.success) {
                const count = parseInt(data.cart_count || 0, 10);
                
                // Update main cart badge
                const cartBtn = document.getElementById('cartNavBtn');
                if (cartBtn) { cartBtn.classList.remove('bump'); void cartBtn.offsetWidth; cartBtn.classList.add('bump'); }
                const badge = document.getElementById('cartBadge');
                if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'inline-flex' : 'none'; }
                
                // Update dropdown badge
                const dropdownBadge = document.getElementById('cartDropdownBadge');
                if (dropdownBadge) {
                    dropdownBadge.textContent = count;
                    dropdownBadge.style.display = count > 0 ? 'inline-flex' : 'none';
                } else if (count > 0) {
                    // If it doesn't exist yet but count > 0, we might need to find its parent container
                    const cartLink = document.querySelector('a[href="/Product/cart"] .bi-cart3')?.parentElement;
                    if (cartLink && !cartLink.querySelector('#cartDropdownBadge')) {
                        const newBadge = document.createElement('span');
                        newBadge.id = 'cartDropdownBadge';
                        newBadge.className = 'st-dd-badge';
                        newBadge.textContent = count;
                        cartLink.appendChild(newBadge);
                    }
                }

                showToast(data.message || 'Đã thêm vào giỏ hàng!', '✅');
            } else {
                showToast(data.message || 'Cần đăng nhập', '⚠️');
            }
        } catch (err) {
            showToast('Lỗi kết nối', '⚠️');
        } finally {
            btn.style.pointerEvents = ''; btn.style.opacity = '';
        }
    };

    document.querySelectorAll('a[href*="addToCart"]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const pid = btn.getAttribute('href').split('/').pop();
            handleAddToCart(`/api/cart/add/${pid}`, btn);
        });
    });

    /* ════════════════════════════════════════════════════════════
       UI Interactions
    ════════════════════════════════════════════════════════════ */
    document.querySelector('.st-toggle')?.addEventListener('click', () => {
        document.querySelector('.st-nav-links')?.classList.toggle('open');
    });

    const style = document.createElement('style');
    style.innerHTML = `@keyframes st-spin { to { transform: rotate(360deg); } } .st-toast { position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:#333; color:#fff; padding:10px 20px; border-radius:30px; display:none; align-items:center; gap:10px; z-index:10001; } .st-toast.show { display:flex; }`;
    document.head.appendChild(style);

});

function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text'; icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password'; icon.className = 'bi bi-eye';
    }
}
