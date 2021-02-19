/*** [I] COURSE MANAGEMENT ***/
var cor = {
  // (A) LIST () : SHOW ALL COURSES
  pg : 1, // CURRENT PAGE
  find : "", // CURRENT SEARCH
  list : function ()  {
    common.ajax({
      url : urladmin + "courses-ajax-list",
      target : "cor-list",
      data : {
        pg : cor.pg,
        search : cor.find
      }
    });
  },

  // (B) GOTOPAGE () : GO TO PAGE
  //  pg : page number
  goToPage : function (pg) { if (pg!=cor.pg) {
    cor.pg = pg;
    cor.list();
  }},

  // (C) SEARCH () : SEARCH COURSES
  search : function () {
    cor.find = document.getElementById("c-search").value;
    cor.pg = 1;
    cor.list();
    return false;
  },

  // (D) ADDEDIT () : SHOW ADD/EDIT DOCKET
  //  code: course code, for edit only
  addEdit : function (code) {
    common.ajax({
      url : urladmin + "courses-ajax-form",
      data : { code : code ? code : "" },
      target : "pageB",
      onpass : function () { common.page("B"); }
    });
  },

  // (E) SAVE () : SAVE COURSE
  save : function () {
    // (E1) GET DATA
    var data = {
      reqT : "save",
      code : document.getElementById("course_code").value,
      name : document.getElementById("course_name").value,
      desc : document.getElementById("course_desc").value
    };
    var ocode = document.getElementById("old_course_code").value;
    if (ocode!="") { data.ocode = ocode; }

    // (E2) AJAX SAVE
    common.ajax({
      url : urlapi + "Course",
      data : data,
      apass : "Course save OK",
      onpass : function (res) {
        cor.list();
        common.page('A');
      }
    });
    return false;
  },
  
  // (F) DEL () : DELETE COURSE
  //  code : course ID
  del : function (code) { if (confirm("Delete course?")) {
    common.ajax({
      url : urlapi + "Course",
      data : {
        reqT : "del",
        code : code
      },
      apass : "Course deleted",
      onpass : function (res) {
        cor.list();
      }
    });
  }}
}

