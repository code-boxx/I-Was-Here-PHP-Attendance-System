var nin = {
  // (A) INITIALIZE - CHECK NFC
  hStatus : null, // html hfc login button text
  init : () => { if ("NDEFReader" in window) {
    nin.hStatus = document.getElementById("nfc-b");
    document.getElementById("nfc-a").disabled = false;
  }},

  // (B) NFC LOGIN
  go : () => {
    // (B1) ON NFC READ
    nfc.onread = evt => {
      // (B1-1) GET TOKEN
      nfc.standby();
      const decoder = new TextDecoder();
      let token = "";
      for (let record of evt.message.records) {
        token = decoder.decode(record.data);
      }

      // (B1-2) API LOGIN
      cb.api({
        mod : "nfcin", act : "login",
        data : { token : token },
        passmsg : false,
        onpass : () => location.href = cbhost.base,
        onfail : () => nin.go()
      });
    };

    // (B2) ON NFC ERROR
    nfc.onerror = err => {
      nfc.stop();
      console.error(err);
      cb.modal("ERROR", err.msg);
      nin.hStatus.innerHTML = "ERROR!";
    };

    // (B3) START SCAN
    nin.hStatus.innerHTML = "Scanning - Tap Token";
    nfc.scan();
  }
};
window.addEventListener("load", nin.init);