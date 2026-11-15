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
  loadPrivacyRequests();
});

function loadPrivacyRequests () {
  cb.api({
    mod : "users", act : "getDeletionRequests", passmsg : false,
    onpass : result => {
      let target = document.getElementById("privacy-requests");
      if (!target) { return; }
      target.replaceChildren();
      (result.data || []).forEach(request => {
        let row = document.createElement("div");
        row.className = "border p-2";
        let summary = document.createElement("div");
        summary.textContent = `${request.requested_at} — ${request.user_name} `
          + `(${request.user_email}) — ${request.request_status}`;
        row.append(summary);
        if (request.request_status==="pending") {
          ["approved", "rejected"].forEach(decision => {
            let button = document.createElement("button");
            button.type = "button";
            button.className = decision==="approved"
              ? "btn btn-sm btn-success me-2" : "btn btn-sm btn-outline-danger me-2";
            button.textContent = decision[0].toUpperCase()+decision.slice(1);
            button.addEventListener("click", () => resolvePrivacyRequest(request.request_id, decision));
            row.append(button);
          });
        }
        target.append(row);
      });
      if (!target.children.length) { target.textContent = "No privacy requests."; }
    }
  });
}

function resolvePrivacyRequest (requestId, decision) {
  let note = window.prompt("Optional decision note", "") ?? "";
  cb.api({
    mod : "users", act : "resolveDeletionRequest",
    data : { requestId : requestId, decision : decision, note : note },
    passmsg : "Privacy request updated",
    onpass : loadPrivacyRequests
  });
}