var cla = {
  // (A) LIST () : SHOW ALL CLASSES
  pg : 1, // CURRENT PAGE
  list : function ()  {
    common.ajax({
      url : urladmin + "classes-ajax-list",
      data : { pg : cla.pg },
      target : "cla-list"
    });
  },

  // (B) GOTOPAGE () : GO TO PAGE
  //  pg : page number
  goToPage : function (pg) {
    cla.pg = pg;
    cla.list();
  },

  // (C) ADDEDIT () : SHOW ADD/EDIT DOCKET
  // id : class ID, for edit only
  addEdit : function (id) {
    common.ajax({
      url : urladmin + "classes-ajax-form",
      data : { id : id ? id : "" },
      target : "pageB",
      onpass : function () {
        picker.attach({target:"class_date"});
        tp.attach({target:"class_time"});
        common.page("B"); 
      }
    });
  },

  // (D) SAVE () : SAVE CLASS
  save : function () {
    // (D1) DATE-TIME YOGA
    var cdate = document.getElementById("class_date").value,
        ctime = document.getElementById("class_time").value,
        chr = parseInt(ctime.substr(0, 2)),
        cmin = ctime.substr(3, 2),
        cap = ctime.substr(6, 2);
    if (cap=="AM" && chr==12) { chr = 0; }
    if (cap=="PM" && chr<12) { chr += 12; }
    if (chr < 10) { chr = "0" + chr; }
    var cfull = `${cdate} ${chr}:${cmin}:00`;

    // (D2) DATA
    var data = {
      reqT : "save",
      code : document.getElementById("course_code").value,
      id : document.getElementById("course_teacher").value,
      date : cfull,
      desc : document.getElementById("class_desc").value
    };
    var cid = document.getElementById("class_id").value;
    if (cid!="") { data.cid = cid; }

    // (D3) AJAX SAVE
    common.ajax({
      url : urlapi + "Class",
      data : data,
      apass : "Class save OK",
      onpass : function (res) {
        cla.list();
        common.page('A');
      }
    });
    return false;
  },

  // (E) DEL () : DELETE CLASS
  //  id : class ID
  del : function (id) { if (confirm("Delete class?")) {
    common.ajax({
      url : urlapi + "Class",
      data : {
        reqT : "del",
        id : id
      },
      apass : "Class deleted",
      onpass : function (res) {
        cla.list();
      }
    });
  }},

  // (F) ATTEND () : SHOW ATTENDANCE LIST
  //  id : class ID
  attend : function (id) {
    common.ajax({
      url : urladmin + "classess-ajax-attend",
      data : {id : id},
      target : "pageB",
      onpass : function () {
        common.page("B");
      }
    });
  },

  // (G) ATTUP() : UPDATE ATTENDANCE
  attup : function (uid) {
    common.ajax({
      url : urlapi + "Class",
      apass : "Attendance updated",
      data : {
        reqT : "attend",
        cid : document.getElementById("attclass").value,
        uid : uid,
        attend : document.getElementById("att-"+uid).checked ? 1 : 0
      }
    });
  }
};
window.addEventListener("load", cla.list);