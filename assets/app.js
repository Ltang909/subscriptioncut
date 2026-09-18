(() => {
  "use strict";

  /* ==========================================================================
     COMPANY DATABASE
     Kept deliberately general on exact click-paths (those go stale fast and
     being wrong actively hurts trust). Grounded in well-documented, slow-
     changing structural facts: which channel actually has to process the
     cancellation, and what's publicly known about the friction there.
     Add/edit entries here — each just needs name, aliases, category,
     difficulty, method, situation, script, counter.
  ========================================================================== */
  const COMPANIES = [
    {
      name: "Amazon Prime", aliases: ["amazon", "prime"], category: "Shopping & streaming",
      difficulty: "medium", method: "Website (Account → Prime Membership)",
      situation: "Amazon settled FTC charges in 2025 over a cancellation flow regulators called deliberately confusing (the \"Iliad Flow\") — multiple screens and offers designed to make you give up partway through. It's improved under that settlement, but expect a few \"are you sure\" screens before it actually confirms.",
      script: "Go to Account → Prime Membership → \"End Membership\" or \"Manage Membership.\" Click through each retention screen without re-reading the offers — the actual cancel option is usually the plain text link, not the highlighted button.",
      counter: "There's no phone call to navigate here — just keep clicking the option that isn't the highlighted upsell button until you reach a final confirmation."
    },
    {
      name: "Adobe Creative Cloud", aliases: ["adobe", "photoshop", "creative cloud"], category: "Software",
      difficulty: "hard", method: "Website or live chat",
      situation: "If you're on an annual plan paid monthly, cancelling before the term ends can trigger an early termination fee — historically up to 50% of your remaining months. Adobe faced FTC action in 2024 over this fee being hidden at signup.",
      script: "Before cancelling, check Account → Plans for your exact plan type and renewal date. If you're mid-term on an annual-paid-monthly plan, ask explicitly: \"Will cancelling today trigger an early termination fee, and how much?\" Get the number in writing before you confirm.",
      counter: "If they quote a fee you weren't clearly told about at signup, say so directly and ask for it to be waived — this is the exact practice regulators have gone after them for."
    },
    {
      name: "SiriusXM", aliases: ["sirius", "sirius xm"], category: "Streaming & radio",
      difficulty: "hard", method: "Phone or chat",
      situation: "SiriusXM settled with the FTC and New York in 2024 specifically over making cancellation far harder than signing up. It's supposed to be easier now, but budget for a retention-heavy call regardless.",
      script: "Say: \"I'm calling to cancel my subscription, effective today. Please confirm cancellation in writing.\" Repeat it after every discount offer — they're required to let you cancel without agreeing to stay.",
      counter: "If they claim you can only \"pause\" or \"downgrade,\" say: \"I want full cancellation, not a pause — please process that now.\""
    },
    {
      name: "The New York Times", aliases: ["nyt", "new york times"], category: "News",
      difficulty: "medium", method: "Phone or chat",
      situation: "Many NYT subscriptions still route cancellation through a chat or phone flow rather than a plain settings toggle, especially bundled or promotional-rate plans.",
      script: "Use the chat option under Help/Contact Us if available — it's faster than phone. State plainly: \"Please cancel my subscription effective today and send written confirmation.\"",
      counter: "If offered a discounted rate to stay, it's fine to take it if it's genuinely what you want — otherwise just repeat the cancellation request."
    },
    {
      name: "Wall Street Journal", aliases: ["wsj", "dow jones"], category: "News",
      difficulty: "hard", method: "Phone (in many states)",
      situation: "WSJ, Barron's, and many Gannett-owned local papers still require a phone call to cancel in a lot of US states, often after a low intro rate that jumps sharply.",
      script: "If you're in California or another state with a strong auto-renewal law, open with that: \"I'm a California resident — by law you're required to let me cancel as easily as I signed up.\" Otherwise: \"I'm calling to cancel, effective today, no offers — please confirm in writing.\"",
      counter: "If they say cancellation must go through a different department, ask to be transferred rather than calling back — you're less likely to lose your place."
    },
    {
      name: "Xfinity / Comcast", aliases: ["comcast", "xfinity"], category: "Internet & cable",
      difficulty: "hard", method: "Phone or in-store",
      situation: "Consistently reported as having no full online self-cancel option — calls frequently get transferred between departments, and identity verification steps have been known to fail and stall the process.",
      script: "Call and say immediately: \"I want to disconnect service, not troubleshoot — please route me to retention/cancellation.\" If a verification step fails, ask for a supervisor rather than repeating the same broken step.",
      counter: "Ask for a disconnect confirmation number and the exact final bill date before hanging up — this is the single most-reported failure point with them."
    },
    {
      name: "OnStar", aliases: ["onstar", "gm onstar"], category: "Connected car services",
      difficulty: "medium", method: "Phone (except CA/VT residents)",
      situation: "Online self-cancellation is only fully available to California and Vermont residents. Everyone else needs to call or use the in-vehicle blue button.",
      script: "Call 1-888-4ONSTAR (1-888-466-7827), say \"cancel my subscription\" at the prompt, and once connected: \"I'd like to fully cancel, no discount offers, please.\" Ask for a confirmation number.",
      counter: "You may need to decline two or three retention offers before they process it — just repeat the request, you don't need to justify it."
    },
    {
      name: "Planet Fitness", aliases: ["planet fitness"], category: "Gym & fitness",
      difficulty: "hard", method: "In-person or certified mail (varies by location)",
      situation: "Gym contracts are franchise-run location by location, and many still require an in-person visit or a certified letter — email or a phone call usually isn't accepted as valid cancellation.",
      script: "Check your specific contract for the accepted method (it's usually printed on your membership agreement). If mail is required, send it certified with return receipt so you have proof it was received.",
      counter: "If a location tells you cancellation isn't possible without a manager present, ask for that in writing and note the date/time you were told — it matters if you end up disputing a charge."
    },
    {
      name: "LA Fitness", aliases: ["la fitness"], category: "Gym & fitness",
      difficulty: "hard", method: "Certified mail",
      situation: "One of the most consistently reported gym cancellation processes — a signed, mailed cancellation form is often the only accepted method, regardless of how you signed up.",
      script: "Send a written cancellation request by certified mail with return receipt to the address on your membership agreement, including your member ID and the effective date you want.",
      counter: "Keep your mailing receipt and the signed return card — if billing continues after that, this is your proof for a card dispute."
    },
    {
      name: "Equinox", aliases: ["equinox"], category: "Gym & fitness",
      difficulty: "hard", method: "Written notice (mail or in-person)",
      situation: "Membership agreements typically require written notice with a set notice period (often 30 days) before your next billing date — a phone call alone usually isn't sufficient.",
      script: "Submit written cancellation per your agreement's exact notice period, and confirm your final billing date in the same message.",
      counter: "If your notice period isn't clearly stated in your emailed agreement, ask the club directly to send you the exact clause before you commit to a cancellation date."
    },
    {
      name: "Audible", aliases: ["audible"], category: "Streaming & audio",
      difficulty: "medium", method: "Website",
      situation: "Amazon-owned, and known for a multi-screen retention flow (free credits, discounted months) before the actual cancel confirmation appears.",
      script: "Account Details → \"Cancel Membership,\" then keep clicking the plain \"no thanks, continue cancelling\" option through each offer screen.",
      counter: "None of the offers require a phone call — just persistence through the click-through maze."
    },
    {
      name: "Peloton", aliases: ["peloton"], category: "Fitness app",
      difficulty: "medium", method: "Website or app",
      situation: "App membership cancellation is generally self-serve, but expect a pause offer before you reach the actual cancel button.",
      script: "Go to Profile → Membership → \"Cancel Membership,\" and pick full cancellation rather than the offered pause if that's what you actually want.",
      counter: "If you bought your bike on a financing plan, note that cancelling the app membership doesn't cancel the equipment financing — those are separate."
    },
    {
      name: "HelloFresh", aliases: ["hellofresh"], category: "Meal kits",
      difficulty: "medium", method: "Website",
      situation: "The default flow nudges you toward \"skipping\" a week rather than cancelling outright, which can make it unclear whether you've actually stopped future charges.",
      script: "Go to Account Settings → Plan Settings, and look specifically for \"Cancel Plan\" rather than \"Skip Week\" — skipping does not stop your next box.",
      counter: "If you skip by mistake and get charged again, that's a legitimate case for a support refund request, not just bad luck."
    },
    {
      name: "Norton / antivirus auto-renewal", aliases: ["norton", "antivirus"], category: "Software",
      difficulty: "medium", method: "Website (account portal, not the app)",
      situation: "Auto-renewal is opt-out by default and often billed a full year at once, sometimes with a price jump from the first-year promo rate.",
      script: "Log into your account on their website (not just the desktop app) and turn off \"automatic renewal\" under billing/subscription settings, then confirm by email.",
      counter: "If you were already charged for a renewal you didn't want, most of these companies offer a refund window if you request it within about 60 days — ask directly."
    },
    {
      name: "App Store or Google Play subscription", aliases: ["app store", "google play", "iphone app", "android app"], category: "Any app-based subscription",
      difficulty: "easy", method: "App Store or Google Play directly",
      situation: "If you subscribed inside an app on your phone, that app almost never controls your billing — Apple or Google does, regardless of what the app's own \"cancel\" button claims to do.",
      script: "iPhone: Settings → [your name] → Subscriptions. Android: Google Play app → Payments & subscriptions → Subscriptions. Find it there and cancel directly — the company's own app or website often can't do this for you.",
      counter: "No retention call to navigate here — the App Store and Play Store cancel flows don't have upsell offers."
    }
  ];

  const GENERIC = {
    difficulty: "unknown", method: "Depends on where you're billed",
    situation: "We don't have this one catalogued yet, but the same playbook applies to almost anything: find out who actually processes billing, then give them exactly one clear sentence.",
    script: "\"I'm writing/calling to cancel my subscription with [company], effective today. Please confirm the cancellation in writing.\" Send this by email if there's any online form or support inbox — a written trail is worth more than a phone call alone.",
    counter: "If you're pushed toward a phone call and get offered discounts, just repeat your original sentence rather than answering follow-up questions about why."
  };

  /* ========================================================================== */

  const $ = (sel) => document.querySelector(sel);
  const input = $("#company-input");
  const suggestBox = $("#suggest");
  const form = $("#letter-form");

  function normalize(s) { return s.trim().toLowerCase(); }

  function findMatches(query) {
    const q = normalize(query);
    if (!q) return [];
    return COMPANIES.filter((c) =>
      c.name.toLowerCase().includes(q) || c.aliases.some((a) => a.includes(q))
    ).slice(0, 6);
  }

  function findExact(query) {
    const q = normalize(query);
    return COMPANIES.find((c) => c.name.toLowerCase() === q || c.aliases.includes(q));
  }

  function renderSuggestions(matches) {
    if (!matches.length) { suggestBox.hidden = true; suggestBox.innerHTML = ""; return; }
    suggestBox.innerHTML = "";
    matches.forEach((c) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.innerHTML = `${escapeHtml(c.name)} <span class="suggest-meta">— ${escapeHtml(c.category)}</span>`;
      btn.addEventListener("click", () => {
        input.value = c.name;
        suggestBox.hidden = true;
        showResult(c);
      });
      suggestBox.appendChild(btn);
    });
    suggestBox.hidden = false;
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  }

  input.addEventListener("input", () => renderSuggestions(findMatches(input.value)));
  input.addEventListener("focus", () => { if (input.value) renderSuggestions(findMatches(input.value)); });
  document.addEventListener("click", (e) => {
    if (!suggestBox.contains(e.target) && e.target !== input) suggestBox.hidden = true;
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    suggestBox.hidden = true;
    const query = input.value.trim();
    if (!query) return;
    const exact = findExact(query);
    if (exact) { showResult(exact); return; }
    const matches = findMatches(query);
    if (matches.length === 1) { showResult(matches[0]); return; }
    showResult({ name: query, category: "Not in our list yet", ...GENERIC });
  });

  function showResult(c) {
    $("#result").hidden = false;
    $("#cf-name").textContent = c.name;
    $("#cf-category").textContent = c.category;
    const stamp = $("#cf-stamp");
    stamp.textContent = c.difficulty === "unknown" ? "Uncatalogued" : c.difficulty[0].toUpperCase() + c.difficulty.slice(1);
    stamp.className = "cf-stamp" + (c.difficulty === "easy" ? " easy" : c.difficulty === "medium" ? " medium" : "");
    $("#cf-method").textContent = c.method;
    $("#cf-situation").textContent = c.situation;
    $("#cf-script").textContent = c.script;
    $("#cf-counter").textContent = c.counter;
    $("#copy-confirm").hidden = true;
    $("#result").scrollIntoView({ behavior: "smooth", block: "start" });
  }

  $("#copy-script").addEventListener("click", async () => {
    const text = $("#cf-script").textContent;
    try {
      await navigator.clipboard.writeText(text);
      $("#copy-confirm").hidden = false;
      setTimeout(() => { $("#copy-confirm").hidden = true; }, 2000);
    } catch {
      alert("Couldn't copy automatically — select the text above and copy it manually.");
    }
  });

  /* ---------------------------- notify form -------------------------------- */
  const notifyForm = $("#notify-form");
  notifyForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const company = $("#notify-company").value.trim();
    const email = $("#notify-email").value.trim();
    const status = $("#notify-status");
    if (!company) return;
    status.hidden = false;
    status.textContent = "Sending...";
    try {
      const res = await fetch("api/notify.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ company, email }),
      });
      if (res.ok) {
        status.textContent = "Got it — thanks, we'll look into it.";
        notifyForm.reset();
      } else {
        status.textContent = "Something went wrong — try again in a moment.";
      }
    } catch {
      status.textContent = "Something went wrong — try again in a moment.";
    }
  });
})();
