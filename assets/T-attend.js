// CLASS ATTENDANCE
var att = {
  // (A) SHOW CLASS ATTENDANCE PAGE
  //  id : int, class ID
  id : null, // CURRENT CLASS ID
  cid : null, // CURRENT COURSE ID
  show : (id) => {
    att.id = id;
    cb.page(2);
    cb.load({
      page : "att/main",
      target : "cb-page-2",
      data : { id : id },
      onload : () => {
        att.cid = document.getElementById("class_course").value;
        att.list();
      }
    });
  },

  // (B) SHOW CLASS ATTENDANCE
  list : () => {
    cb.load({
      page : "att/list",
      target : "att-list",
      data : {
        cid : att.cid, // COURSE ID
        id : att.id  // CLASS ID
      }
    });
  },

  // (C) MANUALLY ADD A STUDENT
  add : () => {
    var field = document.getElementById("att-add");
    cb.api({
      mod : "attendance",
      req : "attend",
      data : {
        uid : field.value,
        cid : att.cid, // COURSE ID
        id : att.id  // CLASS ID
      },
      passmsg : "Attendance Updated",
      onpass : () => {
        field.value = "";
        att.list();
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
    let all = document.querySelectorAll("#att-list .btn-primary"),
        present = [];
    if (all.length>1) { for (let i=0; i<all.length-1; i++) {
      present.push(all[i].value);
    }}

    // (D2) SEND!
    cb.api({
      mod : "attendance",
      req : "save",
      data : {
        cid : att.cid, // COURSE ID
        id : att.id,  // CLASS ID
        list : JSON.stringify(present)
      },
      passmsg : "Attendance Updated"
    });
  }
};
