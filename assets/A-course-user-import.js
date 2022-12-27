var uimport = {
  // (A) LOAD "IMPORT USERS" PAGE
  init : () => {
    cb.page(2);
    cb.load({
      page : "a/course/user/import",
      target : "cb-page-3"
    });
  },
  
  // (B) READ CSV FILE
  read : () => {
    // (B1) HTML ELEMENTS
    let hSelect = document.getElementById("user-import-select"),
        hFile = document.getElementById("user-import-file"),
        hTable = document.getElementById("user-import-table"),
        hList = document.getElementById("user-import-list");
    hSelect.classList.add("d-none");
    hTable.classList.remove("d-none");

    // (B2) READ SELECTED FILE
    let reader = new FileReader(),
    vMail = new RegExp("[a-z0-9]+@[a-z]+\.[a-z]{2,3}"),
    csv = hFile.files[0], row, col, valid = false;

    reader.addEventListener("loadend", () => { try {
      // (B2-1) READ ROW-BY-ROW INTO HTML + CHECK VALID
      for (let r of CSV.parse(reader.result)) {
        row = document.createElement("tr");
        if (r.length != 1) {
          row.className = "table-danger fw-bold";
          row.innerHTML = `<td>?</td><td>Invalid Row</td>`;
        } else {
          col = document.createElement("td");
          col.innerHTML = r[0];
          row.appendChild(col);
          col = document.createElement("td");
          if (vMail.test(r[0])) {
            valid = true;
            row.className = "valid";
          } else {
            row.className = "table-danger fw-bold";
            col.innerHTML = "INVALID";
          }
          row.appendChild(col);
        }
        hList.appendChild(row);
      }

      // (B2-2) START BUTTON
      if (valid) {
        row = document.createElement("tr");
        row.innerHTML = `<td colspan="2">
          <button id="user-import-go" class="btn btn-primary" onclick="uimport.go(1)">Start Import</button>
        </td>`;
        hList.appendChild(row);
      }
    } catch (err) {
      cb.modal("Error opening CSV file", err.message)
      console.error(err);
    }});
    reader.readAsText(csv);
  },
  
  // (C) START IMPORT
  go : first => {
    // (C1) BLOCK SCREEN & DISABLE BUTTON ON INIT CALL
    if (first) {
      document.getElementById("user-import-go").disabled = true;
      cb.loading(true);
    }
    
    // (C2) IMPORT ENTRY
    let row = document.querySelector("#user-import-list .valid");
    if (row!=null) {
      let col = row.querySelectorAll("td");
      cb.api({
        mod : "courses", req : "addUser",
        passmsg : false, loading : false, nofail : true,
        data : {
          cid : cuser.id,
          uid : col[0].innerHTML
        },
        onpass : () => {
          row.classList.remove("valid");
          col[1].innerHTML = "OK";
          uimport.go();
        },
        onfail : msg => {
          row.className = "table-danger fw-bold";
          col[1].innerHTML = msg;
          uimport.go();
        }
      });
    }
    
    // (C3) ALL DONE
    else {
      let btn = document.getElementById("user-import-go");
      btn.innerHTML = "Done - Go Back";
      btn.onclick = () => cb.page(1);
      btn.disabled = false;
      cuser.list();
      cb.loading(false);
    }
  }
};