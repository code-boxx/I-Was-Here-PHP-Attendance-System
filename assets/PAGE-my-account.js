function save () {
  // (A) GET PASSWORD FIELDS
  let pass = document.getElementById("pass"),
      passc = document.getElementById("passc");

  // (B) CHECK PASSWORDS
  if (pass.value != passc.value) {
    cb.modal("Error", "Passwords do not match.");
    return false;
  }

  // (C) PASSWORD STRENGTH
  if (!cb.checker(pass.value)) {
    cb.modal("Error", "Password must be at least 8 characters alphanumeric");
    return false;
  }

  // (D) API CALL
  else {
    cb.api({
      mod : "session", req : "saveL",
      data : { password : pass.value },
      onpass : () => {
        pass.value = "";
        passc.value = "";
      }
    });
  }
  return false;
}
