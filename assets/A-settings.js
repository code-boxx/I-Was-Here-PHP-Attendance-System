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

window.addEventListener("load", () => cb.api({
  mod : "security", act : "getEvents", data : { limit : 100 }, passmsg : false,
  onpass : result => {
    let target = document.getElementById("security-events");
    if (!target) { return; }
    target.replaceChildren();
    (result.data || []).forEach(event => {
      let row = document.createElement("div");
      row.className = "border p-2";
      row.textContent = `${event.created_at} — ${event.event_type} — `
        + `${event.user_name || "Anonymous"} (${event.ip_address || "unknown address"})`;
      target.append(row);
    });
    if (!target.children.length) { target.textContent = "No security events recorded."; }
  }
}));