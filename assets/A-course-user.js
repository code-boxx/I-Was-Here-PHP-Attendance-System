var cuser = {
  // (A) SHOW COURSE USERS PAGE
  pg : 1, // current page
  code : null, // current course code
  show : code => {
    cuser.code = code;
    cb.page(2);
    cb.load({
      page : "A/course/user",
      target : "cb-page-2",
      data : { code : code },
      onload : () => {
        autocomplete.attach({
          target : document.getElementById("course-user-add"),
          mod : "autocomplete", act : "userEmail",
          onpick : res => cuser.add()
        });
        cuser.list();
      }
    });
  },

  // (B) SHOW ALL USERS IN COURSE
  list : () => cb.load({
    page : "A/course/user/list",
    target : "course-user-list",
    data : {
      page : cuser.pg,
      code : cuser.code
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
      mod : "courses", act : "addUser",
      data : {
        code : cuser.code,
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
    mod : "courses", act : "delUser",
    data : {
      code : cuser.code,
      uid : id
    },
    passmsg : "User removed from course",
    onpass : cuser.list
  })),

  // (F) IMPORT USERS TO COURSE
  import : () => im.init({
    name : "USERS TO COURSE",
    at : 3, back : 2,
    eg : "dummy-course-users.csv",
    api : { mod : "courses", act : "addUser" },
    after : () => cuser.list(),
    data : { code : cuser.code },
    cols : [
      ["User's Email", "uid", true]
    ]
  })
};