<script>
document.addEventListener('DOMContentLoaded', () => {
  const $ = (id) => document.getElementById(id);
  const filterInput = $('filter'), tableBody = $('tableBody'), searchCount = $('searchCount'),
        emptyState = $('emptyState'), totalCountLabel = $('totalCount'), prevBtn = $('prevBtn'),
        nextBtn = $('nextBtn'), currentPageLabel = $('currentPage'), totalPagesLabel = $('totalPages');
  const perPage = {{ $perPage ?? 15 }};
  const allRows = Array.from(tableBody.querySelectorAll('[data-row]'));
  let filtered = [...allRows], page = 1, timer;

  function render() {
    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    page = Math.min(Math.max(page, 1), totalPages);
    const start = (page - 1) * perPage;
    allRows.forEach(r => r.style.display = 'none');
    filtered.slice(start, start + perPage).forEach(r => r.style.display = '');
    currentPageLabel.textContent = page;
    totalPagesLabel.textContent = totalPages;
    totalCountLabel.textContent = filtered.length;
    prevBtn.disabled = page === 1;
    nextBtn.disabled = page === totalPages;
    const wrap = tableBody.closest('.overflow-x-auto');
    emptyState.classList.toggle('hidden', filtered.length !== 0);
    wrap.classList.toggle('hidden', filtered.length === 0);
  }

  filterInput.addEventListener('input', (e) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      const q = e.target.value.toLowerCase().trim();
      filtered = allRows.filter(r => r.textContent.toLowerCase().includes(q));
      page = 1;
      searchCount.textContent = q ? `Ditemukan ${filtered.length} data` : '';
      searchCount.classList.toggle('hidden', !q);
      render();
    }, 300);
  });
  prevBtn.addEventListener('click', () => { page--; render(); });
  nextBtn.addEventListener('click', () => { page++; render(); });
  render();
});
</script>
