var uin = {
  // (A) LOAD "IMPORT USERS" PAGE
  init : () => {
    cb.page(2);
    cb.load({
      page : "users/import",
      target : "cb-page-2",
      onload : uin.read
    });
  },

  // (B) READ CSV FILE
  read : () => {
    // (B1) HTML ELEMENTS
    let hField = document.getElementById("cu-import"),
        hList = document.getElementById("user-in-list"),
        vMail = new RegExp("[a-z0-9]+@[a-z]+\.[a-z]{2,3}"),
        vRoles = ["A", "T", "S", "I"],
        csv = hField.files[0], row, col, valid, hasValid = false;
    hField.value = "";

    // (B2) READ SELECTED FILE
    let reader = new FileReader();
    reader.addEventListener("loadend", () => {
      try {
        // READ ROW-BY-ROW INTO HTML + CHECK VALID
        csv = reader.result.split("\r\n");
        csv.forEach(r => {
          r = r.split(",");
          row = document.createElement("div");
          row.className = "row p-1";
          valid = true;
          for (let i=0; i<4; i++) {
            if (r[i]=="") { valid = false; }
            col = document.createElement("div");
            col.className = "col";
            col.innerHTML = r[i];
            row.appendChild(col);
          }
          if (valid) { valid = vMail.test(r[1]); }
          if (valid) { valid = vRoles.includes(r[2]); }
          col = document.createElement("div");
          col.className = "col";
          col.innerHTML = (valid ? "" : "INVALID");
          row.appendChild(col);
          if (valid) {
            row.classList.add("valid");
            hasValid = true;
          }
          hList.appendChild(row);
        });

        // START BUTTON
        if (hasValid) {
          row = document.createElement("div");
          row.className = "row p-1";
          col = document.createElement("div");
          col.innerHTML = "<button id='u-in-go' class='btn btn-primary' onclick='uin.go(1)'>Start Import</button>";
          row.appendChild(col);
          hList.appendChild(row);
        }
      } catch (err) {
        cb.modal("Error opening CSV file", err.message)
        console.error(err);
      }
    });
    reader.readAsText(csv);
  },

  // (C) START IMPORT
  go : (first) => {
    // (C1) BLOCK SCREEN & DISABLE BUTTON ON INIT CALL
    if (first) {
      document.getElementById("u-in-go").disabled = true;
      cb.loading(true);
    }

    // (C2) IMPORT ENTRY
    let entry = document.querySelector("#user-in-list .valid"),
        col, data;
    if (entry!=null) {
      entry.classList.remove("valid");
      col = entry.querySelectorAll(".col");
      cb.api({
        mod : "users",
        req : "saveO",
        data : {
          name : col[0].innerHTML,
          email : col[1].innerHTML,
          role : col[2].innerHTML,
          password : col[3].innerHTML
        },
        passmsg : false,
        loading : false,
        nofail : true,
        onpass : () => {
          col[4].innerHTML = "OK";
          uin.go();
        },
        onfail : (msg) => {
          col[4].innerHTML = msg;
          uin.go();
        }
      });
    }

    // (C3) ALL DONE
    else {
      let btn = document.getElementById("u-in-go");
      btn.innerHTML = "Done - Go Back";
      btn.onclick = () => { cb.page(1); };
      btn.disabled = false;
      usr.list(true);
      cb.loading(false);
    }
  }
};
