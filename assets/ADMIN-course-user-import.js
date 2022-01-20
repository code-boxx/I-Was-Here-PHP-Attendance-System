var cuserin = {
  // (A) LOAD "IMPORT USERS" PAGE
  init : () => {
    cb.page(3);
    cb.load({
      page : "course/user/import",
      target : "cb-page-3",
      onload : cuserin.read
    });
  },

  // (B) READ CSV FILE
  read : () => {
    // (B1) HTML ELEMENTS
    let hField = document.getElementById("course-user-import"),
        hList = document.getElementById("user-in-list"),
        vMail = new RegExp("[a-z0-9]+@[a-z]+\.[a-z]{2,3}"),
        csv = hField.files[0], row, col, valid, hasValid = false;
    hField.value = "";

    // (B2) READ SELECTED FILE
    let reader = new FileReader();
    reader.addEventListener("loadend", () => {
      try {
        // READ ROW-BY-ROW INTO HTML + CHECK VALID
        csv = reader.result.split("\r\n");
        csv.forEach(r => {
          row = document.createElement("div");
          row.className = "d-flex align-items-center border p-2";
          col = document.createElement("div");
          col.className = "col";
          col.innerHTML = r;
          row.appendChild(col);
          valid = vMail.test(r);
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
          row.className = "d-flex align-items-center border p-2";
          col = document.createElement("div");
          col.innerHTML = "<button id='in-user-go' class='btn btn-primary' onclick='cuserin.go(1)'>Start Import</button>";
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
      document.getElementById("in-user-go").disabled = true;
      cb.loading(true);
    }

    // (C2) IMPORT ENTRY
    let entry = document.querySelector("#user-in-list .valid"),
        col, data;
    if (entry!=null) {
      entry.classList.remove("valid");
      col = entry.querySelectorAll(".col");
      cb.api({
        mod : "courses",
        req : "addUser",
        data : {
          cid : cuser.id,
          uid : col[0].innerHTML
        },
        passmsg : false,
        loading : false,
        nofail : true,
        onpass : () => {
          col[1].innerHTML = "OK";
          cuserin.go();
        },
        onfail : (msg) => {
          col[1].innerHTML = msg;
          cuserin.go();
        }
      });
      col[1].innerHTML = "OK";
      cuserin.go();
    }

    // (C3) ALL DONE
    else {
      let btn = document.getElementById("in-user-go");
      btn.innerHTML = "Done - Go Back";
      btn.onclick = () => { cb.page(2); };
      btn.disabled = false;
      cuser.list();
      cb.loading(false);
    }
  }
};
