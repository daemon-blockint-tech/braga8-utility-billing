let selectedDeleteId = null;

export function initPopups() {
  const popups = document.querySelectorAll(".popup");

  document.querySelectorAll("[data-popup]").forEach(trigger => {
      trigger.addEventListener("click", () => {
          const target = trigger.getAttribute("data-popup");
          const popup = document.getElementById(target);

          if (trigger.dataset.id) {
              selectedDeleteId = trigger.dataset.id;
              
              // Logika Teks Dinamis Unit
              const unitName = trigger.getAttribute('data-unit');
              const displayUnit = document.getElementById('display-unit-number');
              if (displayUnit && unitName) displayUnit.innerText = unitName;

              // Logika Teks Dinamis Tenant
              const tenantName = trigger.getAttribute('data-name');
              const displayTenant = document.getElementById('display-tenant-name');
              if (displayTenant && tenantName) displayTenant.innerText = tenantName;

              // Logika Teks Dinamis Invoice
              const invNo = trigger.getAttribute('data-invoice');
              const displayInv = document.getElementById('display-invoice-number');
              if (displayInv && invNo) displayInv.innerText = invNo;
          }

          if (popup) popup.classList.add("active");
      });
  });

  popups.forEach(popup => {
    const closeBtn = popup.querySelector(".popup-close");
    const overlay = popup.querySelector(".popup-overlay");

    closeBtn?.addEventListener("click", () => {
      popup.classList.remove("active");
    });

    overlay?.addEventListener("click", () => {
      popup.classList.remove("active");
    });
  });

  // Menutup popup menggunakan tombol ber-atribut data-close
  document.querySelectorAll("[data-close]").forEach(btn => {
    btn.addEventListener("click", () => {
      const target = btn.getAttribute("data-close");
      const popup = document.getElementById(target);

      if (popup) popup.classList.remove("active");
    });
  });

  // Tombol konfirmasi hapus massal untuk data tabel (Unit, Tenant, Invoice)
  const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener("click", () => {
      if (!selectedDeleteId) {
        console.warn("No ID selected for delete");
        return;
      }

      const form = document.getElementById("delete-form-" + selectedDeleteId);
      if (form) {
        form.submit();
      } else {
        console.error("Form not found for ID:", selectedDeleteId);
      }
    });
  }
}

// Fitur Toggle Intip Password (Mata)
export function initPasswordToggle() {
    const toggleBtns = document.querySelectorAll(".toggle-password");

    toggleBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            // Mengambil input password di dalam wrapper pembungkus yang sama
            const input = this.parentElement.querySelector("input");
            const icon = this.querySelector("i");

            if (input && input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else if (input) {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        });
    });
}