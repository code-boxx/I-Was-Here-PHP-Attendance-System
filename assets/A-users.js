var usr = {
  // (A) SHOW ALL USERS
  pg : 1, // current page
  find : "", // current search
  list : silent => {
    if (silent!==true) { cb.page(1); }
    cb.load({
      page : "A/users/list", target : "user-list",
      data : {
        page : usr.pg,
        search : usr.find
      }
    });
  },

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : pg => { if (pg!=usr.pg) {
    usr.pg = pg;
    usr.list();
  }},

  // (C) SEARCH USER
  search : () => {
    usr.find = document.getElementById("user-search").value;
    usr.pg = 1;
    usr.list();
    return false;
  },

  // (D) SHOW ADD/EDIT DOCKET
  // id : user ID, for edit only
  addEdit : id => cb.load({
    page : "A/users/form", target : "cb-page-2",
    data : { id : id ? id : "" },
    onload : () => cb.page(2)
  }),

  // (E) SAVE USER
  save : () => {
    // (E1) GET DATA
    var data = {
      name : document.getElementById("user_name").value,
      email : document.getElementById("user_email").value,
      password : document.getElementById("user_password").value,
      lvl : document.getElementById("user_level").value
    };
    var id = document.getElementById("user_id").value;
    if (id!="") { data.id = id; }

    // (E2) PASSWORD STRENGTH
    if (!cb.checker(data.password)) {
      cb.modal("Error", "Password must be at least 8 characters alphanumeric");
      return false;
    }

    // (E3) AJAX
    cb.api({
      mod : "users", act : "save",
      data : data,
      passmsg : "User Saved",
      onpass : usr.list
    });
    return false;
  },

  // (F) SUSPEND USER
  //  id : int, user ID
  //  confirm : boolean, confirmed delete
  del : id => cb.modal("Please confirm", "Suspend this user?", () => cb.api({
    mod : "users", act : "suspend",
    data : { id: id },
    passmsg : "User Account Suspended",
    onpass : usr.list
  })),

  // (G) IMPORT USERS
  import : () => im.init({
    name : "USERS",
    at : 2, back : 1,
    eg : "dummy-users.csv",
    api : { mod : "users", act : "import" },
    after : () => usr.list(true),
    cols : [
      ["Name", "name", true],
      ["Email", "email", true],
      ["Password", "password", true],
      ["Level (A,T,U,S)", "level", true]
    ]
  })
};

window.addEventListener("load", () => {
  usr.list();
  autocomplete.attach({
    target : document.getElementById("user-search"),
    mod : "autocomplete", act : "user",
    onpick : res => usr.search()
  });
});