function save () {
  // (A) GET DATA
  var data = {
    name : document.getElementById("user-name").value,
    email : document.getElementById("user-email").value,
    password : document.getElementById("user-pass").value,
    cpassword : document.getElementById("user-cpass").value
  };

  // (B) PASSWORD CHECK
  if (data.password != data.cpassword) {
    cb.modal("Please Check", "Passwords do not match.");
    return false;
  }

  // (C) PASSWORD STRENGTH
  if (!cb.checker(data.password)) {
    cb.modal("Please Check", "Password must be at least 8 characters, alphanumeric.");
    return false;
  }

  // (D) API CALL
  cb.api({
    mod : "session", req : "update",
    data : data,
    passmsg : "Account Updated",
    onpass : () => {
      document.getElementById("user-pass").value = "";
      document.getElementById("user-cpass").value = "";
    }
  });
  return false;
}