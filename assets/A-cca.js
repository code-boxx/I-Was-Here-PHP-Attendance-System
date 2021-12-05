// CLASS ATTENDANCE
var cca = {
  // (A) SHOW CLASS ATTENDANCE PAGE
  //  id : int, class ID
  id : null, // CURRENT CLASS ID
  show : (id) => {
    cca.id = id;
    cb.page(3);
    cb.load({
      page : "cca/main",
      target : "cb-page-3",
      data : {
        cid : cc.id, // COURSE ID
        id : id // CLASS ID
      },
      onload : () => { cca.list(); }
    });
  },

  // (B) SHOW CLASS ATTENDANCE
  list : () => {
    cb.load({
      page : "cca/list",
      target : "cca-list",
      data : {
        cid : cc.id, // COURSE ID
        id : cca.id  // CLASS ID
      }
    });
  },

  // (C) MANUALLY ADD A STUDENT
  add : () => {
    var field = document.getElementById("cca-add");
    cb.api({
      mod : "attendance",
      req : "attend",
      data : {
        uid : field.value,
        cid : cc.id, // COURSE ID
        id : cca.id  // CLASS ID
      },
      passmsg : "Attendance Updated",
      onpass : () => {
        field.value = "";
        cca.list();
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
    let all = document.querySelectorAll("#cca-list .btn-primary"),
        present = [];
    if (all.length>1) { for (let i=0; i<all.length-1; i++) {
      present.push(all[i].value);
    }}

    // (D2) SEND!
    cb.api({
      mod : "attendance",
      req : "save",
      data : {
        cid : cc.id, // COURSE ID
        id : cca.id,  // CLASS ID
        list : JSON.stringify(present)
      },
      passmsg : "Attendance Updated"
    });
  }
};
