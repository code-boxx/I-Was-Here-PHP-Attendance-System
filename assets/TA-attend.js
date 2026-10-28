var attend = {
  // (A) SHOW CLASS ATTENDANCE PAGE
  id : null, // current class id
  dashboardTimer : null,
  back : () => {
    if (attend.dashboardTimer) { clearInterval(attend.dashboardTimer); }
    attend.dashboardTimer = null;
    attend.id = null;
    cb.page(1);
  },
  show : id => {
    attend.id = id;
    cb.page(2);
    cb.load({
      page : "TA/attend",
      target : "cb-page-2",
      data : { id : id },
      onload : () => {
        attend.list();
        attend.dashboard();
        attend.loadScheduleAudit();
        attend.loadOfflineQueue();
        attend.loadSubstitutes();
        if (attend.dashboardTimer) { clearInterval(attend.dashboardTimer); }
        attend.dashboardTimer = setInterval(() => {
          if (!document.hidden && document.getElementById("dashboard-present")) {
            attend.dashboard();
          }
        }, 10000);
      }
    });
  },

  dashboard : () => {
    let classId = attend.id;
    if (classId===null) { return; }
    cb.api({
      mod : "attend", act : "getDashboard",
      data : { classId : classId },
      passmsg : false, loading : false, noerr : true,
      onpass : result => {
        if (attend.id!==classId || !document.getElementById("dashboard-present")) { return; }
        let data = result.data;
        let lateResult = data.late ?? 0;
        document.getElementById("dashboard-present").textContent = data.present;
        document.getElementById("dashboard-absent").textContent = data.absent;
        document.getElementById("dashboard-late").textContent = lateResult;
        if (document.getElementById("dashboard-excused")) {
          document.getElementById("dashboard-excused").textContent = data.excused ?? 0;
        }
        document.getElementById("dashboard-unrecorded").textContent = data.unrecorded;
        document.getElementById("dashboard-total").textContent = data.total;
        let session = document.getElementById("dashboard-session");
        session.textContent = `Attendance session ${data.session_status}`;
        if (data.class_status==="cancelled") { session.textContent = "Class cancelled"; }
        else if (data.holiday_name) { session.textContent = `Non-instruction day: ${data.holiday_name}`; }
        session.className = `badge ${data.session_status === "open" ? "bg-success" : "bg-secondary"}`;
        let summary = document.getElementById("dashboard-summary");
        summary.textContent = `${data.present} present, ${data.absent} absent, ${data.excused ?? 0} excused, ${data.unrecorded} unrecorded, ${lateResult} late.`;
        let rule = document.getElementById("dashboard-late-rule");
        rule.textContent = `Late if more than ${data.late_grace_minutes ?? 10} minutes after class start.`;
        let last = document.getElementById("dashboard-last-checkin");
        last.textContent = data.last_checkin
          ? `Last QR check-in: ${data.last_checkin.user_name} at ${data.last_checkin.checked_in_at}`
          : "No QR check-in recorded.";
        let missing = document.getElementById("dashboard-not-present");
        missing.replaceChildren();
        for (let student of data.not_present_students) {
          let item = document.createElement("li");
          item.textContent = `${student.user_name} — ${student.a_status === null ? "not recorded" : "absent"}`;
          missing.append(item);
        }
        document.getElementById("dashboard-updated").textContent =
          `Updated ${new Date().toLocaleTimeString()}; refreshes every 10 seconds while this tab is visible.`;
        document.getElementById("dashboard-error").textContent = "";
      },
      onfail : message => {
        if (attend.id===classId && document.getElementById("dashboard-error")) {
          document.getElementById("dashboard-error").textContent = message;
        }
      }
    });
  },

  // (B) SHOW CLASS ATTENDANCE
  list : () => cb.load({
    page : "TA/attend/list",
    target : "attend-list",
    data : { id : attend.id }
  }),

  cancelClass : () => {
    let reason = document.getElementById("class-schedule-reason")?.value.trim();
    if (!reason) {
      cb.toast(false, "Reason required", "Provide a reason for cancelling this class.");
      return;
    }
    cb.modal("Cancel class", "The class will be excluded from attendance calculations. Existing attendance history will be preserved.", () => cb.api({
      mod : "classes", act : "cancelClass",
      data : { classId : attend.id, reason : reason },
      passmsg : "Class cancelled",
      onpass : () => attend.show(attend.id)
    }));
  },

  reschedule : () => {
    let date = document.getElementById("class-reschedule-date")?.value;
    let reason = document.getElementById("class-schedule-reason")?.value.trim();
    if (!date || !reason) {
      cb.toast(false, "Details required", "Choose a new date and provide a reason.");
      return;
    }
    cb.api({
      mod : "classes", act : "rescheduleClass",
      data : { classId : attend.id, date : date, reason : reason },
      passmsg : "Class rescheduled",
      onpass : () => attend.show(attend.id)
    });
  },

  loadScheduleAudit : () => {
    let target = document.getElementById("class-schedule-audit");
    if (!target || attend.id===null) { return; }
    cb.api({
      mod : "classes", act : "getScheduleAudit",
      data : { classId : attend.id },
      passmsg : false, loading : false, noerr : true,
      onpass : result => {
        target.replaceChildren();
        let rows = result.data || [];
        if (!rows.length) {
          target.textContent = "No schedule changes recorded.";
          return;
        }
        rows.forEach(event => {
          let entry = document.createElement("p");
          entry.textContent = `${event.changed_at}: ${event.event_type.replace(/_/g, " ")} by ${event.actor_name}. ${event.old_value || "Scheduled"} to ${event.new_value || "Cancelled"}. Reason: ${event.reason}`;
          target.append(entry);
        });
      },
      onfail : error => { target.textContent = error; }
    });
  },

  loadOfflineQueue : () => {
    let classId = attend.id;
    let target = document.getElementById("offline-review-list");
    let message = document.getElementById("offline-review-message");
    if (classId===null || !target || !message) { return; }
    cb.api({
      mod : "attend", act : "getOfflineQueue",
      data : { classId : classId },
      passmsg : false, loading : false, noerr : true,
      onpass : result => {
        if (attend.id!==classId) { return; }
        target.replaceChildren();
        let rows = result.data || [];
        if (!rows.length) {
          message.textContent = "No offline check-ins are awaiting review.";
          return;
        }
        message.textContent = `${rows.length} check-in(s) await review.`;
        rows.forEach(entry => {
          let row = document.createElement("div");
          row.className = "border p-2";
          let detail = document.createElement("p");
          detail.className = "mb-2";
          detail.textContent = `${entry.user_name} (${entry.user_email}) requested present; device time ${entry.recorded_at}. This time is unverified.`;
          let approve = document.createElement("button");
          approve.type = "button";
          approve.className = "btn btn-sm btn-success me-2";
          approve.textContent = "Approve";
          approve.addEventListener("click", () => attend.reviewOffline(entry.event_id, "approved"));
          let reject = document.createElement("button");
          reject.type = "button";
          reject.className = "btn btn-sm btn-outline-danger";
          reject.textContent = "Reject";
          reject.addEventListener("click", () => attend.reviewOffline(entry.event_id, "rejected"));
          row.append(detail, approve, reject);
          target.append(row);
        });
      },
      onfail : error => { message.textContent = error; },
      onerr : () => { message.textContent = "Connection failed while loading queued check-ins."; }
    });
  },

  reviewOffline : (eventId, decision) => {
    let label = decision==="approved" ? "Approval reason" : "Rejection reason";
    let note = prompt(`${label} (required, up to 500 characters):`);
    if (note===null) { return; }
    note = note.trim();
    if (!note || note.length>500) {
      document.getElementById("offline-review-message").textContent =
        "Enter a reason between 1 and 500 characters.";
      return;
    }
    cb.api({
      mod : "attend", act : "reviewOfflineQueue",
      data : {
        classId : attend.id,
        eventId : eventId,
        decision : decision,
        note : note
      },
      passmsg : decision==="approved" ? "Offline check-in approved" : "Offline check-in rejected",
      onpass : () => {
        attend.loadOfflineQueue();
        attend.dashboard();
        attend.list();
      }
    });
  },

  loadSubstitutes : () => {
    let target = document.getElementById("ta-substitute-list");
    if (!target || attend.id===null) { return; }
    cb.api({
      mod : "classes", act : "getSubstitutes",
      data : { classId : attend.id },
      passmsg : false, loading : false, noerr : true,
      onpass : result => {
        target.replaceChildren();
        let rows = result.data || [];
        if (!rows.length) {
          let empty = document.createElement("small");
          empty.className = "text-secondary";
          empty.textContent = "No substitute teachers assigned.";
          target.append(empty);
          return;
        }
        rows.forEach(substitute => {
          let row = document.createElement("div");
          row.className = "d-flex justify-content-between align-items-center border p-2";
          let label = document.createElement("span");
          label.textContent = `${substitute.user_name} (${substitute.user_email}) — expires ${substitute.expires_at}`;
          let remove = document.createElement("button");
          remove.type = "button";
          remove.className = "btn btn-sm btn-outline-danger";
          remove.textContent = "Remove";
          remove.addEventListener("click", () => attend.removeSubstitute(substitute.user_id));
          row.append(label, remove);
          target.append(row);
        });
      }
    });
  },

  addSubstitute : () => {
    let userId = document.getElementById("ta-substitute-select")?.value;
    let expiresAt = document.getElementById("ta-substitute-expiry")?.value;
    if (attend.id===null || !userId || !expiresAt) { return; }
    cb.api({
      mod : "classes", act : "addSubstitute",
      data : { classId : attend.id, userId : userId, expiresAt : expiresAt },
      passmsg : "Temporary substitute access assigned",
      onpass : attend.loadSubstitutes
    });
  },

  removeSubstitute : userId => cb.modal(
    "Please confirm",
    "Revoke this teacher's access to the class?",
    () => cb.api({
      mod : "classes", act : "delSubstitute",
      data : { classId : attend.id, userId : userId },
      passmsg : "Substitute access revoked",
      onpass : attend.loadSubstitutes
    })
  ),

  // (D) SAVE ATTENDANCE
  save : () => {
    // (D1) GET ALL ATTENDANCE RECORDS
    let all = document.querySelectorAll(".att-i"), att = {};
    for (let a of all) {
      let id = a.value;
      att[id] = {
        s : document.getElementById("att-s" + id).value,
        n : document.getElementById("att-n" + id).value
      };
    }

    // (D2) SEND!
    cb.api({
      mod : "attend", act : "save",
      data : {
        id : attend.id,
        att : JSON.stringify(att)
      },
      passmsg : "Attendance Updated"
    });
  },

  audit : () => cb.api({
    mod : "attend", act : "getAudit",
    data : { classId : attend.id },
    passmsg : false,
    onpass : result => {
      let target = document.getElementById("attendance-audit");
      target.replaceChildren();
      let entries = result.data || [];
      if (!entries.length) {
        target.textContent = "No attendance changes have been recorded.";
        return;
      }
      let list = document.createElement("ol");
      for (let entry of entries) {
        let item = document.createElement("li");
        if (entry.entry_type==="schedule") {
          item.textContent = `${entry.changed_at}: ${entry.event_type.replace(/_/g, " ")} by ${entry.actor_name}. ${entry.old_value || "Scheduled"} to ${entry.new_value || "Cancelled"}. Reason: ${entry.change_reason}`;
        } else {
          let status = value => value===null ? "not recorded" : (value==1 ? "present" : (value==2 ? "excused" : "absent"));
          item.textContent = `${entry.changed_at}: ${entry.student_name} changed from ${status(entry.old_status)} to ${status(entry.new_status)} by ${entry.actor_name} (${entry.change_source}). Reason: ${entry.change_reason || "none"}`;
        }
        list.append(item);
      }
      target.append(list);
    }
  })
};