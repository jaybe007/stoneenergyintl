/**
 * STONE ENERGY INT'L LTD - Admin CMS Vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Mobile Sidebar Toggle
  const sidebarToggle = document.getElementById('adminSidebarToggle');
  const sidebar = document.getElementById('adminSidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  // 2. Tab Navigation
  const tabLinks = document.querySelectorAll('.tab-link[data-tab]');
  tabLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const tabTarget = this.getAttribute('data-tab');
      
      tabLinks.forEach(l => l.classList.remove('active'));
      this.classList.add('active');

      document.querySelectorAll('.tab-content').forEach(pane => {
        if (pane.id === tabTarget) {
          pane.style.display = 'block';
        } else {
          pane.style.display = 'none';
        }
      });
    });
  });

  // 3. Confirm Delete Dialog
  document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      const message = this.getAttribute('data-confirm-delete') || 'Are you sure you want to permanently delete this item?';
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // 4. Image Input Live Preview
  const imageInputs = document.querySelectorAll('input[type="file"][data-preview-target]');
  imageInputs.forEach(input => {
    input.addEventListener('change', function () {
      const targetId = this.getAttribute('data-preview-target');
      const previewImg = document.getElementById(targetId);
      if (previewImg && this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewImg.src = e.target.result;
          previewImg.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  });

  // 5. Copy to Clipboard
  window.copyToClipboard = function (text, successMsg = 'Copied to clipboard!') {
    navigator.clipboard.writeText(text).then(() => {
      window.showAdminToast(successMsg, 'success');
    }).catch(err => {
      console.error('Failed to copy: ', err);
    });
  };

  // 6. Admin Toast Utility
  window.showAdminToast = function (message, type = 'info') {
    let container = document.getElementById('adminToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'adminToastContainer';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  };

  // 7. Dynamic Table Live Filter
  const tableSearchInput = document.getElementById('tableSearchInput');
  if (tableSearchInput) {
    tableSearchInput.addEventListener('input', function () {
      const term = this.value.toLowerCase();
      const rows = document.querySelectorAll('.admin-table tbody tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });
    });
  }
});
