(() => {
  "use strict";
  const data = window.CATEGORY_SPEND || [];
  const container = document.getElementById("category-bars");
  if (!container || !data.length) return;

  const max = Math.max(...data.map((d) => d.monthly_cents));
  const fmt = (cents) => "$" + (cents / 100).toFixed(2);

  const categoryLabels = {
    streaming: "Streaming", music: "Music", software: "Software & SaaS",
    cloud_storage: "Cloud storage", fitness: "Fitness", reading: "News & reading",
    gaming: "Gaming", food_delivery: "Food delivery", other: "Other",
  };

  data.forEach((row) => {
    const pct = max > 0 ? (row.monthly_cents / max) * 100 : 0;
    const bar = document.createElement("div");
    bar.className = "category-bar-row";
    bar.innerHTML = `
      <div class="category-bar-label">${categoryLabels[row.category] || row.category}</div>
      <div class="category-bar-track"><div class="category-bar-fill" style="width:${pct}%"></div></div>
      <div class="category-bar-value">${fmt(row.monthly_cents)}/mo</div>
    `;
    container.appendChild(bar);
  });
})();
