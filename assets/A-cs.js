// MANAGE COURSES
var cs = {
  // (A) SHOW ALL COURSES
  pg : 1, // CURRENT PAGE
  find : "", // CURRENT SEARCH
  list : () => {
    cb.page(1);
    cb.load({
      page : "cs/list",
      target : "cs-list",
      data : {
        page : cs.pg,
        search : cs.find
      }
    });
  },

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : (pg) => { if (pg!=cs.pg) {
    cs.pg = pg;
    cs.list();
  }},

  // (C) SEARCH COURSES
  search : () => {
    cs.find = document.getElementById("cs-search").value;
    cs.pg = 1;
    cs.list();
    return false;
  },

  // (D) SHOW ADD/EDIT DOCKET
  // id : course ID, for edit only
  addEdit : (id) => {
    cb.load({
      page : "cs/form",
      target : "cb-page-2",
      data : { id : id ? id : "" },
      onload : () => { cb.page(2); }
    });
  },

  // (E) SAVE COURSE
  save : () => {
    // (E1) GET DATA
    var data = {
      code : document.getElementById("course_code").value,
      name : document.getElementById("course_name").value,
      desc : document.getElementById("course_desc").value,
      start : document.getElementById("course_start").value,
      end : document.getElementById("course_end").value
    };
    var id = document.getElementById("course_id").value;
    if (id!="") { data.id = id; }

    // (E2) AJAX
    cb.api({
      mod : "courses",
      req : "save",
      data : data,
      passmsg : "Course Saved",
      onpass : cs.list
    });
    return false;
  },

  // (F) DELETE COURSE
  //  id : int, course ID
  //  confirm : boolean, confirmed delete
  del : (id, confirm) => {
    if (confirm) {
      cb.api({
        mod : "courses",
        req : "del",
        data : { id: id },
        passmsg : "Course Deleted",
        onpass : cs.list
      });
    } else {
      cb.modal("Please confirm", "All course data and attendance will be lost!", () => {
        cs.del(id, true);
      });
    }
  }
};
window.addEventListener("load", cs.list);
