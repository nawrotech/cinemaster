document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="modal-"]').forEach(modal => {
      modal.addEventListener('hidden.bs.modal', function () {
        location.reload();
      });
    });
  });