// COURSE-USERS
var cu = {
  // (A) SHOW COURSE USERS PAGE
  id : null, // CURRENT COURSE ID
  show : (id) => {
    cu.id = id;
    cb.page(2);
    cb.load({
      page : "cu/main",
      target : "cb-page-2",
      data : { id : id },
      onload : () => { cu.list(); }
    });
  },

  // (B) SHOW ALL USERS IN COURSE
  pg : 1, // CURRENT PAGE
  list : () => {
    cb.load({
      page : "cu/list",
      target : "cu-list",
      data : {
        page : cu.pg,
        id : cu.id
      }
    });
  },

  // (C) GO TO PAGE
  //  pg : int, page number
  goToPage : (pg) => { if (pg!=cu.pg) {
    cu.pg = pg;
    cu.list();
  }},

  // (D) ADD USER TO COURSE
  add : () => {
    // (D1) ADD EMAIL FIELD
    var field = document.getElementById("cu-add");

    // (D2) AJAX
    cb.api({
      mod : "courses",
      req : "addUser",
      data : {
        cid : cu.id,
        uid : field.value
      },
      passmsg : "User Added",
      onpass : () => {
        field.value = "";
        cu.list();
      }
    });
    return false;
  },

  // (E) REMOVE USER FROM COURSE
  //  id : user id
  //  confirm : boolean, confirmed delete
  del : (id, confirm) => {
    if (confirm) {
      cb.api({
        mod : "courses",
        req : "delUser",
        data : {
          cid : cu.id,
          uid : id
        },
        passmsg : "User removed from course",
        onpass : cu.list
      });
    } else {
      cb.modal("Please confirm", "User will be removed from the course, but past attendance will be kept.", () => {
        cu.del(id, true);
      });
    }
  }
};
