var classes = {
  // (A) SHOW COURSE CLASSES PAGE
  pg : 1, // current page
  find : "", // current search
  list : silent => {
    if (silent!==true) { cb.page(1); }
    cb.load({
      page : "A/class/list",
      target : "class-list",
      data : {
        page : classes.pg,
        search : classes.find
      }
    });
  },

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : pg => { if (pg!=classes.pg) {
    classes.pg = pg;
    classes.list();
  }},

  // (C) SEARCH CLASSES
  search : () => {
    classes.find = document.getElementById("class-search").value;
    classes.pg = 1;
    classes.list();
    return false;
  },

  // (D) SHOW ADD/EDIT DOCKET
  // id : class ID, for edit only
  addEdit : id => cb.load({
    page : "A/class/form",
    target : "cb-page-2",
    data : { id : id ? id : "" },
    onload : () => {
      cb.page(2);
      if (id) { classes.refreshSubstitutes(); }
      autocomplete.attach({
        target : document.getElementById("class_course"),
        mod : "autocomplete", act : "course",
        onpick : r => {
          document.getElementById("class_course").value = `[${r.v}] ${r.n}`;
          document.getElementById("class_course_code").value = r.v;
          classes.toggle(true);
        }
      });
    }
  }),

  // (E) TOGGLE ADD/EDIT FORM ON SELECTING COURSE
  toggle : set => {
    // (E1) HTML ELEMENTS
    let hCourse = document.getElementById("class_course"),
        hCode = document.getElementById("class_course_code"),
        hCNote = document.getElementById("class_course_note"),
        hDate = document.getElementById("class_date"),
        hTeacher = document.getElementById("class_teacher"),
        hDesc = document.getElementById("class_desc"),
        hRoom = document.getElementById("class_room"),
        hGroup = document.getElementById("class_group");

    // (E2) COURSE CHOSEN - UPDATE FORM
    if (set) {
      cb.api({
        mod : "autocomplete", act : "icourse",
        data : { code : hCode.value },
        passmsg : false,
        onpass : res => {
          // (E2-1) "DISABLE" COURSE FIRST
          hCourse.readOnly = true;

          // (E2-2) DATE
          hDate.setAttribute("min", res.data.c["course_start"] + "T00:00");
          hDate.setAttribute("max", res.data.c["course_end"] + "T23:59");
          hDate.value = res.data.c["course_start"] + "T00:00";
          hDate.disabled = false;

          // (E2-3) TEACHER
          hTeacher.disabled = false;
          if (res.data.t!=null) {
            hTeacher.innerHTML = "";
            Object.entries(res.data.t).forEach(([i,t]) => {
              let opt = document.createElement("option");
              opt.value = i;
              opt.innerHTML = `${t["user_name"]} (${t["user_email"]})`;
              hTeacher.appendChild(opt);
            });
          } else { hTeacher.innerHTML = "<option value=''>No teachers assigned!</option>"; }

          // (E2-4) DESCRIPTION
          hDesc.disabled = false;
          hRoom.disabled = false;
          hGroup.disabled = false;

          // (E2-5) CLICK COURSE TO CHANGE
          hCourse.onclick = () => classes.toggle(false);
          hCNote.classList.remove("d-none");
        }
      });
    }

    // (E3) UNSET COURSE
    else {
      // (E3-1) RESET COURSE
      hCourse.value = "";
      hCourse.readOnly = false;
      hCourse.onclick = "";
      hCNote.classList.add("d-none");
      hCode.value = "";

      // (E3-2) RESET + DISABLE FIELDS
      hDate.disabled = true;
      hDate.value = "";
      hTeacher.disabled = true;
      hTeacher.innerHTML = "";
      hDesc.disabled = true;
      hDesc.value = "";
      hRoom.disabled = true;
      hRoom.value = "";
      hGroup.disabled = true;
      hGroup.value = "";
    }
  },

  // (F) SAVE CLASS
  save : () => {
    // (F1) GET DATA
    var data = {
      code : document.getElementById("class_course_code").value, // course code
      uid : document.getElementById("class_teacher").value, // user id
      desc : document.getElementById("class_desc").value, // description
      room : document.getElementById("class_room")?.value || "",
      group : document.getElementById("class_group")?.value || "",
      date : document.getElementById("class_date").value.replace("T", " "), // date
      reason : document.getElementById("class-change-reason")?.value || ""
    };
    var id = document.getElementById("class_id").value; // class id
    if (id!="") { data.id = id; }

    // (F2) AJAX
    cb.api({
      mod : "classes", act : "save",
      data : data,
      passmsg : "Class Saved",
      onpass : classes.list
    });
    return false;
  },

  // (G) DELETE CLASS
  //  id : int, class ID
  del : id => cb.modal("Please confirm", "Attendance records of this class will be lost!", () => cb.api({
    mod : "classes", act : "del",
    data : { id: id },
    passmsg : "Class Deleted",
    onpass : classes.list
  })),

  // (H) LOAD SUBSTITUTES FOR CURRENT CLASS
  refreshSubstitutes : () => {
    let classId = document.getElementById("class_id")?.value;
    let list = document.getElementById("class-substitute-list");
    if (!classId || !list) { return; }
    cb.api({
      mod : "classes", act : "getSubstitutes",
      data : { classId : classId },
      passmsg : false,
      onpass : result => {
        let subs = result.data || [];
        list.replaceChildren();
        if (!subs.length) {
          list.innerHTML = "<small class='text-secondary'>No substitute teachers assigned.</small>";
          return;
        }
        subs.forEach(sub => {
          let row = document.createElement("div");
          row.className = "d-flex justify-content-between align-items-center border p-2 mb-2";
          let label = document.createElement("span");
          label.textContent = `${sub.user_name} (${sub.user_email}) — expires ${sub.expires_at}`;
          let remove = document.createElement("button");
          remove.type = "button";
          remove.className = "btn btn-sm btn-outline-danger";
          remove.textContent = "Remove";
          remove.addEventListener("click", () => classes.removeSubstitute(sub.user_id));
          row.append(label, remove);
          list.appendChild(row);
        });
      }
    });
  },

  addSubstitute : () => {
    let classId = document.getElementById("class_id")?.value;
    let userId = document.getElementById("class_substitute_user")?.value;
    let expiresAt = document.getElementById("class_substitute_expiry")?.value;
    if (!classId || !userId || !expiresAt) { return; }
    cb.api({
      mod : "classes", act : "addSubstitute",
      data : { classId : classId, userId : userId, expiresAt : expiresAt },
      passmsg : "Substitute teacher added",
      onpass : () => {
        document.getElementById("class_substitute_user").value = "";
        classes.refreshSubstitutes();
      }
    });
  },

  removeSubstitute : userId => cb.modal("Please confirm", "Remove substitute access for this teacher?", () => cb.api({
    mod : "classes", act : "delSubstitute",
    data : { classId : document.getElementById("class_id").value, userId : userId },
    passmsg : "Substitute removed",
    onpass : classes.refreshSubstitutes
  })),

  // (H) IMPORT CLASSES
  import : () => im.init({
    name : "CLASSES",
    at : 2, back : 1,
    eg : "dummy-classes.csv",
    api : { mod : "classes", act : "import" },
    preview : { mod : "classes", act : "previewImport" },
    after : () => classes.list(true),
    cols : [
      ["Course Code", "code", true],
      ["Date (YYYY-MM-DD HH:MM:SS)", "date", true],
      ["Teacher's Email", "email", "E"],
      ["Description (If any)", "desc"],
      ["Room (If any)", "room"],
      ["Student Group (If any)", "group"]
    ]
  })
};

window.addEventListener("load", () => {
  classes.list();
  autocomplete.attach({
    target : document.getElementById("class-search"),
    mod : "autocomplete", act : "course",
    onpick : res => classes.search()
  });
});