/*** [II] IMPORT COURSES ***/
var cimp = {
  // (G) IMSHOW () : SHOW IMPORT DOCKET
  show : function () {
    common.ajax({
      url : urladmin + "courses-ajax-cimpA",
      target : "pageB",
      onpass : function () { common.page("B"); }
    });
  },
  
  // (H) IMUPLOAD () : UPLOAD IMPORT FILE
  upload : function () {
    common.ajax({
      url : urladmin + "courses-ajax-cimpB",
      target : "pageC",
      data : { csv : document.getElementById("imp_file").files[0] },
      onpass : function () { common.page("C"); }
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
    cimp.impAll = document.getElementsByClassName("improw").length;
    cimp.impNow = 0;
    cimp.import();
  },
  
  // (J) IMPORT () : AJAX IMPORT
  impAll : 0, impNow : 0,
  import : function () {
    // (J1) "WORKING ON THIS ENTRY NOW"
    let stat = document.getElementById("istat"+cimp.impNow);
    stat.innerHTML = "Importing...";

    // (J2) AJAX SAVE
    common.ajax({
      url : urlapi + "Course",
      block : false,
      apass : false,
      afail : false,
      data : {
        reqT : "save",
        code : document.getElementById("icode"+cimp.impNow).innerHTML,
        name : document.getElementById("iname"+cimp.impNow).innerHTML,
        desc : document.getElementById("idesc"+cimp.impNow).innerHTML
      },
      onpass : function (res) { 
        stat.innerHTML = res.message; 
        cimp.next();
      },
      onfail : function (res) {
        stat.innerHTML = res.message; 
        cimp.next();
      }
    });
  },
  
  // (K) NEXT () : "HELPER FUNCTION" FOR IMPORT()
  next : function () {
    cimp.impNow++;
    if (cimp.impNow < cimp.impAll) {
      cimp.import();
    } else {
      cor.list();
      document.getElementById("impback").disabled = false;
      document.getElementById("impgo").value = "Done";
      common.toast("Import complete");
    }
  }
};

/*** [III] ATTACH STUDENTS ***/
var stu = {
  // (L) ATTACH () : ATTACH/DETACH STUDENTS TO COURSE
  //  code : course ID
  attach : function (code) {
    stu.code = code;
    stu.pg = 1;
    common.ajax({
      url : urladmin + "courses-ajax-students",
      data : { code : code },
      target : "pageB",
      onpass : function () {
        common.page("B");
        stu.list();
      }
    });
  },

  // (M) LIST () : SHOW LIST OF STUDENTS FOR CURRENT COURSE
  code : "", // CURRENT SELECTED COURSE
  pg : 1, // CURRENT PAGE
  list : function () {
    common.ajax({
      url : urladmin + "courses-ajax-students-list",
      target : "stu-list",
      data : {
        pg : stu.pg,
        code : stu.code
      }
    });
  },

  // (N) GOTOPAGE () : GO TO PAGE
  goToPage : function (pg) { if (pg != stu.pg) {
    stu.pg = pg;
    stu.list();
  }},

  // (O) ADD() : ADD STUDENTS TO COURSE
  add : function () {
    common.ajax({
      url : urlapi + "Course",
      data : {
        reqT : "stuAdd",
        code : stu.code,
        email : document.getElementById("stuadd").value
      },
      apass : "Student added",
      onpass : function () {
        document.getElementById("stuadd").value = "";
        stu.list();
      }
    });
    return false;
  },

  // (P) DEL () : REMOVE STUDENT FROM COURSE
  //  id : user ID
  del : function (id) { if (confirm("Remove student?")) {
    common.ajax({
      url : urlapi + "Course",
      data : {
        reqT : "stuDel",
        code : stu.code,
        id : id
      },
      apass : "Student removed",
      onpass : function () {
        stu.list();
      }
    });
  }}
};

/*** [IV] IMPORT STUDENTS TO COURSE ***/
var simp = {
  // (Q) SHOW() : SHOW STUDENT IMPORT DOCKET
  show : function () {
    common.ajax({
      url : urladmin + "courses-ajax-simpA",
      target : "pageC",
      onpass : function () { common.page("C"); }
    });
  },
  
  // (R) UPLOAD () : UPLOAD STUDENT IMPORT FILE
  upload : function () {
    common.ajax({
      url : urladmin + "courses-ajax-simpB",
      target : "pageD",
      data : { csv : document.getElementById("imp_file").files[0] },
      onpass : function () { common.page("D"); }
    });
    return false;
  },
  
  // (S) GO () : PROCEED WITH IMPORT
  go : function ()  {
    // (S1) DISABLE "GO" + BACK BUTTON
    document.getElementById("impback").disabled = true;
    var go = document.getElementById("impgo");
    go.value = "Started - Do not reload page!";
    go.disabled = true;

    // (S2) IMPORT ONE-BY-ONE
    simp.impAll = document.getElementsByClassName("improw").length;
    simp.impNow = 0;
    simp.import();
  },
  
  // (T) IMPORT () : AJAX IMPORT
  impAll : 0, impNow : 0,
  import : function () {
    // (T1) "WORKING ON THIS ENTRY NOW"
    let stat = document.getElementById("istat"+simp.impNow);
    stat.innerHTML = "Importing...";

    // (T2) AJAX SAVE
    common.ajax({
      url : urlapi + "Course",
      block : false,
      apass : false,
      afail : false,
      data : {
        reqT : "stuAdd",
        code : stu.code,
        email : document.getElementById("imail"+simp.impNow).innerHTML
      },
      onpass : function (res) { 
        stat.innerHTML = res.message; 
        simp.next();
      },
      onfail : function (res) {
        stat.innerHTML = res.message; 
        simp.next();
      }
    });
  },

  // (U) NEXT () : "HELPER FUNCTION" FOR IMPORT()
  next : function () {
    simp.impNow++;
    if (simp.impNow < simp.impAll) {
      simp.import();
    } else {
      stu.list();
      document.getElementById("impback").disabled = false;
      document.getElementById("impgo").value = "Done";
      common.toast("Import complete");
    }
  }
};

window.addEventListener("load", cor.list);