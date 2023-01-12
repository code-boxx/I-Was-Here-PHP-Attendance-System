var cimport = {
  // (A) LOAD "IMPORT CLASS" PAGE
  init : () => {
    cb.page(1);
    cb.load({
      page : "a/class/import",
      target : "cb-page-2"
    });
    return false;
  },

  // (B) READ CSV FILE
  read : () => {
    // (B1) HTML ELEMENTS
    let hSelect = document.getElementById("class-import-select"),
    hFile = document.getElementById("class-import-file"),
    hTable = document.getElementById("class-import-table"),
    hList = document.getElementById("class-import-list");
    hSelect.classList.add("d-none");
    hTable.classList.remove("d-none");
    
    // (B2) READ SELECTED FILE
    let reader = new FileReader(),
    vMail = new RegExp("[a-z0-9]+@[a-z]+\.[a-z]{2,3}"),
    vDate = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/,
    csv = hFile.files[0], row, col, err, valid = false;

    reader.addEventListener("loadend", () => { try {
      // (B2-1) READ ROW-BY-ROW INTO HTML + CHECK VALID
      for (let r of CSV.parse(reader.result)) {
        row = document.createElement("tr");
        if (r.length != 4) {
          row.className = "table-danger fw-bold";
          row.innerHTML = `<td colspan="4">?</td><td>Invalid Row</td>`;
        } else {
          err = null;
          for (let i=0; i<4; i++) {
            col = document.createElement("td");
            col.innerHTML = r[i]==null?"":r[i];
            row.appendChild(col);
          }
          if (err==null && (r[0]=="" || r[0]==null)) { err = "Invalid Code"; }
          if (err==null && !vDate.test(r[1])) { err = "Invalid Date"; }
          if (err==null && !vMail.test(r[2])) { err = "Invalid Email"; }
          col = document.createElement("td");
          col.innerHTML = err==null ? "" : err;
          row.appendChild(col);
          if (err==null) {
            row.className = "valid";
            valid = true;
          } else { row.className = "table-danger fw-bold"; }
        }
        hList.appendChild(row);
      }

      // (B2-2) START BUTTON
      if (valid) {
        row = document.createElement("tr");
        row.innerHTML = `<td colspan="5">
          <button id="class-import-go" class="btn btn-primary" onclick="cimport.go(1)">Start Import</button>
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
      document.getElementById("class-import-go").disabled = true;
      cb.loading(true);
    }

    // (C2) IMPORT ENTRY
    let row = document.querySelector("#class-import-list .valid");
    if (row!=null) {
      let col = row.querySelectorAll("td");
      cb.api({
        mod : "classes", req : "import",
        passmsg : false, loading : false, nofail : true,
        data : {
          code : col[0].innerHTML,
          date : col[1].innerHTML,
          email : col[2].innerHTML,
          desc : col[3].innerHTML
        },
        onpass : () => {
          row.classList.remove("valid");
          col[4].innerHTML = "OK";
          cimport.go();
        },
        onfail : msg => {
          row.className = "table-danger fw-bold";
          col[4].innerHTML = msg;
          cimport.go();
        }
      });
    }

    // (C3) ALL DONE
    else {
      let btn = document.getElementById("class-import-go");
      btn.innerHTML = "Done - Go Back";
      btn.onclick = () => cb.page(0);
      btn.disabled = false;
      classes.list(true);
      cb.loading(false);
    }
  }
};