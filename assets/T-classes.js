var classes = {
  // (A) SHOW ALL CLASSES
  pg : 1, // CURRENT PAGE
  range : "", // SEARCH RANGE
  date : "", // SEARCH DATE
  list : () => {
    cb.load({
      page : "classes/list",
      target : "c-list",
      data : {
        page : classes.pg,
        range : classes.range,
        date : classes.date
      }
    });
  },

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : (pg) => { if (pg!=classes.pg) {
    classes.pg = pg;
    classes.list();
  }},

  // (C) SEARCH FORM TOGGLE
  stog : () => {
    // (C1) GET ELEMENTS
    var range = document.getElementById("s-range"),
        date = document.getElementById("s-date");

    // (C2) TOGGLE DATE FIELD
    date.disabled = range.value=="" ? true : false ;
  },

  // (D) SEARCH FOR CLASS
  search : () => {
    classes.range = document.getElementById("s-range").value;
    classes.date = document.getElementById("s-date").value;
    classes.pg = 1;
    classes.list();
    return false;
  }
};
window.addEventListener("load", classes.list);
