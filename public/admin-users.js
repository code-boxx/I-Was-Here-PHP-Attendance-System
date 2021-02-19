/*** [I] USER MANAGEMENT ***/
var usr = {
  // (A) LIST () : SHOW ALL USERS
  pg : 1, // CURRENT PAGE
  find : "", // CURRENT SEARCH
  frole : "", // CURRENT SEARCH ROLE
  list : function ()  {
    common.ajax({
      url : urladmin + "users-ajax-list",
      target : "user-list",
      data : {
        pg : usr.pg,
        role : usr.frole,
        search : usr.find
      }
    });
  },

  // (B) GOTOPAGE () : GO TO PAGE
  //  pg : page number
  goToPage : function (pg) { if (pg!=usr.pg) {
    usr.pg = pg;
    usr.list();
  }},

  // (C) SEARCH() : SEARCH USER
  search : function () {
    usr.find = document.getElementById("user-search").value;
    usr.frole = document.getElementById("user-search-role").value;
    usr.pg = 1;
    usr.list();
    return false;
  },

  // (D) ADDEDIT () : SHOW ADD/EDIT DOCKET
  // id : user ID, for edit only
  addEdit : function (id) {
    common.ajax({
      url : urladmin + "users-ajax-form",
      data : { id : id ? id : "" },
      target : "pageB",
      onpass : function () { common.page("B"); }
    });
  },

  // (E) SAVE () : SAVE USER
  save : function () {
    // (E1) GET DATA
    var data = {
      reqT : "save",
      name : document.getElementById("user_name").value,
      email : document.getElementById("user_email").value,
      role : document.getElementById("user_role").value
    };
    var id = document.getElementById("user_id").value;
    var pass = document.getElementById("user_password").value;
    if (id!="") { data.id = id; }
    if (pass!="") { data.pass = pass; }

    // (E2) AJAX
    common.ajax({
      url : urlapi + "User",
      data : data,
      apass : "User save OK",
      onpass : function () {
        usr.list();
        common.page('A');
      }
    });
    return false;
  },

  // (F) DEL () : DELETE USER
  // id : user ID
  del : function (id) { if (confirm("Delete user?")) {
    common.ajax({
      url : urlapi + "User",
      data : {
        reqT : "del",
        id : id
      },
      apass : "User deleted",
      onpass : function () {
        usr.list();
        common.page('A');
      }
    });
  }}
};

/*** [II] USER IMPORT ***/
var uimp = {
  // (G) SHOW () : SHOW IMPORT DOCKET
  show : function () {
    common.ajax({
      url : urladmin + "users-ajax-impA",
      target : "pageB",
      onpass : function () { common.page('B'); }
    });
  },

  // (H) UPLOAD () : UPLOAD IMPORT FILE
  upload : function () {
    common.ajax({
      url : urladmin + "users-ajax-impB",
      target : "pageC",
      data : { 
        role : document.getElementById("imp_role").value,
        csv : document.getElementById("imp_file").files[0] 
      },
      onpass : function () { common.page('C'); }
    });
    return false;
  },

  // (I) GO () : PROCEED WITH IMPORT
  go : function ()  {
    // (I1) DISABLE "GO" + BACK BUTTON
    document.getElementById("impback").disabled = true;
    var go = document.getElementById("impgo");
    go.value = "Started - Do not reload page!";
    go.disabled = true;

    // (I2) IMPORT ONE-BY-ONE
    uimp.impAll = document.getElementsByClassName("improw").length;
    uimp.impNow = 0;
    uimp.import();
  },
  
  // (J) IMPORT () : AJAX IMPORT
  impAll : 0, impNow : 0,
  import : function () {
    // (J1) "WORKING ON THIS ENTRY NOW"
    let stat = document.getElementById("istat"+uimp.impNow);
    stat.innerHTML = "Importing...";

    // (J2) AJAX SAVE
    common.ajax({
      url : urlapi + "User",
      block : false,
      apass : false,
      afail : false,
      data : {
        reqT : "save",
        name : document.getElementById("iname"+uimp.impNow).innerHTML,
        email : document.getElementById("imail"+uimp.impNow).innerHTML,
        pass : document.getElementById("ipass"+uimp.impNow).innerHTML,
        role : document.getElementById("imp_role").value
      },
      onpass : function (res) { 
        stat.innerHTML = res.message; 
        uimp.next();
      },
      onfail : function (res) {
        stat.innerHTML = res.message; 
        uimp.next();
      }
    });
  },
  
  // (K) NEXT () : "HELPER FUNCTION" FOR IMPORT()
  next : function () {
    uimp.impNow++;
    if (uimp.impNow < uimp.impAll) {
      uimp.import();
    } else {
      usr.list();
      document.getElementById("impback").disabled = false;
      document.getElementById("impgo").value = "Done";
      common.toast("Import complete");
    }
  }
};
window.addEventListener("load", usr.list);