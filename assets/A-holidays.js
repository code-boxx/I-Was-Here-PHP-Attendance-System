var holidays = {
  load : () => {
    cb.api({
      mod : "calendar", act : "getHolidays",
      passmsg : false, loading : false,
      onpass : result => holidays.render(result.data || [])
    });
    cb.api({
      mod : "calendar", act : "getAudit",
      passmsg : false, loading : false,
      onpass : result => holidays.renderAudit(result.data || [])
    });
  },

  save : () => {
    let form = document.getElementById("holiday-form");
    let data = Object.fromEntries(new FormData(form).entries());
    cb.api({
      mod : "calendar", act : "saveHoliday",
      data : data,
      passmsg : "Holiday saved",
      onpass : () => {
        form.reset();
        holidays.load();
      }
    });
    return false;
  },

  remove : date => {
    let reason = prompt("Reason for removing this non-instruction date (required):");
    if (reason===null) { return; }
    reason = reason.trim();
    if (!reason || reason.length>500) {
      cb.toast(false, "Reason required", "Enter a reason of 1 to 500 characters.");
      return;
    }
    cb.api({
      mod : "calendar", act : "deleteHoliday",
      data : { date : date, reason : reason },
      passmsg : "Holiday removed",
      onpass : holidays.load
    });
  },

  render : rows => {
    let target = document.getElementById("holiday-list");
    target.replaceChildren();
    if (!rows.length) {
      target.textContent = "No school holidays or non-instruction dates configured.";
      return;
    }
    rows.forEach(holiday => {
      let row = document.createElement("div");
      row.className = "border bg-white p-3 d-flex justify-content-between align-items-center";
      let label = document.createElement("span");
      label.textContent = `${holiday.holiday_date} — ${holiday.holiday_name}`;
      let remove = document.createElement("button");
      remove.type = "button";
      remove.className = "btn btn-sm btn-outline-danger";
      remove.textContent = "Remove";
      remove.addEventListener("click", () => holidays.remove(holiday.holiday_date));
      row.append(label, remove);
      target.append(row);
    });
  },

  renderAudit : events => {
    let target = document.getElementById("holiday-audit");
    target.replaceChildren();
    if (!events.length) {
      target.textContent = "No calendar changes recorded.";
      return;
    }
    events.forEach(event => {
      let row = document.createElement("div");
      row.className = "border bg-white p-2";
      let subject = event.holiday_date || `class #${event.class_id}`;
      row.textContent = `${event.changed_at}: ${event.event_type} on ${subject} by ${event.actor_name}. ${event.old_value || ""} to ${event.new_value || ""}. Reason: ${event.reason}`;
      target.append(row);
    });
  }
};

window.addEventListener("load", holidays.load);
