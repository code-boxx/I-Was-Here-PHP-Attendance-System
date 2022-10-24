var attend = {
  // (A) SHOW CLASS ATTENDANCE PAGE
  id : null, // current class id
  show : id => {
    attend.id = id;
    cb.page(1);
    cb.load({
      page : "t/attend",
      target : "cb-page-2",
      data : { id : id },
      onload : () => {
        selector.attach({
          field : document.getElementById("attend-add"),
          mod : "autocomplete", req : "user",
          data : { role : "S" },
          pick : (d, v) => { attend.add(); }
        });
        attend.list();
      }
    });
  },

  // (B) SHOW CLASS ATTENDANCE
  list : () => cb.load({
    page : "t/attend/list",
    target : "attend-list",
    data : { id : attend.id }
  }),

  // (C) MANUALLY ADD A STUDENT
  add : () => {
    var field = document.getElementById("attend-add");
    cb.api({
      mod : "attendance", req : "attend",
      data : {
        uid : field.value,
        id : attend.id
      },
      passmsg : "Attendance Updated",
      onpass : () => {
        field.value = "";
        attend.list();
      }
    });
    return false;
  },

  // (C) TOGGLE ATTENDANCE STATUS
  toggle : uid => {
    // (C1) GET BUTTON
    let btn = document.getElementById("att"+uid);

    // (C2) TOGGLE STATUS
    if (btn.classList.contains("btn-primary")) {
      btn.classList.remove("btn-primary");
      btn.classList.add("btn-danger");
      btn.innerHTML = "close";
    } else {
      btn.classList.remove("btn-danger");
      btn.classList.add("btn-primary");
      btn.innerHTML = "done";
    }
  },

  // (D) SAVE ATTENDANCE
  save : () => {
    // (D1) GET PRESENT
    // NOTE : LAST BTN-PRIMARY IS "SAVE ATTENDANCE" BUTTON
    let all = document.querySelectorAll("#attend-list .btn-primary"),
        present = [];
    if (all.length>1) { for (let i=0; i<all.length-1; i++) {
      present.push(all[i].value);
    }}

    // (D2) SEND!
    cb.api({
      mod : "attendance", req : "save",
      data : {
        id : attend.id,
        list : JSON.stringify(present)
      },
      passmsg : "Attendance Updated"
    });
  }
};