var cla = {
  // (A) LIST () : SHOW ALL CLASSES
  pg : 1, // CURRENT PAGE
  list : function ()  {
    common.ajax({
      url : urlroot + "attend-ajax-list",
      data : { pg : cla.pg },
      target : "cla-list"
    });
  },

  // (B) GOTOPAGE () : GO TO PAGE
  //  pg : page number
  goToPage : function (pg) {
    cla.pg = pg;
    cla.list();
  }
}
window.addEventListener("load", cla.list);