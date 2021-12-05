// COURSE-CLASSES
var cc = {
  // (A) SHOW COURSE CLASSES PAGE
  id : null, // CURRENT COURSE ID
  show : (id) => {
    cc.id = id;
    cb.page(2);
    cb.load({
      page : "cc/main",
      target : "cb-page-2",
      data : { id : id },
      onload : () => { cc.list(); }
    });
  },

  // (B) SHOW ALL CLASSES IN COURSE
  pg : 1, // CURRENT PAGE
  list : () => {
    cb.page(2);
    cb.load({
      page : "cc/list",
      target : "cc-list",
      data : {
        page : cc.pg,
        cid : cc.id
      }
    });
  },

  // (C) GO TO PAGE
  //  pg : int, page number
  goToPage : (pg) => { if (pg!=cc.pg) {
    cc.pg = pg;
    cc.list();
  }},

  // (D) SHOW ADD/EDIT DOCKET
  // id : class ID, for edit only
  addEdit : (id) => {
    cb.load({
      page : "cc/form",
      target : "cb-page-3",
      data : {
        id : id ? id : "", // CLASS ID
        cid : cc.id // COURSE ID
      },
      onload : () => { cb.page(3); }
    });
  },

  // (E) SAVE CLASS
  save : () => {
    // (E1) GET DATA
    var data = {
      cid : cc.id, // COURSE ID
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
      onpass : cc.list
    });
    return false;
  },

  // (F) DELETE CLASS
  //  id : int, class ID
  //  confirm : boolean, confirmed delete
  del : (id, confirm) => {
    if (confirm) {
      cb.api({
        mod : "classes",
        req : "del",
        data : { id: id },
        passmsg : "Class Deleted",
        onpass : cc.list
      });
    } else {
      cb.modal("Please confirm", "Attendance records of this class will be lost!", () => {
        cc.del(id, true);
      });
    }
  }
};
