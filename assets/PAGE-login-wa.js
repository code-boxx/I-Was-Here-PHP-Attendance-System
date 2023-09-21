var wa = {
  // (A) INIT
  init : () => { if ("credentials" in navigator) {
    document.getElementById("wa-in").disabled = false;
  }},

  // (B) WEBAUTH LOGIN PART A
  go : () => {
    const email = document.getElementById("login-email");
    if (email.validity.valid) {
      cb.api({
        mod : "wain", act : "loginA",
        data : { email: email.value },
        passmsg : false,
        onpass : async res => {
          let pk = JSON.parse(res.data);
          helper.bta(pk);
          console.log(pk);
          wa.login(await navigator.credentials.get(pk));
        }
      });
    } else {
      cb.modal("ERROR", "Please enter a valid email address.")
    }
  },

  // (C) WEBAUTH LOGIN PART B
  login : cred => {
    const email = document.getElementById("login-email");
    cb.api({
      mod : "wain", act : "loginB",
      data : {
        email: email.value,
        id : cred.rawId ? helper.atb(cred.rawId) : null,
        client : cred.response.clientDataJSON  ? helper.atb(cred.response.clientDataJSON) : null,
        auth : cred.response.authenticatorData ? helper.atb(cred.response.authenticatorData) : null,
        sig : cred.response.signature ? helper.atb(cred.response.signature) : null,
        user : cred.response.userHandle ? helper.atb(cred.response.userHandle) : null
      },
      passmsg : false,
      onpass : res => location.href = cbhost.base
    });
  }
};
window.addEventListener("load", wa.init);