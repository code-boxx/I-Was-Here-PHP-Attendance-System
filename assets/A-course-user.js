var cuser = {
  // (A) SHOW COURSE USERS PAGE
  pg : 1, // current page
  id : null, // current course id
  show : id => {
    cuser.id = id;
    cb.page(1);
    cb.load({
      page : "a/course/user",
      target : "cb-page-2",
      data : { id : id },
      onload : () => {
        selector.attach({
          field : document.getElementById("course-user-add"),
          mod : "autocomplete", req : "user",
          pick : (d, v) => { cuser.add(); }
        });
        cuser.list();
      }
    });
  },

  // (B) SHOW ALL USERS IN COURSE
  list : () => cb.load({
    page : "a/course/user/list",
    target : "course-user-list",
    data : {
      page : cuser.pg,
      id : cuser.id
    }
  }),

  // (C) GO TO PAGE
  //  pg : int, page number
  goToPage : pg => { if (pg!=cuser.pg) {
    cuser.pg = pg;
    cuser.list();
  }},

  // (D) ADD USER TO COURSE
  add : () => {
    // (D1) ADD EMAIL FIELD
    var field = document.getElementById("course-user-add");

    // (D2) AJAX
    cb.api({
      mod : "courses", req : "addUser",
      data : {
        cid : cuser.id,
        uid : field.value
      },
      passmsg : "User Added",
      onpass : () => {
        field.value = "";
        cuser.list();
      }
    });
    return false;
  },

  // (E) REMOVE USER FROM COURSE
  //  id : user id
  del : id => cb.modal("Please confirm", "User will be removed from the course, but past attendance will be kept.", () => cb.api({
    mod : "courses", req : "delUser",
    data : {
      cid : cuser.id,
      uid : id
    },
    passmsg : "User removed from course",
    onpass : cuser.list
  }))
};