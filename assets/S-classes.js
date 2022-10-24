var classes = {
  // (A) SHOW ALL CLASSES
  pg : 1, // current page
  range : "", // search range
  date : "", // search date
  list : () => cb.load({
    page : "s/class/list",
    target : "class-list",
    data : {
      page : classes.pg,
      range : classes.range,
      date : classes.date
    }
  }),

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : pg => { if (pg!=classes.pg) {
    classes.pg = pg;
    classes.list();
  }},

  // (C) SEARCH FORM TOGGLE
  stog : () => {
    // (C1) GET ELEMENTS
    var range = document.getElementById("search-range"),
        date = document.getElementById("search-date");

    // (C2) TOGGLE DATE FIELD
    date.disabled = range.value=="" ? true : false ;
  },

  // (D) SEARCH FOR CLASS
  search : () => {
    classes.range = document.getElementById("search-range").value;
    classes.date = document.getElementById("search-date").value;
    classes.pg = 1;
    classes.list();
    return false;
  }
};
window.addEventListener("load", classes.list);