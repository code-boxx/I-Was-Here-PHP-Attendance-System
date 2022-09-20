var cimport = {
  // (A) LOAD "IMPORT COURSES" PAGE
  init : () => {
    cb.page(1);
    cb.load({
      page : "a/course/import",
      target : "cb-page-2"
    });
    return false;
  },

  // (B) READ CSV FILE
  read : () => {
    // (B1) HTML ELEMENTS
    let hSelect = document.getElementById("course-import-select"),
    hFile = document.getElementById("course-import-file"),
    hTable = document.getElementById("course-import-table"),
    hList = document.getElementById("course-import-list");
    hSelect.classList.add("d-none");
    hTable.classList.remove("d-none");
    
    // (B2) READ SELECTED FILE
    let reader = new FileReader(),
    vDate = /^\d{4}-\d{2}-\d{2}$/,
    csv = hFile.files[0], row, col, err, valid = false;
    
    reader.addEventListener("loadend", () => { try {
      // (B2-1) READ ROW-BY-ROW INTO HTML + CHECK VALID
      csv = reader.result.split("\r\n");
      csv.forEach(r => {
        r = r.split(",");
        row = document.createElement("tr");
        if (r.length != 5) {
          row.className = "table-danger fw-bold";
          row.innerHTML = `<td colspan="5">?</td><td>Invalid Row</td>`;
        } else {
          err = null;
          for (let i=0; i<5; i++) {
            col = document.createElement("td");
            col.innerHTML = r[i]==null?"":r[i];
            row.appendChild(col);
          }
          if (err==null && r[0]=="") { err = "Invalid Code"; }
          if (err==null && r[1]=="") { err = "Invalid Name"; }
          if (err==null && !vDate.test(r[3])) { err = "Invalid Start"; }
          if (err==null && !vDate.test(r[4])) { err = "Invalid End"; }
          if (err==null) { if (new Date(r[4]) < new Date(r[3])) { err = "End Earlier Than Start"; }}
          col = document.createElement("td");
          col.innerHTML = err==null ? "" : err;
          row.appendChild(col);
          if (err==null) {
            row.className = "valid";
            valid = true;
          } else { row.className = "table-danger fw-bold"; }
        }
        hList.appendChild(row);
      });

      // (B2-2) START BUTTON
      if (valid) {
        row = document.createElement("tr");
        row.innerHTML = `<td colspan="6">
          <button id="course-import-go" class="btn btn-primary" onclick="cimport.go(1)">Start Import</button>
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
      document.getElementById("course-import-go").disabled = true;
      cb.loading(true);
    }

    // (C2) IMPORT ENTRY
    let row = document.querySelector("#course-import-list .valid");
    if (row!=null) {
      let col = row.querySelectorAll("td");
      cb.api({
        mod : "courses", req : "import",
        passmsg : false, loading : false, nofail : true,
        data : {
          code : col[0].innerHTML,
          name : col[1].innerHTML,
          desc : col[2].innerHTML,
          start : col[3].innerHTML,
          end : col[4].innerHTML
        },
        onpass : () => {
          row.classList.remove("valid");
          col[5].innerHTML = "OK";
          cimport.go();
        },
        onfail : msg => {
          col[5].innerHTML = msg;
          cimport.go();
        }
      });
    }

    // (C3) ALL DONE
    else {
      let btn = document.getElementById("course-import-go");
      btn.innerHTML = "Done - Go Back";
      btn.onclick = () => cb.page(0);
      btn.disabled = false;
      course.list(true);
      cb.loading(false);
    }
  }
};