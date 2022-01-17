var course = {
  // (A) SHOW ALL COURSES
  pg : 1, // CURRENT PAGE
  find : "", // CURRENT SEARCH
  list : () => {
    cb.page(1);
    cb.load({
      page : "course/list",
      target : "course-list",
      data : {
        page : course.pg,
        search : course.find
      }
    });
  },

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : (pg) => { if (pg!=course.pg) {
    course.pg = pg;
    course.list();
  }},

  // (C) SEARCH COURSES
  search : () => {
    course.find = document.getElementById("course-search").value;
    course.pg = 1;
    course.list();
    return false;
  },

  // (D) SHOW ADD/EDIT DOCKET
  // id : course ID, for edit only
  addEdit : (id) => {
    cb.load({
      page : "course/form",
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
      onpass : course.list
    });
    return false;
  },

  // (F) DELETE COURSE
  //  id : int, course ID
  del : (id) => {
    cb.modal("Please confirm", "All course data and attendance will be lost!", () => {
      cb.api({
        mod : "courses",
        req : "del",
        data : { id: id },
        passmsg : "Course Deleted",
        onpass : course.list
      });
    });
  }
};
window.addEventListener("load", course.list);
