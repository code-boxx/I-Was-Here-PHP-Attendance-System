var classes = {
  // (A) SHOW COURSE CLASSES PAGE
  id : null, // CURRENT COURSE ID
  show : (id) => {
    classes.id = id;
    cb.page(2);
    cb.load({
      page : "class",
      target : "cb-page-2",
      data : { id : id },
      onload : () => { classes.list(); }
    });
  },

  // (B) SHOW ALL CLASSES IN COURSE
  pg : 1, // CURRENT PAGE
  list : () => {
    cb.page(2);
    cb.load({
      page : "class/list",
      target : "class-list",
      data : {
        page : classes.pg,
        cid : classes.id
      }
    });
  },

  // (C) GO TO PAGE
  //  pg : int, page number
  goToPage : (pg) => { if (pg!=classes.pg) {
    classes.pg = pg;
    classes.list();
  }},

  // (D) SHOW ADD/EDIT DOCKET
  // id : class ID, for edit only
  addEdit : (id) => {
    cb.load({
      page : "class/form",
      target : "cb-page-3",
      data : {
        id : id ? id : "", // CLASS ID
        cid : classes.id // COURSE ID
      },
      onload : () => { cb.page(3); }
    });
  },

  // (E) SAVE CLASS
  save : () => {
    // (E1) GET DATA
    var data = {
      cid : classes.id, // COURSE ID
      uid : document.getElementById("class_teacher").value, // USER ID
      desc : document.getElementById("class_desc").value,
      date : document.getElementById("class_date").value.replace("T", " ")
    };
    var id = document.getElementById("class_id").value; // CLASS ID
    if (id!="") { data.id = id; }

    // (E2) AJAX
    cb.api({
      mod : "classes",
      req : "save",
      data : data,
      passmsg : "Class Saved",
      onpass : classes.list
    });
    return false;
  },

  // (F) DELETE CLASS
  //  id : int, class ID
  del : (id) => {
    cb.modal("Please confirm", "Attendance records of this class will be lost!", () => {
      cb.api({
        mod : "classes",
        req : "del",
        data : { id: id },
        passmsg : "Class Deleted",
        onpass : classes.list
      });
    });
  }
};
