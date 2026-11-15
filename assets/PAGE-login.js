function login () {
  cb.api({
    mod : "session", act : "login",
    data : {
      email : document.getElementById("login-email").value,
      password : document.getElementById("login-pass").value
    },
    passmsg : false,
    onpass : result => {
      if (result.data && result.data.mfa_required) {
        let code = window.prompt("Enter your administrator MFA code");
        if (code) {
          cb.api({
            mod : "session", act : "mfaVerify", data : { code : code },
            passmsg : false, onpass : () => location.href = cbhost.base
          });
        }
      } else {
        location.href = cbhost.base;
      }
    }
  });
  return false;
}