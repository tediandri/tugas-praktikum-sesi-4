// Validasi sisi klien (Bootstrap) — validasi server tetap dijalankan di PHP.
document.querySelectorAll('.needs-validation').forEach((form) => {
  form.addEventListener('submit', (e) => {
    if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    form.classList.add('was-validated');
  });
});

// Isi modal konfirmasi hapus.
const modal = document.getElementById('hapusModal');
if (modal) {
  modal.addEventListener('show.bs.modal', (ev) => {
    const b = ev.relatedTarget;
    modal.querySelector('[name=nim]').value = b.dataset.nim;
    modal.querySelector('#hapusNama').textContent = b.dataset.nama;
  });
}
