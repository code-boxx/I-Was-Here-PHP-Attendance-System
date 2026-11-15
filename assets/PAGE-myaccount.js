function save () {
  // (A) GET DATA
  var data = {
    name : document.getElementById("user-name").value,
    cpass : document.getElementById("user-cpass").value,
    pass : document.getElementById("user-npass").value
  };

  // (B) PASSWORD CHECK
  if (data.pass != document.getElementById("user-ncpass").value) {
    cb.modal("Please Check", "Passwords do not match.");
    return false;
  }

  var sessionManager = {
    load : () => cb.api({
      mod : "session", act : "getActiveSessions", passmsg : false,
      onpass : result => {
        let target = document.getElementById("active-sessions");
        if (!target) { return; }
        target.replaceChildren();
        (result.data || []).forEach(session => {
          let row = document.createElement("div");
          row.className = "d-flex justify-content-between align-items-center border p-2";
          let label = document.createElement("span");
          label.textContent = `${session.current ? "This device" : "Other device"} — last active ${session.last_seen_at}`;
          let button = document.createElement("button");
          button.type = "button";
          button.className = "btn btn-sm btn-outline-danger";
          button.textContent = "Revoke";
          button.addEventListener("click", () => sessionManager.revoke(session.session_id));
          row.append(label, button);
          target.append(row);
        });
        if (!target.children.length) { target.textContent = "No active sessions found."; }
      }
    }),
    revoke : sessionId => cb.modal(
      "Revoke session",
      "That session will be signed out on its next request.",
      () => cb.api({
        mod : "session", act : "revokeSession",
        data : { sessionId : sessionId },
        passmsg : "Session revoked",
        onpass : sessionManager.load
      })
    )
  };

  window.addEventListener("load", sessionManager.load);

  var notificationPreferences = {
    save : () => {
      cb.api({
        mod : "notifications", act : "savePreferences",
        data : {
          attendanceWarning : document.getElementById("notify-attendance-warning").checked ? "1" : "0",
          correctionDecision : document.getElementById("notify-correction-decision").checked ? "1" : "0",
          classReminder : document.getElementById("notify-class-reminder").checked ? "1" : "0"
        },
        passmsg : "Notification preferences saved"
      });
      return false;
    }
  };

  window.mfa = {
    start : () => cb.api({
      mod : "session", act : "mfaStart", passmsg : false,
      onpass : result => {
        let code = window.prompt("Scan this URI in your authenticator app, then enter the 6-digit code:\n" + result.data.uri);
        if (code) {
          cb.api({
            mod : "session", act : "mfaEnable", data : { code : code },
            passmsg : false,
            onpass : response => {
              let output = document.getElementById("mfa-result");
              output.classList.remove("d-none");
              output.textContent = "Save these recovery codes securely:\n" + response.data.recovery_codes.join("\n");
            }
          });
        }
      }
    }),
    disable : () => {
      let code = window.prompt("Enter your current MFA code");
      if (code) { cb.api({ mod : "session", act : "mfaDisable", data : { code : code }, passmsg : "MFA disabled" }); }
    }
  };

  // (C) PASSWORD STRENGTH
  if (!cb.checker(data.pass)) {
    cb.modal("Please Check", "Password must be at least 8 characters, alphanumeric.");
    return false;
  }
// @T
  // (D) API CALL
  cb.api({
    mod : "session", act : "update",
    data : data,
    passmsg : "Account Updated",
    onpass : () => {
      document.getElementById("user-cpass").value = "";
      document.getElementById("user-npass").value = "";
      document.getElementById("user-ncpass").value = "";
    }
  });
  return false;
}