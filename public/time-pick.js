var tp = {
  // (A) CREATE TIME PICKER
  instances : [], // All time picker instances
  init: function (wrapper, target) {
  // wrapper - container to generate time picker into
  // target - optional, target input field for inline time pickers

    // (A1) CREATE NEW INSTANCE + "GET ID"
    let id = tp.instances.length;
    tp.instances.push({ wrap : wrapper });
    let inst = tp.instances[id];
    if (target != undefined) { inst.target = target; }

    // (A2) TIME PICKER ITSELF
    let picker = document.createElement("div");
    picker.className = "tp";
    inst.wrap.appendChild(picker);

    // (A3) *THE* BUTTONATOR - HR + MIN + AM/PM
    let buttonator = function (segment) {
      // Button Container
      let box = document.createElement("div");
      box.className = "tp-box";

      // Up Button
      let up = document.createElement("div");
      up.innerHTML = "&#65087;";
      up.className = "tp-up";

      // Current Value
      let val = document.createElement("input");
      val.type = "text";
      val.disabled = true;
      val.className = "tp-val";
      if (segment == "hr") { val.value = "01"; }
      else if (segment == "min") { val.value = "00"; }
      else { val.value = "AM"; }
      inst[segment] = val;

      // Down Button
      let down = document.createElement("div");
      down.innerHTML = "&#65088;";
      down.className = "tp-up";

      // Button click handlers
      if (segment == "ap") {
        up.addEventListener("click", function(){
          tp.sap(id);
        });
        down.addEventListener("click", function(){
          tp.sap(id);
        });
      } else {
        up.addEventListener("mousedown", function(){
          tp.spin(id, segment, 1);
        });
        down.addEventListener("mousedown", function(){
          tp.spin(id, segment, 0);
        });
        up.addEventListener("mouseup", tp.sspin);
        up.addEventListener("mouseleave", tp.sspin);
        down.addEventListener("mouseup", tp.sspin);
        down.addEventListener("mouseleave", tp.sspin);
      }

      // Append all the buttons
      box.appendChild(up);
      box.appendChild(val);
      box.appendChild(down);
      picker.appendChild(box);
    };
    buttonator("hr");
    buttonator("min");
    buttonator("ap");
    
    // (A4) OK BUTTON
    let ok = document.createElement("input");
    ok.type = "button";
    ok.value = "OK";
    ok.className = "tp-ok";
    ok.addEventListener("click", function(){
      tp.set(id);
    });
    picker.appendChild(ok);
  },

  // (B) "HOLD TO SPIN" FOR HOUR + MIN
  stimer : null, // Spin timer
  ssensitive : 100, // lower will spin faster
  spin : function (id, segment, direction) {
    if (tp.stimer == null) {
      tp.sid = id;
      tp.sseg = segment;
      tp.smax = segment == "hr" ? 12 : 59;
      tp.smin = segment == "hr" ? 1 : 0;
      tp.sdir = direction;
      tp.hmspin();
      tp.stimer = setInterval(tp.hmspin, tp.ssensitive);
    }
  },

  // (C) STOP HR/MIN SPIN
  sspin : function () {
    if (tp.stimer != null) {
      clearInterval(tp.stimer);
      tp.stimer = null;
    }
  },

  // (D) SPIN HR OR MIN
  sid : null, // Instance ID
  sseg : null, // Segment to spin
  smax : null, // Maximum value (12 for hr, 59 for min)
  smin : null, // Minimum value (1 for hr, 0 for min)
  sdir : null, // Spin direction
  hmspin : function () {
    // (D1) CURRENT VALUE
    let box = tp.instances[tp.sid][tp.sseg],
        cv = parseInt(box.value);

    // (D2) SPIN!
    if (tp.sdir) { cv++; }
    else { cv--; }
    if (cv < tp.smin) { cv = tp.smin; }
    if (cv > tp.smax) { cv = tp.smax; }
    if (cv < 10) { cv = "0" + cv; }

    // (D3) UPDATE DISPLAY
    box.value = cv;
  },
  
  // (E) SPIN AM/PM
  sap : function (id) {
    // (E1) GET CURRENT VALUE
    let box = tp.instances[id]["ap"],
        cv = box.value;

    // (E2) SET VALUE
    box.value = (cv == "AM") ? "PM" : "AM";
  },
  
  // (F) SET SELECTED TIME
  set : function (id) {
    // (F1) GET + FORMAT HH:MM AM/PM
    let inst = tp.instances[id],
    timestamp = tp.instances[id]["hr"].value + ":" +
                tp.instances[id]["min"].value + " " +
                tp.instances[id]["ap"].value;

    // (F2) SET TIMESTAMP
    inst.target.value = timestamp;

    // (F3) CLOSE TIME PICKER (POPUP ONLY)
    if (id==0) {
      inst.wrap.classList.remove("show");
    }
  },

  // (G) ATTACH TIME PICKER TO TARGET
  attach : function (opt) {
  // target - input field
  // wrap - optional, inline time picker

    // (G1) SET INPUT FIELD READONLY
    let target = document.getElementById(opt.target);
    target.readOnly = true;
    
    // (G2) INLINE VERSION - GENERATE TIME PICKER HTML
    if (opt.wrap) {
      tp.init(document.getElementById(opt.wrap), target);
    }
    
    // (G3) POPUP VERSION - SHOW POPUP ON CLICK
    else {
      target.addEventListener("click", function(){
        // Get + set popup time
        let cv = this.value;
        if (cv == "") {
          tp.instances[0].hr.value = "01";
          tp.instances[0].min.value = "00";
          tp.instances[0].ap.value = "AM";
        } else {
          tp.instances[0].hr.value = cv.substring(0, 2);
          tp.instances[0].min.value = cv.substring(3, 5);
          tp.instances[0].ap.value = cv.substring(6, 8);
        }
        // Set target + show popup
        tp.instances[0].target = target;
        tp.instances[0].wrap.classList.add("show");
      });
    }
  }
};

// (H) CREATE "DEFAULT" POPUP TIME PICKER ON LOAD
window.addEventListener("DOMContentLoaded", function(){
  let pop = document.createElement("div");
  document.body.appendChild(pop);
  pop.id = "tp-pop";
  tp.init(pop);
});