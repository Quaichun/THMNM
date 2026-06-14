<?php include 'app/views/shares/header.php'; ?>

<!-- 
    TRANG DEMO JQUERY FRONT-END 
    Sử dụng hoàn toàn jQuery để tương tác với các API của hệ thống.
-->

<div class="st-page">
<div class="st-container">

    <div class="st-page-head fade-up">
        <div>
            <h1><i class="bi bi-code-square"></i> jQuery API Frontend</h1>
            <p>Trang demo tương tác Web API sử dụng thư viện <strong>jQuery</strong></p>
        </div>
        <div style="display:flex; gap:10px;">
            <button id="btnLoadProducts" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i> Tải lại danh sách
            </button>
        </div>
    </div>

    <!-- Thanh lọc (jQuery driven) -->
    <div class="st-filter-bar fade-up" style="margin-bottom:24px;">
        <div style="display:flex; gap:15px; flex-wrap:wrap; align-items:center;">
            <div style="flex:1">
                <input type="text" id="jquerySearch" class="form-control" placeholder="Tìm kiếm sản phẩm (jQuery)...">
            </div>
            <select id="jqueryCat" class="form-select" style="width:200px">
                <option value="">Tất cả danh mục</option>
            </select>
            <select id="jquerySort" class="form-select" style="width:180px">
                <option value="id_desc">Mới nhất</option>
                <option value="price_asc">Giá tăng dần</option>
                <option value="price_desc">Giá giảm dần</option>
            </select>
        </div>
    </div>

    <!-- Kết quả -->
    <div id="jqueryResults" class="st-product-grid fade-up">
        <!-- Dữ liệu sẽ được nạp vào đây bằng jQuery -->
        <div class="st-loading-spinner" style="grid-column: 1/-1; text-align:center; padding:50px">
             <div class="spinner-border text-primary"></div>
             <p class="mt-2" style="color:var(--text-muted)">Đang tải dữ liệu bằng jQuery...</p>
        </div>
    </div>

    <!-- Phân trang -->
    <div id="jqueryPagination" class="st-pagination fade-up" style="margin-top:30px; justify-content:center; display:flex; gap:8px"></div>

</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentLimit = 8;

    // 1. Tải danh mục bằng jQuery
    function loadCategories() {
        $.ajax({
            url: '/api/category/index',
            method: 'GET',
            success: function(data) {
                if (data.success) {
                    const $select = $('#jqueryCat');
                    data.categories.forEach(cat => {
                        $select.append(`<option value="${cat.id}">${cat.name}</option>`);
                    });
                }
            }
        });
    }

    // 2. Tải sản phẩm bằng jQuery
    function loadProducts(page = 1) {
        const query = $('#jquerySearch').val();
        const catId = $('#jqueryCat').val();
        const sort = $('#jquerySort').val();

        $('#jqueryResults').html('<div style="grid-column:1/-1;text-align:center;padding:50px"><div class="spinner-border text-primary"></div></div>');

        $.ajax({
            url: '/api/product/index',
            method: 'GET',
            data: {
                page: page,
                limit: currentLimit,
                search: query,
                category_id: catId,
                sort: sort
            },
            success: function(data) {
                $('#jqueryResults').empty();
                
                if (!data.products || data.products.length === 0) {
                    $('#jqueryResults').html('<div style="grid-column:1/-1;text-align:center;padding:100px"><h3>Không tìm thấy sản phẩm</h3></div>');
                    return;
                }

                data.products.forEach(p => {
                    const html = `
                        <div class="st-product-card" style="opacity:0; transform:translateY(20px)">
                            <div class="st-card-img">
                                <img src="/${p.image || 'app/views/shares/placeholder.png'}" alt="${p.name}">
                                <div class="st-card-actions">
                                    <button class="btn-api-add" data-id="${p.id}"><i class="bi bi-cart-plus"></i></button>
                                </div>
                            </div>
                            <div class="st-card-info">
                                <a href="/Product/show/${p.id}" class="st-card-title">${p.name}</a>
                                <div class="st-card-price">${parseInt(p.price).toLocaleString('vi-VN')}₫</div>
                            </div>
                        </div>
                    `;
                    const $card = $(html).appendTo('#jqueryResults');
                    // Hiệu ứng Fade-in cho đẹp
                    setTimeout(() => $card.css({ opacity: 1, transform: 'translateY(0)', transition: 'all 0.4s ease' }), 50);
                });

                renderPagination(data.total, page);
            },
            error: function() {
                $('#jqueryResults').html('<div class="alert alert-danger">Lỗi khi tải dữ liệu.</div>');
            }
        });
    }

    // 3. Phân trang jQuery
    function renderPagination(total, page) {
        const totalPages = Math.ceil(total / currentLimit);
        const $pag = $('#jqueryPagination').empty();

        for (let i = 1; i <= totalPages; i++) {
            const $btn = $(`<button class="btn ${i === page ? 'btn-primary' : 'btn-outline-primary'}">${i}</button>`);
            $btn.click(() => {
                currentPage = i;
                loadProducts(i);
            });
            $pag.append($btn);
        }
    }

    // 4. Thêm vào giỏ hàng bằng jQuery
    $(document).on('click', '.btn-api-add', function() {
        const id = $(this).data('id');
        const token = localStorage.getItem('jwt_token');

        if (!token) {
            alert('Vui lòng đăng nhập trước!');
            window.location.href = '/Account/login';
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '/api/cart/store',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'Authorization': 'Bearer ' + token },
            data: JSON.stringify({ product_id: id, quantity: 1 }),
            success: function(res) {
                if (res.success) {
                    showToast('Đã thêm sản phẩm (jQuery)!', '🛒');
                } else {
                    alert(res.message || 'Lỗi');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-cart-plus"></i>');
            }
        });
    });

    // Event listeners
    $('#jquerySearch').on('input', function() {
        currentPage = 1;
        loadProducts(1);
    });
    $('#jqueryCat, #jquerySort').change(() => {
        currentPage = 1;
        loadProducts(1);
    });
    $('#btnLoadProducts').click(() => loadProducts(currentPage));

    // Khởi tạo
    loadCategories();
    loadProducts(1);
});
</script>

<style>
.st-product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 24px;
}
.btn-api-add {
    background: var(--primary);
    color: white;
    border: none;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<?php include 'app/views/shares/footer.php'; ?>
