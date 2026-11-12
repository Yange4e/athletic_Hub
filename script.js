/* ---------------------------
  script.js — добавленный блок Cart + API интеграция
  Вставьте этот код в конец вашего script.js (или вместо существующих реализаций корзины)
---------------------------- */
(function () {
  const CART_KEY = 'athletic_hub_cart';

  // ======= Storage helpers =======
  function getCart() {
    try {
      const raw = localStorage.getItem(CART_KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      console.error('Error read cart:', e);
      return [];
    }
  }
  function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  }
  function clearCart() {
    localStorage.removeItem(CART_KEY);
  }

  // ======= UI selectors (adjust if your markup differs) =======
  const cartListEl = document.getElementById('cartItemsList');
  const subtotalEl = document.getElementById('subtotalValue');
  const totalItemsEl = document.getElementById('totalItemsCount');
  const discountEl = document.getElementById('discountValue');
  const totalPayableEl = document.getElementById('totalPayable');
  const checkoutBtn = document.getElementById('checkoutBtn');

  // ======= Add to cart (call from product page / buttons) =======
  function addToCart(productId, variantId, qty = 1) {
    const cart = getCart();
    const key = (i) => `${i.product_id}:${i.variant_id}`;
    const idx = cart.findIndex(i => i.product_id == productId && i.variant_id == variantId);
    if (idx !== -1) {
      cart[idx].quantity = Math.max(1, cart[idx].quantity + qty);
    } else {
      cart.push({ product_id: Number(productId), variant_id: Number(variantId), quantity: Number(qty) });
    }
    saveCart(cart);
    renderCart(); // update UI immediately
  }

  // ======= Fetch variant details in bulk =======
  async function fetchVariantsBulk(variantIds = []) {
    if (!variantIds || variantIds.length === 0) return [];
    const form = new FormData();
    variantIds.forEach(id => form.append('variant_ids[]', id));
    const resp = await fetch('/api/variants_bulk.php', { method: 'POST', body: form });
    if (!resp.ok) {
      console.error('variants_bulk fetch failed', resp.status);
      return [];
    }
    return resp.json();
  }

  // ======= Format currency =======
  function formatCurrency(v) {
    const n = Math.round(Number(v) || 0);
    return `${n.toLocaleString('ru-RU')} ₸`;
  }

  // ======= Render cart =======
  async function renderCart() {
    if (!cartListEl) return;
    const cart = getCart();
    cartListEl.innerHTML = '';

    if (cart.length === 0) {
      // show empty message handled in page; still update summary
      updateSummary([]);
      return;
    }

    const variantIds = [...new Set(cart.map(i => i.variant_id))];
    const variants = await fetchVariantsBulk(variantIds);
    const map = {};
    variants.forEach(v => map[v.id] = v);

    // build list
    cart.forEach(item => {
      const v = map[item.variant_id] || {};
      const title = v.title || 'Товар';
      const variantName = v.variant_name || '';
      const price = Number(v.price || 0);
      const qty = Number(item.quantity || 1);
      const rowTotal = price * qty;

      // create DOM node
      const node = document.createElement('div');
      node.className = 'item-card';
      node.id = `item-${escapeId(item.product_id)}-${escapeId(item.variant_id)}`;
      node.innerHTML = `
        <img src="${v.image || 'https://placehold.co/100x100?text=No+Img'}" class="item-card-image" alt="${title}">
        <div class="item-details">
          <h3>${title}</h3>
          <div class="item-price-unit">${variantName} — ${formatCurrency(price)} за шт.</div>
        </div>
        <div class="item-controls">
          <div class="quantity-control">
            <button class="qty-btn dec">-</button>
            <span class="item-qty">${qty}</span>
            <button class="qty-btn inc">+</button>
          </div>
          <div class="item-subtotal">${formatCurrency(rowTotal)}</div>
          <button class="remove-btn" title="Удалить"><i class="fa-solid fa-times-circle"></i></button>
        </div>
      `;

      // attach handlers
      const btnDec = node.querySelector('.qty-btn.dec');
      const btnInc = node.querySelector('.qty-btn.inc');
      const remBtn = node.querySelector('.remove-btn');

      btnDec.addEventListener('click', () => {
        changeQty(item.product_id, item.variant_id, -1);
      });
      btnInc.addEventListener('click', () => {
        changeQty(item.product_id, item.variant_id, 1);
      });
      remBtn.addEventListener('click', () => {
        removeFromCart(item.product_id, item.variant_id);
      });

      cartListEl.appendChild(node);
    });

    updateSummary(cart, map);
  }

  function escapeId(id) {
    return String(id).replace(/\./g, '-').replace(/[^a-zA-Z0-9\-_]/g, '');
  }

  // ======= Modify quantity / remove =======
  function changeQty(productId, variantId, delta) {
    const cart = getCart();
    const idx = cart.findIndex(i => i.product_id == productId && i.variant_id == variantId);
    if (idx === -1) return;
    cart[idx].quantity = Math.max(1, cart[idx].quantity + delta);
    saveCart(cart);
    renderCart();
  }
  function removeFromCart(productId, variantId) {
    let cart = getCart();
    cart = cart.filter(i => !(i.product_id == productId && i.variant_id == variantId));
    saveCart(cart);
    renderCart();
  }

  // ======= Calculate totals and update summary UI =======
  // map: variant_id->variant object (contains price)
  function updateSummary(cart, map = {}) {
    // default: if map missing, do best-effort parse from localStorage (prices might be stale)
    let subtotal = 0;
    let totalItems = 0;
    cart.forEach(it => {
      const price = Number((map[it.variant_id] && map[it.variant_id].price) || 0);
      subtotal += price * (it.quantity || 1);
      totalItems += (it.quantity || 1);
    });

    const discount = subtotal > 150000 ? subtotal * 0.05 : 0;
    const totalPayable = subtotal - discount;

    if (totalItemsEl) totalItemsEl.textContent = totalItems;
    if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
    if (discountEl) discountEl.textContent = formatCurrency(discount);
    if (totalPayableEl) totalPayableEl.textContent = formatCurrency(totalPayable);

    if (checkoutBtn) checkoutBtn.disabled = (totalItems === 0);
  }

  // ======= Checkout (submit to API) =======
  async function openCheckout() {
    // show modal if exists in page
    const modal = document.getElementById('checkoutModal');
    if (modal) modal.classList.add('open');
  }

  async function submitCheckout(event) {
    event.preventDefault();
    const cart = getCart();
    if (!cart.length) { alert('Корзина пуста'); return; }

    // collect form fields
    const phone = document.getElementById('phone')?.value || '';
    const city = document.getElementById('city')?.value || '';
    const address = document.getElementById('address')?.value || '';
    const postal_code = document.getElementById('postal_code')?.value || '';

    if (!phone || !city || !address) {
      alert('Заполните телефон, город и адрес');
      return;
    }

    const form = new FormData();
    form.append('phone', phone);
    form.append('city', city);
    form.append('address', address);
    form.append('postal_code', postal_code);
    form.append('items_json', JSON.stringify(cart));

    // disable submit UI while pending
    const submitBtn = document.querySelector('#checkoutForm .btn-submit') || null;
    if (submitBtn) submitBtn.disabled = true;

    try {
      const resp = await fetch('/api/create_order.php', { method: 'POST', body: form });
      const data = await resp.json();
      if (!data.success) {
        alert(`Ошибка: ${data.message || 'Ошибка при создании заказа'}`);
        if (submitBtn) submitBtn.disabled = false;
        return;
      }
      // success - clear cart (or remove only ordered items)
      clearCart();
      renderCart();
      // show status modal if exists
      const statusModal = document.getElementById('statusModal');
      if (statusModal) {
        // fill order info
        document.getElementById('orderId').textContent = '#' + data.order_id;
        // optionally fetch status via api/order_status.php
        statusModal.classList.add('open');
      } else {
        alert('Заказ создан. Номер: ' + data.order_id);
      }
      if (submitBtn) submitBtn.disabled = false;
      // close checkout modal
      const checkoutModal = document.getElementById('checkoutModal');
      if (checkoutModal) checkoutModal.classList.remove('open');
    } catch (e) {
      console.error('checkout error', e);
      alert('Ошибка при создании заказа');
      if (submitBtn) submitBtn.disabled = false;
    }
  }

  // ======= Wire UI events (buttons on pages) =======
  function wireAddToCartButtons() {
    // buttons must have data-product-id and either data-variant-id or find a select in same card with .variant-select
    document.querySelectorAll('.add-to-cart, .btn-add-cart').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const card = btn.closest('.product-card') || document;
        const pid = btn.dataset.productId || card.dataset.productId || btn.getAttribute('data-product-id');
        let vid = btn.dataset.variantId || card.querySelector('.variant-select')?.value || btn.getAttribute('data-variant-id');
        if (!pid) { console.warn('product id missing on add-to-cart button'); return; }
        if (!vid) {
          // try to fetch first variant via API (best-effort)
          alert('Выберите вариант товара (цвет/размер) перед добавлением.');
          return;
        }
        addToCart(Number(pid), Number(vid), 1);
      });
    });
  }

  // ======= Init: attach events if elements exist on page =======
  document.addEventListener('DOMContentLoaded', () => {
    // attach checkout form
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) checkoutForm.addEventListener('submit', submitCheckout);

    const checkoutOpenBtn = document.getElementById('checkoutBtn');
    if (checkoutOpenBtn) checkoutOpenBtn.addEventListener('click', openCheckout);

    // select all / remove selected are handled in cart.php already; this script handles basic add/remove/qty/checkout
    wireAddToCartButtons();
    renderCart();
  });

  // expose addToCart globally so inline handlers can call it
  window.athleticAddToCart = addToCart;
  window.athleticRenderCart = renderCart;
})();