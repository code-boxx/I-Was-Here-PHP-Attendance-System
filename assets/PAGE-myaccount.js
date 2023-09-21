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