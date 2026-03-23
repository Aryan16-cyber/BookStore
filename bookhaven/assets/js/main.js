// BookHaven — Main JS

// ── Payment method selector ──
document.querySelectorAll('.pay-method').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.pay-method').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('payment_method').value = btn.dataset.method;
  });
});

// ── Quantity controls on book detail ──
const qtyInput = document.getElementById('quantity');
if (qtyInput) {
  document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      let v = parseInt(qtyInput.value) || 1;
      if (btn.dataset.action === 'inc') v++;
      else if (btn.dataset.action === 'dec' && v > 1) v--;
      qtyInput.value = v;
    });
  });
}

// ── Auto-dismiss flash messages ──
const flash = document.querySelector('.flash');
if (flash) setTimeout(() => flash.style.display = 'none', 4000);

// ── Search form on Enter ──
const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') e.target.closest('form').submit();
  });
}
