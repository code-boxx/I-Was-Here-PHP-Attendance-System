var cla = {
  // (A) LIST () : SHOW ALL CLASSES
  pg : 1, // CURRENT PAGE
  list : function ()  {
    common.ajax({
      url : urladmin + "attend-ajax-list",
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
  
  // (C) ATTEND () : SHOW ATTENDANCE LIST
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

  // (D) ATTUP() : UPDATE ATTENDANCE
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
}
window.addEventListener("load", cla.list);