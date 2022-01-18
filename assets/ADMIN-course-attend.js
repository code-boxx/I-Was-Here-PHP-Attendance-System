var attend = {
  // (A) SHOW CLASS ATTENDANCE PAGE
  //  id : int, class ID
  id : null, // CURRENT CLASS ID
  show : (id) => {
    attend.id = id;
    cb.page(3);
    cb.load({
      page : "class/attend",
      target : "cb-page-3",
      data : {
        cid : classes.id, // COURSE ID
        id : id // CLASS ID
      },
      onload : () => {
        selector.attach({
          field : document.getElementById("attend-add"),
          mod : "attendance", req : "search",
          data : { role : "S" },
          pick : (d, v) => { attend.add(); }
        });
        attend.list();
      }
    });
  },

  // (B) SHOW CLASS ATTENDANCE
  list : () => {
    cb.load({
      page : "class/attend/list",
      target : "attend-list",
      data : {
        cid : classes.id, // COURSE ID
        id : attend.id  // CLASS ID
      }
    });
  },

  // (C) MANUALLY ADD A STUDENT
  add : () => {
    var field = document.getElementById("attend-add");
    cb.api({
      mod : "attendance",
      req : "attend",
      data : {
        uid : field.value,
        cid : classes.id, // COURSE ID
        id : attend.id  // CLASS ID
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
  toggle : (uid) => {
    // (C1) GET BUTTON
    let btn = document.getElementById("att"+uid),
        ico = btn.getElementsByTagName("span")[0];

    // (C2) TOGGLE STATUS
    if (btn.classList.contains("btn-primary")) {
      btn.classList.remove("btn-primary");
      btn.classList.add("btn-danger");
      ico.innerHTML = "close";
    } else {
      btn.classList.remove("btn-danger");
      btn.classList.add("btn-primary");
      ico.innerHTML = "done";
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
      mod : "attendance",
      req : "save",
      data : {
        cid : classes.id, // COURSE ID
        id : attend.id,  // CLASS ID
        list : JSON.stringify(present)
      },
      passmsg : "Attendance Updated"
    });
  }
};
