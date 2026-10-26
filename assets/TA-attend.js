var attend = {
  // (A) SHOW CLASS ATTENDANCE PAGE
  id : null, // current class id
  show : id => {
    attend.id = id;
    cb.page(2);
    cb.load({
      page : "TA/attend",
      target : "cb-page-2",
      data : { id : id },
      onload : () => attend.list()
    });
  },

  // (B) SHOW CLASS ATTENDANCE
  list : () => cb.load({
    page : "TA/attend/list",
    target : "attend-list",
    data : { id : attend.id }
  }),

  // (C) TOGGLE ATTENDANCE STATUS
  toggle : uid => {
    // (C1) GET BUTTON
    let btn = document.getElementById("att-s"+uid);

    // (C2) TOGGLE STATUS
    if (btn.classList.contains("btn-primary")) {
      btn.classList.remove("btn-primary");
      btn.classList.add("btn-danger");
      btn.classList.remove("icon-checkmark");
      btn.classList.add("icon-cross");
      btn.setAttribute("aria-pressed", "false");
      btn.setAttribute("aria-label", "Absent; mark present for " + btn.dataset.student);
    } else {
      btn.classList.remove("btn-danger");
      btn.classList.add("btn-primary");
      btn.classList.remove("icon-cross");
      btn.classList.add("icon-checkmark");
      btn.setAttribute("aria-pressed", "true");
      btn.setAttribute("aria-label", "Present; mark absent for " + btn.dataset.student);
    }
  },

  // (D) SAVE ATTENDANCE
  save : () => {
    // (D1) GET ALL ATTENDANCE RECORDS
    let all = document.querySelectorAll(".att-i"), att = {};
    for (let a of all) {
      let id = a.value;
      att[id] = {
        s : document.getElementById("att-s" + id).classList.contains("btn-primary") ? "1" : "0",
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
        item.textContent = `${entry.changed_at}: ${entry.student_name} changed from ${entry.old_status === null ? "not recorded" : (entry.old_status == 1 ? "present" : "absent")} to ${entry.new_status == 1 ? "present" : "absent"} by ${entry.actor_name} (${entry.change_source}). Reason: ${entry.change_reason || "none"}`;
        list.append(item);
      }
      target.append(list);
    }
  })
};