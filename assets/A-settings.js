function save () {
  // (A) GET ALL DATA
  let data = {};
  for (let i of document.querySelectorAll("#set-list input")) {
    data[i.name] = i.value;
  }

  // (B) API CALL
  cb.api({
    mod : "settings", act : "save",
    data : { settings : JSON.stringify(data) },
    passmsg : "Settings Saved"
  });
  return false;
}

function loadSecurityEvents () {
  cb.api({
    mod : "security", act : "getEvents",
    data : { limit : 100, eventType : document.getElementById("security-event-filter").value },
    passmsg : false,
    onpass : result => {
    let target = document.getElementById("security-events");
    if (!target) { return; }
    target.replaceChildren();
    (result.data || []).forEach(event => {
      let row = document.createElement("div");
      row.className = "border p-2";
      let summary = document.createElement("div");
      summary.textContent = `${event.created_at} — ${event.event_type} — `
        + `${event.user_name || "Anonymous"} (${event.ip_address || "unknown address"})`;
      row.append(summary);
      if (event.details) {
        let details = document.createElement("details");
        let title = document.createElement("summary");
        title.textContent = "Event details";
        let content = document.createElement("pre");
        content.className = "mb-0 mt-2";
        try {
          content.textContent = JSON.stringify(JSON.parse(event.details), null, 2);
        } catch (error) {
          content.textContent = "Details unavailable.";
        }
        details.append(title, content);
        row.append(details);
      }
      target.append(row);
    });
    if (!target.children.length) { target.textContent = "No security events recorded."; }
    }
  });
}

window.addEventListener("load", () => {
  document.getElementById("security-event-filter").addEventListener("change", loadSecurityEvents);
  loadSecurityEvents();
});