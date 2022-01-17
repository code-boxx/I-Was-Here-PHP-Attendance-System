var usr = {
  // (A) SHOW ALL USERS
  pg : 1, // CURRENT PAGE
  find : "", // CURRENT SEARCH
  role : "", // CURRENT ROLE
  list : (silent) => {
    if (silent!==true) { cb.page(1); }
    cb.load({
      page : "users/list",
      target : "user-list",
      data : {
        page : usr.pg,
        search : usr.find,
        role : usr.role
      }
    });
  },

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : (pg) => { if (pg!=usr.pg) {
    usr.pg = pg;
    usr.list();
  }},

  // (C) SEARCH USER
  search : () => {
    usr.find = document.getElementById("user-search").value;
    usr.role = document.getElementById("user-search-role").value;
    usr.pg = 1;
    usr.list();
    return false;
  },

  // (D) SHOW ADD/EDIT DOCKET
  // id : user ID, for edit only
  addEdit : (id) => {
    cb.load({
      page : "users/form",
      target : "cb-page-2",
      data : { id : id ? id : "" },
      onload : () => { cb.page(2); }
    });
  },

  // (E) SAVE USER
  save : () => {
    // (E1) GET DATA
    var data = {
      name : document.getElementById("user_name").value,
      email : document.getElementById("user_email").value,
      role : document.getElementById("user_role").value,
      password : document.getElementById("user_password").value
    };
    var id = document.getElementById("user_id").value;
    if (id!="") { data.id = id; }

    // (E2) AJAX
    cb.api({
      mod : "users",
      req : "save",
      data : data,
      passmsg : "User Saved",
      onpass : usr.list
    });
    return false;
  },

  // (F) DELETE (INACTIVATE) USER
  //  id : int, user ID
  del : (id) => {
    cb.modal("Please confirm", "Inactivate user? User will no longer be able to sign in, but existing data will be kept.", () => {
      cb.api({
        mod : "users",
        req : "del",
        data : { id: id },
        passmsg : "User Inactivated",
        onpass : usr.list
      });
    });
  }
};
window.addEventListener("load", usr.list);
