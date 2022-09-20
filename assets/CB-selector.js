var selector = {
  // (A) SETTINGS & PROPERTIES
  min : 2, // minimum 2 characters to trigger suggestions
  delay : 500, // delay before suggestion in ms
  open : null, // currently open suggestion box

  // (B) ATTACH SEARCH-SELECTOR
  //  field : target html field
  //  mod : api module to fetch data from
  //  req : api request to fetch data from
  //  data : object, additional data to send
  //  pick : function to call on pick
  attach : instance => {
    // (B1) GENERATE HTML
    instance.wrapper = document.createElement("div"); // wrapper
    instance.suggest = document.createElement("ul"); // suggestion box
    instance.wrapper.className = "selWrap";
    instance.suggest.className = "selSuggest list-group d-none";

    // (B2) ATTACH HTML
    instance.field.setAttribute("autocomplete", "off");
    instance.field.parentElement.insertBefore(instance.wrapper, instance.field);
    instance.wrapper.appendChild(instance.field);
    instance.wrapper.appendChild(instance.suggest);

    // (B3) PROPERTIES
    instance.timer = null; // suggestion timer
    if (instance.data==undefined) { instance.data = {}; }

    // (B4) CLOSE THIS SUGGESTION BOX
    instance.close = () => {
      window.clearTimeout(instance.timer);
      instance.suggest.innerHTML = "";
      instance.suggest.classList.add("d-none");
    };

    // (B5) FETCH DATA FROM API
    instance.fetch = () => {
      // (B5-1) JUST IN CASE...
      window.clearTimeout(instance.timer);

      // (B5-2) DATA
      let data = instance.data;
      data.search = instance.field.value;

      // (B5-3) API CALL
      cb.api({
        mod : instance.mod, req : instance.req,
        data : data,
        loading : false,
        passmsg : false,
        onpass : res => {
          // (B5-4) NO RESULTS
          if (res.data==null) { instance.close(); }

          // (B5-5) DRAW RESULTS
          else {
            instance.suggest.innerHTML = "";
            for (let r of res.data) {
              let row = document.createElement("li");
              row.className = "list-group-item";
              row.innerHTML = r.d;
              row.onclick = () => {
                instance.field.value = r.v;
                instance.close();
                instance.pick(r.d, r.v);
              };
              instance.suggest.appendChild(row);
            }
            instance.suggest.classList.remove("d-none");
            selector.open = instance; // set currently open
          }
        }
      });
    };

    // (B6) LISTEN TO KEY PRESS
    instance.field.addEventListener("keyup", evt => {
      // (B6-1) CLEAR OLD TIMER & SUGGESTION BOX
      instance.close();

      // (B6-2) CREATE NEW TIMER - FETCH DATA FROM SERVER
      if (instance.field.value.length >= selector.min) {
        instance.timer = setTimeout(instance.fetch, selector.delay);
      }
    });
  },

  // (C) AUTOCLOSE SUGGESTION BOX ON CLICK ELSEWHERE
  checkclose : evt => { if (selector.open != null) {
    let instance = selector.open;
    if (instance.wrapper.contains(evt.target)==false &&
        instance.field.contains(evt.target)==false) {
      instance.close();
      selector.open = null;
    }
  }}
};
window.addEventListener("click", selector.checkclose);