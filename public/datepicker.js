var picker = {
  // (A) ATTACH DATEPICKER TO TARGET
  // target : datepicker will populate this field
  // container : datepicker will be generated in this container
  // startmon : start on Monday (default false)
  // disableday : array of days to disable, e.g. [2,7] to disable Tue and Sun
  attach : function (opt) {
    // (A1) CREATE NEW DATEPICKER
    var dp = document.createElement("div");
    dp.dataset.target = opt.target;
    dp.dataset.startmon = opt.startmon ? "1" : "0";
    dp.classList.add("picker");
    if (opt.disableday) {
      dp.dataset.disableday = JSON.stringify(opt.disableday);
    }

    // (A2) DEFAULT TO CURRENT MONTH + YEAR - NOTE: UTC+0!
    var today = new Date(),
        thisMonth = today.getUTCMonth(), // Note: Jan is 0
        thisYear = today.getUTCFullYear(),
        months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                  "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    // (A3) MONTH SELECT
    var select = document.createElement("select"),
        option = null;
    select.classList.add("picker-m");
    for (var mth in months) {
      option = document.createElement("option");
      option.value = parseInt(mth) + 1;
      option.text = months[mth];
      select.appendChild(option);
    }
    select.selectedIndex = thisMonth;
    select.addEventListener("change", function(){ picker.draw(this); });
    dp.appendChild(select);

    // (A4) YEAR SELECT
    var yRange = 10; // Year range to show, I.E. from thisYear-yRange to thisYear+yRange
    select = document.createElement("select");
    select.classList.add("picker-y");
    for (var y = thisYear-yRange; y < thisYear+yRange; y++) {
      option = document.createElement("option");
      option.value = y;
      option.text = y;
      select.appendChild(option);
    }
    select.selectedIndex = yRange;
    select.addEventListener("change", function(){ picker.draw(this); });
    dp.appendChild(select);

    // (A5) DAY SELECT
    var days = document.createElement("div");
    days.classList.add("picker-d");
    dp.appendChild(days);

    // (A6) ATTACH DATE PICKER TO TARGET CONTAINER + DRAW THE DATES
    picker.draw(select);

    // (A6-I) INLINE DATE PICKER
    if (opt.container) { document.getElementById(opt.container).appendChild(dp); }

    // (A6-P) POPUP DATE PICKER
    else {
      // (A6-P-1) MARK THIS AS A "POPUP"
      var uniqueID = 0;
      while (document.getElementById("picker-" + uniqueID) != null) {
        uniqueID = Math.floor(Math.random() * (100 - 2)) + 1;
      }
      dp.dataset.popup = "1";
      dp.dataset.dpid = uniqueID;

      // (A6-P-2) CREATE WRAPPER
      var wrapper = document.createElement("div");
      wrapper.id = "picker-" + uniqueID;
      wrapper.classList.add("picker-wrap");
      wrapper.appendChild(dp);

      // (A6-P-3) ATTACH ONCLICK TO SHOW/HIDE DATEPICKER
      var target = document.getElementById(opt.target);
      target.dataset.dp = uniqueID;
      target.readOnly = true; // Prevent onscreen keyboar on mobile devices
      target.onfocus = function () {
        document.getElementById("picker-" + this.dataset.dp).classList.add("show");
      };
      wrapper.addEventListener("click", function (evt) {
        if (evt.target.classList.contains("picker-wrap")) {
          this.classList.remove("show");
        }
      });

      // (A6-P-4) ATTACH POPUP DATEPICKER TO BODY
      document.body.appendChild(wrapper);
    }
  },


  // (B) DRAW THE DAYS IN MONTH
  // el : HTML reference to either year or month selector
  draw : function (el) {
    // (B1) GET DATE PICKER COMPONENTS
    var parent = el.parentElement,
        year = parent.getElementsByClassName("picker-y")[0].value,
        month = parent.getElementsByClassName("picker-m")[0].value,
        days = parent.getElementsByClassName("picker-d")[0];

    // (B2) DATE RANGE CALCULATION - NOTE: UTC+0!
    var daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate(),
        startDay = new Date(Date.UTC(year, month-1, 1)).getUTCDay(), // Note: Sun = 0
        endDay = new Date(Date.UTC(year, month-1, daysInMonth)).getUTCDay(),
        startDay = startDay==0 ? 7 : startDay,
        endDay = endDay==0 ? 7 : endDay;

    // (B3) GENERATE DATE SQUARES (IN ARRAY FIRST)
    var squares = [],
        disableday = null;
    if (parent.dataset.disableday) {
      disableday = JSON.parse(parent.dataset.disableday);
    }

    // (B4) EMPTY SQUARES BEFORE FIRST DAY OF MONTH
    if (parent.dataset.startmon=="1" && startDay!=1) {
      for (var i=1; i<startDay; i++) { squares.push("B"); }
    }
    if (parent.dataset.startmon=="0" && startDay!=7) {
      for (var i=0; i<startDay; i++) { squares.push("B"); }
    }

    // (B5) DAYS OF MONTH
    // (B5-1) ALL DAYS ENABLED, JUST ADD
    if (disableday==null) {
      for (var i=1; i<=daysInMonth; i++) { squares.push([i, false]);  }
    }

    // (B5-2) SOME DAYS DISABLED
    else {
      var thisday = startDay;
      for (var i=1; i<=daysInMonth; i++) {
        // CHECK IF DAY IS DISABLED
        var disabled = disableday.includes(thisday);
        // DAY OF MONTH, DISABLED
        squares.push([i, disabled]); 
        // NEXT DAY
        thisday++;
        if (thisday==8) { thisday = 1; }
      }
    }

    // (B6) EMPTY SQUARES AFTER LAST DAY OF MONTH
    if (parent.dataset.startmon=="1" && endDay!=7) {
      for (var i=endDay; i<7; i++) { squares.push("B"); }
    }
    if (parent.dataset.startmon=="0" && endDay!=6) {
      for (var i=endDay; i<(endDay==7?13:6); i++) { squares.push("B"); }
    }

    // (B7) DRAW HTML
    var daynames = ["Mon", "Tue", "Wed", "Thur", "Fri", "Sat"];
    if (parent.dataset.startmon=="1") { daynames.push("Sun"); }
    else { daynames.unshift("Sun"); }

    // (B7-1) HEADER
    var table = document.createElement("table"),
        row = table.insertRow()
        cell = null;
    row.classList.add("picker-d-h");
    for (let d of daynames) {
      cell = row.insertCell();
      cell.innerHTML = d;
    }

    // (B7-2) DATE CELLS
    var total = squares.length;
    row = table.insertRow();
    for (var i=0; i<total; i++) {
      if (i!=total && i%7==0) { row = table.insertRow(); }
      cell = row.insertCell();
      if (squares[i] == "B") { 
        cell.classList.add("picker-d-b"); 
      } else { 
        cell.innerHTML = squares[i][0]; 
        // NOT ALLOWED TO CHOOSE THIS DAY
        if (squares[i][1]) {
          cell.classList.add("picker-d-dd");
        }
        // ALLOWED TO CHOOSE THIS DAY
        else {
          cell.classList.add("picker-d-d");
          cell.addEventListener("click", function(){ picker.pick(this); });
        }
      }
    }

    // (B7-3) ATTACH NEW CALENDAR TO DATEPICKER
    days.innerHTML = "";
    days.appendChild(table);
  },

  // (C) CHOOSE A DATE
  // el : HTML reference to selected date cell
  pick : function (el) {
    // (C1) GET ALL COMPONENTS
    var parent = el.parentElement;
    while (!parent.classList.contains("picker")) {
      parent = parent.parentElement;
    }

    // (C2) GET FULL SELECTED YEAR MONTH DAY
    var year = parent.getElementsByClassName("picker-y")[0].value,
        month = parent.getElementsByClassName("picker-m")[0].value,
        day = el.innerHTML;

    // YYYY-MM-DD FORMAT - CHANGE FORMAT HERE IF YOU WANT !
    if (parseInt(month)<10) { month = "0" + month; }
    if (parseInt(day)<10) { day = "0" + day; }
    var fullDate = year + "-" + month + "-" + day;

    // (C3) UPDATE SELECTED DATE
    document.getElementById(parent.dataset.target).value = fullDate;

    // (C4) POPUP ONLY - CLOSE THE POPUP
    if (parent.dataset.popup == "1") {
      document.getElementById("picker-" + parent.dataset.dpid).classList.remove("show");
    }
  }
};