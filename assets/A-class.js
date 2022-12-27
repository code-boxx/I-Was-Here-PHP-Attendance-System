var classes = {
  // (A) SHOW COURSE CLASSES PAGE
  pg : 1, // current page
  find : "", // current search
  list : () => {
    cb.page(0);
    cb.load({
      page : "a/class/list",
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
    page : "a/class/form",
    target : "cb-page-2",
    data : { id : id ? id : "" },
    onload : () => {
      cb.page(1);
      selector.attach({
        field : document.getElementById("class_course"),
        mod : "autocomplete", req : "course",
        pick : (d, v) => {
          document.getElementById("class_course").value = d;
          document.getElementById("class_course_id").value = v;
          classes.toggle(true);
        }
      });
    }
  }),

  // (E) TOGGLE ADD/EDIT FORM ON SELECTING COURSE
  toggle : set => {
    // (E1) HTML ELEMENTS
    let hCourse = document.getElementById("class_course"),
        hCID = document.getElementById("class_course_id"),
        hCNote = document.getElementById("class_course_note"),
        hDate = document.getElementById("class_date"),
        hTeacher = document.getElementById("class_teacher"),
        hDesc = document.getElementById("class_desc");

    // (E2) COURSE CHOSEN - UPDATE FORM
    if (set) {
      // (E2-1) "DISABLE" COURSE FIRST
      hCourse.readOnly = true;

      cb.api({
        mod : "autocomplete", req : "icourse",
        data : { id : hCID.value },
        passmsg : false,
        onpass : res => {
          // (E2-2) DATE
          hDate.setAttribute("min", res.data.c["course_start"] + " 00:00:00");
          hDate.setAttribute("max", res.data.c["course_end"] + " 23:59:59");
          hDate.value = res.data.c["course_start"] + " 00:00:00";
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
      hCID.value = "";

      // (E3-2) RESET + DISABLE FIELDS
      hDate.disabled = true;
      hDate.value = "";
      hTeacher.disabled = true;
      hTeacher.innerHTML = "";
      hDesc.disabled = true;
      hDesc.value = "";
    }
  },

  // (F) SAVE CLASS
  save : () => {
    // (F1) GET DATA
    var data = {
      cid : document.getElementById("class_course_id").value, // course id
      uid : document.getElementById("class_teacher").value, // user id
      desc : document.getElementById("class_desc").value, // description
      date : document.getElementById("class_date").value.replace("T", " ") // date
    };
    var id = document.getElementById("class_id").value; // class id
    if (id!="") { data.id = id; }
    
    // (F2) AJAX
    cb.api({
      mod : "classes", req : "save",
      data : data,
      passmsg : "Class Saved",
      onpass : classes.list
    });
    return false;
  },

  // (G) DELETE CLASS
  //  id : int, class ID
  del : id => cb.modal("Please confirm", "Attendance records of this class will be lost!", () => cb.api({
    mod : "classes", req : "del",
    data : { id: id },
    passmsg : "Class Deleted",
    onpass : classes.list
  }))
};
window.addEventListener("load", classes.list);