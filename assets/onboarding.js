(() => {
  "use strict";

  document.querySelectorAll(".onb-item").forEach((item) => {
    const check = item.querySelector(".onb-check");
    const fields = item.querySelector(".onb-fields");
    check.addEventListener("change", () => { fields.hidden = !check.checked; });

    const trial = item.querySelector(".onb-trial");
    const trialEnd = item.querySelector(".onb-trial-end");
    trial.addEventListener("change", () => { trialEnd.hidden = !trial.checked; });
  });

  document.getElementById("onboarding-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const status = document.getElementById("onboarding-status");
    const items = [];

    document.querySelectorAll(".onb-item").forEach((item) => {
      const check = item.querySelector(".onb-check");
      if (!check.checked) return;
      const tierSelect = item.querySelector(".onb-tier");
      const tierOption = tierSelect.options[tierSelect.selectedIndex];
      const trial = item.querySelector(".onb-trial");
      items.push({
        catalog_service_id: Number(item.dataset.serviceId),
        custom_name: item.dataset.name,
        category: item.dataset.category,
        tier_name: tierOption ? tierOption.dataset.name : null,
        price_cents: tierOption ? Number(tierOption.value) : 0,
        cadence: item.querySelector(".onb-cadence").value,
        started_on: item.querySelector(".onb-started").value,
        is_free_trial: trial.checked,
        trial_ends_on: trial.checked ? item.querySelector(".onb-trial-end").value : null,
      });
    });

    if (!items.length) {
      window.location.href = "dashboard.php";
      return;
    }

    status.hidden = false;
    status.textContent = "Saving...";
    try {
      const res = await fetch("api/onboarding-submit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ items }),
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.ok) {
        if (data.skipped && data.skipped.length) {
          status.textContent = `Saved ${data.created}, but skipped: ${data.skipped.join(', ')}. You can add them manually from the dashboard.`;
          status.hidden = false;
          return; // stay so the user sees what was skipped
        }
        window.location.href = "dashboard.php";
      } else {
        status.textContent = "Something went wrong, try again.";
      }
    } catch {
      status.textContent = "Something went wrong, try again.";
    }
  });
})();
