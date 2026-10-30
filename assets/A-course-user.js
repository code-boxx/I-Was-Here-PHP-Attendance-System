var cuser = {
  // (A) SHOW COURSE USERS PAGE
  pg : 1, // current page
  code : null, // current course code
  find : "",
  role : "all",
  sort : "name",
  askReason : () => {
    let reason = window.prompt("Enter the reason for this roster change (required):");
    if (reason===null) { return null; }
    reason = reason.trim();
    if (!reason) {
      cb.modal("Reason required", "Enter a reason to record this roster change.");
      return null;
    }
    if (reason.length>500) {
      cb.modal("Reason too long", "Use a reason of 500 characters or fewer.");
      return null;
    }
    return reason;
  },
  show : code => {
    cuser.code = code;
    cuser.pg = 1;
    cuser.find = "";
    cuser.role = "all";
    cuser.sort = "name";
    cb.page(2);
    cb.load({
      page : "A/course/user",
      target : "cb-page-2",
      data : { code : code },
      onload : () => {
        autocomplete.attach({
          target : document.getElementById("course-user-add"),
          mod : "autocomplete", act : "userEmail",
          onpick : res => cuser.add()
        });
        cuser.list();
        cuser.audit();
      }
    });
  },

  // (B) SHOW ALL USERS IN COURSE
  list : () => cb.load({
    page : "A/course/user/list",
    target : "course-user-list",
    data : {
      page : cuser.pg,
      code : cuser.code,
      search : cuser.find,
      role : cuser.role,
      sort : cuser.sort
    }
  }),

  audit : () => cb.load({
    page : "A/course/user/audit",
    target : "course-user-audit",
    data : { code : cuser.code }
  }),

  // (C) GO TO PAGE
  //  pg : int, page number
  goToPage : pg => { if (pg!=cuser.pg) {
    cuser.pg = pg;
    cuser.list();
  }},

  // (D) ADD USER TO COURSE
  add : () => {
    // (D1) ADD EMAIL FIELD
    var field = document.getElementById("course-user-add");
    var reason = cuser.askReason();
    if (reason===null) { return false; }

    // (D2) AJAX
    cb.api({
      mod : "courses", act : "addUser",
      data : {
        code : cuser.code,
        uid : field.value,
        reason : reason
      },
      passmsg : "User Added",
      onpass : () => {
        field.value = "";
        cuser.list();
        cuser.audit();
      }
    });
    return false;
  },

  addMany : () => {
    let users = document.getElementById("course-users-bulk").value;
    let reason = cuser.askReason();
    if (reason===null) { return false; }
    cb.api({
      mod : "courses", act : "addUsers",
      data : { code : cuser.code, users : users, reason : reason },
      passmsg : "Bulk enrollment complete",
      onpass : () => {
        document.getElementById("course-users-bulk").value = "";
        cuser.list();
        cuser.audit();
      }
    });
    return false;
  },

  search : () => {
    cuser.find = document.getElementById("course-user-search").value.trim();
    cuser.role = document.getElementById("course-user-role").value;
    cuser.sort = document.getElementById("course-user-sort").value;
    cuser.pg = 1;
    cuser.list();
    return false;
  },

  filter : () => {
    cuser.find = document.getElementById("course-user-search").value.trim();
    cuser.role = document.getElementById("course-user-role").value;
    cuser.sort = document.getElementById("course-user-sort").value;
    cuser.pg = 1;
    cuser.list();
  },

  delSelected : () => {
    let ids = Array.from(document.querySelectorAll(".course-user-select:checked"))
      .map(input => input.value);
    if (ids.length===0) {
      cb.modal("No users selected", "Select at least one user to remove.");
      return;
    }
    let reason = cuser.askReason();
    if (reason===null) { return; }
    cb.modal("Please confirm", `Remove ${ids.length} selected users from this course? Past attendance records will be kept.`, () => cb.api({
      mod : "courses", act : "delUsers",
      data : { code : cuser.code, uids : JSON.stringify(ids), reason : reason },
      passmsg : "Selected users removed",
      onpass : () => { cuser.list(); cuser.audit(); }
    }));
  },

  moveSelected : () => {
    let ids = Array.from(document.querySelectorAll(".course-user-select:checked"))
      .map(input => input.value);
    let target = document.getElementById("course-user-move-to").value;
    if (ids.length===0) {
      cb.modal("No students selected", "Select one or more students to move.");
      return;
    }
    if (!target) {
      cb.modal("No destination selected", "Choose a destination course.");
      return;
    }
    let reason = cuser.askReason();
    if (reason===null) { return; }
    cb.modal(
      "Please confirm",
      `Move ${ids.length} selected students to the chosen course? Their existing attendance records will be retained.`,
      () => cb.api({
        mod : "courses", act : "moveStudents",
        data : {
          from : cuser.code,
          to : target,
          uids : JSON.stringify(ids),
          reason : reason
        },
        passmsg : "Selected students moved",
        onpass : () => {
          cuser.pg = 1;
          cuser.list();
          cuser.audit();
        }
      })
    );
  },

  // (E) REMOVE USER FROM COURSE
  //  id : user id
  del : id => {
    let reason = cuser.askReason();
    if (reason===null) { return; }
    cb.modal("Please confirm", "User will be removed from the course, but past attendance will be kept.", () => cb.api({
      mod : "courses", act : "delUser",
      data : { code : cuser.code, uid : id, reason : reason },
      passmsg : "User removed from course",
      onpass : () => { cuser.list(); cuser.audit(); }
    }));
  },

  undo : eventId => {
    let reason = cuser.askReason();
    if (reason===null) { return; }
    cb.modal(
      "Undo roster operation",
      "Undo all enrollment changes in this operation? The change is only reversed if no later roster edits conflict.",
      () => cb.api({
        mod : "courses", act : "undoRosterChange",
        data : { eventId : eventId, reason : reason },
        passmsg : "Roster operation undone",
        onpass : () => { cuser.pg = 1; cuser.list(); cuser.audit(); }
      })
    );
  },

  // (F) IMPORT USERS TO COURSE
  import : () => {
    let reason = cuser.askReason();
    if (reason===null) { return; }
    im.init({
      name : "USERS TO COURSE",
      at : 3, back : 2,
      eg : "dummy-course-users.csv",
      api : { mod : "courses", act : "addUser" },
      preview : { mod : "courses", act : "previewUserImport" },
      after : () => { cuser.list(); cuser.audit(); },
      data : { code : cuser.code, reason : reason },
      cols : [
        ["User's Email", "uid", "E"]
      ]
    });
  }
};