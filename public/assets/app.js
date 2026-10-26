document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', event => {
      if (!confirm(el.getAttribute('data-confirm') || 'Lanjutkan tindakan ini?')) event.preventDefault();
    });
  });

  const barcodeInput = document.querySelector('#barcode');
  if (barcodeInput) barcodeInput.focus();

  const search = document.querySelector('#productSearch');
  const results = document.querySelector('#productResults');
  const cartBody = document.querySelector('#cartBody');
  const cartForm = document.querySelector('#saleForm');
  if (!search || !results || !cartBody || !cartForm) return;

  let products = [];
  let cart = [];
  const fmt = n => new Intl.NumberFormat('id-ID', {style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(n)||0);
  const csrf = cartForm.querySelector('[name="csrf_token"]').value;
  const subtotalNode = document.querySelector('#subtotal');
  const discountInput = document.querySelector('#discount');
  const paidInput = document.querySelector('#paid');
  const totalNode = document.querySelector('#total');
  const changeNode = document.querySelector('#change');
  const hiddenItems = document.querySelector('#itemsJson');

  async function loadProducts(q='') {
    try {
      const response = await fetch(`${window.APP_BASE}/api/products.php?q=${encodeURIComponent(q)}`);
      const data = await response.json();
      products = data.products || [];
      results.innerHTML = '';
      products.forEach(p => {
        const row = document.createElement('button');
        row.type = 'button';
        row.className = 'product-result';
        const left = document.createElement('span');
        left.textContent = `${p.name} (${p.barcode})`;
        const right = document.createElement('strong');
        right.textContent = `${fmt(p.sell_price)} · Stok ${p.stock}`;
        row.append(left,right);
        row.addEventListener('click', () => addProduct(p));
        results.append(row);
      });
      if (!products.length) results.innerHTML = '<div class="empty">Produk tidak ditemukan.</div>';
    } catch { results.innerHTML = '<div class="empty">Gagal memuat produk.</div>'; }
  }
  function addProduct(p) {
    const item = cart.find(i => Number(i.id) === Number(p.id));
    if (item) {
      if (Number(item.qty) + 1 > Number(p.stock)) return alert('Jumlah melebihi stok.');
      item.qty = Number(item.qty) + 1;
    } else {
      if (Number(p.stock) <= 0) return alert('Stok produk habis.');
      cart.push({id:Number(p.id),barcode:p.barcode,name:p.name,unit:p.unit,price:Number(p.sell_price),stock:Number(p.stock),qty:1});
    }
    renderCart();
    search.value = '';
    results.innerHTML = '';
    search.focus();
  }
  function renderCart() {
    cartBody.innerHTML = '';
    cart.forEach((item, idx) => {
      const tr = document.createElement('tr');
      const tdName = document.createElement('td'); tdName.textContent = item.name;
      const tdPrice = document.createElement('td'); tdPrice.textContent = fmt(item.price);
      const tdQty = document.createElement('td');
      const qty = document.createElement('input'); qty.type='number'; qty.min='0.01'; qty.step='0.01'; qty.max=String(item.stock); qty.value=String(item.qty); qty.required=true;
      qty.addEventListener('change', () => {
        const v = Number(qty.value);
        if (!Number.isFinite(v) || v <= 0 || v > item.stock) { alert('Jumlah tidak valid atau melebihi stok.'); qty.value=item.qty; return; }
        item.qty=v; renderCart();
      });
      tdQty.append(qty);
      const tdTotal = document.createElement('td'); tdTotal.className='right'; tdTotal.textContent=fmt(item.qty*item.price);
      const tdAct = document.createElement('td');
      const del = document.createElement('button'); del.type='button'; del.className='btn btn-danger'; del.textContent='Hapus';
      del.addEventListener('click', () => { cart.splice(idx,1); renderCart(); });
      tdAct.append(del); tr.append(tdName,tdPrice,tdQty,tdTotal,tdAct); cartBody.append(tr);
    });
    const subtotal = cart.reduce((s,i)=>s+i.price*i.qty,0);
    const discount = Math.min(subtotal,Math.max(0,Number(discountInput.value)||0));
    const total = subtotal-discount;
    subtotalNode.textContent=fmt(subtotal); totalNode.textContent=fmt(total);
    changeNode.textContent=fmt(Math.max(0,(Number(paidInput.value)||0)-total));
    hiddenItems.value=JSON.stringify(cart.map(i=>({id:i.id,qty:i.qty})));
  }
  search.addEventListener('input', () => loadProducts(search.value.trim()));
  search.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
      event.preventDefault();
      const exact = products.find(p => p.barcode === search.value.trim());
      if (exact) addProduct(exact);
      else if (products.length === 1) addProduct(products[0]);
    }
  });
  discountInput.addEventListener('input',renderCart);
  paidInput.addEventListener('input',renderCart);
  cartForm.addEventListener('submit', event => {
    if (!cart.length) { event.preventDefault(); alert('Keranjang masih kosong.'); return; }
    const subtotal=cart.reduce((s,i)=>s+i.price*i.qty,0);
    const total=subtotal-Math.min(subtotal,Math.max(0,Number(discountInput.value)||0));
    if ((Number(paidInput.value)||0) < total) { event.preventDefault(); alert('Uang pembayaran belum cukup.'); }
    hiddenItems.value=JSON.stringify(cart.map(i=>({id:i.id,qty:i.qty})));
  });
  loadProducts();
});
