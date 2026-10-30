var im = {
  // (A) PROPERTIES
  // (A1) "IMPORT PROFILE", SEE (B) BELOW
  api : null,
  previewApi : null,
  after : null,
  data : null,
  cols : null,
  rows : [],
  queue : [],

  // (A2) HTML ELEMENTS
  hSelect : null, // select file section
  hPick : null, // file picker
  hTable : null, // import section
  hList : null, // import list
  hBack : null, // back button
  hGo : null, // import go button
  hErrors : null,

  // (B) LOAD IMPORT PAGE
  // name : item, user, etc...
  // at : load import page to cb-page-at
  // back : cb-page-back to return to
  // eg : example csv file
  // api : api to call - { mod : "module", act: "action" }
  // after : function to call after import
  // data : additional data to append, if any
  // cols : required columns
  //  [NAME, PARAMETER, CHECK]
  //  [NAME, PARAMETER, true] : must be defined
  //  [NAME, PARAMETER, false] : can be empty
  //  [NAME, PARAMETER, "N"] : must be number
  init : profile => {
    // (B1) "SET IMPORT SESSION"
    im.api = profile.api;
    im.previewApi = profile.preview ? profile.preview : null;
    im.after = profile.after;
    im.data = profile.data ? profile.data : null ;
    im.cols = profile.cols;

    // (B2) LOAD IMPORT PAGE
    let cols = [];
    for (let c of profile.cols) { cols.push(c[0]); }
    cb.page(profile.at);
    cb.load({
      page : "A/import",
      target : "cb-page-" + profile.at,
      data : {
        name : profile.name,
        back : profile.back,
        eg : profile.eg,
        cols : JSON.stringify(cols)
      },
      onload : () => {
        im.hSelect = document.getElementById("import-select");
        im.hPick = document.getElementById("import-file");
        im.hTable = document.getElementById("import-table");
        im.hList = document.getElementById("import-list");
        im.hBack = document.getElementById("import-back");
        im.hGo = document.getElementById("import-go");
        im.hErrors = document.getElementById("import-errors");
      }
    });
  },

  // (C) READ CSV FILE
  read : () => {
    if (!im.hPick.files || im.hPick.files.length == 0) {
      cb.modal("No file selected", "Choose a CSV file to preview.");
      return;
    }
    if (im.hPick.files[0].size > 5 * 1024 * 1024) {
      cb.modal("File too large", "Choose a CSV file smaller than 5 MB.");
      return;
    }

    // (C1) SWITCH SECTION
    im.hSelect.classList.add("d-none");
    im.hTable.classList.remove("d-none");
    im.hList.replaceChildren();
    im.hGo.disabled = true;
    im.hErrors.classList.add("d-none");
    im.rows = [];
    im.queue = [];

    let clen = im.cols.length, reader = new FileReader();

    // (C3) READ SELECTED CSV & GENERATE HTML TABLE
    reader.addEventListener("loadend", () => {
      if (reader.error) {
        cb.modal("Error opening CSV file", reader.error.message);
        return;
      }
      try {
        // (C3-1) READ ROWS + CHECK VALID
        let rows = CSV.parse(reader.result);
        if (rows.length > 500) {
          cb.modal("Too many rows", "Import at most 500 rows at a time.");
          im.hSelect.classList.remove("d-none");
          im.hTable.classList.add("d-none");
          return;
        }
        if (rows.length == 0) {
          cb.modal("Empty CSV file", "The selected file does not contain any rows.");
          return;
        }

        let prepared = rows.map((r, index) => {
          let err = r.length===clen ? im.validate(r) : `Invalid row: expected ${clen} columns, found ${r.length}.`;
          let data = {};
          if (r.length===clen) {
            for (let i=0; i<clen; i++) {
              data[im.cols[i][1]] = r[i] == null ? "" : String(r[i]).trim();
            }
          }
          return { index : index + 1, data : data, values : r, error : err };
        });
        if (im.previewApi) {
          let valid = prepared.filter(row => row.error==null);
          if (valid.length===0) {
            im.drawRows(prepared);
            im.hErrors.classList.remove("d-none");
            return;
          }
          cb.api({
            mod : im.previewApi.mod,
            act : im.previewApi.act,
            data : {
              rows : JSON.stringify(valid.map(row => Object.assign({}, row.data, im.data || {})))
            },
            loading : true,
            passmsg : false,
            nofail : true,
            onpass : res => {
              if (!Array.isArray(res.data) || res.data.length!==valid.length) {
                cb.modal("Preview failed", "The server returned an invalid preview. No rows were imported.");
                return;
              }
              valid.forEach((row, i) => {
                row.status = res.data[i].status;
                row.message = res.data[i].message;
              });
              im.drawRows(prepared);
            },
            onfail : msg => cb.modal("Preview failed", `${msg}. No rows were imported.`)
          });
        } else {
          im.drawRows(prepared);
        }
      } catch (err) {
        cb.modal("Error opening CSV file", err.message);
        console.error(err);
      }
    });

    // (C4) GO!
    reader.readAsText(im.hPick.files[0]);
  },

  // (D) START IMPORT
  go : first => {
    // (D1) BLOCK SCREEN & DISABLE BUTTONS
    if (first) {
      im.hBack.disabled = true;
      im.hGo.disabled = true;
      cb.loading(true);
      im.queue = im.rows.filter(row => row.status==="new" || row.status==="changed" || row.status==="ready");
    }

    // (D2) IMPORT ENTRY
    let next = im.queue.shift();
    if (next!=null) {
      let data = Object.assign({}, next.data, im.data || {});
      cb.api({
        mod : im.api.mod, act : im.api.act,
        passmsg : false, loading : false, nofail : true,
        data : data,
        onpass : () => {
          next.status = "imported";
          next.message = "Saved.";
          im.drawRows(im.rows);
          im.go();
        },
        onfail : msg => {
          next.status = "error";
          next.message = msg;
          im.drawRows(im.rows);
          im.go();
        }
      });
    }

    // (D3) ALL DONE
    else {
      im.hBack.disabled = false;
      cb.loading(false);
      if (im.after) { im.after(); }
      let errors = im.rows.filter(row =>
        row.status==="error" || row.status==="conflicting" || row.status==="duplicate" || row.error
      );
      if (errors.length>0) { im.hErrors.classList.remove("d-none"); }
      cb.modal("Import complete", `${im.rows.filter(row => row.status==="imported").length} row(s) saved; ${errors.length} row(s) need attention.`);
    }
  },

  drawRows : rows => {
    im.rows = rows;
    im.hList.replaceChildren();
    for (let entry of rows) {
      let tr = document.createElement("tr");
      let values = Array.isArray(entry.values) ? entry.values : im.cols.map(col => entry.data[col[1]] ?? "");
      for (let i=0; i<im.cols.length; i++) {
        let td = document.createElement("td");
        td.textContent = values[i] ?? "";
        tr.appendChild(td);
      }
      let status = entry.error ? "conflicting" : (entry.status || "ready");
      let message = entry.error || entry.message || "Ready to import.";
      let td = document.createElement("td");
      td.textContent = `${status}: ${message}`;
      tr.appendChild(td);
      tr.className = ["conflicting", "error"].includes(status)
        ? "table-danger" : (["duplicate", "unchanged"].includes(status)
          ? "table-warning" : (status==="imported" ? "table-success" : ""));
      im.hList.appendChild(tr);
    }
    im.hGo.disabled = !rows.some(row => row.status==="new" || row.status==="changed" || row.status==="ready");
    im.hErrors.classList.toggle("d-none", !rows.some(row =>
      row.error || ["error", "conflicting", "duplicate"].includes(row.status)
    ));
  },

  downloadErrors : () => {
    let rows = im.rows.filter(row => row.error || row.status==="error" || row.status==="conflicting" || row.status==="duplicate");
    if (!rows.length) {
      cb.modal("No row errors", "There are no conflicting or failed rows to download.");
      return;
    }
    let escape = value => `"${String(value ?? "").replace(/"/g, '""')}"`;
    let csv = [
      ["Row", ...im.cols.map(col => col[0]), "Raw row", "Status", "Details"].map(escape).join(","),
      ...rows.map(row => {
        let values = Array.isArray(row.values) ? row.values : im.cols.map(col => row.data[col[1]] ?? "");
        return [
          row.index,
          ...im.cols.map((_, i) => values[i] ?? ""),
          Array.isArray(row.values) ? row.values.join(" | ") : "",
          row.error ? "conflicting" : row.status,
          row.error || row.message || ""
        ].map(escape).join(",");
      })
    ].join("\r\n");
    let link = document.createElement("a");
    link.href = URL.createObjectURL(new Blob([csv], { type : "text/csv;charset=utf-8" }));
    link.download = "import-row-errors.csv";
    link.click();
    URL.revokeObjectURL(link.href);
  }
};

im.validate = row => {
  if (row.length != im.cols.length) { return "Invalid column count"; }
  for (let i=0; i<im.cols.length; i++) {
    let value = row[i] == null ? "" : String(row[i]).trim();
    if (im.cols[i][2] === true && value === "") {
      return `${im.cols[i][0]} - Cannot be empty`;
    }
    if (im.cols[i][2] === "E" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      return `${im.cols[i][0]} - Invalid email`;
    }
    if (im.cols[i][2] === "N" && (value === "" || !Number.isFinite(Number(value)))) {
      return `${im.cols[i][0]} - Invalid number`;
    }
  }
  return null;
};

if (typeof module !== "undefined") { module.exports = im; }