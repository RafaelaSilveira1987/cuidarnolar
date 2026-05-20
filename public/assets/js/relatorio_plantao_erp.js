document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.shift-card-header').forEach(function (header) {
    header.addEventListener('click', function () {
      header.closest('.shift-card').classList.toggle('expanded');
    });
  });
});
